<?php
/**
 * 资讯中心管理：列表 / 搜索 / 筛选 / 排序置顶 / 上下架 / 删除 / 新增 / 编辑
 * 正文使用轻量富文本编辑器（contenteditable），保存时服务端统一走白名单过滤
 */
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/admin_layout.php';

session_start();
require_admin();

$pdo = db();
$flash = null;
$flashType = 'success';

/**
 * 已有封面素材：列出 images/news 下的图片，供直接选用
 */
function news_available_covers(): array
{
    $base = __DIR__ . '/../images/news';
    if (!is_dir($base)) {
        return [];
    }
    $files = array_values(array_filter((array) scandir($base), static function (string $f) use ($base): bool {
        return $f !== '.' && $f !== '..' && is_file($base . '/' . $f)
            && preg_match('#\.(jpe?g|png|gif|webp)$#i', $f);
    }));
    sort($files, SORT_NATURAL);
    return array_map(static fn(string $f) => 'images/news/' . $f, $files);
}

/** 未 slug 时生成一个不重复标识 */
function make_slug(PDO $pdo): string
{
    do {
        $slug = 'n-' . date('Ymd') . '-' . bin2hex(random_bytes(4));
        $st = $pdo->prepare('SELECT 1 FROM news WHERE slug = ? LIMIT 1');
        $st->execute([$slug]);
    } while ($st->fetchColumn());
    return $slug;
}

// ---------------- 写操作 ----------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $flash = '页面已过期，请刷新后重试';
        $flashType = 'error';
    } else {
        $action = (string) ($_POST['action'] ?? '');

        // --- 删除 ---
        if ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                $st = $pdo->prepare('DELETE FROM news WHERE id = ?');
                $st->execute([$id]);
                $flash = '资讯已删除';
            }

            // --- 上下架 / 置顶 ---
        } elseif ($action === 'toggle_status') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                $pdo->prepare('UPDATE news SET status = 1 - status WHERE id = ?')->execute([$id]);
                $flash = '已切换发布状态';
            }
        } elseif ($action === 'toggle_top') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                $cur = $pdo->prepare('SELECT sort_weight FROM news WHERE id = ?');
                $cur->execute([$id]);
                $w = (int) $cur->fetchColumn();
                $new = $w >= 1000 ? 0 : 1000 + (int) $pdo->query('SELECT MAX(sort_weight) FROM news')->fetchColumn();
                $pdo->prepare('UPDATE news SET sort_weight = ? WHERE id = ?')->execute([$new, $id]);
                $flash = $new >= 1000 ? '已置顶到列表最前' : '已取消置顶';
            }

            // --- 保存（新增 / 编辑） ---
        } elseif ($action === 'save') {
            $id = (int) ($_POST['id'] ?? 0);

            $title = trim((string) ($_POST['title'] ?? ''));
            $tagInput = trim((string) ($_POST['tag_select'] ?? ''));
            $tagCustom = trim((string) ($_POST['tag_custom'] ?? ''));
            $tag = $tagInput === '__custom__' ? $tagCustom : $tagInput;
            $tag = mb_substr($tag, 0, 30, 'UTF-8');

            $cover = trim((string) ($_POST['cover'] ?? ''));
            $excerptInput = trim((string) ($_POST['excerpt'] ?? ''));
            $rawContent = (string) ($_POST['content'] ?? '');
            $content = sanitize_html($rawContent);

            $published = trim((string) ($_POST['published_date'] ?? ''));
            if (!preg_match('#^\d{4}-\d{2}-\d{2}$#', $published)) {
                $published = date('Y-m-d');
            }
            $status = ((string) ($_POST['status'] ?? '1')) === '1' ? 1 : 0;
            $sortWeight = (int) ($_POST['sort_weight'] ?? 0);

            if ($title === '') {
                $flash = '标题不能为空';
                $flashType = 'error';
            } else {
                // 摘要为空时自动从正文提取
                $excerpt = $excerptInput !== ''
                    ? mb_substr($excerptInput, 0, 500, 'UTF-8')
                    : html_summary($content, 100);

                // 封面：只允许站内相对路径或 http(s)，避免误填危险串
                if ($cover !== '' && !preg_match('#^(https?://|images/)#i', $cover)) {
                    $cover = '';
                }

                if ($id > 0) {
                    $st = $pdo->prepare(
                        'UPDATE news
                         SET title = ?, tag = ?, cover = ?, excerpt = ?, content = ?,
                             published_date = ?, status = ?, sort_weight = ?
                         WHERE id = ?'
                    );
                    $st->execute([$title, $tag, $cover, $excerpt, $content, $published, $status, $sortWeight, $id]);
                    $flash = '资讯已更新';
                } else {
                    $slug = make_slug($pdo);
                    $st = $pdo->prepare(
                        'INSERT INTO news
                         (slug, title, tag, cover, excerpt, content, published_date, status, sort_weight)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
                    );
                    $st->execute([$slug, $title, $tag, $cover, $excerpt, $content, $published, $status, $sortWeight]);
                    $id = (int) $pdo->lastInsertId();
                    $flash = '资讯已发布';
                }

                header('Location: news.php?saved=' . $id);
                exit;
            }
        }
    }
}

if (isset($_GET['saved'])) {
    $flash = $flash ?? ($_SESSION['lex_news_saved'] ?? '保存成功');
}

// ---------------- 编辑 / 新建表单 ----------------
$editId = (int) ($_GET['edit'] ?? 0);
$isNew = isset($_GET['new']);

if ($editId > 0 || $isNew) {
    $item = [
        'id' => 0,
        'title' => '',
        'tag' => '企业动态',
        'cover' => '',
        'excerpt' => '',
        'content' => '',
        'published_date' => date('Y-m-d'),
        'status' => 1,
        'sort_weight' => 0,
    ];

    if ($editId > 0) {
        $st = $pdo->prepare('SELECT * FROM news WHERE id = ?');
        $st->execute([$editId]);
        $found = $st->fetch();
        if (!$found) {
            admin_header('资讯编辑', 'news.php');
            echo '<div class="alert alert-error">资讯不存在或已被删除</div>';
            echo '<a class="btn btn-ghost" href="news.php">返回列表</a>';
            admin_footer();
            exit;
        }
        $item = $found;
    }

    $covers = news_available_covers();
    $tagOptions = ['企业动态', '技术前沿', '合作动态', '资质荣誉', '展会动态', '行业洞察'];

    admin_header($editId > 0 ? '编辑资讯' : '发布资讯', 'news.php');
    admin_flash($flash, $flashType);
    ?>
    <form method="post" id="newsForm" class="news-form">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">

        <div class="card">
            <div class="field">
                <label for="titleInput">标题 <span class="req">*</span></label>
                <input class="input" id="titleInput" name="title" type="text" maxlength="200"
                       required placeholder="请输入资讯标题"
                       value="<?= e($item['title']) ?>">
            </div>

            <div class="form-grid">
                <div class="field">
                    <label for="tagSelect">分类标签</label>
                    <select class="input" id="tagSelect" name="tag_select">
                        <?php $tagKnown = in_array($item['tag'], $tagOptions, true) || $item['tag'] === ''; ?>
                        <?php foreach ($tagOptions as $opt): ?>
                            <option value="<?= e($opt) ?>" <?= $item['tag'] === $opt ? 'selected' : '' ?>><?= e($opt) ?></option>
                        <?php endforeach; ?>
                        <option value="__custom__" <?= !$tagKnown ? 'selected' : '' ?>>自定义…</option>
                    </select>
                    <input class="input" id="tagCustom" name="tag_custom" type="text" maxlength="30"
                           placeholder="自定义分类"
                           value="<?= $tagKnown ? '' : e($item['tag']) ?>"
                           style="margin-top:8px;display:<?= $tagKnown ? 'none' : 'block' ?>">
                </div>

                <div class="field">
                    <label for="dateInput">发布日期</label>
                    <input class="input" id="dateInput" name="published_date" type="date"
                           value="<?= e($item['published_date']) ?>">
                </div>

                <div class="field">
                    <label for="statusSelect">状态</label>
                    <select class="input" id="statusSelect" name="status">
                        <option value="1" <?= $item['status'] ? 'selected' : '' ?>>立即发布</option>
                        <option value="0" <?= !$item['status'] ? 'selected' : '' ?>>存为草稿</option>
                    </select>
                </div>

                <div class="field">
                    <label for="weightInput">排序权重</label>
                    <input class="input" id="weightInput" name="sort_weight" type="number"
                           value="<?= (int) $item['sort_weight'] ?>">
                    <div class="hint">数字越大越靠前，置顶会自动填 1000+</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="field">
                <label>封面图片</label>
                <div class="cover-row">
                    <div class="cover-preview" id="coverPreview"
                         style="background-image:url('<?= $item['cover'] ? e($item['cover']) : '' ?>')">
                        <span class="cover-preview-tip" id="coverTip"><?= $item['cover'] ? '' : '尚未选择封面' ?></span>
                    </div>
                    <div class="cover-ops">
                        <input class="input" id="coverInput" name="cover" type="text"
                               placeholder="封面路径，如 images/news/news-1.jpg"
                               value="<?= e($item['cover']) ?>">
                        <div class="cover-btns">
                            <label class="btn btn-ghost" style="cursor:pointer">
                                上传新图
                                <input type="file" id="coverFile" accept="image/*" hidden>
                            </label>
                            <button type="button" class="btn btn-ghost" id="pickCoverBtn">选用已有图</button>
                        </div>
                        <div class="hint">支持 JPG / PNG / GIF / WebP，单张不超过 5MB</div>
                        <div class="cover-gallery" id="coverGallery" style="display:none">
                            <?php foreach ($covers as $c): ?>
                                <div class="cover-thumb" data-url="<?= e($c) ?>"
                                     style="background-image:url('../<?= e($c) ?>')" title="<?= e($c) ?>"></div>
                            <?php endforeach; ?>
                            <?php if (!$covers): ?>
                                <div class="hint">images/news 目录下暂无图片</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="field">
                <label for="excerptInput">摘要</label>
                <textarea class="input" id="excerptInput" name="excerpt" rows="2"
                          maxlength="500" placeholder="列表页显示的简短摘要，留空会自动从正文提取"><?= e($item['excerpt']) ?></textarea>
            </div>
        </div>

        <div class="card">
            <div class="field">
                <label>正文内容</label>
                <div class="editor-toolbar" id="editorToolbar">
                    <button type="button" data-cmd="bold" title="加粗"><b>B</b></button>
                    <button type="button" data-cmd="italic" title="斜体"><i>I</i></button>
                    <button type="button" data-cmd="underline" title="下划线"><u>U</u></button>
                    <span class="tb-sep"></span>
                    <button type="button" data-cmd="formatBlock" data-arg="h3" title="小标题">H3</button>
                    <button type="button" data-cmd="formatBlock" data-arg="p" title="正文段落">正文</button>
                    <span class="tb-sep"></span>
                    <button type="button" data-cmd="insertUnorderedList" title="无序列表">• 列表</button>
                    <button type="button" data-cmd="insertOrderedList" title="有序列表">1. 列表</button>
                    <span class="tb-sep"></span>
                    <button type="button" data-cmd="createLink" title="插入链接">链接</button>
                    <button type="button" id="insertImageBtn" title="插入图片">图片</button>
                    <span class="tb-sep"></span>
                    <button type="button" data-cmd="removeFormat" title="清除格式">清格式</button>
                    <input type="file" id="inlineFile" accept="image/*" hidden>
                </div>
                <div class="editor" id="contentEditor" contenteditable="true"><?= $item['content'] ?></div>
                <textarea name="content" id="contentField" hidden></textarea>
                <div class="hint">编辑完成后直接点下方“保存”。支持从 Word 或网页复制粘贴，格式会自动简化。</div>
            </div>
        </div>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit">保存资讯</button>
            <a class="btn btn-ghost" href="news.php">取消</a>
            <?php if ($editId > 0): ?>
                <a class="link-btn" style="margin-left:auto"
                   href="<?= e(front_url('#/news/' . (int) $item['id'])) ?>" target="_blank" rel="noopener">在前台预览 →</a>
            <?php endif; ?>
        </div>
    </form>

    <script>
    (function () {
        var form = document.getElementById('newsForm');
        var editor = document.getElementById('contentEditor');
        var field = document.getElementById('contentField');

        // 粘贴内容时去掉外站样式，只留干净的段落与行内格式
        editor.addEventListener('paste', function (ev) {
            ev.preventDefault();
            var text = (ev.clipboardData || window.clipboardData).getData('text/plain') || '';
            var html = (ev.clipboardData || window.clipboardData).getData('text/html') || '';
            if (html) {
                var box = document.createElement('div');
                box.innerHTML = html;
                box.querySelectorAll('script,style,iframe').forEach(function (n) { n.remove(); });
                box.querySelectorAll('*').forEach(function (n) {
                    n.removeAttribute('style'); n.removeAttribute('class'); n.removeAttribute('id');
                });
                document.execCommand('insertHTML', false, box.innerHTML);
            } else {
                var parts = text.split(/\n{2,}/);
                var out = parts.map(function (p) {
                    return '<p>' + p.replace(/&/g, '&amp;').replace(/</g, '&lt;')
                                    .replace(/\n/g, '<br>') + '</p>';
                }).join('');
                document.execCommand('insertHTML', false, out);
            }
        });

        // 工具栏
        var csrf = '<?= e(csrf_token()) ?>';
        document.getElementById('editorToolbar').addEventListener('click', function (ev) {
            var btn = ev.target.closest('button[data-cmd]');
            if (!btn) return;
            var cmd = btn.dataset.cmd, arg = btn.dataset.arg || null;
            if (cmd === 'createLink') {
                var url = prompt('请输入链接地址（http:// 开头）');
                if (url) document.execCommand('createLink', false, url);
                return;
            }
            document.execCommand(cmd, false, arg);
            editor.focus();
        });

        // 上传并插入图片（封面与正文共用）
        function uploadImage(file, done) {
            if (!file) return;
            if (file.size > 5 * 1024 * 1024) { alert('图片不能超过 5MB'); return; }
            var fd = new FormData();
            fd.append('file', file);
            fd.append('csrf_token', csrf);
            var host = location.pathname.replace(/\/admin\/[^/]*$/, '');
            fetch(host + '/admin/upload.php', { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (j) { done(j); })
                .catch(function () { alert('上传失败，请重试'); });
        }

        document.getElementById('insertImageBtn').addEventListener('click', function () {
            document.getElementById('inlineFile').click();
        });
        document.getElementById('inlineFile').addEventListener('change', function () {
            var file = this.files[0];
            var node = this;
            uploadImage(file, function (j) {
                if (j.ok) {
                    editor.focus();
                    document.execCommand('insertHTML', false,
                        '<p><img src="' + j.url + '" alt="" style="max-width:100%"></p><p><br></p>');
                } else {
                    alert(j.message || '上传失败');
                }
                node.value = '';
            });
        });

        // 封面上传
        var coverInput = document.getElementById('coverInput');
        var preview = document.getElementById('coverPreview');
        var tip = document.getElementById('coverTip');
        function setCover(url) {
            coverInput.value = url;
            if (url) {
                preview.style.backgroundImage = "url('../" + url.replace(/^\/?images\//, 'images/') + "')";
                tip.textContent = '';
            } else {
                preview.style.backgroundImage = 'none';
                tip.textContent = '尚未选择封面';
            }
        }
        setCover(coverInput.value);
        coverInput.addEventListener('input', function () { setCover(this.value.trim()); });

        document.getElementById('coverFile').addEventListener('change', function () {
            var node = this;
            uploadImage(this.files[0], function (j) {
                if (j.ok) { setCover(j.url); } else { alert(j.message || '上传失败'); }
                node.value = '';
            });
        });

        var gallery = document.getElementById('coverGallery');
        document.getElementById('pickCoverBtn').addEventListener('click', function () {
            gallery.style.display = gallery.style.display === 'none' ? 'flex' : 'none';
        });
        gallery.addEventListener('click', function (ev) {
            var th = ev.target.closest('.cover-thumb');
            if (th) { setCover(th.dataset.url); gallery.style.display = 'none'; }
        });

        // 自定义标签显隐
        var tagSelect = document.getElementById('tagSelect');
        var tagCustom = document.getElementById('tagCustom');
        function syncTag() {
            tagCustom.style.display = tagSelect.value === '__custom__' ? 'block' : 'none';
        }
        tagSelect.addEventListener('change', syncTag);
        syncTag();

        // 提交：把富文本内容同步到隐藏字段
        form.addEventListener('submit', function (ev) {
            field.value = editor.innerHTML;
            if (editor.textContent.trim() === '' && !editor.querySelector('img')) {
                ev.preventDefault();
                alert('请填写正文内容');
                editor.focus();
                return;
            }
            if (tagSelect.value === '__custom__' && tagCustom.value.trim() === '') {
                tagCustom.value = '未分类';
            }
        });
    })();
    </script>
    <?php
    admin_footer();
    exit;
}

// ---------------- 列表 ----------------
$keyword = trim((string) ($_GET['q'] ?? ''));
$tagFilter = trim((string) ($_GET['tag'] ?? ''));
$statusFilter = (string) ($_GET['status'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));

$where = [];
$params = [];
if ($keyword !== '') {
    $where[] = '(title LIKE :kw OR excerpt LIKE :kw)';
    $params[':kw'] = '%' . $keyword . '%';
}
if ($tagFilter !== '') {
    $where[] = 'tag = :tag';
    $params[':tag'] = $tagFilter;
}
if ($statusFilter === '1' || $statusFilter === '0') {
    $where[] = 'status = :st';
    $params[':st'] = (int) $statusFilter;
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM news $whereSql");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();

$pageSize = ADMIN_PAGE_SIZE;
$totalPages = max(1, (int) ceil($total / $pageSize));
$page = min($page, $totalPages);
$offset = ($page - 1) * $pageSize;

$listStmt = $pdo->prepare(
    "SELECT id, title, tag, cover, excerpt, published_date, status, sort_weight, views, updated_at
     FROM news $whereSql
     ORDER BY sort_weight DESC, published_date DESC, id DESC
     LIMIT $pageSize OFFSET $offset"
);
$listStmt->execute($params);
$rows = $listStmt->fetchAll();

$allTags = $pdo->query('SELECT DISTINCT tag FROM news WHERE tag <> "" ORDER BY tag')->fetchAll(PDO::FETCH_COLUMN);

$publishedCount = (int) $pdo->query('SELECT COUNT(*) FROM news WHERE status = 1')->fetchColumn();
$draftCount = (int) $pdo->query('SELECT COUNT(*) FROM news WHERE status = 0')->fetchColumn();

$queryBase = array_filter(['q' => $keyword, 'tag' => $tagFilter, 'status' => $statusFilter], static fn($v) => $v !== '' && $v !== null);

admin_header('资讯中心', 'news.php');
admin_flash($flash, $flashType);
?>

<div class="toolbar">
    <form class="form-inline" method="get">
        <input class="input" type="text" name="q" placeholder="搜索标题 / 摘要" value="<?= e($keyword) ?>">
        <select class="input" name="tag">
            <option value="">全部分类</option>
            <?php foreach ($allTags as $t): ?>
                <option value="<?= e($t) ?>" <?= $tagFilter === $t ? 'selected' : '' ?>><?= e($t) ?></option>
            <?php endforeach; ?>
        </select>
        <select class="input" name="status">
            <option value="">全部状态</option>
            <option value="1" <?= $statusFilter === '1' ? 'selected' : '' ?>>已发布</option>
            <option value="0" <?= $statusFilter === '0' ? 'selected' : '' ?>>草稿</option>
        </select>
        <button class="btn btn-ghost" type="submit">筛选</button>
        <?php if ($queryBase): ?>
            <a class="link-btn" href="news.php">清除</a>
        <?php endif; ?>
    </form>

    <a class="btn btn-primary" href="news.php?new=1">＋ 发布新资讯</a>
</div>

<div class="mini-stats">
    <span>共 <strong><?= $total ?></strong> 条</span>
    <span>已发布 <strong><?= $publishedCount ?></strong></span>
    <span>草稿 <strong><?= $draftCount ?></strong></span>
</div>

<div class="table-wrap">
    <?php if (!$rows): ?>
        <div class="empty">
            <div class="empty-mark">✎</div>
            <div><?= $queryBase ? '没有符合条件的资讯' : '还没有资讯，点击右上角发布第一条' ?></div>
        </div>
    <?php else: ?>
        <table class="admin-table">
            <thead>
            <tr>
                <th style="width:60px">封面</th>
                <th>标题</th>
                <th style="width:110px">分类</th>
                <th style="width:110px">发布日期</th>
                <th style="width:70px">浏览</th>
                <th style="width:90px">状态</th>
                <th style="width:170px">操作</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td>
                        <?php if ($r['cover']): ?>
                            <div class="list-thumb" style="background-image:url('../<?= e($r['cover']) ?>')"></div>
                        <?php else: ?>
                            <div class="list-thumb is-empty">无</div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:6px">
                            <?php if ((int) $r['sort_weight'] >= 1000): ?><span class="pin">置顶</span><?php endif; ?>
                            <a class="row-title" href="news.php?edit=<?= (int) $r['id'] ?>"><?= e($r['title']) ?></a>
                        </div>
                        <div class="row-sub"><?= e(mb_substr($r['excerpt'], 0, 46, 'UTF-8')) ?></div>
                    </td>
                    <td><span class="tag"><?= e($r['tag'] ?: '未分类') ?></span></td>
                    <td class="nowrap" style="color:var(--a-text-2)"><?= e($r['published_date']) ?></td>
                    <td class="nowrap" style="color:var(--a-text-2)"><?= (int) $r['views'] ?></td>
                    <td>
                        <?php if ($r['status']): ?>
                            <span class="tag tag-read" style="color:#7fd4a8">已发布</span>
                        <?php else: ?>
                            <span class="tag tag-read">草稿</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="row-actions">
                            <a class="link-btn" href="news.php?edit=<?= (int) $r['id'] ?>">编辑</a>
                            <form method="post">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                <input type="hidden" name="action" value="toggle_status">
                                <button class="link-btn" type="submit"><?= $r['status'] ? '下架' : '发布' ?></button>
                            </form>
                            <form method="post">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                <input type="hidden" name="action" value="toggle_top">
                                <button class="link-btn" type="submit"><?= (int) $r['sort_weight'] >= 1000 ? '取消置顶' : '置顶' ?></button>
                            </form>
                            <form method="post" onsubmit="return confirm('确定删除这条资讯？删除后无法恢复。')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
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
            <a href="news.php?<?= http_build_query(array_merge($queryBase, ['page' => $page - 1])) ?>">上一页</a>
        <?php endif; ?>
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <?php if ($p === $page): ?>
                <span class="current"><?= $p ?></span>
            <?php else: ?>
                <a href="news.php?<?= http_build_query(array_merge($queryBase, ['page' => $p])) ?>"><?= $p ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
            <a href="news.php?<?= http_build_query(array_merge($queryBase, ['page' => $page + 1])) ?>">下一页</a>
        <?php endif; ?>
        <span class="pager-info">共 <?= $total ?> 条 · 第 <?= $page ?>/<?= $totalPages ?> 页</span>
    </div>
<?php else: ?>
    <div class="pager"><span class="pager-info">共 <?= $total ?> 条资讯</span></div>
<?php endif; ?>

<?php
admin_footer();
