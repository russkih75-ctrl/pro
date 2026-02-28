<?php
if (!defined('ABSPATH')) exit;

function kv_schema_article(int $post_id): string {
  $p = get_post($post_id);
  if (!$p) return '';

  $site = home_url('/');
  $url  = get_permalink($post_id);

  $img = '';
  if (has_post_thumbnail($post_id)) {
    $img = get_the_post_thumbnail_url($post_id, 'full') ?: '';
  }

  $ptype = get_post_type($post_id);
  $schema_type = ($ptype === 'guide') ? 'BlogPosting' : 'Article';
  $author_name = get_the_author_meta('display_name', (int)$p->post_author) ?: 'Автор';

  $payload = [
    '@context' => 'https://schema.org',
    '@type' => $schema_type,
    'mainEntityOfPage' => ['@type'=>'WebPage','@id'=>$url],
    'headline' => get_the_title($post_id),
    'datePublished' => get_the_date(DATE_W3C, $post_id),
    'dateModified' => get_the_modified_date(DATE_W3C, $post_id),
    'author' => [
      '@type' => 'Person',
      'name' => $author_name,
      '@id' => $site . '#founder',
    ],
    'publisher' => [
      '@type' => 'Organization',
      '@id' => $site . '#organization',
      'name' => get_bloginfo('name'),
    ],
    'image' => $img ? [$img] : [],
    'wordCount' => str_word_count(wp_strip_all_tags((string)$p->post_content)),
    'articleSection' => array_values(array_map(static function ($c) {
      return (string)($c->name ?? '');
    }, get_the_category($post_id) ?: [])),
    'keywords' => implode(', ', wp_get_post_tags($post_id, ['fields' => 'names'])),
    'speakable' => [
      '@type' => 'SpeakableSpecification',
      'cssSelector' => ['.entry-title', '.article-layout__main .entry-content > p:first-of-type']
    ],
    'inLanguage' => 'ru-RU',
  ];

  return '<script type="application/ld+json">' . wp_json_encode($payload, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) . '</script>';
}

