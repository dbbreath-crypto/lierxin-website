<?php
/**
 * 报价参数配置：在前台报价页用到的全部计价因子，均可在此修改
 * 保存后前台 quote.html 实时生效（刷新页面即可）
 */
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/quote_config.php';
require __DIR__ . '/../inc/admin_layout.php';

session_start();
require_admin();

$schema = quote_config_schema();
$flash = null;
$flashType = 'success';

// ---------- 写操作 ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $flash = '页面已过期，请刷新后重试';
        $flashType = 'error';
    } else {
        $action = (string) ($_POST['action'] ?? 'save');

        if ($action === 'reset') {
            quote_config_reset();
            $flash = '已恢复为系统默认参数';
        } else {
            $values = $_POST['cfg'] ?? [];
            if (!is_array($values)) {
                $values = [];
            }
            $saved = quote_config_save($values);
            $flash = '参数已保存（本次覆盖 ' . $saved . ' 项，其余沿用默认值）';
        }
    }
}

$current = quote_config_all();
$updatedAt = quote_config_updated_at();
$overrideCount = 0;
try {
    $overrideCount = (int) db()->query('SELECT COUNT(*) FROM quote_config')->fetchColumn();
} catch (Throwable $ex) {
    $overrideCount = 0;
}

admin_header('报价参数', 'quote_settings.php');
admin_flash($flash, $flashType);
?>

<div class="card" style="margin-bottom:18px">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap">
        <div style="flex:1;min-width:260px">
            <h3 style="margin:0 0 6px;font-size:16px">计价参数总览</h3>
            <p style="margin:0;color:var(--a-text-2);font-size:13px;line-height:1.7">
                这里控制在<a href="<?= e(site_url('quote.html')) ?>" target="_blank" rel="noopener" style="color:var(--a-gold)">在线报价页</a>
                上实时算出的价格与交期。修改后<strong>前台刷新页面即生效</strong>，不需要重新部署。<br>
                留空或填回默认值即表示「沿用系统默认」，不会写进数据库。
            </p>
        </div>
        <div style="text-align:right;font-size:12px;color:var(--a-muted);line-height:1.8">
            自定义项：<strong style="color:var(--a-gold)"><?= $overrideCount ?></strong> 项<br>
            <?php if ($updatedAt): ?>
                最后修改：<?= e($updatedAt) ?>
            <?php else: ?>
                尚未修改任何参数
            <?php endif; ?>
        </div>
    </div>
</div>

<form method="post" id="cfgForm">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">

    <?php foreach ($schema as $group => $def): ?>
        <div class="card cfg-card">
            <div class="cfg-head">
                <h3><?= e($def['label']) ?></h3>
                <span class="cfg-desc"><?= e($def['desc']) ?></span>
            </div>
            <div class="cfg-grid">
                <?php foreach ($def['items'] as $key => $item): ?>
                    <?php
                    $flat = $group . '.' . $key;
                    $isCustom = abs((float) $current[$flat] - (float) $item['default']) > 1e-9;
                    ?>
                    <label class="cfg-item<?= $isCustom ? ' is-custom' : '' ?>">
                        <span class="cfg-label">
                            <?php if ($isCustom): ?>
                                <i class="cfg-dot" title="已自定义，默认 <?= e((string) $item['default']) ?>"></i>
                            <?php endif; ?>
                            <?= e($item['label']) ?>
                        </span>
                        <span class="cfg-input">
                            <input class="input" type="number" step="<?= e((string) $item['step']) ?>"
                                   name="cfg[<?= e($flat) ?>]" value="<?= e((string) $current[$flat]) ?>">
                            <em class="cfg-unit"><?= e($item['unit']) ?></em>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="cfg-actions">
        <button class="btn btn-primary" type="submit">保存参数</button>
        <a class="btn btn-ghost" href="<?= e(site_url('quote.html')) ?>" target="_blank" rel="noopener">打开报价页查看效果</a>
        <a class="btn btn-ghost" href="quotes.php">查看询价记录</a>
    </div>
</form>

<form method="post" style="margin-top:14px"
      onsubmit="return confirm('确定把所有参数恢复为系统默认值？自定义过的数值会被清除。')">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="reset">
    <button class="btn btn-ghost" type="submit" style="color:#d8636a;border-color:rgba(229,72,77,.35)">恢复默认参数</button>
</form>

<?php
admin_footer();
