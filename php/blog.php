<?php

declare(strict_types=1);

/**
 * Shared blog helpers — used by the public /blog pages and the /admin panel.
 */

date_default_timezone_set('Asia/Kolkata');

/** MySQL connection (see php/config.php). One shared connection per request. */
function blog_db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $c = require __DIR__ . '/config.php';
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $c['db_host'], $c['db_port'], $c['db_name']);
        $pdo = new PDO($dsn, $c['db_user'], $c['db_pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $pdo->exec("SET time_zone = '+05:30'");
    }
    return $pdo;
}

const BLOG_SITE_URL = 'https://skylinkmedia.co.in/';
const BLOG_PER_PAGE = 9;
const BLOG_UPLOAD_DIR = __DIR__ . '/../uploads/blog';
const BLOG_UPLOAD_URL = 'uploads/blog'; // relative to site root
// Pretty article URLs (/blog/my-post) need the rewrite rule in the root .htaccess.
// Set to false if the host doesn't support mod_rewrite — links fall back to post.php?slug=.
const BLOG_PRETTY_URLS = true;

/** Admin session (shared by /admin and the draft-preview mode on /blog). */
function blog_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_name('skl_admin');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

function blog_is_admin(): bool
{
    return !empty($_SESSION['admin_id']);
}

function blog_now(): string
{
    return date('Y-m-d H:i:s');
}

function blog_slugify(string $text): string
{
    $text = mb_strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/u', '-', $text) ?? '';
    $text = trim($text, '-');
    return mb_substr($text, 0, 90) ?: 'post';
}

function blog_unique_slug(PDO $pdo, string $slug, int $ignoreId = 0): string
{
    $candidate = $slug;
    $n = 2;
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM posts WHERE slug = :slug AND id != :id');
    while (true) {
        $stmt->execute([':slug' => $candidate, ':id' => $ignoreId]);
        if ((int)$stmt->fetchColumn() === 0) {
            return $candidate;
        }
        $candidate = $slug . '-' . $n++;
    }
}

/** Link to a post from a page inside /blog (both /blog/ and /blog/<slug> live one level deep). */
function blog_post_href(string $slug): string
{
    return BLOG_PRETTY_URLS ? rawurlencode($slug) : 'post.php?slug=' . rawurlencode($slug);
}

function blog_post_canonical(string $slug): string
{
    return BLOG_SITE_URL . 'blog/' . (BLOG_PRETTY_URLS ? rawurlencode($slug) : 'post.php?slug=' . rawurlencode($slug));
}

function blog_format_date(string $datetime): string
{
    $ts = strtotime($datetime);
    return $ts ? date('j M Y', $ts) : '';
}

/** Plain text of an HTML body, with block boundaries kept as spaces (so paragraphs don't run together). */
function blog_plain_text(string $html): string
{
    $text = strip_tags(str_replace('<', ' <', $html));
    return trim(preg_replace('/\s+/u', ' ', html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');
}

function blog_reading_time(string $html): int
{
    $words = str_word_count(blog_plain_text($html));
    return max(1, (int)ceil($words / 200));
}

/** Plain-text summary: the excerpt if set, else the start of the article. */
function blog_summary(array $post, int $length = 160): string
{
    $text = trim($post['excerpt'] ?? '');
    if ($text === '') {
        $text = blog_plain_text($post['content'] ?? '');
    }
    if (mb_strlen($text) > $length) {
        $text = rtrim(mb_substr($text, 0, $length - 1), " ,.;:-") . '…';
    }
    return $text;
}

/** WHERE clause for posts visible to the public. */
function blog_public_where(): string
{
    return "status = 'published' AND published_at <= :now";
}

function blog_categories(PDO $pdo): array
{
    $stmt = $pdo->prepare("SELECT category, COUNT(*) AS total FROM posts WHERE " . blog_public_where() . " AND category != '' GROUP BY category ORDER BY category");
    $stmt->execute([':now' => blog_now()]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Allowlist HTML sanitizer for article bodies. The editor is admin-only, but this keeps
 * pasted markup (Word, Google Docs) from bringing scripts, inline styles or layout-breaking tags.
 */
function blog_sanitize_html(string $html): string
{
    $allowed = [
        'p' => [], 'br' => [], 'h2' => ['id'], 'h3' => ['id'], 'h4' => ['id'],
        'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [], 's' => [], 'sub' => [], 'sup' => [],
        'a' => ['href', 'title', 'target', 'rel'],
        'ul' => [], 'ol' => [], 'li' => [], 'blockquote' => [], 'hr' => [],
        'pre' => [], 'code' => [],
        'img' => ['src', 'alt', 'width', 'height', 'loading'],
        'figure' => [], 'figcaption' => [],
        'table' => [], 'thead' => [], 'tbody' => [], 'tr' => [], 'th' => ['colspan', 'rowspan'], 'td' => ['colspan', 'rowspan'],
        'iframe' => ['src', 'width', 'height', 'allow', 'allowfullscreen', 'title'],
    ];
    // Headings the author typed as H1 would compete with the post title, so demote them.
    $rename = ['h1' => 'h2', 'h5' => 'h4', 'h6' => 'h4'];

    $html = trim($html);
    if ($html === '') {
        return '';
    }

    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="UTF-8"><div id="blog-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    $root = $doc->getElementById('blog-root');
    if (!$root) {
        return '';
    }

    $clean = function (DOMNode $node) use (&$clean, $doc, $allowed, $rename): void {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMComment) {
                $node->removeChild($child);
                continue;
            }
            if (!$child instanceof DOMElement) {
                continue;
            }
            $tag = strtolower($child->tagName);
            if (in_array($tag, ['script', 'style', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select', 'meta', 'link'], true)) {
                $node->removeChild($child);
                continue;
            }
            if (isset($rename[$tag])) {
                $replacement = $doc->createElement($rename[$tag]);
                while ($child->firstChild) {
                    $replacement->appendChild($child->firstChild);
                }
                $node->replaceChild($replacement, $child);
                $child = $replacement;
                $tag = $rename[$tag];
            }
            if (!isset($allowed[$tag])) {
                // Unknown wrapper (span, font…): keep its content, drop the tag.
                $clean($child);
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }
            foreach (iterator_to_array($child->attributes) as $attr) {
                if (!in_array(strtolower($attr->name), $allowed[$tag], true)) {
                    $child->removeAttribute($attr->name);
                }
            }
            foreach (['href', 'src'] as $urlAttr) {
                if ($child->hasAttribute($urlAttr)) {
                    $url = trim($child->getAttribute($urlAttr));
                    if (!preg_match('#^(https?:|mailto:|tel:|/|\.\./|\#|[a-z0-9_\-./]+$)#i', $url) || preg_match('#^\s*(javascript|data|vbscript):#i', $url)) {
                        $child->removeAttribute($urlAttr);
                    }
                }
            }
            if ($tag === 'a' && !$child->hasAttribute('href')) {
                // Link lost its (unsafe) URL: keep the text, drop the dead <a>.
                $clean($child);
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }
            if ($tag === 'iframe') {
                // Only video embeds from YouTube / Vimeo.
                if (!preg_match('#^https://(www\.)?(youtube\.com|youtube-nocookie\.com|player\.vimeo\.com)/#i', $child->getAttribute('src'))) {
                    $node->removeChild($child);
                    continue;
                }
            }
            if ($tag === 'a' && preg_match('#^https?://#i', $child->getAttribute('href')) && stripos($child->getAttribute('href'), 'skylinkmedia.co.in') === false) {
                $child->setAttribute('target', '_blank');
                $child->setAttribute('rel', 'noopener');
            }
            if ($tag === 'img') {
                $child->setAttribute('loading', 'lazy');
            }
            $clean($child);
        }
    };
    $clean($root);

    $out = '';
    foreach ($root->childNodes as $child) {
        $out .= $doc->saveHTML($child);
    }
    // Drop empty paragraphs the editor leaves behind.
    $out = preg_replace('#<p>(\s|&nbsp;|\x{00A0}|<br\s*/?>)*</p>#iu', '', $out) ?? $out;
    return trim($out);
}

/**
 * Save an uploaded image into uploads/blog, resized to max $maxWidth and re-encoded
 * (re-encoding strips anything that isn't pixels). Returns the path relative to site root.
 */
function blog_store_upload(array $file, int $maxWidth = 1600): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed — please try again.');
    }
    if ($file['size'] > 8 * 1024 * 1024) {
        throw new RuntimeException('Image is too large (max 8 MB).');
    }
    $info = @getimagesize($file['tmp_name']);
    $types = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp', IMAGETYPE_GIF => 'gif'];
    if (!$info || !isset($types[$info[2]])) {
        throw new RuntimeException('Please upload a JPG, PNG, WebP or GIF image.');
    }

    $src = match ($info[2]) {
        IMAGETYPE_JPEG => @imagecreatefromjpeg($file['tmp_name']),
        IMAGETYPE_PNG => @imagecreatefrompng($file['tmp_name']),
        IMAGETYPE_WEBP => @imagecreatefromwebp($file['tmp_name']),
        IMAGETYPE_GIF => @imagecreatefromgif($file['tmp_name']),
    };
    if (!$src) {
        throw new RuntimeException('That image could not be read.');
    }

    [$w, $h] = [$info[0], $info[1]];
    if ($w > $maxWidth) {
        $newH = (int)round($h * $maxWidth / $w);
        $resized = imagecreatetruecolor($maxWidth, $newH);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $src, 0, 0, 0, 0, $maxWidth, $newH, $w, $h);
        imagedestroy($src);
        $src = $resized;
    } else {
        imagepalettetotruecolor($src);
        imagealphablending($src, false);
        imagesavealpha($src, true);
    }

    if (!is_dir(BLOG_UPLOAD_DIR) && !mkdir(BLOG_UPLOAD_DIR, 0755, true)) {
        throw new RuntimeException('Upload folder is not writable.');
    }
    $name = date('Ymd') . '-' . bin2hex(random_bytes(6)) . '.webp';
    if (!imagewebp($src, BLOG_UPLOAD_DIR . '/' . $name, 82)) {
        imagedestroy($src);
        throw new RuntimeException('Could not save the image.');
    }
    imagedestroy($src);
    return BLOG_UPLOAD_URL . '/' . $name;
}
