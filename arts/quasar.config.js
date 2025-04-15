// quasar.config.js
// const { configure } = require('quasar/wrappers');
import { defineConfig } from '#q-app/wrappers'

module.exports = defineConfig(function (ctx) {
  return {
    // app boot file (/src/boot)
    // https://v2.quasar.dev/quasar-cli-vite/boot-files
    boot: ['pinia', 'i18n', 'axios'],

    // https://v2.quasar.dev/quasar-cli-vite/quasar-config-file#css
    css: ['app.scss'],

    // https://github.com/quasarframework/quasar/tree/dev/extras
    extras: [
      'roboto-font', // optional, you are not bound to it
      'material-icons', // optional, you are not bound to it
    ],

    build: {
      // Enable Vue devtools and proper component naming
      vueDevTools: true,
      vueRouterMode: 'history', // available values: 'hash', 'history'

      publicPath: '/' + process.env.PRODUCT_NAME === undefined ? 'arts' : process.env.PRODUCT_NAME, //this will be injected into index.html, like
      productName: process.env.PRODUCT_NAME === undefined ? 'arts' : process.env.PRODUCT_NAME,
      appName: process.env.APP,
      buildVersion: process.env.PRODUCT_VER == undefined ? 'X' : process.env.PRODUCT_VER,

      env: ctx.dev ? { API: '/api', VER: process.env.PRODUCT_VER } : { API: '', VER: process.env.PRODUCT_VER },

      // Define global constants
      // env: {
      //   __VUE_OPTIONS_API__: JSON.stringify(true),
      //   __VUE_PROD_DEVTOOLS__: JSON.stringify(ctx.dev)
      // },

      // Webpack configuration
      extendWebpack(cfg) {
        cfg.optimization.moduleIds = 'named';
        cfg.devtool = ctx.dev ? 'source-map' : false;
      },

      uglifyOptions: {
        compress: {
          // 在UglifyJs删除没有用到的代码时不输出警告
          warnings: false,
          // 删除所有的 `console` 语句，可以兼容ie浏览器
          drop_console: true,
          // 内嵌定义了但是只用到一次的变量
          collapse_vars: true,
          // 提取出出现多次但是没有定义成变量去引用的静态值
          reduce_vars: true
        },
        output: {
          // 最紧凑的输出
          beautify: false,
          // 删除所有的注释
          comments: false
        }
      },
      // transpile: false,

      // Vite configuration (for Quasar v2.10+)
      viteVuePluginOptions: {
        reactivityTransform: true,
        template: {
          compilerOptions: {
            whitespace: 'condense',
            comments: ctx.dev
          }
        }
      }
    },

    // Full list of options: https://v2.quasar.dev/quasar-cli-vite/quasar-config-file#devserver
    devServer: {
      // https: true,
      open: true, // opens browser window automatically
      https: false,
      host: 'devx',
      port: ctx.mode.spa ? '8080' : ctx.mode.pwa ? 9080 : 9090,
      proxy: [
        {
          context: ['/api'],
          target: 'http://devx',
          pathRewrite: { '^/api': '' },
        },
      ],
    },
    // Framework options
    framework: {
      config: {
        // Enable better component introspection
        devtools: ctx.dev
      },
      plugins: [
        'LocalStorage',
        'SessionStorage',
        'Notify',
        'Dialog',
        'Cookies'
      ]
    },
  };
});
