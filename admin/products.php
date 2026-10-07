<?php
/**
 * 产品中心管理：列表 / 搜索 / 排序置顶 / 上下架 / 删除 / 新增 / 编辑
 * 规格参数按「数值|名称」每行一条填写；补充说明为可选富文本
 */
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/admin_layout.php';

session_start();
require_admin();

$pdo = db();
$flash = null;
$flashType = 'success';

/**
 * 已有封面素材：列出 images/products 下的图片，供直接选用
 */
function product_available_covers(): array
{
    $base = __DIR__ . '/../images/products';
    if (!is_dir($base)) {
        return [];
    }
    $files = array_values(array_filter((array) scandir($base), static function (string $f) use ($base): bool {
        return $f !== '.' && $f !== '..' && is_file($base . '/' . $f)
            && preg_match('#\.(jpe?g|png|gif|webp)$#i', $f);
    }));
    sort($files, SORT_NATURAL);
    return array_map(static fn(string $f) => 'images/products/' . $f, $files);
}

/** 未填 slug 时生成一个不重复标识 */
function product_make_slug(PDO $pdo): string
{
    do {
        $slug = 'p-' . date('Ymd') . '-' . bin2hex(random_bytes(4));
        $st = $pdo->prepare('SELECT 1 FROM products WHERE slug = ? LIMIT 1');
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
                $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
                $flash = '产品已删除';
            }

            // --- 上下架 / 置顶 ---
        } elseif ($action === 'toggle_status') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                $pdo->prepare('UPDATE products SET status = 1 - status WHERE id = ?')->execute([$id]);
                $flash = '已切换发布状态';
            }
        } elseif ($action === 'toggle_top') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                $cur = $pdo->prepare('SELECT sort_weight FROM products WHERE id = ?');
                $cur->execute([$id]);
                $w = (int) $cur->fetchColumn();
                $new = $w >= 1000 ? 0 : 1000 + (int) $pdo->query('SELECT MAX(sort_weight) FROM products')->fetchColumn();
                $pdo->prepare('UPDATE products SET sort_weight = ? WHERE id = ?')->execute([$new, $id]);
                $flash = $new >= 1000 ? '已置顶到列表最前' : '已取消置顶';
            }

            // --- 保存（新增 / 编辑） ---
        } elseif ($action === 'save') {
            $id = (int) ($_POST['id'] ?? 0);

            $title    = trim((string) ($_POST['title'] ?? ''));
            $enTitle  = mb_substr(trim((string) ($_POST['en_title'] ?? '')), 0, 60, 'UTF-8');
            $cover    = trim((string) ($_POST['cover'] ?? ''));
            $short    = mb_substr(trim((string) ($_POST['short_desc'] ?? '')), 0, 500, 'UTF-8');
            $features = trim((string) ($_POST['features'] ?? ''));

            $detailTag     = mb_substr(trim((string) ($_POST['detail_tag'] ?? '')), 0, 60, 'UTF-8');
            $detailHeading = mb_substr(trim((string) ($_POST['detail_heading'] ?? '')), 0, 200, 'UTF-8');
            $detailDesc    = trim((string) ($_POST['detail_desc'] ?? ''));
            $detailList    = trim((string) ($_POST['detail_list'] ?? ''));
            $specs         = trim((string) ($_POST['specs'] ?? ''));
            $content       = sanitize_html((string) ($_POST['content'] ?? ''));

            $status = ((string) ($_POST['status'] ?? '1')) === '1' ? 1 : 0;
            $sortWeight = (int) ($_POST['sort_weight'] ?? 0);

            if ($title === '') {
                $flash = '产品名称不能为空';
                $flashType = 'error';
            } else {
                // 封面：只允许站内相对路径或 http(s)
                if ($cover !== '' && !preg_match('#^(https?://|images/)#i', $cover)) {
                    $cover = '';
                }
                // 详情页标签为空时用英文标识兜底
                if ($detailTag === '') {
                    $detailTag = $enTitle;
                }

                if ($id > 0) {
                    $st = $pdo->prepare(
                        'UPDATE products
                         SET title = ?, en_title = ?, cover = ?, short_desc = ?, features = ?,
                             detail_tag = ?, detail_heading = ?, detail_desc = ?, detail_list = ?,
                             specs = ?, content = ?, status = ?, sort_weight = ?
                         WHERE id = ?'
                    );
                    $st->execute([
                        $title, $enTitle, $cover, $short, $features,
                        $detailTag, $detailHeading, $detailDesc, $detailList,
                        $specs, $content, $status, $sortWeight, $id,
                    ]);
                    $flash = '产品已更新';
                } else {
                    $slug = product_make_slug($pdo);
                    $st = $pdo->prepare(
                        'INSERT INTO products
                         (slug, title, en_title, cover, short_desc, features,
                          detail_tag, detail_heading, detail_desc, detail_list, specs, content, status, sort_weight)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                    );
                    $st->execute([
                        $slug, $title, $enTitle, $cover, $short, $features,
                        $detailTag, $detailHeading, $detailDesc, $detailList, $specs, $content, $status, $sortWeight,
                    ]);
                    $id = (int) $pdo->lastInsertId();
                    $flash = '产品已发布';
                }

                header('Location: products.php?saved=' . $id);
                exit;
            }
        }
    }
}

if (isset($_GET['saved'])) {
    $flash = $flash ?? '保存成功';
}

// ---------------- 编辑 / 新建表单 ----------------
$editId = (int) ($_GET['edit'] ?? 0);
$isNew = isset($_GET['new']);

if ($editId > 0 || $isNew) {
    $item = [
        'id' => 0,
        'title' => '',
        'en_title' => '',
        'cover' => '',
        'short_desc' => '',
        'features' => '',
        'detail_tag' => '',
        'detail_heading' => '',
        'detail_desc' => '',
        'detail_list' => '',
        'specs' => '',
        'content' => '',
        'status' => 1,
        'sort_weight' => 0,
    ];

    if ($editId > 0) {
        $st = $pdo->prepare('SELECT * FROM products WHERE id = ?');
        $st->execute([$editId]);
        $found = $st->fetch();
        if (!$found) {
            admin_header('产品编辑', 'products.php');
            echo '<div class="alert alert-error">产品不存在或已被删除</div>';
            echo '<a class="btn btn-ghost" href="products.php">返回列表</a>';
            admin_footer();
            exit;
        }
        $item = $found;
    }

    $covers = product_available_covers();

    admin_header($editId > 0 ? '编辑产品' : '新增产品', 'products.php');
    admin_flash($flash, $flashType);
    ?>
    <form method="post" id="productForm" class="news-form">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">

        <div class="card">
            <div class="form-grid">
                <div class="field">
                    <label for="titleInput">产品名称 <span class="req">*</span></label>
                    <input class="input" id="titleInput" name="title" type="text" maxlength="200"
                           required placeholder="如：HDI线路板"
                           value="<?= e($item['title']) ?>">
                </div>
                <div class="field">
                    <label for="enInput">英文标识</label>
                    <input class="input" id="enInput" name="en_title" type="text" maxlength="60"
                           placeholder="如：HDI BOARD"
                           value="<?= e($item['en_title']) ?>">
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
                <label>产品封面</label>
                <div class="cover-row">
                    <div class="cover-preview" id="coverPreview"
                         style="background-image:url('<?= $item['cover'] ? e($item['cover']) : '' ?>')">
                        <span class="cover-preview-tip" id="coverTip"><?= $item['cover'] ? '' : '尚未选择封面' ?></span>
                    </div>
                    <div class="cover-ops">
                        <input class="input" id="coverInput" name="cover" type="text"
                               placeholder="封面路径，如 images/products/hdi.jpg"
                               value="<?= e($item['cover']) ?>">
                        <div class="cover-btns">
                            <label class="btn btn-ghost" style="cursor:pointer">
                                上传新图
                                <input type="file" id="coverFile" accept="image/*" hidden>
                            </label>
                            <button type="button" class="btn btn-ghost" id="pickCoverBtn">选用已有图</button>
                        </div>
                        <div class="hint">支持 JPG / PNG / GIF / WebP，单张不超过 5MB，存到 images/products/</div>
                        <div class="cover-gallery" id="coverGallery" style="display:none">
                            <?php foreach ($covers as $c): ?>
                                <div class="cover-thumb" data-url="<?= e($c) ?>"
                                     style="background-image:url('../<?= e($c) ?>')" title="<?= e($c) ?>"></div>
                            <?php endforeach; ?>
                            <?php if (!$covers): ?>
                                <div class="hint">images/products 目录下暂无图片</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="field">
                <label for="shortInput">列表简介</label>
                <textarea class="input" id="shortInput" name="short_desc" rows="2"
                          maxlength="500" placeholder="产品卡片上显示的一句话简介"><?= e($item['short_desc']) ?></textarea>
            </div>

            <div class="field">
                <label for="featuresInput">卡片标签</label>
                <textarea class="input" id="featuresInput" name="features" rows="3"
                          placeholder="每行一个，如：任意层HDI"><?= e($item['features']) ?></textarea>
                <div class="hint">每行一个，展示在卡片下方的标签位，建议 3 个</div>
            </div>
        </div>

        <div class="card">
            <div class="field">
                <label>详情页内容</label>
                <div class="form-grid">
                    <div class="field">
                        <label for="tagInput">详情页英文标签</label>
                        <input class="input" id="tagInput" name="detail_tag" type="text" maxlength="60"
                               placeholder="如：HDI BOARD（留空则取英文标识）"
                               value="<?= e($item['detail_tag']) ?>">
                    </div>
                    <div class="field">
                        <label for="headingInput">详情页小标题</label>
                        <input class="input" id="headingInput" name="detail_heading" type="text" maxlength="200"
                               placeholder="如：HDI高密度互连线路板"
                               value="<?= e($item['detail_heading']) ?>">
                    </div>
                </div>
                <div class="field">
                    <label for="descInput">详情页描述</label>
                    <textarea class="input" id="descInput" name="detail_desc" rows="3"
                              placeholder="详情页顶部与产品介绍区的段落"><?= e($item['detail_desc']) ?></textarea>
                </div>
                <div class="field">
                    <label for="listInput">产品要点</label>
                    <textarea class="input" id="listInput" name="detail_list" rows="5"
                              placeholder="每行一条，详情页以列表形式展示"><?= e($item['detail_list']) ?></textarea>
                </div>
            </div>

            <div class="field">
                <label for="specsInput">规格参数</label>
                <textarea class="input" id="specsInput" name="specs" rows="7"
                          placeholder="每行一条，格式：数值|名称"><?= e($item['specs']) ?></textarea>
                <div class="hint">每行一条，竖线前为数值、竖线后为名称。示例：<code>1-28层|板层范围</code></div>
            </div>
        </div>

        <div class="card">
            <div class="field">
                <label>补充说明（可选）</label>
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
                <div class="hint">可留空。填写后展示在规格参数之后，用于补充工艺说明、应用案例等内容。</div>
            </div>
        </div>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit">保存产品</button>
            <a class="btn btn-ghost" href="products.php">取消</a>
            <?php if ($editId > 0): ?>
                <a class="link-btn" style="margin-left:auto"
                   href="<?= e(front_url('#/products/' . (int) $item['id'])) ?>" target="_blank" rel="noopener">在前台预览 →</a>
            <?php endif; ?>
        </div>
    </form>

    <script>
    (function () {
        var form = document.getElementById('productForm');
        var editor = document.getElementById('contentEditor');
        var field = document.getElementById('contentField');
        var csrf = '<?= e(csrf_token()) ?>';
        var UPLOAD_DIR = 'products';

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

        function uploadImage(file, done) {
            if (!file) return;
            if (file.size > 5 * 1024 * 1024) { alert('图片不能超过 5MB'); return; }
            var fd = new FormData();
            fd.append('file', file);
            fd.append('dir', UPLOAD_DIR);
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
            var node = this;
            uploadImage(this.files[0], function (j) {
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

        form.addEventListener('submit', function () {
            field.value = editor.innerHTML;
        });
    })();
    </script>
    <?php
    admin_footer();
    exit;
}

// ---------------- 列表 ----------------
$keyword = trim((string) ($_GET['q'] ?? ''));
$statusFilter = (string) ($_GET['status'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));

$where = [];
$params = [];
if ($keyword !== '') {
    $where[] = '(title LIKE :kw OR short_desc LIKE :kw OR en_title LIKE :kw)';
    $params[':kw'] = '%' . $keyword . '%';
}
if ($statusFilter === '1' || $statusFilter === '0') {
    $where[] = 'status = :st';
    $params[':st'] = (int) $statusFilter;
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM products $whereSql");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();

$pageSize = ADMIN_PAGE_SIZE;
$totalPages = max(1, (int) ceil($total / $pageSize));
$page = min($page, $totalPages);
$offset = ($page - 1) * $pageSize;

$listStmt = $pdo->prepare(
    "SELECT id, title, en_title, cover, short_desc, status, sort_weight, views, updated_at
     FROM products $whereSql
     ORDER BY sort_weight DESC, id ASC
     LIMIT $pageSize OFFSET $offset"
);
$listStmt->execute($params);
$rows = $listStmt->fetchAll();

$publishedCount = (int) $pdo->query('SELECT COUNT(*) FROM products WHERE status = 1')->fetchColumn();
$draftCount = (int) $pdo->query('SELECT COUNT(*) FROM products WHERE status = 0')->fetchColumn();

$queryBase = array_filter(['q' => $keyword, 'status' => $statusFilter], static fn($v) => $v !== '' && $v !== null);

admin_header('产品中心', 'products.php');
admin_flash($flash, $flashType);
?>

<div class="toolbar">
    <form class="form-inline" method="get">
        <input class="input" type="text" name="q" placeholder="搜索产品 / 简介" value="<?= e($keyword) ?>">
        <select class="input" name="status">
            <option value="">全部状态</option>
            <option value="1" <?= $statusFilter === '1' ? 'selected' : '' ?>>已发布</option>
            <option value="0" <?= $statusFilter === '0' ? 'selected' : '' ?>>草稿</option>
        </select>
        <button class="btn btn-ghost" type="submit">筛选</button>
        <?php if ($queryBase): ?>
            <a class="link-btn" href="products.php">清除</a>
        <?php endif; ?>
    </form>

    <a class="btn btn-primary" href="products.php?new=1">＋ 新增产品</a>
</div>

<div class="mini-stats">
    <span>共 <strong><?= $total ?></strong> 条</span>
    <span>已发布 <strong><?= $publishedCount ?></strong></span>
    <span>草稿 <strong><?= $draftCount ?></strong></span>
</div>

<div class="table-wrap">
    <?php if (!$rows): ?>
        <div class="empty">
            <div class="empty-mark">▤</div>
            <div><?= $queryBase ? '没有符合条件的产品' : '还没有产品，点击右上角新增第一个' ?></div>
        </div>
    <?php else: ?>
        <table class="admin-table">
            <thead>
            <tr>
                <th style="width:60px">封面</th>
                <th>产品名称</th>
                <th style="width:130px">英文标识</th>
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
                            <a class="row-title" href="products.php?edit=<?= (int) $r['id'] ?>"><?= e($r['title']) ?></a>
                        </div>
                        <div class="row-sub"><?= e(mb_substr($r['short_desc'], 0, 46, 'UTF-8')) ?></div>
                    </td>
                    <td class="nowrap" style="color:var(--a-text-2)"><?= e($r['en_title'] ?: '—') ?></td>
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
                            <a class="link-btn" href="products.php?edit=<?= (int) $r['id'] ?>">编辑</a>
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
                            <form method="post" onsubmit="return confirm('确定删除这个产品？删除后无法恢复。')">
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
            <a href="products.php?<?= http_build_query(array_merge($queryBase, ['page' => $page - 1])) ?>">上一页</a>
        <?php endif; ?>
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <?php if ($p === $page): ?>
                <span class="current"><?= $p ?></span>
            <?php else: ?>
                <a href="products.php?<?= http_build_query(array_merge($queryBase, ['page' => $p])) ?>"><?= $p ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
            <a href="products.php?<?= http_build_query(array_merge($queryBase, ['page' => $page + 1])) ?>">下一页</a>
        <?php endif; ?>
        <span class="pager-info">共 <?= $total ?> 条 · 第 <?= $page ?>/<?= $totalPages ?> 页</span>
    </div>
<?php else: ?>
    <div class="pager"><span class="pager-info">共 <?= $total ?> 个产品</span></div>
<?php endif; ?>

<?php
admin_footer();
