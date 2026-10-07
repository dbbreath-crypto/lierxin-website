<?php
/**
 * 图形验证码：生成 4 位验证码
 * 优先用 GD 输出 PNG；PHP 没有 GD 时自动降级为 SVG（视觉一致，同样带干扰线与随机色）
 * 前端 <img src="api/captcha.php"> 展示，点击可刷新
 */
require __DIR__ . '/../inc/bootstrap.php';

session_start();

// 去掉容易混淆的 0 O 1 I L
$pool = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
$length = 4;
$code = '';
for ($i = 0; $i < $length; $i++) {
    $code .= $pool[random_int(0, strlen($pool) - 1)];
}

$_SESSION['captcha'] = $code;
$_SESSION['captcha_time'] = time();

$width = 118;
$height = 42;

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

if (function_exists('imagecreatetruecolor')) {
    render_png_captcha($code, $width, $height);
} else {
    render_svg_captcha($code, $width, $height);
}
exit;

/**
 * GD 版本：输出 PNG（生产环境）
 */
function render_png_captcha(string $code, int $width, int $height): void
{
    $im = imagecreatetruecolor($width, $height);

    // 配色沿用官网深色科技风
    $bg = imagecolorallocate($im, 15, 23, 42);
    imagefilledrectangle($im, 0, 0, $width, $height, $bg);

    // 噪点
    for ($i = 0; $i < 220; $i++) {
        $noise = imagecolorallocate($im, random_int(40, 90), random_int(50, 100), random_int(70, 130));
        imagesetpixel($im, random_int(0, $width - 1), random_int(0, $height - 1), $noise);
    }

    // 干扰线
    for ($i = 0; $i < 4; $i++) {
        $line = imagecolorallocate($im, random_int(60, 110), random_int(70, 120), random_int(90, 150));
        imageline($im, random_int(0, 20), random_int(0, $height),
            random_int($width - 20, $width), random_int(0, $height), $line);
    }

    $font = 5;
    $charWidth = imagefontwidth($font);
    $startX = (int) (($width - $charWidth * strlen($code)) / 2);
    $baseY = (int) (($height - imagefontheight($font)) / 2);

    for ($i = 0; $i < strlen($code); $i++) {
        $color = imagecolorallocate($im, random_int(190, 235), random_int(170, 215), random_int(120, 190));
        imagestring($im, $font, $startX + $i * $charWidth, $baseY + random_int(-3, 3), $code[$i], $color);
    }

    $border = imagecolorallocate($im, 45, 60, 90);
    imagerectangle($im, 0, 0, $width - 1, $height - 1, $border);

    header('Content-Type: image/png');
    imagepng($im);
    imagedestroy($im);
}

/**
 * SVG 降级版本：缺少 GD 时使用（无需任何图形扩展）
 */
function render_svg_captcha(string $code, int $width, int $height): void
{
    $chars = '';
    $len = strlen($code);
    $step = (int) ($width / ($len + 1));

    for ($i = 0; $i < $len; $i++) {
        $x = (int) ($step * ($i + 0.55));
        $y = (int) ($height / 2 + random_int(-4, 4));
        $rotate = random_int(-12, 12);
        $color = sprintf('rgb(%d,%d,%d)', random_int(200, 240), random_int(180, 220), random_int(130, 195));
        $chars .= sprintf(
            '<text x="%d" y="%d" font-family="Menlo,monospace" font-size="24" font-weight="bold" fill="%s" text-anchor="middle" transform="rotate(%d %d %d)">%s</text>',
            $x, $y + 8, $color, $rotate, $x, $y, $code[$i]
        );
    }

    // 干扰线
    $lines = '';
    for ($i = 0; $i < 4; $i++) {
        $lines .= sprintf(
            '<line x1="%d" y1="%d" x2="%d" y2="%d" stroke="rgb(%d,%d,%d)" stroke-width="1" opacity="0.55"/>',
            random_int(0, 20), random_int(0, $height),
            random_int($width - 20, $width), random_int(0, $height),
            random_int(60, 110), random_int(70, 120), random_int(90, 150)
        );
    }

    $svg = sprintf(
        '<?xml version="1.0" encoding="UTF-8"?>'
        . '<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d">'
        . '<rect width="%d" height="%d" fill="#0f172a"/>%s%s'
        . '<rect x="0.5" y="0.5" width="%d" height="%d" fill="none" stroke="#2d3c5a"/>'
        . '</svg>',
        $width, $height, $width, $height,
        $width, $height, $lines, $chars,
        $width - 1, $height - 1
    );

    header('Content-Type: image/svg+xml; charset=utf-8');
    echo $svg;
}
