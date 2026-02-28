<?php
/**
 * AJAX handler for calculator/wizard lead form.
 * Sends the lead to Telegram via Bot API (wp_remote_post).
 *
 * WP options used:
 *   kv_tg_bot_token  – Telegram bot token
 *   kv_tg_chat_id    – Chat/group ID to send leads to
 *
 * @package Kvadratyra
 */
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_kv_calc_lead', 'kv_calc_lead_handler');
add_action('wp_ajax_nopriv_kv_calc_lead', 'kv_calc_lead_handler');

function kv_calc_lead_handler(): void {
    // Verify nonce
    if (!check_ajax_referer('kv_calc_nonce', '_nonce', false)) {
        wp_send_json_error(['message' => 'Ошибка безопасности. Обновите страницу.'], 403);
    }

    // Rate-limit: 1 submission per 30s per IP
    $ip   = sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '');
    $tkey = 'kv_calc_' . md5($ip);
    if (get_transient($tkey)) {
        wp_send_json_error(['message' => 'Подождите 30 секунд перед повторной отправкой.'], 429);
    }
    set_transient($tkey, 1, 30);

    // Sanitize fields
    $phone     = sanitize_text_field($_POST['phone'] ?? '');
    $messenger = sanitize_text_field($_POST['messenger'] ?? '');
    $city      = sanitize_text_field($_POST['city'] ?? '');
    $call_time = sanitize_text_field($_POST['call_time'] ?? '');
    $service   = sanitize_text_field($_POST['service'] ?? '');
    $details   = sanitize_text_field($_POST['details'] ?? '');
    $estimate  = sanitize_text_field($_POST['estimate'] ?? '');
    $page_url  = esc_url_raw($_POST['page_url'] ?? home_url('/'));

    // At least one contact is required: phone OR messenger.
    $phone_digits = preg_replace('/\D/', '', $phone);
    $messenger_trim = trim((string)$messenger);
    $messenger_len = function_exists('mb_strlen') ? mb_strlen($messenger_trim) : strlen($messenger_trim);
    if (strlen($phone_digits) < 10 && $messenger_len < 3) {
        wp_send_json_error(['message' => 'Укажите телефон или контакт в мессенджере.'], 422);
    }

    // Telegram send
    $token   = get_option('kv_tg_bot_token', '');
    $chat_id = get_option('kv_tg_chat_id', '');

    if (!$token || !$chat_id) {
        // Fallback: save as WP option log (admin can retrieve later)
        $leads = get_option('kv_calc_leads', []);
        $leads[] = [
            'time'      => current_time('mysql'),
            'phone'     => $phone,
            'messenger' => $messenger,
            'city'      => $city,
            'service'   => $service,
            'details'   => $details,
            'estimate'  => $estimate,
        ];
        update_option('kv_calc_leads', array_slice($leads, -200));
        wp_send_json_success(['message' => 'Заявка принята! Перезвоним в течение 15 минут.']);
    }

    $call_time_labels = [
        'any'     => 'Любое',
        'morning' => 'Утро (9–12)',
        'day'     => 'День (12–17)',
        'evening' => 'Вечер (17–20)',
    ];

    $text  = "📋 <b>Новая заявка с калькулятора</b>\n\n";
    if ($phone)     $text .= "📞 Телефон: <code>{$phone}</code>\n";
    if ($messenger) $text .= "💬 Мессенджер: {$messenger}\n";
    if ($city)      $text .= "📍 Город: {$city}\n";
    if ($service)   $text .= "🔧 Услуга: {$service}\n";
    if ($details)   $text .= "📝 Детали: {$details}\n";
    if ($estimate)  $text .= "💰 Оценка: {$estimate}\n";
    if ($call_time) $text .= "🕐 Звонить: " . ($call_time_labels[$call_time] ?? $call_time) . "\n";
    $text .= "\n🔗 " . $page_url;

    $response = wp_remote_post("https://api.telegram.org/bot{$token}/sendMessage", [
        'body'    => [
            'chat_id'    => $chat_id,
            'text'       => $text,
            'parse_mode' => 'HTML',
        ],
        'timeout' => 10,
    ]);

    if (is_wp_error($response)) {
        // Save locally as fallback
        $leads = get_option('kv_calc_leads', []);
        $leads[] = [
            'time'      => current_time('mysql'),
            'phone'     => $phone,
            'messenger' => $messenger,
            'city'      => $city,
            'service'   => $service,
            'details'   => $details,
            'estimate'  => $estimate,
            'tg_error'  => $response->get_error_message(),
        ];
        update_option('kv_calc_leads', array_slice($leads, -200));
    }

    wp_send_json_success(['message' => 'Заявка принята! Перезвоним в течение 15 минут.']);
}

/**
 * Localize AJAX URL and nonce for frontend.
 */
add_action('wp_enqueue_scripts', function () {
    if (is_admin()) return;

    wp_localize_script('kv-main', 'kvCalc', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('kv_calc_nonce'),
    ]);
}, 30);
