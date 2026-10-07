<?php
/**
 * 修改当前管理员密码
 */
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/admin_layout.php';

session_start();
require_admin();

$pdo = db();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $error = '页面已过期，请刷新后重试';
    } else {
        $old = (string) ($_POST['old_password'] ?? '');
        $new = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');

        $st = $pdo->prepare('SELECT id, password_hash FROM admins WHERE id = ?');
        $st->execute([$_SESSION['admin_id']]);
        $admin = $st->fetch();

        if (!$admin || !password_verify($old, $admin['password_hash'])) {
            $error = '当前密码不正确';
        } elseif (mb_strlen($new, 'UTF-8') < 8) {
            $error = '新密码至少需要 8 位';
        } elseif ($new !== $confirm) {
            $error = '两次输入的新密码不一致';
        } elseif ($new === $old) {
            $error = '新密码不能与当前密码相同';
        } else {
            $hash = password_hash($new, PASSWORD_BCRYPT);
            $upd = $pdo->prepare('UPDATE admins SET password_hash = ? WHERE id = ?');
            $upd->execute([$hash, $_SESSION['admin_id']]);
            $success = '密码修改成功，请使用新密码登录';
        }
    }
}

admin_header('修改密码', 'change_password.php');
?>
<?php if ($error): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>

<div class="card" style="max-width:460px">
    <form method="post" autocomplete="off">
        <?= csrf_field() ?>
        <div class="field">
            <label for="old_password">当前密码</label>
            <input class="input" type="password" id="old_password" name="old_password" required autofocus>
        </div>
        <div class="field">
            <label for="new_password">新密码（至少 8 位）</label>
            <input class="input" type="password" id="new_password" name="new_password" required>
        </div>
        <div class="field">
            <label for="confirm_password">确认新密码</label>
            <input class="input" type="password" id="confirm_password" name="confirm_password" required>
        </div>
        <button class="btn btn-primary" type="submit">保存修改</button>
    </form>

    <p style="margin-top:18px;font-size:12px;color:var(--a-muted);line-height:1.7">
        建议定期更换密码，避免使用与其他网站相同的密码。<br>
        密码以加密方式存储，系统无法找回，请妥善保管。
    </p>
</div>
<?php
admin_footer();
