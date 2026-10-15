<?php

declare(strict_types=1);

require __DIR__ . '/_bootstrap.php';
require_admin();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$post = null;
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM posts WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $post = $stmt->fetch();
    if (!$post) {
        flash('That post no longer exists.', 'error');
        admin_redirect('index.php');
    }
}

$defaults = [
    'title' => '', 'slug' => '', 'excerpt' => '', 'content' => '', 'cover_image' => '',
    'category' => '', 'author' => $_SESSION['admin_name'] ?? '', 'status' => 'draft',
    'meta_title' => '', 'meta_description' => '', 'published_at' => blog_now(),
];
$data = $post ? array_merge($defaults, $post) : $defaults;
$errors = [];
$publishedInput = '';
if ($post && ($post['status'] === 'published' || $post['published_at'] > blog_now())) {
    // Drafts show an empty date (= publish now) unless they were given a future date.
    $publishedInput = date('Y-m-d\TH:i', strtotime($post['published_at']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? 'draft';
    // Scrub to valid UTF-8 so odd pasted bytes can't break the MySQL insert.
    $input = fn(string $key): string => trim(mb_scrub((string)($_POST[$key] ?? ''), 'UTF-8'));

    $data['title'] = $input('title');
    $data['slug'] = $input('slug');
    $data['excerpt'] = $input('excerpt');
    $data['content'] = blog_sanitize_html($input('content'));
    $data['category'] = $input('category');
    $data['author'] = $input('author');
    $data['meta_title'] = $input('meta_title');
    $data['meta_description'] = $input('meta_description');
    $data['status'] = $action === 'publish' ? 'published' : 'draft';

    $publishedInput = trim((string)($_POST['published_at'] ?? ''));
    $publishedTs = $publishedInput !== '' ? strtotime($publishedInput) : time();
    if ($publishedTs === false) {
        $errors[] = 'The publish date is not valid.';
        $publishedTs = time();
    }
    $data['published_at'] = date('Y-m-d H:i:s', $publishedTs);

    if ($data['title'] === '') {
        $errors[] = 'Please add a title.';
    }
    if (mb_strlen($data['title']) > 200) {
        $errors[] = 'Title is too long (max 200 characters).';
    }
    if ($data['status'] === 'published' && trim(strip_tags($data['content'], '<img><iframe>')) === '') {
        $errors[] = "The article is empty — add some content before publishing (or save it as a draft).";
    }
    foreach (['excerpt' => 500, 'category' => 80, 'author' => 120, 'meta_title' => 200, 'meta_description' => 320] as $field => $max) {
        if (mb_strlen($data[$field]) > $max) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . " is too long (max $max characters).";
        }
    }

    // Cover image
    if (!empty($_POST['remove_cover'])) {
        $data['cover_image'] = '';
    }
    // Store the new cover only once everything else is valid, so failed saves leave no orphan files.
    if (!$errors && ($_FILES['cover']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        try {
            $data['cover_image'] = blog_store_upload($_FILES['cover']);
        } catch (RuntimeException $ex) {
            $errors[] = 'Cover image: ' . $ex->getMessage();
        }
    }

    if (!$errors) {
        $slugBase = blog_slugify($data['slug'] !== '' ? $data['slug'] : $data['title']);
        $data['slug'] = blog_unique_slug($pdo, $slugBase, $id);
        $now = blog_now();
        $fields = [
            ':title' => $data['title'], ':slug' => $data['slug'], ':excerpt' => $data['excerpt'],
            ':content' => $data['content'], ':cover' => $data['cover_image'], ':category' => $data['category'],
            ':author' => $data['author'], ':status' => $data['status'], ':meta_title' => $data['meta_title'],
            ':meta_description' => $data['meta_description'], ':published_at' => $data['published_at'], ':updated' => $now,
        ];
        if ($id) {
            $fields[':id'] = $id;
            $pdo->prepare('UPDATE posts SET title = :title, slug = :slug, excerpt = :excerpt, content = :content,
                cover_image = :cover, category = :category, author = :author, status = :status, meta_title = :meta_title,
                meta_description = :meta_description, published_at = :published_at, updated_at = :updated WHERE id = :id')
                ->execute($fields);
        } else {
            $fields[':created'] = $now;
            $pdo->prepare('INSERT INTO posts (title, slug, excerpt, content, cover_image, category, author, status,
                meta_title, meta_description, published_at, created_at, updated_at) VALUES (:title, :slug, :excerpt, :content,
                :cover, :category, :author, :status, :meta_title, :meta_description, :published_at, :created, :updated)')
                ->execute($fields);
            $id = (int)$pdo->lastInsertId();
        }

        if ($data['status'] === 'draft') {
            flash('Draft saved.');
        } elseif ($publishedTs > time()) {
            flash('Scheduled — goes live on ' . date('j M Y, g:i A', $publishedTs) . '.');
        } else {
            flash('Published! It is live on the blog now.');
        }
        admin_redirect('edit.php?id=' . $id);
    }
}

$categories = $pdo->query("SELECT DISTINCT category FROM posts WHERE category != '' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
$isPublished = $post && $post['status'] === 'published';
$isScheduled = $isPublished && $post['published_at'] > blog_now();
$previewUrl = $post ? '../blog/post.php?slug=' . rawurlencode($post['slug']) . ($isPublished && !$isScheduled ? '' : '&preview=1') : '';

admin_header($post ? 'Edit: ' . $post['title'] : 'New post', $post ? 'posts' : 'new');
?>
    <form class="editor" method="post" enctype="multipart/form-data" id="post-form">
      <?= csrf_field() ?>
      <?php if ($id): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?>

      <div class="page-head">
        <div>
          <p class="eyebrow"><span class="eyebrow-line"></span> <a href="index.php">Posts</a> /
            <?php if (!$post): ?>New<?php elseif ($isScheduled): ?>Scheduled<?php elseif ($isPublished): ?>Published<?php else: ?>Draft<?php endif; ?></p>
          <h1><?= $post ? 'Edit post' : 'New post' ?></h1>
        </div>
        <?php if ($previewUrl): ?><a class="btn btn-ghost" href="<?= e($previewUrl) ?>" target="_blank" rel="noopener"><?= $isPublished && !$isScheduled ? 'View live ↗' : 'Preview ↗' ?></a><?php endif; ?>
      </div>

      <?php foreach ($errors as $error): ?><div class="flash flash-error" role="alert"><?= e($error) ?></div><?php endforeach; ?>

      <div class="editor-grid">
        <div class="editor-main">
          <label class="field field-title"><span>Title</span>
            <input type="text" name="title" id="title" value="<?= e($data['title']) ?>" maxlength="200" placeholder="A headline worth clicking" required></label>
          <label class="field"><span>URL slug <em>— skylinkmedia.co.in/blog/<b id="slug-preview"><?= e($data['slug'] ?: 'your-post-url') ?></b></em></span>
            <input type="text" name="slug" id="slug" value="<?= e($data['slug']) ?>" maxlength="120" placeholder="auto-generated from the title" data-touched="<?= $data['slug'] !== '' ? '1' : '0' ?>"></label>
          <div class="field"><span>Content</span>
            <textarea name="content" id="content" rows="20"><?= e($data['content']) ?></textarea></div>
          <label class="field"><span>Excerpt <em>— short summary shown on the blog list (optional)</em></span>
            <textarea name="excerpt" rows="3" maxlength="500" data-count="500"><?= e($data['excerpt']) ?></textarea></label>
        </div>

        <aside class="editor-side">
          <section class="panel">
            <h2>Publish</h2>
            <label class="field"><span>Publish date &amp; time <em>(empty = now, future = scheduled)</em></span>
              <input type="datetime-local" name="published_at" value="<?= e($publishedInput) ?>"></label>
            <div class="panel-actions">
              <button class="btn btn-primary btn-block" type="submit" name="action" value="publish"><?= $isPublished ? 'Update' : 'Publish' ?> <span aria-hidden="true">↗</span></button>
              <button class="btn btn-ghost btn-block" type="submit" name="action" value="draft"><?= $isPublished ? 'Unpublish (move to draft)' : 'Save draft' ?></button>
            </div>
          </section>

          <section class="panel">
            <h2>Cover image</h2>
            <div class="cover-preview<?= $data['cover_image'] ? ' has-image' : '' ?>" id="cover-preview">
              <?php if ($data['cover_image']): ?><img src="../<?= e($data['cover_image']) ?>" alt=""><?php endif; ?>
              <span>No cover yet</span>
            </div>
            <label class="file-input"><input type="file" name="cover" id="cover" accept="image/jpeg,image/png,image/webp,image/gif"><span>Choose image…</span></label>
            <p class="hint">JPG / PNG / WebP, up to 8 MB. Wide images (1600×900) look best. Saved as optimised WebP.</p>
            <?php if ($data['cover_image']): ?><label class="check"><input type="checkbox" name="remove_cover" value="1"> Remove cover image</label><?php endif; ?>
          </section>

          <section class="panel">
            <h2>Details</h2>
            <label class="field"><span>Category</span>
              <input type="text" name="category" value="<?= e($data['category']) ?>" maxlength="80" list="category-list" placeholder="e.g. SEO, Social Media"></label>
            <datalist id="category-list"><?php foreach (array_unique(array_merge($categories, ['SEO', 'Social Media', 'Meta Ads', 'Google Ads', 'Web Design'])) as $cat): ?><option value="<?= e($cat) ?>"><?php endforeach; ?></datalist>
            <label class="field"><span>Author</span>
              <input type="text" name="author" value="<?= e($data['author']) ?>" maxlength="120"></label>
          </section>

          <section class="panel">
            <h2>SEO</h2>
            <label class="field"><span>Meta title <em>(defaults to post title)</em></span>
              <input type="text" name="meta_title" value="<?= e($data['meta_title']) ?>" maxlength="200" data-count="60"></label>
            <label class="field"><span>Meta description <em>(defaults to excerpt)</em></span>
              <textarea name="meta_description" rows="3" maxlength="320" data-count="160"><?= e($data['meta_description']) ?></textarea></label>
          </section>
        </aside>
      </div>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/tinymce@7.9.3/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
      const csrf = <?= json_encode(csrf_token()) ?>;
      tinymce.init({
        selector: '#content',
        license_key: 'gpl',
        min_height: 520,
        menubar: false,
        branding: false,
        promotion: false,
        skin: 'oxide-dark',
        content_css: 'dark',
        plugins: 'autoresize autolink link lists image media table code wordcount fullscreen',
        toolbar: 'blocks | bold italic underline | bullist numlist blockquote | link image media table | removeformat | code fullscreen',
        block_formats: 'Paragraph=p; Heading 2=h2; Heading 3=h3; Heading 4=h4',
        convert_urls: false,
        image_caption: true,
        image_dimensions: false,
        image_advtab: false,
        automatic_uploads: true,
        paste_data_images: true,
        images_file_types: 'jpg,jpeg,png,webp,gif',
        table_default_styles: {},
        table_default_attributes: {},
        link_default_target: '_blank',
        content_style: "@import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700&display=swap'); body{font-family:Manrope,system-ui,sans-serif;font-size:16px;line-height:1.7;max-width:760px;margin:24px auto;padding:0 12px} img{max-width:100%;height:auto} h2,h3,h4{letter-spacing:-.02em;line-height:1.2} blockquote{border-left:3px solid #e34116;margin-left:0;padding-left:18px;color:#c9c7c0}",
        images_upload_handler: (blobInfo) => {
          const body = new FormData();
          body.append('file', blobInfo.blob(), blobInfo.filename());
          body.append('csrf', csrf);
          return fetch('upload.php', { method: 'POST', body, credentials: 'same-origin' })
            .then((r) => r.json())
            .then((json) => {
              if (!json.location) throw new Error(json.error || 'Upload failed');
              return json.location;
            });
        },
      });

      // Slug follows the title until edited by hand.
      const title = document.getElementById('title');
      const slug = document.getElementById('slug');
      const slugPreview = document.getElementById('slug-preview');
      const slugify = (s) => s.toLowerCase().normalize('NFKD').replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 90);
      const syncSlug = () => { slugPreview.textContent = slugify(slug.value || title.value) || 'your-post-url'; };
      title.addEventListener('input', () => { if (slug.dataset.touched !== '1') slug.value = slugify(title.value); syncSlug(); });
      slug.addEventListener('input', () => { slug.dataset.touched = '1'; syncSlug(); });

      // Cover preview
      const cover = document.getElementById('cover');
      const coverPreview = document.getElementById('cover-preview');
      cover.addEventListener('change', () => {
        const file = cover.files[0];
        if (!file) return;
        coverPreview.querySelector('img')?.remove();
        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        coverPreview.prepend(img);
        coverPreview.classList.add('has-image');
        cover.nextElementSibling.textContent = file.name;
      });

      // Character counters (soft limits for SEO fields)
      document.querySelectorAll('[data-count]').forEach((el) => {
        const note = document.createElement('small');
        note.className = 'counter';
        el.after(note);
        const update = () => {
          const n = el.value.length, max = +el.dataset.count;
          note.textContent = n + ' / ' + max;
          note.classList.toggle('is-over', n > max);
        };
        el.addEventListener('input', update);
        update();
      });

      // Warn before leaving with unsaved changes
      let dirty = false;
      const form = document.getElementById('post-form');
      form.addEventListener('input', () => { dirty = true; });
      tinymce.on('AddEditor', ({ editor }) => editor.on('input change', () => { dirty = true; }));
      form.addEventListener('submit', () => { dirty = false; });
      window.addEventListener('beforeunload', (ev) => { if (dirty) { ev.preventDefault(); ev.returnValue = ''; } });
    </script>
<?php admin_footer(); ?>
