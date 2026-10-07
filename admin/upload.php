<?php
/**
 * 后台图片上传（资讯封面 / 资讯正文配图 / 行业方案配图）
 * POST multipart/form-data: file, dir=news|solutions|products（可选，默认 news）
 * 返回：{ ok:true, url:"images/news/xxx.jpg" } 或 { ok:false, message:"..." }
 */
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/admin_layout.php';

session_start();
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['ok' => false, 'message' => '仅支持 POST 上传'], 405);
}

// CSRF 校验（表单里带 csrf_token 字段）
if (!csrf_check()) {
    json_out(['ok' => false, 'message' => '页面已过期，请刷新后重试'], 403);
}

if (empty($_FILES['file'])) {
    json_out(['ok' => false, 'message' => '没有收到文件'], 400);
}

$file = $_FILES['file'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    $errors = [
        UPLOAD_ERR_INI_SIZE   => '文件超过服务器限制（upload_max_filesize）',
        UPLOAD_ERR_FORM_SIZE  => '文件超过表单限制',
        UPLOAD_ERR_PARTIAL    => '文件只有部分被上传',
        UPLOAD_ERR_NO_FILE    => '没有文件被上传',
        UPLOAD_ERR_NO_TMP_DIR => '缺少临时目录',
        UPLOAD_ERR_CANT_WRITE => '写入磁盘失败',
        UPLOAD_ERR_EXTENSION  => '上传被扩展阻止',
    ];
    json_out(['ok' => false, 'message' => $errors[$file['error']] ?? '上传失败（错误码 ' . $file['error'] . '）'], 400);
}

if ($file['size'] > 5 * 1024 * 1024) {
    json_out(['ok' => false, 'message' => '图片不能超过 5MB'], 400);
}

// 必须是真实图片
$info = @getimagesize($file['tmp_name']);
if ($info === false) {
    json_out(['ok' => false, 'message' => '文件不是有效的图片'], 400);
}

$mimeMap = [
    IMAGETYPE_JPEG => 'jpg',
    IMAGETYPE_PNG  => 'png',
    IMAGETYPE_GIF  => 'gif',
    IMAGETYPE_WEBP => 'webp',
];
if (!isset($mimeMap[$info[2]])) {
    json_out(['ok' => false, 'message' => '仅支持 JPG / PNG / GIF / WebP 格式'], 400);
}
$ext = $mimeMap[$info[2]];

// 目标目录白名单：只允许上传到这两个图片目录
$sub = (string) ($_POST['dir'] ?? 'news');
if (!in_array($sub, ['news', 'solutions', 'products'], true)) {
    $sub = 'news';
}

$dir = __DIR__ . '/../images/' . $sub;
if (!is_dir($dir)) {
    @mkdir($dir, 0775, true);
}
if (!is_dir($dir) || !is_writable($dir)) {
    json_out(['ok' => false, 'message' => '上传目录不可写，请检查 images/' . $sub . ' 权限'], 500);
}

// 二次哈希，避免同一个文件的多余副本
$hash = substr(hash_file('sha256', $file['tmp_name']), 0, 10);
$name = 'u-' . date('Ymd') . '-' . $hash . '.' . $ext;
$target = $dir . '/' . $name;

if (!file_exists($target)) {
    if (!move_uploaded_file($file['tmp_name'], $target)) {
        json_out(['ok' => false, 'message' => '保存文件失败'], 500);
    }
    @chmod($target, 0664);
}

json_out([
    'ok'  => true,
    'url' => 'images/' . $sub . '/' . $name,
    'size' => (int) @filesize($target),
]);
