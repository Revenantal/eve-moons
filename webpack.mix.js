let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.scripts([
    'node_modules/mdbootstrap/js/jquery-3.3.1.min.js',
    'node_modules/mdbootstrap/js/popper.js',
    'node_modules/mdbootstrap/js/bootstrap.js',
    'node_modules/mdbootstrap/js/mdb.js'
], 'public/js/mdbootstrap.js');



mix.js('resources/assets/js/app.js', 'public/js')
   .sass('resources/assets/sass/app.scss', 'public/css')
    .sass('resources/assets/sass/public.scss', 'public/css')
    .sass('resources/assets/sass/secure.scss', 'public/css');
