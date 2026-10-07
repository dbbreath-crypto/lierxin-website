<?php
/**
 * 报价参数接口（公开只读）
 * GET api/quote_config.php → { ok:true, data:{ sqm:{...}, setup:{...}, ... } }
 *
 * 前台 quote.html 启动时拉取一次；拉取失败会自动使用 js/quote.js 里的内置默认值。
 */
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/quote_config.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    json_out(['ok' => false, 'message' => '仅支持 GET'], 405);
}

json_out([
    'ok'        => true,
    'data'      => quote_config_js(),
    'updatedAt' => quote_config_updated_at(),
]);
