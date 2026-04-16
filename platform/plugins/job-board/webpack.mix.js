let mix = require('laravel-mix');

const path = require('path');
let directory = path.basename(path.resolve(__dirname));

const source = 'platform/plugins/' + directory;
const dist = 'public/vendor/core/plugins/' + directory;

mix
    .sass(source + '/resources/assets/sass/avatar.scss', dist + '/css')
    .sass(source + '/resources/assets/sass/currencies.scss', dist + '/css')
    .sass(source + '/resources/assets/sass/account-admin.scss', dist + '/css')
    .sass(source + '/resources/assets/sass/invoice.scss', dist + '/css')
    .sass(source + '/resources/assets/sass/review.scss', dist + '/css')
    .sass(source + '/resources/assets/sass/style-dashboard.scss', dist + '/css')
    .sass(source + '/resources/assets/sass/style-dashboard-rtl.scss', dist + '/css')
    .js(source + '/resources/assets/js/job.js', dist + '/js')
    .js(source + '/resources/assets/js/avatar.js', dist + '/js')
    .js(source + '/resources/assets/js/currencies.js', dist + '/js')
    .js(source + '/resources/assets/js/app.js', dist + '/js')
    .js(source + '/resources/assets/js/main.js', dist + '/js')
    .js(source + '/resources/assets/js/account-admin.js', dist + '/js')
    .js(source + '/resources/assets/js/employer-colleagues.js', dist + '/js')
    .js(source + '/resources/assets/js/global-custom-fields.js', dist + '/js')
    .js(source + '/resources/assets/js/custom-fields.js', dist + '/js')
    .js(source + '/resources/assets/js/bulk-import.js', dist + '/js')
    .js(source + '/resources/assets/js/export.js', dist + '/js')
    .js(source + '/resources/assets/js/coupon.js', dist + '/js')
    .js(source + '/resources/assets/js/main-dashboard.js', dist + '/js')
    .js(source + '/resources/assets/js/components.js', dist + '/js')

if (mix.inProduction()) {
    mix
        .copy(dist + '/js/job.js', source + '/public/js')
        .copy(dist + '/js/avatar.js', source + '/public/js')
        .copy(dist + '/js/currencies.js', source + '/public/js')
        .copy(dist + '/js/app.js', source + '/public/js')
        .copy(dist + '/js/main.js', source + '/public/js')
        .copy(dist + '/js/account-admin.js', source + '/public/js')
        .copy(dist + '/js/employer-colleagues.js', source + '/public/js')
        .copy(dist + '/js/global-custom-fields.js', source + '/public/js')
        .copy(dist + '/js/custom-fields.js', source + '/public/js')
        .copy(dist + '/js/bulk-import.js', source + '/public/js')
        .copy(dist + '/js/export.js', source + '/public/js')
        .copy(dist + '/js/coupon.js', source + '/public/js')
        .copy(dist + '/js/main-dashboard.js', source + '/public/js')
        .copy(dist + '/js/components.js', source + '/public/js')

        .copy(dist + '/css/avatar.css', source + '/public/css')
        .copy(dist + '/css/currencies.css', source + '/public/css')
        .copy(dist + '/css/account-admin.css', source + '/public/css')
        .copy(dist + '/css/invoice.css', source + '/public/css')
        .copy(dist + '/css/review.css', source + '/public/css')
        .copy(dist + '/css/style-dashboard.css', source + '/public/css')
        .copy(dist + '/css/style-dashboard-rtl.css', source + '/public/css')
}
