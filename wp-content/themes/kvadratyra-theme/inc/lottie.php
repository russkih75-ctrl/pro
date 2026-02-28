<?php
/**
 * Lottie loader (CWV-safe):
 * - Loads only when enabled in theme settings
 * - Loads on front page + pages with calculator
 * - Lazy-loads lottie-player via IntersectionObserver (no JS until section visible)
 *
 * @package Kvadratyra
 */
if (!defined('ABSPATH')) exit;

add_action('wp_enqueue_scripts', function () {
    if (is_admin()) return;
    if (!get_option('kv_enable_lottie', 0) && !is_front_page()) return;

    // Instead of eagerly loading, we inject a tiny IO-based loader
    add_action('wp_footer', function () {
        ?>
        <script>
        (function(){
            if(!('IntersectionObserver' in window)) return;
            var loaded = false;
            function loadLottie(){
                if(loaded) return;
                loaded = true;
                var s = document.createElement('script');
                s.src = 'https://unpkg.com/@lottiefiles/lottie-player@2.0.8/dist/lottie-player.js';
                s.defer = true;
                document.body.appendChild(s);
            }
            var targets = document.querySelectorAll('lottie-player, [data-lottie-src]');
            if(!targets.length) return;
            var io = new IntersectionObserver(function(entries){
                entries.forEach(function(e){
                    if(e.isIntersecting){ loadLottie(); io.disconnect(); }
                });
            }, {rootMargin: '200px 0px'});
            targets.forEach(function(el){ io.observe(el); });
        })();
        </script>
        <?php
    }, 99);
}, 30);
