<?php
/**
 * 后台登录
 */
require __DIR__ . '/../inc/bootstrap.php';

session_start();

if (is_admin()) {
    header('Location: index.php');
    exit;
}

$error = '';

// 登录失败锁定（按 session 计数）
$lockedUntil = (int) ($_SESSION['login_lock_until'] ?? 0);
$isLocked = $lockedUntil > time();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isLocked) {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    try {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT id, username, password_hash FROM admins WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            // 登录成功
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int) $admin['id'];
            $_SESSION['admin_user'] = $admin['username'];
            unset($_SESSION['login_fail'], $_SESSION['login_lock_until']);

            try {
                $upd = $pdo->prepare('UPDATE admins SET last_login_at = NOW(), last_login_ip = ? WHERE id = ?');
                $upd->execute([client_ip(), $admin['id']]);
            } catch (Throwable $ex) {
                // 记录失败不影响登录
            }

            header('Location: index.php');
            exit;
        }

        $_SESSION['login_fail'] = (int) ($_SESSION['login_fail'] ?? 0) + 1;
        if ($_SESSION['login_fail'] >= LOGIN_MAX_FAIL) {
            $_SESSION['login_lock_until'] = time() + LOGIN_LOCK_SECONDS;
            $error = '错误次数过多，请 ' . (int) (LOGIN_LOCK_SECONDS / 60) . ' 分钟后再试';
        } else {
            $error = '账号或密码不正确，还可尝试 ' . (LOGIN_MAX_FAIL - $_SESSION['login_fail']) . ' 次';
        }
    } catch (Throwable $ex) {
        error_log('[lex login] ' . $ex->getMessage());
        $error = '系统暂时不可用，请稍后重试';
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $isLocked) {
    $error = '错误次数过多，请稍后再试';
}

$pageTitle = '登录';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>登录 — 利尔鑫管理后台</title>
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>
<div class="login-page">
    <div class="login-card">
        <div class="login-brand">
            <div class="mark">LX</div>
            <h2>利尔鑫管理后台</h2>
            <p>LIERXIN ADMIN CONSOLE</p>
        </div>

        <?php if ($error !== ''): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" autocomplete="off">
            <div class="field">
                <label for="username">管理员账号</label>
                <input class="input" type="text" id="username" name="username"
                       value="<?= e((string) ($_POST['username'] ?? '')) ?>" required autofocus>
            </div>
            <div class="field">
                <label for="password">密码</label>
                <input class="input" type="password" id="password" name="password" required>
            </div>
            <button class="btn btn-primary" style="width:100%" type="submit" <?= $isLocked ? 'disabled' : '' ?>>
                登录后台
            </button>
        </form>

        <div class="login-foot">仅限授权人员访问 · 操作将被记录</div>
    </div>
</div>
</body>
</html>
