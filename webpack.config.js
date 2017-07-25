const Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('web/assets/')
    .setPublicPath('/assets')
    .cleanupOutputBeforeBuild()
    .addEntry('app', './app/Resources/assets/js/app.js')
    .addStyleEntry('styles', './app/Resources/assets/scss/app.scss')
    .enableSassLoader()
    .enableVueLoader()
    .enableSourceMaps(!Encore.isProduction())
;

module.exports = Encore.getWebpackConfig();