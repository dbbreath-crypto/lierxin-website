<?php
/**
 * 后台概览
 */
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/admin_layout.php';

session_start();
require_admin();

$pdo = db();

$stat = [
    'total'   => (int) $pdo->query('SELECT COUNT(*) FROM messages')->fetchColumn(),
    'unread'  => (int) $pdo->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn(),
    'today'   => (int) $pdo->query('SELECT COUNT(*) FROM messages WHERE DATE(created_at) = CURDATE()')->fetchColumn(),
    'week'    => (int) $pdo->query('SELECT COUNT(*) FROM messages WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)')->fetchColumn(),
];

$latest = $pdo->query(
    'SELECT id, name, company, product, is_read, created_at
     FROM messages ORDER BY id DESC LIMIT 8'
)->fetchAll();

$adminRow = null;
try {
    $st = $pdo->prepare('SELECT username, last_login_at, last_login_ip, created_at FROM admins WHERE id = ?');
    $st->execute([$_SESSION['admin_id']]);
    $adminRow = $st->fetch();
} catch (Throwable $ex) {
    // 忽略
}

admin_header('概览', 'index.php');
?>
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-label">留言总数</div>
        <div class="stat-value"><?= $stat['total'] ?></div>
        <div class="stat-sub">累计收到的客户留言</div>
    </div>
    <div class="stat-card is-gold">
        <div class="stat-label">未读留言</div>
        <div class="stat-value"><?= $stat['unread'] ?></div>
        <div class="stat-sub"><a href="messages.php?unread=1" style="color:var(--a-gold)">立即处理 →</a></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">今日新增</div>
        <div class="stat-value"><?= $stat['today'] ?></div>
        <div class="stat-sub">近 7 天 <?= $stat['week'] ?> 条</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">当前管理员</div>
        <div class="stat-value" style="font-size:20px;padding-top:8px"><?= e($_SESSION['admin_user'] ?? '-') ?></div>
        <div class="stat-sub">
            <?php if ($adminRow && $adminRow['last_login_at']): ?>
                上次登录 <?= e($adminRow['last_login_at']) ?>
            <?php else: ?>
                首次登录
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
        <h3 style="font-size:15px;font-weight:600">最新留言</h3>
        <a class="link-btn" href="messages.php">查看全部 →</a>
    </div>

    <?php if (!$latest): ?>
        <div class="empty">
            <div class="empty-mark">✉</div>
            <div>还没有收到留言</div>
            <div style="font-size:13px;margin-top:6px">客户在官网「联系我们」提交后会显示在这里</div>
        </div>
    <?php else: ?>
        <div class="table-wrap" style="border:none">
            <table class="admin-table">
                <thead>
                <tr>
                    <th style="width:120px">客户</th>
                    <th style="width:130px">咨询产品</th>
                    <th style="width:150px">提交时间</th>
                    <th style="width:90px">状态</th>
                    <th style="width:80px">操作</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($latest as $m): ?>
                    <tr>
                        <td>
                            <?php if (!$m['is_read']): ?><span class="dot-unread"></span><?php endif; ?>
                            <strong><?= e($m['name']) ?></strong>
                            <?php if ($m['company']): ?>
                                <div style="font-size:12px;color:var(--a-muted)"><?= e($m['company']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td><span class="tag"><?= e($m['product'] ?: '未选择') ?></span></td>
                        <td class="nowrap" style="color:var(--a-text-2)"><?= e($m['created_at']) ?></td>
                        <td><span class="tag <?= $m['is_read'] ? 'tag-read' : '' ?>"><?= $m['is_read'] ? '已读' : '未读' ?></span></td>
                        <td><a class="link-btn" href="messages.php?view=<?= (int) $m['id'] ?>">详情</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<div class="card" style="margin-top:20px">
    <h3 style="font-size:15px;font-weight:600;margin-bottom:12px">后续可扩展模块</h3>
    <p style="color:var(--a-text-2);font-size:13px;line-height:1.9">
        本后台已搭好统一的鉴权、数据库与页面框架，接下来可直接扩展：<br>
        · <strong>产品管理</strong> —— 官网产品中心的数据化增删改（目前写死在 js/app.js 的 productsData）<br>
        · <strong>资讯管理</strong> —— 新闻动态的发布与编辑<br>
        · <strong>方案管理</strong> —— 行业方案内容维护<br>
        届时只需在 <code style="color:var(--a-gold)">inc/admin_layout.php</code> 的导航里加一项，并新建对应页面即可。
    </p>
</div>
<?php
admin_footer();
