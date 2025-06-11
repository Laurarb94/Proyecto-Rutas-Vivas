const Encore = require('@symfony/webpack-encore');
const path = require('path');

Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');

Encore
    // Directorio donde Webpack guardará los archivos generados
    .setOutputPath('public/build/')
    .setPublicPath('/build')

    // Usar el archivo de entrada principal
    .addEntry('app', './assets/app.js')

    // Habilitar el versionado de los archivos para evitar problemas de cache
    .enableVersioning()

    // Configuración adicional para el entorno de desarrollo
    .enableSourceMaps(!Encore.isProduction())

    // Habilitar el soporte de Babel para JavaScript (recomendado para compatibilidad con navegadores antiguos)
    .configureBabel((config) => {
        config.plugins.push('@babel/plugin-proposal-class-properties');
    })

    .enableSingleRuntimeChunk();

// Exporta la configuración final
module.exports = Encore.getWebpackConfig();
