<?php
declare(strict_types=1);

// Direct RSS endpoint for /feed/dzen/ (bypasses WordPress rewrite conflicts).
$wpLoad = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'wp-load.php';
if (!file_exists($wpLoad)) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'wp-load.php not found';
    exit;
}

require_once $wpLoad;

header('Content-Type: application/rss+xml; charset=utf-8');

$siteTitle = get_bloginfo('name');
$siteLink = home_url('/');
$siteDesc = get_bloginfo('description');

$posts = get_posts([
    'post_type' => ['post', 'guide'],
    'post_status' => 'publish',
    'posts_per_page' => 50,
    'orderby' => 'date',
    'order' => 'DESC',
]);

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
<title><?php echo esc_html($siteTitle); ?></title>
<link><?php echo esc_url($siteLink); ?></link>
<description><?php echo esc_html($siteDesc); ?></description>
<language>ru</language>
<atom:link href="<?php echo esc_url(home_url('/feed/dzen/')); ?>" rel="self" type="application/rss+xml" />
<?php foreach ($posts as $p) :
    $title = get_the_title($p);
    $link = get_permalink($p);
    $pubDate = get_post_time('D, d M Y H:i:s +0000', true, $p);
    $author = get_the_author_meta('display_name', (int) $p->post_author);
    $content = apply_filters('the_content', (string) $p->post_content);
    // RSS safety fallback: if the editor forgot an inline image,
    // prepend featured image so Dzen can always pick a banner.
    if (!preg_match('/<img\\b/i', $content)) {
        $thumbId = get_post_thumbnail_id($p);
        if ($thumbId) {
            $thumbUrl = wp_get_attachment_image_url($thumbId, 'full');
            if ($thumbUrl) {
                $thumbAlt = get_post_meta($thumbId, '_wp_attachment_image_alt', true);
                if (!$thumbAlt) {
                    $thumbAlt = $title;
                }
                $thumbHtml = sprintf(
                    '<p><img src="%s" alt="%s" /></p>',
                    esc_url($thumbUrl),
                    esc_attr((string) $thumbAlt)
                );
                $content = $thumbHtml . $content;
            }
        }
    }
    $content = str_replace(']]>', ']]&gt;', $content);
    $excerpt = has_excerpt($p) ? get_the_excerpt($p) : wp_trim_words(wp_strip_all_tags($content), 40, '...');
    ?>
<item>
<title><?php echo esc_html($title); ?></title>
<link><?php echo esc_url($link); ?></link>
<guid isPermaLink="true"><?php echo esc_url($link); ?></guid>
<pubDate><?php echo esc_html($pubDate); ?></pubDate>
<dc:creator><?php echo esc_html((string) $author); ?></dc:creator>
<description><![CDATA[<?php echo $excerpt; ?>]]></description>
<content:encoded><![CDATA[<?php echo $content; ?>]]></content:encoded>
</item>
<?php endforeach; ?>
</channel>
</rss>
