<?php
if (!defined('ABSPATH')) exit;

/**
 * IndexNow support (Yandex + Bing support IndexNow).
 * - queue + dedupe
 * - low frequency sending via WP Cron
 * - auto-enqueue on post/page publish
 * - key file served via WordPress rewrite
 */

function kv_indexnow_key(): string {
  $key = get_option('kv_indexnow_key', '');
  if (!$key) {
    // Auto-generate key on first use
    $key = wp_generate_uuid4();
    $key = str_replace('-', '', $key);
    update_option('kv_indexnow_key', $key, true);
  }
  return (string) $key;
}

function kv_indexnow_endpoint(): string {
  return (string) get_option('kv_indexnow_endpoint', 'https://yandex.com/indexnow');
}

function kv_indexnow_queue_option(): string { return 'kv_indexnow_queue'; }

function kv_indexnow_enqueue_url(string $url): void {
  $url = esc_url_raw($url);
  if (!$url) return;

  $queue = get_option(kv_indexnow_queue_option(), []);
  if (!is_array($queue)) $queue = [];

  $queue[$url] = time(); // dedupe by key
  // Hard cap to avoid unbounded growth
  if (count($queue) > 5000) {
    $queue = array_slice($queue, -2000, null, true);
  }
  update_option(kv_indexnow_queue_option(), $queue, false);
}

function kv_indexnow_dequeue_batch(int $limit = 100): array {
  $queue = get_option(kv_indexnow_queue_option(), []);
  if (!is_array($queue) || !$queue) return [];

  // FIFO: sort by time
  asort($queue);
  $urls = array_slice(array_keys($queue), 0, $limit);
  foreach ($urls as $u) unset($queue[$u]);
  update_option(kv_indexnow_queue_option(), $queue, false);
  return $urls;
}

function kv_indexnow_send(array $urls): array {
  $key = kv_indexnow_key();
  if (!$key || !$urls) return ['ok' => false, 'error' => 'missing_key_or_urls'];

  $payload = [
    'host' => parse_url(home_url('/'), PHP_URL_HOST),
    'key' => $key,
    'keyLocation' => home_url('/' . $key . '.txt'),
    'urlList' => array_values($urls),
  ];

  $resp = wp_remote_post(kv_indexnow_endpoint(), [
    'timeout' => 15,
    'headers' => ['Content-Type' => 'application/json; charset=utf-8'],
    'body' => wp_json_encode($payload, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
  ]);

  if (is_wp_error($resp)) {
    return ['ok' => false, 'error' => $resp->get_error_message()];
  }

  $code = (int) wp_remote_retrieve_response_code($resp);
  $body = (string) wp_remote_retrieve_body($resp);

  $ok = $code >= 200 && $code < 300;

  update_option('kv_indexnow_last_send', [
    'time'  => current_time('mysql'),
    'count' => count($urls),
    'code'  => $code,
    'ok'    => $ok,
  ], false);

  $log = get_option('kv_indexnow_send_log', []);
  if (!is_array($log)) $log = [];
  $log[] = [
    'time' => current_time('mysql'),
    'urls' => array_values($urls),
    'code' => $code,
    'ok'   => $ok,
  ];
  if (count($log) > 20) $log = array_slice($log, -20);
  update_option('kv_indexnow_send_log', $log, false);

  return ['ok' => $ok, 'code' => $code, 'body' => $body];
}

// ── Cron runner ──
function kv_indexnow_cron_hook(): string { return 'kv_indexnow_cron'; }

add_action('init', function () {
  if (get_option('kv_indexnow_enabled', null) === null) {
    add_option('kv_indexnow_enabled', 1, '', false);
  }
  if (!wp_next_scheduled(kv_indexnow_cron_hook())) {
    wp_schedule_event(time() + 300, 'hourly', kv_indexnow_cron_hook());
  }
}, 30);

add_action(kv_indexnow_cron_hook(), function () {
  if (!get_option('kv_indexnow_enabled', 1)) return;
  $batch = kv_indexnow_dequeue_batch(100);
  if (!$batch) return;

  $res = kv_indexnow_send($batch);
  if (!$res['ok']) {
    // Retry with counter — max 3 attempts per URL, then drop.
    $retries = get_option('kv_indexnow_retries', []);
    foreach ($batch as $u) {
      $count = isset($retries[$u]) ? (int) $retries[$u] : 0;
      if ($count < 3) {
        kv_indexnow_enqueue_url($u);
        $retries[$u] = $count + 1;
      } else {
        unset($retries[$u]);
      }
    }
    update_option('kv_indexnow_retries', $retries, false);
  } else {
    // Success — clean up retry counters for sent URLs.
    $retries = get_option('kv_indexnow_retries', []);
    foreach ($batch as $u) unset($retries[$u]);
    if ($retries) {
      update_option('kv_indexnow_retries', $retries, false);
    } else {
      delete_option('kv_indexnow_retries');
    }
  }
});

// ── Auto-enqueue on post/page publish or trash ──
add_action('transition_post_status', function ($new_status, $old_status, $post) {
  if (!in_array($post->post_type, ['post', 'page', 'service', 'project', 'guide'], true)) return;

  if ($new_status === 'publish' || ($old_status === 'publish' && $new_status === 'trash')) {
    $url = get_permalink($post);
    if ($url) {
      kv_indexnow_enqueue_url($url);
    }
  }
}, 10, 3);

// ── Serve key verification file via WP rewrite ──
add_action('init', function () {
  $key = get_option('kv_indexnow_key', '');
  if (!$key) return;
  add_rewrite_rule('^' . preg_quote($key, '/') . '\.txt$', 'index.php?kv_indexnow_verify=1', 'top');
}, 25);

add_filter('query_vars', function ($vars) {
  $vars[] = 'kv_indexnow_verify';
  return $vars;
});

add_action('template_redirect', function () {
  if (!get_query_var('kv_indexnow_verify')) return;
  $key = kv_indexnow_key();
  header('Content-Type: text/plain; charset=utf-8');
  echo $key;
  exit;
}, 0);

// ── Admin settings (lightweight) ──
add_action('admin_menu', function () {
  add_submenu_page(
    'options-general.php',
    'IndexNow',
    'IndexNow',
    'manage_options',
    'kv-indexnow',
    'kv_indexnow_admin_page'
  );
});

function kv_indexnow_collect_all_urls(): array {
  $urls = [];
  $types = ['post', 'page', 'guide'];
  foreach ($types as $type) {
    $posts = get_posts([
      'post_type'      => $type,
      'post_status'    => 'publish',
      'posts_per_page' => -1,
      'fields'         => 'ids',
    ]);
    foreach ($posts as $id) {
      $url = get_permalink($id);
      if ($url) $urls[] = $url;
    }
  }
  $urls[] = home_url('/');
  $urls[] = home_url('/feed/dzen/');
  return array_unique($urls);
}

function kv_indexnow_admin_page() {
  if (!current_user_can('manage_options')) return;

  $notices = [];

  if (isset($_POST['kv_indexnow_save']) && check_admin_referer('kv_indexnow_settings')) {
    update_option('kv_indexnow_enabled', isset($_POST['kv_indexnow_enabled']) ? 1 : 0);
    update_option('kv_indexnow_endpoint', sanitize_url($_POST['kv_indexnow_endpoint'] ?? 'https://yandex.com/indexnow'));
    $notices[] = '<div class="notice notice-success"><p>Настройки сохранены.</p></div>';
  }

  if (isset($_POST['kv_indexnow_push_all']) && check_admin_referer('kv_indexnow_push_all')) {
    $all = kv_indexnow_collect_all_urls();
    foreach ($all as $u) kv_indexnow_enqueue_url($u);
    $cnt = count($all);
    $notices[] = '<div class="notice notice-success"><p>Добавлено в очередь: ' . $cnt . ' URL. Отправка по cron в ближайший час.</p></div>';
  }

  if (isset($_POST['kv_indexnow_push_single']) && check_admin_referer('kv_indexnow_push_single')) {
    $single_url = esc_url_raw(trim($_POST['kv_indexnow_single_url'] ?? ''));
    if ($single_url) {
      kv_indexnow_enqueue_url($single_url);
      $notices[] = '<div class="notice notice-success"><p>URL добавлен в очередь: ' . esc_html($single_url) . '</p></div>';
    } else {
      $notices[] = '<div class="notice notice-error"><p>Введите корректный URL.</p></div>';
    }
  }

  if (isset($_POST['kv_indexnow_send_now']) && check_admin_referer('kv_indexnow_send_now')) {
    $batch = kv_indexnow_dequeue_batch(100);
    if ($batch) {
      $res = kv_indexnow_send($batch);
      if ($res['ok']) {
        $notices[] = '<div class="notice notice-success"><p>Отправлено: ' . count($batch) . ' URL. Код ответа: ' . (int)($res['code'] ?? 0) . '</p></div>';
      } else {
        foreach ($batch as $u) kv_indexnow_enqueue_url($u);
        $notices[] = '<div class="notice notice-error"><p>Ошибка отправки: ' . esc_html($res['error'] ?? $res['body'] ?? 'unknown') . '. URL возвращены в очередь.</p></div>';
      }
    } else {
      $notices[] = '<div class="notice notice-warning"><p>Очередь пуста.</p></div>';
    }
  }

  $enabled  = get_option('kv_indexnow_enabled', 1);
  $endpoint = kv_indexnow_endpoint();
  $key      = kv_indexnow_key();
  $queue    = get_option(kv_indexnow_queue_option(), []);
  $queueCnt = is_array($queue) ? count($queue) : 0;
  $lastSend = get_option('kv_indexnow_last_send', []);
  $log      = get_option('kv_indexnow_send_log', []);

  foreach ($notices as $n) echo $n;
  ?>
  <div class="wrap">
    <h1>IndexNow — Моментальная индексация</h1>

    <h2>Настройки</h2>
    <form method="post">
      <?php wp_nonce_field('kv_indexnow_settings'); ?>
      <table class="form-table">
        <tr>
          <th>Включён</th>
          <td><label><input type="checkbox" name="kv_indexnow_enabled" <?php checked($enabled); ?>> Активировать отправку</label></td>
        </tr>
        <tr>
          <th>Endpoint</th>
          <td><input type="url" name="kv_indexnow_endpoint" value="<?php echo esc_attr($endpoint); ?>" class="regular-text"></td>
        </tr>
        <tr>
          <th>API-ключ</th>
          <td><code><?php echo esc_html($key); ?></code><br><small>Файл: <a href="<?php echo esc_url(home_url('/' . $key . '.txt')); ?>" target="_blank"><?php echo esc_html($key); ?>.txt</a></small></td>
        </tr>
        <tr>
          <th>В очереди</th>
          <td><strong><?php echo $queueCnt; ?></strong> URL</td>
        </tr>
        <?php if ($lastSend): ?>
        <tr>
          <th>Последняя отправка</th>
          <td><?php echo esc_html($lastSend['time'] ?? '—'); ?> — <?php echo (int)($lastSend['count'] ?? 0); ?> URL, код <?php echo (int)($lastSend['code'] ?? 0); ?></td>
        </tr>
        <?php endif; ?>
      </table>
      <p class="submit"><button type="submit" name="kv_indexnow_save" class="button button-primary">Сохранить</button></p>
    </form>

    <hr>
    <h2>Быстрый обход (Push URLs)</h2>

    <form method="post" style="margin-bottom:16px;">
      <?php wp_nonce_field('kv_indexnow_push_all'); ?>
      <p>Добавить ВСЕ опубликованные URL (посты, страницы, гайды) в очередь IndexNow:</p>
      <button type="submit" name="kv_indexnow_push_all" class="button button-secondary">Добавить все URL в очередь</button>
    </form>

    <form method="post" style="margin-bottom:16px;">
      <?php wp_nonce_field('kv_indexnow_push_single'); ?>
      <p>Добавить конкретный URL:</p>
      <input type="url" name="kv_indexnow_single_url" placeholder="https://kvadratyra.ru/..." class="regular-text" style="margin-right:8px;">
      <button type="submit" name="kv_indexnow_push_single" class="button button-secondary">Добавить в очередь</button>
    </form>

    <form method="post" style="margin-bottom:16px;">
      <?php wp_nonce_field('kv_indexnow_send_now'); ?>
      <p>Отправить очередь на IndexNow прямо сейчас (до 100 URL):</p>
      <button type="submit" name="kv_indexnow_send_now" class="button button-primary">Отправить сейчас</button>
    </form>

    <?php if (!empty($log)) : ?>
    <hr>
    <h2>Последние отправки</h2>
    <table class="widefat striped" style="max-width:800px;">
      <thead><tr><th>Время</th><th>URL</th><th>Код</th><th>Статус</th></tr></thead>
      <tbody>
      <?php foreach (array_reverse(array_slice($log, -10)) as $entry) : ?>
        <tr>
          <td><?php echo esc_html($entry['time'] ?? '—'); ?></td>
          <td style="max-width:400px;word-break:break-all;"><?php echo esc_html(implode(', ', array_slice((array)($entry['urls'] ?? []), 0, 5))); ?><?php if (count((array)($entry['urls'] ?? [])) > 5) echo ' ...+' . (count($entry['urls']) - 5); ?></td>
          <td><?php echo (int)($entry['code'] ?? 0); ?></td>
          <td><?php echo ($entry['ok'] ?? false) ? '✓' : '✗'; ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
  <?php
}
