<?php
/**
 * 404 Error Page — Engaging design with navigation assistance
 *
 * @package Kvadratyra
 */

get_header();
?>

<section class="error-404">
    <div class="container">
        <div class="error-404__num" aria-hidden="true">404</div>
        <h1 class="error-404__title">Страница не найдена</h1>
        <p class="error-404__text">
            Возможно, страница была удалена или вы перешли по неверной ссылке.
            Воспользуйтесь навигацией ниже, чтобы найти нужную информацию.
        </p>
        <div class="btn-group" style="justify-content:center;flex-wrap:wrap;">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn--primary btn--lg">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px;vertical-align:-4px;"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                На главную
            </a>
            <a href="<?php echo esc_url(home_url('/geo/')); ?>" class="btn btn--lg">
                География
            </a>
            <a href="<?php echo esc_url(home_url('/blog/')); ?>" class="btn btn--lg">
                Блог
            </a>
        </div>

        <div class="grid grid--3" style="margin-top:64px;">
            <a href="<?php echo esc_url(home_url('/krovlya/')); ?>" class="card card--link service-card">
                <div class="service-card__body">
                    <div class="service-card__tag">Кровля</div>
                    <h3 class="card__title">Монтаж кровли</h3>
                    <div class="card__text">Металлочерепица, профнастил, мягкая кровля.</div>
                </div>
            </a>
            <a href="<?php echo esc_url(home_url('/fasady/')); ?>" class="card card--link service-card">
                <div class="service-card__body">
                    <div class="service-card__tag">Фасады</div>
                    <h3 class="card__title">Отделка фасадов</h3>
                    <div class="card__text">Сайдинг, панели, штукатурка, утепление.</div>
                </div>
            </a>
            <a href="<?php echo esc_url(home_url('/zabory/')); ?>" class="card card--link service-card">
                <div class="service-card__body">
                    <div class="service-card__tag">Заборы</div>
                    <h3 class="card__title">Установка заборов</h3>
                    <div class="card__text">Профнастил, евроштакетник, 3D-сетка.</div>
                </div>
            </a>
        </div>
    </div>
</section>

<?php get_footer(); ?>
