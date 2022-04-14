const mix = require('laravel-mix');
const webpackConfig = require('./webpack.config');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your theme assets. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.webpackConfig(webpackConfig)
    .options({ processCssUrls: false })
    .copy('node_modules/jquery/dist/jquery.min.js', 'assets/vendor/jquery.min.js')
    .js('assets/vendor/bootstrap-5.1.3/bootstrap.js', 'assets/vendor/bootstrap-5.1.3/js/bootstrap.min.js')
    .sass('assets/vendor/bootstrap-5.1.3/bootstrap.scss', 'assets/vendor/bootstrap-5.1.3/css/bootstrap.css')
    .sass('assets/vendor/bootstrap-icons/bootstrap-icons.scss', 'assets/vendor/bootstrap-icons/bootstrap-icons.css')
    .copy('node_modules/bootstrap-icons/font/fonts/', 'assets/vendor/bootstrap-icons/fonts/')
;
