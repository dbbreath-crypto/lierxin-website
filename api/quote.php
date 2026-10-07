<?php
/**
 * 在线报价询价提交接口
 * 仅接受 POST(form-data)，返回 JSON：{ ok: bool, message: string }
 *
 * 字段：
 *   name / phone / email / company / remark / captcha
 *   params  （JSON 字符串，报价参数，前端计算后一并提交）
 *   estimate_total / estimate_unit / lead_time
 */
require __DIR__ . '/../inc/bootstrap.php';

session_start();
header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    json_out(['ok' => false, 'message' => '请求方式不正确'], 405);
}

function fail(string $message, int $status = 400): void
{
    json_out(['ok' => false, 'message' => $message], $status);
}

// ---------- 取值 ----------
$name    = trim((string) ($_POST['name'] ?? ''));
$phone   = trim((string) ($_POST['phone'] ?? ''));
$email   = trim((string) ($_POST['email'] ?? ''));
$company = trim((string) ($_POST['company'] ?? ''));
$remark  = trim((string) ($_POST['remark'] ?? ''));
$captcha = strtoupper(trim((string) ($_POST['captcha'] ?? '')));
$params  = trim((string) ($_POST['params'] ?? ''));
$total   = (float) ($_POST['estimate_total'] ?? 0);
$unit    = (float) ($_POST['estimate_unit'] ?? 0);
$lead    = trim((string) ($_POST['lead_time'] ?? ''));

// ---------- 验证码 ----------
$sessionCode = strtoupper((string) ($_SESSION['captcha'] ?? ''));
if ($sessionCode === '' || $captcha === '') {
    fail('请输入验证码');
}
if (time() - (int) ($_SESSION['captcha_time'] ?? 0) > 600) {
    unset($_SESSION['captcha'], $_SESSION['captcha_time']);
    fail('验证码已过期，请点击图片刷新后重试');
}
if (!hash_equals($sessionCode, $captcha)) {
    unset($_SESSION['captcha'], $_SESSION['captcha_time']);
    fail('验证码不正确');
}
unset($_SESSION['captcha'], $_SESSION['captcha_time']);

// ---------- 字段校验 ----------
$nameLen = mb_strlen($name, 'UTF-8');
if ($nameLen < 2 || $nameLen > 50) {
    fail('请填写真实姓名（2-50 个字）');
}
if (!preg_match('/^[0-9+\-\s()（）]{6,30}$/', $phone)) {
    fail('请填写有效的联系电话');
}
if ($email !== '' && (mb_strlen($email, 'UTF-8') > 120 || !filter_var($email, FILTER_VALIDATE_EMAIL))) {
    fail('邮箱格式不正确');
}
if (mb_strlen($company, 'UTF-8') > 120) {
    fail('公司名称过长');
}
if (mb_strlen($remark, 'UTF-8') > 1000) {
    fail('备注请控制在 1000 字以内');
}

// params 必须是合法 JSON，且体积有限
$paramsJson = '{}';
if ($params !== '') {
    $decoded = json_decode($params, true);
    if (!is_array($decoded)) {
        fail('报价参数格式不正确');
    }
    if (mb_strlen($params, 'UTF-8') > 8000) {
        fail('报价参数过长');
    }
    // 只保留标量，避免把任意结构写进库
    $clean = [];
    foreach ($decoded as $k => $v) {
        if (is_scalar($v)) {
            $clean[(string) $k] = (string) $v;
        } elseif (is_array($v)) {
            $clean[(string) $k] = implode('、', array_map('strval', $v));
        }
    }
    $paramsJson = json_encode($clean, JSON_UNESCAPED_UNICODE);
}

if ($total < 0 || $total > 99999999) {
    fail('估算金额异常，请重新计算后再提交');
}
$lead = mb_substr($lead, 0, 60, 'UTF-8');

// ---------- 防灌水 ----------
$ip = client_ip();
try {
    $pdo = db();

    $stmt = $pdo->prepare('SELECT MAX(created_at) AS last_at FROM quotes WHERE ip = ?');
    $stmt->execute([$ip]);
    $row = $stmt->fetch();
    if (!empty($row['last_at'])) {
        $wait = SUBMIT_INTERVAL - (time() - strtotime($row['last_at']));
        if ($wait > 0) {
            fail("提交过于频繁，请 {$wait} 秒后再试", 429);
        }
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) AS cnt FROM quotes WHERE ip = ? AND created_at >= (NOW() - INTERVAL 1 DAY)');
    $stmt->execute([$ip]);
    $row = $stmt->fetch();
    if ((int) ($row['cnt'] ?? 0) >= SUBMIT_DAILY_LIMIT) {
        fail('今日询价次数已达上限，欢迎明日再提交或直接电话联系我们', 429);
    }

    $insert = $pdo->prepare(
        'INSERT INTO quotes
           (name, phone, email, company, params, estimate_total, estimate_unit, lead_time, remark, ip, user_agent)
         VALUES
           (:name, :phone, :email, :company, :params, :total, :unit, :lead, :remark, :ip, :ua)'
    );
    $insert->execute([
        ':name'    => $name,
        ':phone'   => $phone,
        ':email'   => $email,
        ':company' => $company,
        ':params'  => $paramsJson,
        ':total'   => $total,
        ':unit'    => $unit,
        ':lead'    => $lead,
        ':remark'  => $remark,
        ':ip'      => $ip,
        ':ua'      => mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255, 'UTF-8'),
    ]);

    json_out([
        'ok'      => true,
        'message' => '询价已提交，我们的工程团队会在 2 小时内与您确认最终报价',
        'id'      => (int) $pdo->lastInsertId(),
    ]);
} catch (Throwable $ex) {
    error_log('[lex quote] ' . $ex->getMessage());
    json_out(['ok' => false, 'message' => '服务器繁忙，请稍后重试'], 500);
}
