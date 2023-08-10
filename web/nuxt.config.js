export default {
  // Global page headers: https://go.nuxtjs.dev/config-head
  head: {
    title: 'web',
    htmlAttrs: {
      lang: 'en'
    },
    meta: [
      { charset: 'utf-8' },
      { name: 'viewport', content: 'width=device-width, initial-scale=1' },
      { hid: 'description', name: 'description', content: '' },
      { name: 'format-detection', content: 'telephone=no' },
      { hid: 'theme-color', name: 'theme-color', content: '#E12328' }
    ],
    link: [
      { rel: 'shortcut icon', type: 'image/svg+xml', href: '/logo.svg' },
      { rel: 'apple-touch-icon', type: 'image/svg+xml', href: '/logo.svg' }
    ]
  },

  loading: {
    color: '#FFF',
    throttle: 0,
    height: '2px'
  },

  // Global CSS: https://go.nuxtjs.dev/config-css
  css: [
  ],

  // Plugins to run before rendering page: https://go.nuxtjs.dev/config-plugins
  plugins: [
    {
      src: '~/plugins/vue-scrollto',
      ssr: false
    },
    {
      src: '~/plugins/vee-validate',
      ssr: false
    },
    {
      src: '~/plugins/vue-click-outside',
      ssr: false
    },
    {
      src: '~/plugins/vue-moment'
    },
    {
      src: '~/plugins/mixins'
    },
  ],

  // Auto import components: https://go.nuxtjs.dev/config-components
  components: [
    '~components/global',
    '~components/global/modal',
    '~components/global/template',
    '~components/templates/wrapper'
  ],

  // Modules for dev and build (recommended): https://go.nuxtjs.dev/config-modules
  buildModules: [
    '@nuxtjs/google-fonts',
    '@nuxtjs/dotenv'
  ],

  // Modules: https://go.nuxtjs.dev/config-modules
  modules: [
    // https://go.nuxtjs.dev/axios
    '@nuxtjs/axios',
    '@nuxtjs/recaptcha',
    '@nuxt/image',
    [
      'nuxt-compress', {
        gzip: {
          threshold: 8192
        },
        brotli: {
          threshold: 8192
        }
      }
    ]
  ],

  recaptcha: {
    hideBadge: true,
    size: 'compact',
    siteKey: '6LcgZzkgAAAAADfytZzgIMCYJpwN4pBOGPHhcRQX',
    version: 2,
  },

  googleFonts: {
    download: true,
    inject: true,
    useStylesheet: true,
    families: {
      'Source+Sans+Pro': [400,500,600,700]
    },
    display: 'swap'
  },

  router: {
    scrollBehavior(to, from, savedPosition) {
      if (to.path !== from.path) {
        document.body.scrollTo(0, 0)
        return { x: 0, y: 0 }
      }
    }
  },

  // Axios module configuration: https://go.nuxtjs.dev/config-axios
  axios: {
    credentials: false,
    baseURL: process.env.API_URL,
  },

  // Build Configuration: https://go.nuxtjs.dev/config-build
  build: {
    transpile: ["vee-validate/dist/rules"],
    loaders: {
      cssModules: {
        modules: {
          localIdentName: "css__[hash:base64:6]",
        }
      }
    },
    /*
    ** You can extend webpack config here
    */
    extend(config, {
      isDev,
      isClient
    }) {
      config.module.rules.forEach(rule => {
        if (String(rule.test) === String(/\.(png|jpe?g|gif|svg|webp)$/)) {
          // add a second loader when loading images
          rule.use.push({
            loader: 'image-webpack-loader',
            options: {
              svgo: {
                plugins: [
                  // use these settings for internet explorer for proper scalable SVGs
                  // https://css-tricks.com/scale-svg/
                  {
                    removeViewBox: false
                  },
                  {
                    removeDimensions: true
                  }
                ]
              }
            }
          })
        }
      })
    }
  },

  render: {
    // Setting up cache for 'static' directory - a year in milliseconds
    // https://web.dev/uses-long-cache-ttl
    static: {
      maxAge: 60 * 60 * 24 * 365 * 1000,
    },
  },
  
  serverMiddleware: [
    (req, res, next) => {
      if (/\/{2,}/.test(req.url)) {
        const url = req.url.replace(/\/{2,}/g, '/')
        res.writeHead(301, {
          'Location': url
        })
        return res.end()
      }
      next()
    }
  ]
}
