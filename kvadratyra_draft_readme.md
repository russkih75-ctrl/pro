### Что это
Черновая сборка сайта `kvadratyra.ru`:
- кастомная тема `kvadratyra-theme` (Schema, TOC, FAQ, слайдер, квиз)
- MU‑плагин `kv-geo` (гео‑матрица через виртуальные страницы `/geo/...`, валидатор уникальности, IndexNow очередь, robots Clean‑param, geo‑sitemap)

### Где лежит в проекте
- Тема: `wp-content/themes/kvadratyra-theme/`
- MU‑плагин: `wp-content/mu-plugins/kv-geo.php` и `wp-content/mu-plugins/kv-geo/`

### Как поставить на WordPress
1) Залейте папку темы `kvadratyra-theme` в `wp-content/themes/`.\n
2) Залейте MU‑плагин:\n
   - `kv-geo.php` в `wp-content/mu-plugins/`\n
   - папку `kv-geo/` в `wp-content/mu-plugins/`\n
3) В админке WP включите тему `Kvadratyra Theme (Vite)`.\n
4) Обновите правила ЧПУ: WP → Настройки → Постоянные ссылки → “Сохранить”. (нужно для rewrite `/geo/...`).\n

### Проверка
- Откройте `https://ВАШ_ДОМЕН/geo/borisoglebsk/`\n
- Откройте `https://ВАШ_ДОМЕН/kv-geo-sitemap.xml`\n

### Важно про черновик (noindex)
Пока в `cities.json` нет `photos` (и в целом мало локальных фактов), GEO‑страницы будут автоматически:\n
- **noindex,nofollow**\n
- **не попадут** в `kv-geo-sitemap.xml`\n
Это нормально для “черновика”. Когда вы дадите фото/кейсы/офисы — я заполню данные, и страницы начнут индексироваться.\n

### Где править города/районы
`wp-content/mu-plugins/kv-geo/data/cities.json`\n

