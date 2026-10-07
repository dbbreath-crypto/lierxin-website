<?php
/**
 * 留言提交接口
 * 仅接受 POST(form-data)，返回 JSON：{ ok: bool, message: string }
 */
require __DIR__ . '/../inc/bootstrap.php';

session_start();
header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    json_out(['ok' => false, 'message' => '请求方式不正确'], 405);
}

/**
 * 统一失败返回
 */
function fail(string $message, int $status = 400): void
{
    json_out(['ok' => false, 'message' => $message], $status);
}

// ---------- 取值 ----------
$name    = trim((string) ($_POST['name'] ?? ''));
$phone   = trim((string) ($_POST['phone'] ?? ''));
$email   = trim((string) ($_POST['email'] ?? ''));
$company = trim((string) ($_POST['company'] ?? ''));
$product = trim((string) ($_POST['product'] ?? ''));
$content = trim((string) ($_POST['content'] ?? ''));
$captcha = strtoupper(trim((string) ($_POST['captcha'] ?? '')));

// ---------- 验证码 ----------
$sessionCode = strtoupper((string) ($_SESSION['captcha'] ?? ''));
if ($sessionCode === '' || $captcha === '') {
    fail('请输入验证码');
}
// 验证码 10 分钟有效
if (time() - (int) ($_SESSION['captcha_time'] ?? 0) > 600) {
    unset($_SESSION['captcha'], $_SESSION['captcha_time']);
    fail('验证码已过期，请点击图片刷新后重试');
}
if (!hash_equals($sessionCode, $captcha)) {
    // 无论对错都作废，防止暴力尝试
    unset($_SESSION['captcha'], $_SESSION['captcha_time']);
    fail('验证码不正确');
}
unset($_SESSION['captcha'], $_SESSION['captcha_time']);

// ---------- 字段校验 ----------
$nameLen = mb_strlen($name, 'UTF-8');
if ($nameLen < 2 || $nameLen > 50) {
    fail('请填写真实姓名（2-50 个字）');
}

// 手机号 / 座机 / 带区号的多种写法
if (!preg_match('/^[0-9+\-\s()（）]{6,30}$/', $phone)) {
    fail('请填写有效的联系电话');
}

if ($email !== '') {
    if (mb_strlen($email, 'UTF-8') > 120 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        fail('邮箱格式不正确');
    }
}

if (mb_strlen($company, 'UTF-8') > 120) {
    fail('公司名称过长');
}

$allowedProducts = [
    'HDI线路板',
    '多层PCB板',
    'PCBA贴装组装',
    '软硬结合板',
    '高频高速板',
    '其他ODM/OEM定制',
    '',
];
if (!in_array($product, $allowedProducts, true)) {
    fail('咨询产品不在可选范围内');
}

$contentLen = mb_strlen($content, 'UTF-8');
if ($contentLen < 5) {
    fail('留言内容请至少填写 5 个字，方便我们准确回复');
}
if ($contentLen > 2000) {
    fail('留言内容请控制在 2000 字以内');
}

// ---------- 防灌水 ----------
$ip = client_ip();
try {
    $pdo = db();

    // 同 IP 最小间隔
    $stmt = $pdo->prepare('SELECT MAX(created_at) AS last_at FROM messages WHERE ip = ?');
    $stmt->execute([$ip]);
    $row = $stmt->fetch();
    if (!empty($row['last_at'])) {
        $lastTs = strtotime($row['last_at']);
        $wait = SUBMIT_INTERVAL - (time() - $lastTs);
        if ($wait > 0) {
            fail("提交过于频繁，请 {$wait} 秒后再试", 429);
        }
    }

    // 同 IP 24 小时上限
    $stmt = $pdo->prepare('SELECT COUNT(*) AS cnt FROM messages WHERE ip = ? AND created_at >= (NOW() - INTERVAL 1 DAY)');
    $stmt->execute([$ip]);
    $row = $stmt->fetch();
    if ((int) ($row['cnt'] ?? 0) >= SUBMIT_DAILY_LIMIT) {
        fail('今日留言次数已达上限，欢迎明日再提交或直接电话联系我们', 429);
    }

    // ---------- 入库 ----------
    $insert = $pdo->prepare(
        'INSERT INTO messages (name, phone, email, company, product, content, ip, user_agent)
         VALUES (:name, :phone, :email, :company, :product, :content, :ip, :ua)'
    );
    $insert->execute([
        ':name'    => $name,
        ':phone'   => $phone,
        ':email'   => $email,
        ':company' => $company,
        ':product' => $product,
        ':content' => $content,
        ':ip'      => $ip,
        ':ua'      => mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255, 'UTF-8'),
    ]);

    json_out(['ok' => true, 'message' => '感谢您的留言，我们会在 24 小时内与您联系']);
} catch (Throwable $ex) {
    error_log('[lex message] ' . $ex->getMessage());
    json_out(['ok' => false, 'message' => '服务器繁忙，请稍后重试'], 500);
}
