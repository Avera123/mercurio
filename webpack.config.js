var Encore = require('@symfony/webpack-encore');

Encore
    // the project directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // the public path used by the web server to access the previous directory
    .setPublicPath('/build')
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    // uncomment to create hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    // uncomment to define the assets of the project
    .addEntry('js/app', './assets/app.js')
    .addEntry('js/modernizr.min', './assets/js/modernizr.min.js')

    .addStyleEntry('plugins/css/morris', './assets/plugins/morris/morris.css')
    .addStyleEntry('css/bootstrap.min', './assets/css/bootstrap.min.css')
    .addStyleEntry('css/components', './assets/css/components.css')
    .addStyleEntry('css/core', './assets/css/core.css')
    .addStyleEntry('css/icons', './assets/css/icons.css')
    .addStyleEntry('css/menu', './assets/css/menu.css')
    .addStyleEntry('css/pages', './assets/css/pages.css')
    .addStyleEntry('css/responsive', './assets/css/responsive.css')
    .addStyleEntry('plugins/css/switchery', './assets/css/responsive.css')

    .addEntry('js/jquery.min', './assets/js/jquery.min.js')
    .addEntry('js/bootstrap.min', './assets/js/bootstrap.min.js')
    .addEntry('js/detect', './assets/js/detect.js')
    .addEntry('js/fastclick', './assets/js/fastclick.js')
    .addEntry('js/jquery.blockUI', './assets/js/jquery.blockUI.js')
    .addEntry('js/waves', './assets/js/waves.js')
    .addEntry('js/jquery.slimscroll', './assets/js/jquery.slimscroll.js')
    .addEntry('js/jquery.scrollTo.min', './assets/js/jquery.scrollTo.min.js')
    .addEntry('js/plugins/switchery.min', './assets/plugins/switchery/switchery.min.js')
    .addEntry('js/plugins/jquery.waypoints.min', './assets/plugins/waypoints/jquery.waypoints.min.js')
    .addEntry('js/plugins/jquery.counterup.min', './assets/plugins/counterup/jquery.counterup.min.js')
    // uncomment if you use Sass/SCSS files
    // .enableSassLoader()

    // uncomment for legacy applications that require $/jQuery as a global variable
    .autoProvidejQuery()
;

module.exports = Encore.getWebpackConfig();
