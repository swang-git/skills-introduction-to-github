// https://github.com/michael-ciniawsky/postcss-load-config

import autoprefixer from 'autoprefixer'
// import rtlcss from 'postcss-rtlcss'

export default {
  plugins: [
    // https://github.com/postcss/autoprefixer
    autoprefixer({
      overrideBrowserslist: [
        'last 4 Chrome versions',
        'last 4 Firefox versions',
        'last 4 Edge versions',
        'last 4 Safari versions',
        'last 4 Android versions',
        'last 4 ChromeAndroid versions',
        'last 4 FirefoxAndroid versions',
<<<<<<< HEAD
        'last 4 iOS versions'
      ]
    })
=======
        'last 4 iOS versions',
      ],
    }),
>>>>>>> f67d697ec603fc6e69dd4d286f3f63a3be8036be

    // https://github.com/elchininet/postcss-rtlcss
    // If you want to support RTL css, then
    // 1. yarn/pnpm/bun/npm install postcss-rtlcss
    // 2. optionally set quasar.config.js > framework > lang to an RTL language
    // 3. uncomment the following line (and its import statement above):
    // rtlcss()
<<<<<<< HEAD
  ]
=======
  ],
>>>>>>> f67d697ec603fc6e69dd4d286f3f63a3be8036be
}
