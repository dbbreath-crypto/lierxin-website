<?php
/**
 * 公共引导：配置加载、数据库连接、会话、通用工具
 * 所有入口（api/、admin/）都先 include 本文件
 */
define('LEX_APP', true);
date_default_timezone_set('Asia/Shanghai');

require __DIR__ . '/../config.php';

// ---- session 目录兜底（部分环境默认目录不可写） ----
$savePath = session_save_path();
if ($savePath === '' || !is_writable($savePath)) {
    @session_save_path(sys_get_temp_dir());
}

/**
 * 获取 PDO 单例
 */
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

/**
 * 站点根路径（依据当前脚本路径推算，兼容子目录部署）
 * 例：/admin/index.php → ''；/lierxin-website/admin/x → '/lierxin-website'
 */
function site_base(): string
{
    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/'));
    $dir = str_replace('\\', '/', dirname($script));          // 如 /admin
    $base = dirname($dir);                                     // 如 / 或 /lierxin-website
    if ($base === '/' || $base === '.' || $base === '') {
        $base = '';
    }
    return $base;
}

/**
 * 站点内任意文件的 URL
 * 例：site_url('quote.html') → /quote.html
 */
function site_url(string $path): string
{
    return site_base() . '/' . ltrim($path, '/');
}

/**
 * 前台页面 URL（后台里跳前台统一用它）
 * 例：/admin/index.php        → /index.html#/
 *     /lierxin-website/admin/x → /lierxin-website/index.html#/
 *
 * @param string $hash 前台 hash 路由，如 '#/' '#/news/3'
 */
function front_url(string $hash = '#/'): string
{
    return site_url('index.html' . $hash);
}

/**
 * HTML 转义输出
 */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * 输出 JSON 并终止
 */
function json_out(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * 获取客户端 IP（考虑反代的情况）
 */
function client_ip(): string
{
    foreach (['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'] as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = trim(explode(',', $_SERVER[$key])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * CSRF token
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_check(): bool
{
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], (string) $_POST['csrf_token']);
}

/**
 * 是否允许跨域（本站前后端同域，仅允许本站来源的 POST）
 */
function same_origin_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
}

/**
 * 后台鉴权
 */
function is_admin(): bool
{
    return !empty($_SESSION['admin_id']);
}

function require_admin(): void
{
    if (!is_admin()) {
        header('Location: login.php');
        exit;
    }
}

/* ---------------- 富文本 HTML 白名单过滤 ---------------- */

/**
 * 清洗富文本：保留排版类标签，剔除脚本、事件属性与危险协议。
 * 说明：本机可用 PHP 未编译 DOM 扩展，因此采用 strip_tags + 属性白名单的正则方案。
 */
function sanitize_html(?string $html): string
{
    $html = (string) $html;
    if ($html === '') {
        return '';
    }

    // 1. 注释
    $html = preg_replace('#<!--.*?-->#s', '', $html);

    // 2. 成对的危险标签（连同内容一起去掉）
    $html = preg_replace(
        '#<(script|style|iframe|object|embed|form|textarea|select|svg|math|noscript)\b[^>]*>.*?</\1\s*>#is',
        '',
        $html
    );
    // 3. 单独出现的危险标签
    $html = preg_replace(
        '#</?(script|style|iframe|object|embed|link|meta|base|form|input|button|base|param)\b[^>]*>#is',
        '',
        $html
    );

    // 4. 标签白名单
    $allowed = '<p><br><hr><h2><h3><h4><h5><strong><b><em><i><u><s>'
        . '<blockquote><ul><ol><li><a><img><figure><figcaption>'
        . '<div><span><table><thead><tbody><tr><th><td><pre><code>';
    $html = strip_tags($html, $allowed);

    // 5. 属性白名单（逐个标签处理）
    $html = preg_replace_callback('#<([a-zA-Z0-9]+)([^>]*)>#s', static function (array $m): string {
        $tag = strtolower($m[1]);
        $attrPart = $m[2] ?? '';
        $selfClose = str_ends_with(rtrim($attrPart), '/');
        $attrPart = rtrim(preg_replace('#/\s*$#', '', $attrPart));

        $attrs = sanitize_html_attrs($tag, $attrPart);
        return '<' . $tag . ($attrs !== '' ? ' ' . $attrs : '') . ($selfClose ? ' /' : '') . '>';
    }, $html);

    // 6. 压缩连续空行
    $html = preg_replace('#(\s*<p>\s*</p>\s*)+#', "\n", $html);

    return trim($html);
}

/**
 * 按标签过滤属性，返回拼好的属性串
 */
function sanitize_html_attrs(string $tag, string $attrPart): string
{
    static $allowMap = [
        'a'     => ['href', 'title', 'target', 'rel'],
        'img'   => ['src', 'alt', 'width', 'height', 'loading'],
        'td'    => ['colspan', 'rowspan'],
        'th'    => ['colspan', 'rowspan'],
        'li'    => [],
        'div'   => ['class'],
        'span'  => ['class'],
        'p'     => ['class'],
        'table' => ['class'],
    ];

    $allow = $allowMap[$tag] ?? [];
    if (!$allow || trim($attrPart) === '') {
        return '';
    }

    $out = [];
    if (preg_match_all('#([a-zA-Z_:-]+)\s*=\s*("([^"]*)"|\'([^\']*)\'|([^\s"\'>]+))#', $attrPart, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $m) {
            $name = strtolower($m[1]);
            if (!in_array($name, $allow, true)) {
                continue;
            }
            $value = $m[3] !== '' ? $m[3] : ($m[4] !== '' ? $m[4] : ($m[5] ?? ''));
            $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

            // 协议过滤：只允许 http(s) / 站内相对路径 / 锚点 / mailto / tel
            if ($name === 'href' || $name === 'src') {
                $probe = strtolower(trim($value));
                $probeNoSpace = str_replace(["\t", "\n", "\r", ' '], '', $probe);
                $isRelative = $probe !== '' && !preg_match('#^[a-z][a-z0-9+.-]*:#', $probeNoSpace);
                $isSafe = preg_match('#^(https?://|mailto:|tel:|/|\#)#', $probeNoSpace);
                if (!$isRelative && !$isSafe) {
                    continue;
                }
            }
            if ($name === 'target' && $value !== '_blank') {
                continue;
            }

            $out[$name] = $name . '="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"';
        }
    }

    // 外链补 noopener
    if ($tag === 'a' && isset($out['href']) && isset($out['target'])) {
        $out['rel'] = 'rel="noopener noreferrer"';
    }

    return implode(' ', $out);
}

/**
 * 从富文本里提取纯文本摘要（用于自动填充 excerpt）
 */
function html_summary(string $html, int $limit = 100): string
{
    $text = strip_tags(preg_replace('#<br\s*/?>#i', "\n", $html));
    $text = html_entity_decode(trim($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace('#\s+#u', ' ', $text) ?? '';
    if (mb_strlen($text, 'UTF-8') > $limit) {
        $text = mb_substr($text, 0, $limit, 'UTF-8') . '…';
    }
    return $text;
}
