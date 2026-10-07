<?php
/**
 * 前台产品中心接口（无需登录）
 *
 * GET api/products.php?action=list&page=1&limit=12
 *     -> { ok:true, data:[...], total, page, pages }
 * GET api/products.php?action=detail&id=1
 *     -> { ok:true, data:{...}, prev:{...}|null, next:{...}|null }
 */
require __DIR__ . '/../inc/bootstrap.php';

session_start();

$pdo = db();
$action = (string) ($_GET['action'] ?? 'list');

/** 封面兜底 */
function product_cover(array $r): string
{
    $cover = trim((string) ($r['cover'] ?? ''));
    if ($cover !== '') {
        return $cover;
    }
    // 未设置封面时，按 slug 猜测 images/products/{slug}.jpg
    $slug = trim((string) ($r['slug'] ?? ''));
    return $slug !== '' ? 'images/products/' . $slug . '.jpg' : 'images/products-hero.jpg';
}

/** 多行文本转数组 */
function product_lines(?string $text): array
{
    $text = (string) $text;
    if (trim($text) === '') {
        return [];
    }
    $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
    $out = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line !== '') {
            $out[] = $line;
        }
    }
    return $out;
}

/** 规格参数：每行「数值|名称」 */
function product_specs(?string $text): array
{
    $out = [];
    foreach (product_lines($text) as $line) {
        $parts = explode('|', $line, 2);
        if (count($parts) === 2) {
            $out[] = ['value' => trim($parts[0]), 'name' => trim($parts[1])];
        } else {
            $out[] = ['value' => $line, 'name' => ''];
        }
    }
    return $out;
}

try {
    // ---------- 详情 ----------
    if ($action === 'detail') {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            // 统一 200 + ok:false：非 200 会被 nginx 错误页覆盖，前端拿不到 JSON
            json_out(['ok' => false, 'message' => '参数错误']);
        }

        $st = $pdo->prepare('SELECT * FROM products WHERE id = ? AND status = 1');
        $st->execute([$id]);
        $row = $st->fetch();

        if (!$row) {
            json_out(['ok' => false, 'message' => '产品不存在或已下架']);
        }

        // 浏览量自增
        $pdo->prepare('UPDATE products SET views = views + 1 WHERE id = ?')->execute([$id]);

        // 上一篇 / 下一篇（与列表顺序一致：权重降序、id 升序），用于「其他产品」
        $prev = $pdo->prepare(
            'SELECT id, title, en_title, cover, short_desc, features
             FROM products
             WHERE status = 1 AND (sort_weight > ? OR (sort_weight = ? AND id < ?))
             ORDER BY sort_weight ASC, id DESC LIMIT 1'
        );
        $prev->execute([$row['sort_weight'], $row['sort_weight'], $row['id']]);

        $next = $pdo->prepare(
            'SELECT id, title, en_title, cover, short_desc, features
             FROM products
             WHERE status = 1 AND (sort_weight < ? OR (sort_weight = ? AND id > ?))
             ORDER BY sort_weight DESC, id ASC LIMIT 1'
        );
        $next->execute([$row['sort_weight'], $row['sort_weight'], $row['id']]);

        $prevRow = $prev->fetch() ?: null;
        $nextRow = $next->fetch() ?: null;
        $toCard = static function (?array $r): ?array {
            if (!$r) {
                return null;
            }
            return [
                'id'         => (int) $r['id'],
                'title'      => $r['title'],
                'en'         => $r['en_title'],
                'cover'      => product_cover($r),
                'shortDesc'  => $r['short_desc'],
                'features'   => product_lines($r['features']),
            ];
        };

        json_out([
            'ok' => true,
            'data' => [
                'id'            => (int) $row['id'],
                'title'         => $row['title'],
                'en'            => $row['en_title'],
                'cover'         => product_cover($row),
                'shortDesc'     => $row['short_desc'],
                'features'      => product_lines($row['features']),
                'detail_tag'    => $row['detail_tag'],
                'detail_heading'=> $row['detail_heading'],
                'detail_desc'   => $row['detail_desc'] ?? '',
                'detail_list'   => product_lines($row['detail_list']),
                'specs'         => product_specs($row['specs']),
                'content'       => $row['content'] ?? '',
                'views'         => (int) $row['views'],
            ],
            'prev' => $toCard($prevRow),
            'next' => $toCard($nextRow),
        ]);
    }

    // ---------- 列表 ----------
    $page  = max(1, (int) ($_GET['page'] ?? 1));
    $limit = (int) ($_GET['limit'] ?? 12);
    $limit = max(1, min(60, $limit));

    $total = (int) $pdo->query('SELECT COUNT(*) FROM products WHERE status = 1')->fetchColumn();

    $pages = max(1, (int) ceil($total / $limit));
    $page = min($page, $pages);
    $offset = ($page - 1) * $limit;

    $listStmt = $pdo->prepare(
        "SELECT id, slug, title, en_title, cover, short_desc, features, views
         FROM products WHERE status = 1
         ORDER BY sort_weight DESC, id ASC
         LIMIT $limit OFFSET $offset"
    );
    $listStmt->execute();

    $items = array_map(static function (array $r): array {
        return [
            'id'        => (int) $r['id'],
            'title'     => $r['title'],
            'en'        => $r['en_title'],
            'cover'     => product_cover($r),
            'shortDesc' => $r['short_desc'],
            'features'  => product_lines($r['features']),
            'views'     => (int) $r['views'],
        ];
    }, $listStmt->fetchAll());

    json_out([
        'ok' => true,
        'data' => $items,
        'total' => $total,
        'page' => $page,
        'pages' => $pages,
    ]);
} catch (Throwable $e) {
    json_out(['ok' => false, 'message' => '产品读取失败'], 500);
}
