<?php
/**
 * MU-plugin: Favicons endpoints + head links + one-time SEO fields setup.
 *
 * Goals:
 * - Stable 200 OK for /favicon.svg, /icon-120.png, /favicon.ico, /site.webmanifest
 * - Use WordPress Site Icon as source of truth
 * - One-time admin automation: fix tagline + attachment SEO fields
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Return absolute path to the Site Icon source file.
 */
function kv_site_icon_file_path(): ?string
{
    $id = (int) get_option('site_icon');
    if ($id <= 0) return null;
    $path = get_attached_file($id);
    return (is_string($path) && $path !== '' && file_exists($path)) ? $path : null;
}

/**
 * Resize image file to PNG binary (square).
 */
function kv_resize_to_png_bytes(string $sourcePath, int $size): ?string
{
    // Prefer WP image editor.
    if (function_exists('wp_get_image_editor')) {
        $editor = wp_get_image_editor($sourcePath);
        if (!is_wp_error($editor)) {
            $editor->resize($size, $size, true);
            $tmp = wp_tempnam("kv-icon-{$size}.png");
            if (is_string($tmp) && $tmp !== '') {
                $saved = $editor->save($tmp, 'image/png');
                if (!is_wp_error($saved) && isset($saved['path']) && file_exists($saved['path'])) {
                    $bytes = file_get_contents($saved['path']);
                    @unlink($saved['path']);
                    if (is_string($bytes) && $bytes !== '') return $bytes;
                }
                @unlink($tmp);
            }
        }
    }

    // GD fallback.
    if (!function_exists('imagecreatefromstring')) return null;
    $raw = @file_get_contents($sourcePath);
    if (!is_string($raw) || $raw === '') return null;
    $im = @imagecreatefromstring($raw);
    if (!$im) return null;

    $dst = imagecreatetruecolor($size, $size);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);

    $w = imagesx($im);
    $h = imagesy($im);
    $side = min($w, $h);
    $srcX = (int) floor(($w - $side) / 2);
    $srcY = (int) floor(($h - $side) / 2);

    imagecopyresampled($dst, $im, 0, 0, $srcX, $srcY, $size, $size, $side, $side);
    imagedestroy($im);

    ob_start();
    imagepng($dst);
    imagedestroy($dst);
    $bytes = ob_get_clean();
    return (is_string($bytes) && $bytes !== '') ? $bytes : null;
}

/**
 * Build minimal ICO file with embedded PNG image.
 * ICO header + 1 directory entry + PNG payload.
 */
function kv_png_to_ico_bytes(string $pngBytes, int $size): string
{
    $pngLen = strlen($pngBytes);
    $w = $size >= 256 ? 0 : $size;
    $h = $size >= 256 ? 0 : $size;

    $header = pack('vvv', 0, 1, 1);
    $dir = pack(
        'CCCCvvVV',
        $w,
        $h,
        0,
        0,
        1,
        32,
        $pngLen,
        6 + 16
    );
    return $header . $dir . $pngBytes;
}

function kv_send_bytes(string $contentType, string $bytes, int $maxAge = 604800): void
{
    status_header(200);
    header('Content-Type: ' . $contentType);
    header('Content-Length: ' . strlen($bytes));
    header('Cache-Control: public, max-age=' . (int) $maxAge);
    echo $bytes;
    exit;
}

function kv_send_404(): void
{
    status_header(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Not found';
    exit;
}

add_action('init', function () {
    $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
    $path = (string) parse_url($uri, PHP_URL_PATH);
    if ($path === '') return;

    // One-time automation when admin visits any page.
    if (is_user_logged_in() && current_user_can('manage_options')) {
        $desiredTagline = 'Кровля, фасады, заборы — замер, доставка, монтаж';

        if (!get_option('kv_tagline_fixed')) {
            if (get_option('blogdescription') !== $desiredTagline) {
                update_option('blogdescription', $desiredTagline);
            }
            update_option('kv_tagline_fixed', 1, true);
        }

        $siteIconId = (int) get_option('site_icon');
        $doneFor = (int) get_option('kv_site_icon_seo_updated_for');
        if ($siteIconId > 0 && $doneFor !== $siteIconId) {
            $alt = 'Квадратура — значок сайта (логотип): кровля, фасады, заборы';
            update_post_meta($siteIconId, '_wp_attachment_image_alt', $alt);

            wp_update_post([
                'ID' => $siteIconId,
                'post_title' => 'Значок сайта «Квадратура» — кровля, фасады, заборы',
                'post_excerpt' => 'Фавикон и логотип компании «Квадратура»',
                'post_content' => 'Официальный значок сайта компании «Квадратура». Кровля, фасады, заборы — замер, доставка, монтаж.',
            ]);

            update_option('kv_site_icon_seo_updated_for', $siteIconId, true);
        }
    }

    // Endpoints.
    if ($path === '/favicon.svg') {
        $svg = <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 120 120">
  <rect width="120" height="120" rx="28" fill="#070b10"/>
  <path d="M60 22L22 52v46h76V52L60 22z" fill="none" stroke="#e8c547" stroke-width="8" stroke-linejoin="round"/>
  <path d="M46 98V64h28v34" fill="none" stroke="#e8c547" stroke-width="8" stroke-linejoin="round"/>
  <circle cx="60" cy="52" r="4" fill="#e8c547"/>
</svg>
SVG;
        kv_send_bytes('image/svg+xml; charset=utf-8', $svg, 604800);
    }

    if ($path === '/site.webmanifest') {
        $name = get_bloginfo('name');
        $short = $name;
        $icon32 = '/favicon.ico';
        $icon120 = '/icon-120.png';

        $manifest = [
            'name' => $name,
            'short_name' => $short,
            'start_url' => '/',
            'display' => 'standalone',
            'background_color' => '#070b10',
            'theme_color' => '#070b10',
            'icons' => [
                ['src' => $icon120, 'sizes' => '120x120', 'type' => 'image/png'],
                ['src' => $icon32, 'sizes' => '32x32', 'type' => 'image/x-icon'],
            ],
        ];

        $bytes = wp_json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($bytes) || $bytes === '') $bytes = '{}';
        kv_send_bytes('application/manifest+json; charset=utf-8', $bytes, 604800);
    }

    $iconSource = kv_site_icon_file_path();

    if ($path === '/icon-120.png') {
        if (!$iconSource) kv_send_404();
        $png = kv_resize_to_png_bytes($iconSource, 120);
        if (!$png) kv_send_404();
        kv_send_bytes('image/png', $png, 604800);
    }

    if ($path === '/favicon.ico') {
        if (!$iconSource) kv_send_404();
        $png = kv_resize_to_png_bytes($iconSource, 32);
        if (!$png) kv_send_404();
        $ico = kv_png_to_ico_bytes($png, 32);
        kv_send_bytes('image/x-icon', $ico, 604800);
    }
}, 0);

add_action('wp_head', function () {
    // Add explicit links for Yandex requirements (SVG + 120x120 + manifest).
    echo "\n" . '<link rel="icon" href="' . esc_url(home_url('/favicon.svg')) . '" type="image/svg+xml">';
    echo "\n" . '<link rel="icon" href="' . esc_url(home_url('/icon-120.png')) . '" type="image/png" sizes="120x120">';
    echo "\n" . '<link rel="manifest" href="' . esc_url(home_url('/site.webmanifest')) . '">';
    echo "\n";
}, 5);

