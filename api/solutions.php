<?php
/**
 * 前台行业方案接口（无需登录）
 *
 * GET api/solutions.php?action=list&page=1&limit=12
 *     -> { ok:true, data:[...], total, page, pages }
 * GET api/solutions.php?action=detail&id=1
 *     -> { ok:true, data:{...}, prev:{...}|null, next:{...}|null }
 */
require __DIR__ . '/../inc/bootstrap.php';

session_start();

$pdo = db();
$action = (string) ($_GET['action'] ?? 'list');

/** 封面兜底：未设置封面时给一张默认图 */
function solution_cover(string $cover): string
{
    $cover = trim($cover);
    return $cover === '' ? 'images/solutions-hero.jpg' : $cover;
}

/** 亮点文本（每行一条）转成数组 */
function solution_highlights(?string $text): array
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

try {
    // ---------- 详情 ----------
    if ($action === 'detail') {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            // 统一 200 + ok:false：非 200 响应会被 nginx 错误页覆盖，前端拿不到 JSON
            json_out(['ok' => false, 'message' => '参数错误']);
        }

        $st = $pdo->prepare('SELECT * FROM solutions WHERE id = ? AND status = 1');
        $st->execute([$id]);
        $row = $st->fetch();

        if (!$row) {
            json_out(['ok' => false, 'message' => '方案不存在或已下架']);
        }

        // 浏览量自增
        $pdo->prepare('UPDATE solutions SET views = views + 1 WHERE id = ?')->execute([$id]);

        // 上一篇 / 下一篇：与列表顺序一致（权重降序、id 升序）
        $prev = $pdo->prepare(
            'SELECT id, title FROM solutions
             WHERE status = 1 AND (sort_weight > ? OR (sort_weight = ? AND id < ?))
             ORDER BY sort_weight ASC, id DESC LIMIT 1'
        );
        $prev->execute([$row['sort_weight'], $row['sort_weight'], $row['id']]);

        $next = $pdo->prepare(
            'SELECT id, title FROM solutions
             WHERE status = 1 AND (sort_weight < ? OR (sort_weight = ? AND id > ?))
             ORDER BY sort_weight DESC, id ASC LIMIT 1'
        );
        $next->execute([$row['sort_weight'], $row['sort_weight'], $row['id']]);

        json_out([
            'ok' => true,
            'data' => [
                'id'         => (int) $row['id'],
                'title'      => $row['title'],
                'en_title'   => $row['en_title'],
                'cover'      => solution_cover($row['cover']),
                'summary'    => $row['summary'],
                'highlights' => solution_highlights($row['highlights']),
                'content'    => $row['content'] ?? '',
                'views'      => (int) $row['views'],
            ],
            'prev' => $prev->fetch() ?: null,
            'next' => $next->fetch() ?: null,
        ]);
    }

    // ---------- 列表 ----------
    $page  = max(1, (int) ($_GET['page'] ?? 1));
    $limit = (int) ($_GET['limit'] ?? 12);
    $limit = max(1, min(60, $limit));

    $countStmt = $pdo->prepare('SELECT COUNT(*) FROM solutions WHERE status = 1');
    $countStmt->execute();
    $total = (int) $countStmt->fetchColumn();

    $pages = max(1, (int) ceil($total / $limit));
    $page = min($page, $pages);
    $offset = ($page - 1) * $limit;

    $listStmt = $pdo->prepare(
        "SELECT id, title, en_title, cover, summary, views
         FROM solutions WHERE status = 1
         ORDER BY sort_weight DESC, id ASC
         LIMIT $limit OFFSET $offset"
    );
    $listStmt->execute();

    $items = array_map(static function (array $r): array {
        return [
            'id'       => (int) $r['id'],
            'title'    => $r['title'],
            'en_title' => $r['en_title'],
            'cover'    => solution_cover($r['cover']),
            'summary'  => $r['summary'],
            'views'    => (int) $r['views'],
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
    json_out(['ok' => false, 'message' => '方案读取失败'], 500);
}
