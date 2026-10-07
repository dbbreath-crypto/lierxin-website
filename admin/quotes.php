<?php
/**
 * 在线报价询价管理：列表 / 搜索 / 状态筛选 / 详情 / 改状态 / 备注 / 删除 / 导出
 */
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/admin_layout.php';

session_start();
require_admin();

$pdo = db();
$flash = null;
$flashType = 'success';

$STATUS = [
    0 => ['label' => '待联系', 'cls' => 'tag'],
    1 => ['label' => '已联系', 'cls' => 'tag'],
    2 => ['label' => '已成交', 'cls' => 'tag'],
    3 => ['label' => '已关闭', 'cls' => 'tag'],
];

// 参数 key → 中文名，用于详情页展示报价参数
$PARAM_LABEL = [
    'length'    => '长度(cm)',
    'width'     => '宽度(cm)',
    'qty'       => '数量(pcs)',
    'variety'   => '拼板款数',
    'layer'     => '层数',
    'material'  => '板材',
    'thickness' => '板厚(mm)',
    'copper'    => '铜厚(oz)',
    'solder'    => '阻焊颜色',
    'silk'      => '字符',
    'surface'   => '表面处理',
    'via'       => '过孔处理',
    'test'      => '测试方式',
    'shape'     => '成型方式',
    'special'   => '特殊工艺',
    'dateCode'  => '印周期',
    'report'    => '检验报告',
    'invoice'   => '发票',
    'ship'      => '快递',
    'delivery'  => '出货方式',
];

// ---------- 写操作 ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $flash = '页面已过期，请刷新后重试';
        $flashType = 'error';
    } else {
        $action = (string) ($_POST['action'] ?? '');
        $id = (int) ($_POST['id'] ?? 0);

        if ($id > 0 && $action === 'delete') {
            $pdo->prepare('DELETE FROM quotes WHERE id = ?')->execute([$id]);
            $flash = '询价记录已删除';
        } elseif ($id > 0 && $action === 'status') {
            $next = (int) ($_POST['status'] ?? 0);
            if (isset($STATUS[$next])) {
                $pdo->prepare('UPDATE quotes SET status = ? WHERE id = ?')->execute([$next, $id]);
                $flash = '状态已更新为「' . $STATUS[$next]['label'] . '」';
            }
        } elseif ($id > 0 && $action === 'note') {
            $note = trim((string) ($_POST['admin_note'] ?? ''));
            $pdo->prepare('UPDATE quotes SET admin_note = ? WHERE id = ?')
                ->execute([mb_substr($note, 0, 2000, 'UTF-8'), $id]);
            $flash = '备注已保存';
        }
    }
}

// ---------- 导出 CSV ----------
if (isset($_GET['export']) && $_GET['export'] === '1') {
    $rows = $pdo->query('SELECT * FROM quotes ORDER BY id DESC')->fetchAll();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="lierxin-quotes-' . date('Ymd-His') . '.csv"');
    echo "\xEF\xBB\xBF";
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', '姓名', '电话', '邮箱', '公司', '估算总价', '单片价', '预计交期', '状态', '备注', '提交时间']);
    foreach ($rows as $r) {
        fputcsv($out, [
            $r['id'],
            $r['name'],
            $r['phone'],
            $r['email'],
            $r['company'],
            $r['estimate_total'],
            $r['estimate_unit'],
            $r['lead_time'],
            $STATUS[(int) $r['status']]['label'] ?? '未知',
            preg_replace('/\s+/', ' ', (string) $r['remark']),
            $r['created_at'],
        ]);
    }
    fclose($out);
    exit;
}

$viewId = (int) ($_GET['view'] ?? 0);

// ---------- 详情 ----------
if ($viewId > 0) {
    $st = $pdo->prepare('SELECT * FROM quotes WHERE id = ?');
    $st->execute([$viewId]);
    $q = $st->fetch();

    if (!$q) {
        admin_header('询价详情', 'quotes.php');
        echo '<div class="alert alert-error">询价记录不存在或已被删除</div>';
        echo '<a class="btn btn-ghost" href="quotes.php">返回列表</a>';
        admin_footer();
        exit;
    }

    $params = json_decode((string) $q['params'], true);
    if (!is_array($params)) {
        $params = [];
    }
    $statusInfo = $STATUS[(int) $q['status']] ?? $STATUS[0];

    admin_header('询价详情', 'quotes.php');
    admin_flash($flash, $flashType);
    ?>
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:20px;flex-wrap:wrap">
            <div>
                <strong style="font-size:17px"><?= e($q['name']) ?></strong>
                <span class="<?= e($statusInfo['cls']) ?>" style="margin-left:10px"><?= e($statusInfo['label']) ?></span>
            </div>
            <div style="color:var(--a-text-2);font-size:13px">
                估算总计 <strong style="color:var(--a-gold);font-size:18px">￥<?= e(number_format((float) $q['estimate_total'], 2)) ?></strong>
                · 单片 ￥<?= e(number_format((float) $q['estimate_unit'], 2)) ?>
                · 交期 <?= e($q['lead_time']) ?>
            </div>
        </div>

        <dl class="detail-grid">
            <dt>联系电话</dt>
            <dd><a href="tel:<?= e($q['phone']) ?>" style="color:var(--a-gold)"><?= e($q['phone']) ?></a></dd>

            <dt>电子邮箱</dt>
            <dd><?= $q['email'] ? e($q['email']) : '<span style="color:var(--a-muted)">未填写</span>' ?></dd>

            <dt>公司名称</dt>
            <dd><?= $q['company'] ? e($q['company']) : '<span style="color:var(--a-muted)">未填写</span>' ?></dd>

            <dt>提交时间</dt>
            <dd><?= e($q['created_at']) ?></dd>

            <dt>来源 IP</dt>
            <dd style="color:var(--a-text-2)"><?= e($q['ip']) ?></dd>
        </dl>

        <h4 style="margin:26px 0 12px;font-size:15px">报价参数</h4>
        <?php if (!$params): ?>
            <div class="empty">该记录没有参数数据</div>
        <?php else: ?>
            <dl class="detail-grid">
                <?php foreach ($params as $k => $v): ?>
                    <?php if ($v === '' || $v === null) { continue; } ?>
                    <dt><?= e($PARAM_LABEL[$k] ?? $k) ?></dt>
                    <dd><?= e((string) $v) ?></dd>
                <?php endforeach; ?>
            </dl>
        <?php endif; ?>

        <?php if ($q['remark']): ?>
            <h4 style="margin:26px 0 12px;font-size:15px">客户备注</h4>
            <div class="detail-content"><?= e($q['remark']) ?></div>
        <?php endif; ?>

        <h4 style="margin:26px 0 12px;font-size:15px">处理</h4>
        <form method="post" style="margin-bottom:16px">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int) $q['id'] ?>">
            <input type="hidden" name="action" value="note">
            <textarea class="input" name="admin_note" rows="3" placeholder="内部备注（客户看不到）"><?= e((string) $q['admin_note']) ?></textarea>
            <button class="btn btn-ghost" type="submit" style="margin-top:10px">保存备注</button>
        </form>

        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
            <a class="btn btn-ghost" href="quotes.php">← 返回列表</a>
            <?php foreach ($STATUS as $key => $info): ?>
                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int) $q['id'] ?>">
                    <input type="hidden" name="action" value="status">
                    <input type="hidden" name="status" value="<?= (int) $key ?>">
                    <button class="btn <?= (int) $q['status'] === $key ? 'btn-primary' : 'btn-ghost' ?>" type="submit">
                        <?= e($info['label']) ?>
                    </button>
                </form>
            <?php endforeach; ?>
            <form method="post" onsubmit="return confirm('确定删除这条询价记录？删除后无法恢复。')">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $q['id'] ?>">
                <input type="hidden" name="action" value="delete">
                <button class="btn btn-ghost" type="submit" style="color:#d8636a;border-color:rgba(229,72,77,.35)">删除</button>
            </form>
        </div>
    </div>
    <?php
    admin_footer();
    exit;
}

// ---------- 列表 ----------
$keyword = trim((string) ($_GET['q'] ?? ''));
$statusFilter = isset($_GET['status']) && $_GET['status'] !== '' ? (int) $_GET['status'] : null;
$page = max(1, (int) ($_GET['page'] ?? 1));

$where = [];
$params = [];
if ($keyword !== '') {
    $where[] = '(name LIKE :kw OR phone LIKE :kw OR email LIKE :kw OR company LIKE :kw)';
    $params[':kw'] = '%' . $keyword . '%';
}
if ($statusFilter !== null && isset($STATUS[$statusFilter])) {
    $where[] = 'status = :st';
    $params[':st'] = $statusFilter;
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM quotes $whereSql");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();

$pageSize = ADMIN_PAGE_SIZE;
$totalPages = max(1, (int) ceil($total / $pageSize));
$page = min($page, $totalPages);
$offset = ($page - 1) * $pageSize;

$listStmt = $pdo->prepare(
    "SELECT id, name, phone, email, company, estimate_total, estimate_unit, lead_time, status, created_at
     FROM quotes $whereSql
     ORDER BY id DESC
     LIMIT $pageSize OFFSET $offset"
);
$listStmt->execute($params);
$rows = $listStmt->fetchAll();

$queryBase = array_filter([
    'q'      => $keyword,
    'status' => $statusFilter !== null ? (string) $statusFilter : null,
]);

admin_header('在线报价询价', 'quotes.php');
admin_flash($flash, $flashType);
?>

<div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:18px;flex-wrap:wrap">
    <form class="form-inline" method="get">
        <input class="input" type="text" name="q" placeholder="搜索姓名 / 电话 / 公司"
               value="<?= e($keyword) ?>">
        <?php if ($statusFilter !== null): ?>
            <input type="hidden" name="status" value="<?= (int) $statusFilter ?>">
        <?php endif; ?>
        <button class="btn btn-ghost" type="submit">搜索</button>
        <?php if ($keyword !== '' || $statusFilter !== null): ?>
            <a class="link-btn" href="quotes.php">清除筛选</a>
        <?php endif; ?>
    </form>

    <div style="display:flex;gap:10px;flex-wrap:wrap">
        <?php foreach ($STATUS as $key => $info): ?>
            <a class="btn <?= $statusFilter === $key ? 'btn-primary' : 'btn-ghost' ?>"
               href="quotes.php?status=<?= (int) $key ?>"><?= e($info['label']) ?></a>
        <?php endforeach; ?>
        <a class="btn btn-primary" href="quotes.php?export=1">导出 CSV</a>
    </div>
</div>

<div class="table-wrap">
    <?php if (!$rows): ?>
        <div class="empty">
            <div class="empty-mark">¥</div>
            <div><?= ($keyword !== '' || $statusFilter !== null) ? '没有符合条件的询价' : '还没有收到在线询价' ?></div>
        </div>
    <?php else: ?>
        <table class="admin-table">
            <thead>
            <tr>
                <th style="width:150px">客户</th>
                <th style="width:140px">联系方式</th>
                <th style="width:130px">估算总价</th>
                <th style="width:140px">预计交期</th>
                <th style="width:100px">状态</th>
                <th style="width:150px">提交时间</th>
                <th style="width:120px">操作</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $q): ?>
                <?php $si = $STATUS[(int) $q['status']] ?? $STATUS[0]; ?>
                <tr>
                    <td>
                        <strong><?= e($q['name']) ?></strong>
                        <?php if ($q['company']): ?>
                            <div style="font-size:12px;color:var(--a-muted)"><?= e($q['company']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="nowrap" style="color:var(--a-text-2)">
                        <?= e($q['phone']) ?>
                        <?php if ($q['email']): ?>
                            <div style="font-size:12px;color:var(--a-muted)"><?= e($q['email']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong style="color:var(--a-gold)">￥<?= e(number_format((float) $q['estimate_total'], 2)) ?></strong>
                        <div style="font-size:12px;color:var(--a-muted)">单片 ￥<?= e(number_format((float) $q['estimate_unit'], 2)) ?></div>
                    </td>
                    <td class="nowrap" style="color:var(--a-text-2)"><?= e($q['lead_time']) ?></td>
                    <td><span class="<?= e($si['cls']) ?>"><?= e($si['label']) ?></span></td>
                    <td class="nowrap" style="color:var(--a-text-2)"><?= e($q['created_at']) ?></td>
                    <td>
                        <div class="row-actions">
                            <a class="link-btn" href="quotes.php?view=<?= (int) $q['id'] ?>">详情</a>
                            <form method="post" onsubmit="return confirm('确定删除这条询价记录？')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $q['id'] ?>">
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
            <a href="quotes.php?<?= http_build_query(array_merge($queryBase, ['page' => $page - 1])) ?>">上一页</a>
        <?php endif; ?>
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <?php if ($p === $page): ?>
                <span class="current"><?= $p ?></span>
            <?php else: ?>
                <a href="quotes.php?<?= http_build_query(array_merge($queryBase, ['page' => $p])) ?>"><?= $p ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
            <a href="quotes.php?<?= http_build_query(array_merge($queryBase, ['page' => $page + 1])) ?>">下一页</a>
        <?php endif; ?>
        <span class="pager-info">共 <?= $total ?> 条 · 第 <?= $page ?>/<?= $totalPages ?> 页</span>
    </div>
<?php else: ?>
    <div class="pager"><span class="pager-info">共 <?= $total ?> 条询价</span></div>
<?php endif; ?>

<div style="margin-top:16px;font-size:12px;color:var(--a-muted)">
    前台页面：<a href="<?= e(site_url('quote.html')) ?>" target="_blank" rel="noopener" style="color:var(--a-gold)">quote.html</a>
    （独立页面，未加入主导航）
    · 计价参数：<a href="quote_settings.php" style="color:var(--a-gold)">报价参数配置</a>
</div>

<?php
admin_footer();
