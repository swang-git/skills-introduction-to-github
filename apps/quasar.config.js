module.exports = function (ctx) { // can be async too
  // console.log(ctx)
  return {
    boot: [
      'pinia',
      // 'i18n',
      'axios',
    ],
    extras: [
      // ctx.mode.pwa // we're adding only if working on a PWA
      //   ? 'roboto-font'
      //   : null
      'roboto-font',
      'material-icons'
    ],
    css: [
      ctx.mode.spa ? 'app.scss' : null, // looks for /src/css/app-spa.sass
      // ctx.mode.cordova ? 'app-cordova.sass' : null  // looks for /src/css/app-cordova.sass
    ],
    build: {
      target: {
        // browser: [ 'es2019', 'edge88', 'firefox78', 'chrome87', 'safari13.1' ],
        // node: 'node'
      },

      vueRouterMode: 'history', // available values: 'hash', 'history'
      // vueRouterBase,
      // vueDevtools,
      // vueOptionsAPI: false,

      // rebuildCache: true, // rebuilds Vite/linter/etc cache on startup

      publicPath: '/' + process.env.PRODUCT_NAME  === undefined ? 'apps' : process.env.PRODUCT_NAME, // this will be injected into index.html, like
      productName: process.env.PRODUCT_NAME === undefined ? 'apps' : process.env.PRODUCT_NAME,
      appName: process.env.APP,
      env: ctx.dev ? { API: '/api', VER: process.env.PRODUCT_VER } : { API: '', VER: process.env.PRODUCT_VER },

      // publicPath: '/',
      // analyze: true,
      // env: {},
      // rawDefine: {}
      // ignorePublicFolder: true,
      // minify: false,
      // polyfillModulePreload: true,
      // distDir

      // extendViteConf (viteConf) {},
      // viteVuePluginOptions: {},

      scopeHoisting: true,
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

      vitePlugins: [
        ['vite-plugin-checker', {
          eslint: {
            lintCommand: 'eslint "./**/*.{js,mjs,cjs,vue}"'
          }
        }, { server: false }]
      ]
    },
    devServer: {
      https: false,
      host: 'devx',
      // port: ctx.mode.spa ? '8080' : (ctx.mode.pwa ? 9080 : 9090),
      proxy: [
        {
          context: ['/api'],
          target: 'http://devx',
          pathRewrite: { '^/api': '' },
          // bypass: function (req, res, proxyOptions) {
          //   if (req.headers.accept.indexOf('html') !== -1) {
          //     console.log('Skipping proxy for browser request.');
          //     return '/index.html';
          //   }
          // },
        },
      ],
    },
    // https://v2.quasar.dev/quasar-cli-vite/quasar-config-js#framework
    framework: {
      config: {},

      // iconSet: 'material-icons', // Quasar icon set
      // lang: 'en-US', // Quasar language pack

      // For special cases outside of where the auto-import strategy can have an impact
      // (like functional components as one of the examples),
      // you can manually specify Quasar components/directives to be available everywhere:
      //
      // components: [],
      // directives: [],

      // Quasar plugins
      plugins: [
        'LocalStorage',
        'SessionStorage',
        'Notify',
        'Dialog',
        'Cookies'
      ]
    },
    client: {
      // logging: 'info',
      overlay: true,
      progress: true,
      // webSocketURL: {
      //   // hostname: '0.0.0.0',
      //   hostname: 'devx',
      //   // pathname: '/ws',
      //   password: 'Ybsjll11',
      //   port: 80,
      //   // port: 8080,
      //   // protocol: 'ws',
      //   username: 'swang71@comcast.net',
      // },
    },
    // open: true,
    // open: ['/exlist'],
    // open: {
    //   app: {
    //     name: 'google-chrome',
    //   }
    // }
    // compress: true,
    // onListening: function (devServer) {
    //   if (!devServer) {
    //     throw new Error('webpack-dev-server is not defined');
    //   }
    //   const port = devServer.server.address().port;
    //   console.log('Listening on port:', port);
    // },
  }
}
