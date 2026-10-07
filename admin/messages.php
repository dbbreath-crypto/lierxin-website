<?php
/**
 * 留言管理：列表 / 搜索 / 详情 / 标记已读 / 删除 / 导出
 */
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/admin_layout.php';

session_start();
require_admin();

$pdo = db();
$flash = null;
$flashType = 'success';

// ---------- 写操作 ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $flash = '页面已过期，请刷新后重试';
        $flashType = 'error';
    } else {
        $action = (string) ($_POST['action'] ?? '');
        $id = (int) ($_POST['id'] ?? 0);

        if ($id > 0) {
            if ($action === 'delete') {
                $st = $pdo->prepare('DELETE FROM messages WHERE id = ?');
                $st->execute([$id]);
                $flash = '留言已删除';
            } elseif ($action === 'toggle_read') {
                $st = $pdo->prepare('UPDATE messages SET is_read = 1 - is_read WHERE id = ?');
                $st->execute([$id]);
                $flash = '已更新阅读状态';
            }
        }
    }
}

// ---------- 导出 CSV ----------
if (isset($_GET['export']) && $_GET['export'] === '1') {
    $rows = $pdo->query('SELECT id, name, phone, email, company, product, content, is_read, created_at
                         FROM messages ORDER BY id DESC')->fetchAll();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="lierxin-messages-' . date('Ymd-His') . '.csv"');

    // BOM 让 Excel 正确识别 UTF-8
    echo "\xEF\xBB\xBF";
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', '姓名', '电话', '邮箱', '公司', '咨询产品', '留言内容', '状态', '提交时间']);
    foreach ($rows as $r) {
        fputcsv($out, [
            $r['id'],
            $r['name'],
            $r['phone'],
            $r['email'],
            $r['company'],
            $r['product'],
            preg_replace('/\s+/', ' ', $r['content']),
            $r['is_read'] ? '已读' : '未读',
            $r['created_at'],
        ]);
    }
    fclose($out);
    exit;
}

$viewId = (int) ($_GET['view'] ?? 0);

// ---------- 详情页 ----------
if ($viewId > 0) {
    $st = $pdo->prepare('SELECT * FROM messages WHERE id = ?');
    $st->execute([$viewId]);
    $msg = $st->fetch();

    if (!$msg) {
        admin_header('留言详情', 'messages.php');
        echo '<div class="alert alert-error">留言不存在或已被删除</div>';
        echo '<a class="btn btn-ghost" href="messages.php">返回列表</a>';
        admin_footer();
        exit;
    }

    // 打开详情即标记为已读
    if (!$msg['is_read']) {
        $pdo->prepare('UPDATE messages SET is_read = 1 WHERE id = ?')->execute([$viewId]);
        $msg['is_read'] = 1;
    }

    admin_header('留言详情', 'messages.php');
    admin_flash($flash, $flashType);
    ?>
    <div class="card">
        <dl class="detail-grid">
            <dt>客户姓名</dt>
            <dd><strong><?= e($msg['name']) ?></strong></dd>

            <dt>联系电话</dt>
            <dd><a href="tel:<?= e($msg['phone']) ?>" style="color:var(--a-gold)"><?= e($msg['phone']) ?></a></dd>

            <dt>电子邮箱</dt>
            <dd>
                <?php if ($msg['email']): ?>
                    <a href="mailto:<?= e($msg['email']) ?>" style="color:var(--a-gold)"><?= e($msg['email']) ?></a>
                <?php else: ?>
                    <span style="color:var(--a-muted)">未填写</span>
                <?php endif; ?>
            </dd>

            <dt>公司名称</dt>
            <dd><?= $msg['company'] ? e($msg['company']) : '<span style="color:var(--a-muted)">未填写</span>' ?></dd>

            <dt>咨询产品</dt>
            <dd><span class="tag"><?= e($msg['product'] ?: '未选择') ?></span></dd>

            <dt>提交时间</dt>
            <dd><?= e($msg['created_at']) ?></dd>

            <dt>来源 IP</dt>
            <dd style="color:var(--a-text-2)"><?= e($msg['ip']) ?></dd>

            <dt>浏览器</dt>
            <dd style="color:var(--a-text-2);font-size:12px;word-break:break-all"><?= e($msg['user_agent']) ?></dd>
        </dl>

        <div class="detail-content"><?= e($msg['content']) ?></div>

        <div style="margin-top:24px;display:flex;gap:10px;flex-wrap:wrap">
            <a class="btn btn-ghost" href="messages.php">← 返回列表</a>
            <form method="post" onsubmit="return confirm('确定要删除这条留言吗？删除后无法恢复。')">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $msg['id'] ?>">
                <input type="hidden" name="action" value="delete">
                <button class="btn btn-ghost" type="submit" style="color:#d8636a;border-color:rgba(229,72,77,.35)">删除留言</button>
            </form>
        </div>
    </div>
    <?php
    admin_footer();
    exit;
}

// ---------- 列表 ----------
$keyword = trim((string) ($_GET['q'] ?? ''));
$onlyUnread = (($_GET['unread'] ?? '') === '1');
$page = max(1, (int) ($_GET['page'] ?? 1));

$where = [];
$params = [];

if ($keyword !== '') {
    $where[] = '(name LIKE :kw OR phone LIKE :kw OR email LIKE :kw OR company LIKE :kw OR content LIKE :kw)';
    $params[':kw'] = '%' . $keyword . '%';
}
if ($onlyUnread) {
    $where[] = 'is_read = 0';
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM messages $whereSql");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();

$pageSize = ADMIN_PAGE_SIZE;
$totalPages = max(1, (int) ceil($total / $pageSize));
$page = min($page, $totalPages);
$offset = ($page - 1) * $pageSize;

$listStmt = $pdo->prepare(
    "SELECT id, name, phone, email, company, product, content, is_read, created_at
     FROM messages $whereSql
     ORDER BY id DESC
     LIMIT $pageSize OFFSET $offset"
);
$listStmt->execute($params);
$rows = $listStmt->fetchAll();

$queryBase = array_filter([
    'q' => $keyword,
    'unread' => $onlyUnread ? '1' : null,
]);

admin_header('留言管理', 'messages.php');
admin_flash($flash, $flashType);
?>

<div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:18px;flex-wrap:wrap">
    <form class="form-inline" method="get">
        <input class="input" type="text" name="q" placeholder="搜索姓名 / 电话 / 公司 / 内容"
               value="<?= e($keyword) ?>">
        <input type="hidden" name="unread" value="<?= $onlyUnread ? '1' : '' ?>">
        <button class="btn btn-ghost" type="submit">搜索</button>
        <?php if ($keyword !== '' || $onlyUnread): ?>
            <a class="link-btn" href="messages.php">清除筛选</a>
        <?php endif; ?>
    </form>

    <div style="display:flex;gap:10px">
        <a class="btn btn-ghost" href="messages.php?unread=1">仅看未读</a>
        <a class="btn btn-primary" href="messages.php?export=1">导出 CSV</a>
    </div>
</div>

<div class="table-wrap">
    <?php if (!$rows): ?>
        <div class="empty">
            <div class="empty-mark">✉</div>
            <div><?= ($keyword !== '' || $onlyUnread) ? '没有符合条件的留言' : '还没有收到留言' ?></div>
        </div>
    <?php else: ?>
        <table class="admin-table">
            <thead>
            <tr>
                <th style="width:150px">客户</th>
                <th style="width:130px">联系方式</th>
                <th>留言内容</th>
                <th style="width:130px">咨询产品</th>
                <th style="width:150px">提交时间</th>
                <th style="width:150px">操作</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $m): ?>
                <tr>
                    <td>
                        <?php if (!$m['is_read']): ?><span class="dot-unread"></span><?php endif; ?>
                        <strong><?= e($m['name']) ?></strong>
                        <?php if ($m['company']): ?>
                            <div style="font-size:12px;color:var(--a-muted)"><?= e($m['company']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="nowrap" style="color:var(--a-text-2)">
                        <?= e($m['phone']) ?>
                        <?php if ($m['email']): ?>
                            <div style="font-size:12px;color:var(--a-muted)"><?= e($m['email']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="cell-content">
                        <?= e(mb_substr($m['content'], 0, 60, 'UTF-8')) ?><?= mb_strlen($m['content'], 'UTF-8') > 60 ? '…' : '' ?>
                    </td>
                    <td><span class="tag"><?= e($m['product'] ?: '未选择') ?></span></td>
                    <td class="nowrap" style="color:var(--a-text-2)"><?= e($m['created_at']) ?></td>
                    <td>
                        <div class="row-actions">
                            <a class="link-btn" href="messages.php?view=<?= (int) $m['id'] ?>">详情</a>
                            <form method="post">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                                <input type="hidden" name="action" value="toggle_read">
                                <button class="link-btn" type="submit"><?= $m['is_read'] ? '标未读' : '标已读' ?></button>
                            </form>
                            <form method="post" onsubmit="return confirm('确定删除这条留言？')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                                <input type="hidden" name="action" value="delete">
                                <button class="link-btn danger" type="submit">删除</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php if ($totalPages > 1): ?>
    <div class="pager">
        <?php if ($page > 1): ?>
            <a href="messages.php?<?= http_build_query(array_merge($queryBase, ['page' => $page - 1])) ?>">上一页</a>
        <?php endif; ?>
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <?php if ($p === $page): ?>
                <span class="current"><?= $p ?></span>
            <?php else: ?>
                <a href="messages.php?<?= http_build_query(array_merge($queryBase, ['page' => $p])) ?>"><?= $p ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
            <a href="messages.php?<?= http_build_query(array_merge($queryBase, ['page' => $page + 1])) ?>">下一页</a>
        <?php endif; ?>
        <span class="pager-info">共 <?= $total ?> 条 · 第 <?= $page ?>/<?= $totalPages ?> 页</span>
    </div>
<?php else: ?>
    <div class="pager"><span class="pager-info">共 <?= $total ?> 条留言</span></div>
<?php endif; ?>

<?php
admin_footer();
