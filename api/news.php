<?php
/**
 * 前台资讯接口（无需登录）
 *
 * GET api/news.php?action=list&page=1&limit=9&tag=企业动态
 *     -> { ok:true, data:[...], total, page, pages }
 * GET api/news.php?action=detail&id=1
 *     -> { ok:true, data:{...}, prev:{...}|null, next:{...}|null }
 * GET api/news.php?action=tags
 *     -> { ok:true, data:["企业动态", ...] }
 */
require __DIR__ . '/../inc/bootstrap.php';

session_start();

$pdo = db();
$action = (string) ($_GET['action'] ?? 'list');

// 封面兜底：封面为空时给一张默认的资讯图
function cover_url(string $cover): string
{
    $cover = trim($cover);
    if ($cover === '') {
        return 'images/news-hero.jpg';
    }
    // 站外图片直接放行；站内相对路径保持原样
    return $cover;
}

try {
    if ($action === 'tags') {
        $rows = $pdo->query(
            'SELECT tag, COUNT(*) AS cnt FROM news WHERE status = 1 AND tag <> ""
             GROUP BY tag ORDER BY cnt DESC'
        )->fetchAll();
        json_out(['ok' => true, 'data' => $rows]);
    }

    if ($action === 'detail') {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            // 一律 200 + ok:false：nginx 会用自带错误页覆盖非 200 的响应体，
            // 前端就拿不到 JSON，只能显示"加载失败"而不是准确原因
            json_out(['ok' => false, 'message' => '参数错误']);
        }

        $st = $pdo->prepare('SELECT * FROM news WHERE id = ? AND status = 1');
        $st->execute([$id]);
        $row = $st->fetch();

        if (!$row) {
            json_out(['ok' => false, 'message' => '资讯不存在或已下架']);
        }

        // 浏览量自增
        $pdo->prepare('UPDATE news SET views = views + 1 WHERE id = ?')->execute([$id]);

        // 上一篇 / 下一篇：按发布日期倒序
        $prev = $pdo->prepare(
            'SELECT id, title FROM news
             WHERE status = 1 AND (published_date > ? OR (published_date = ? AND id > ?))
             ORDER BY published_date ASC, id ASC LIMIT 1'
        );
        $prev->execute([$row['published_date'], $row['published_date'], $row['id']]);

        $next = $pdo->prepare(
            'SELECT id, title FROM news
             WHERE status = 1 AND (published_date < ? OR (published_date = ? AND id < ?))
             ORDER BY published_date DESC, id DESC LIMIT 1'
        );
        $next->execute([$row['published_date'], $row['published_date'], $row['id']]);

        $prevRow = $prev->fetch() ?: null;
        $nextRow = $next->fetch() ?: null;

        json_out([
            'ok' => true,
            'data' => [
                'id'          => (int) $row['id'],
                'title'       => $row['title'],
                'tag'         => $row['tag'],
                'cover'       => cover_url($row['cover']),
                'excerpt'     => $row['excerpt'],
                'content'     => $row['content'] ?? '',
                'date'        => $row['published_date'],
                'views'       => (int) $row['views'],
            ],
            'prev' => $prevRow,
            'next' => $nextRow,
        ]);
    }

    // ---------- 列表 ----------
    $page  = max(1, (int) ($_GET['page'] ?? 1));
    $limit = (int) ($_GET['limit'] ?? 9);
    $limit = max(1, min(30, $limit));
    $tag   = trim((string) ($_GET['tag'] ?? ''));

    $whereSql = 'WHERE status = 1';
    $params = [];
    if ($tag !== '') {
        $whereSql .= ' AND tag = :tag';
        $params[':tag'] = $tag;
    }

    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM news $whereSql");
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    $pages = max(1, (int) ceil($total / $limit));
    $page = min($page, $pages);
    $offset = ($page - 1) * $limit;

    $listStmt = $pdo->prepare(
        "SELECT id, title, tag, cover, excerpt, published_date, views
         FROM news $whereSql
         ORDER BY sort_weight DESC, published_date DESC, id DESC
         LIMIT $limit OFFSET $offset"
    );
    $listStmt->execute($params);

    $items = array_map(static function (array $r): array {
        return [
            'id'    => (int) $r['id'],
            'title' => $r['title'],
            'tag'   => $r['tag'],
            'img'   => cover_url($r['cover']),
            'excerpt' => $r['excerpt'],
            'date'  => $r['published_date'],
            'views' => (int) $r['views'],
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
    json_out(['ok' => false, 'message' => '资讯读取失败'], 500);
}
