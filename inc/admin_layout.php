<?php
/**
 * 后台页面布局（顶部导航 + 页脚）
 * 后续新增模块时，只需往 $nav 里加一项即可
 */
if (!defined('LEX_APP')) {
    http_response_code(403);
    exit('Forbidden');
}

if (!function_exists('db')) {
    require __DIR__ . '/bootstrap.php';
}

/**
 * 后台导航配置
 * key = 页面文件名，用于高亮；soon = true 的项为预留模块
 */
function admin_nav(): array
{
    return [
        'index.php' => ['label' => '概览', 'icon' => '◲'],
        'messages.php' => ['label' => '留言管理', 'icon' => '✉'],
        'quotes.php' => ['label' => '在线报价', 'icon' => '¥'],
        'quote_settings.php' => ['label' => '报价参数', 'icon' => '⚙'],
        'products.php' => ['label' => '产品中心', 'icon' => '▤'],
        'news.php' => ['label' => '资讯中心', 'icon' => '✎'],
        'solutions.php' => ['label' => '行业方案', 'icon' => '◈'],
        'change_password.php' => ['label' => '修改密码', 'icon' => '⚿'],
    ];
}

function admin_header(string $title, string $active = ''): void
{
    $nav = admin_nav();
    $adminName = $_SESSION['admin_user'] ?? 'admin';

    // 未读数（用于导航角标）
    $unread = 0;
    try {
        $unread = (int) db()->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn();
    } catch (Throwable $ex) {
        // 建表前或数据库异常时忽略
    }
    ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> — 利尔鑫管理后台</title>
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>
<div class="admin-wrap">
    <aside class="admin-side">
        <div class="admin-brand">
            <div class="admin-brand-mark">LX</div>
            <div class="admin-brand-text">
                <strong>利尔鑫</strong>
                <span>管理后台</span>
            </div>
        </div>

        <nav class="admin-nav">
            <?php foreach ($nav as $file => $item): ?>
                <a class="admin-nav-item<?= $active === $file ? ' is-active' : '' ?>" href="<?= e($file) ?>">
                    <span class="admin-nav-icon"><?= $item['icon'] ?></span>
                    <span><?= e($item['label']) ?></span>
                    <?php if ($file === 'messages.php' && $unread > 0): ?>
                        <span class="admin-badge"><?= $unread ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="admin-side-foot">
            <div class="admin-user">
                <span class="admin-user-dot"></span>
                <?= e($adminName) ?>
            </div>
            <a class="admin-logout" href="logout.php">退出登录</a>
            <div class="admin-side-note">资讯、留言在此维护<br>产品模块可按同样方式扩展</div>
        </div>
    </aside>

    <main class="admin-main">
        <header class="admin-top">
            <h1><?= e($title) ?></h1>
            <a class="admin-top-link" href="<?= e(front_url('#/')) ?>" target="_blank" rel="noopener">查看官网前台 →</a>
        </header>
        <div class="admin-body">
<?php
}

function admin_footer(): void
{
    ?>
        </div>
        <footer class="admin-foot">利尔鑫管理后台 &copy; <?= date('Y') ?></footer>
    </main>
</div>
</body>
</html>
<?php
}

/**
 * 顶部操作提示条
 */
function admin_flash(?string $message, string $type = 'success'): void
{
    if ($message === null || $message === '') {
        return;
    }
    $class = $type === 'error' ? 'alert alert-error' : 'alert alert-success';
    echo '<div class="' . $class . '">' . e($message) . '</div>';
}
