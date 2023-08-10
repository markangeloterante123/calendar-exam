import Vue from 'vue'
import { mapGetters, mapMutations } from 'vuex'
import SecureLS from 'secure-ls'

import aes from 'crypto-js/aes'
import encHex from 'crypto-js/enc-hex'
import padZeroPadding from 'crypto-js/pad-zeropadding'

Vue.mixin({
  computed: {
    ...mapGetters({
      getShowStatus: 'global/modal/getShowStatus',
      getShowStatus: 'global/modal/getShowStatus',
      getTemplateShowStatus: 'global/templates/getTemplateShowStatus',
      getContentLoaderStatus: 'global/content-loader/getContentLoaderStatus',
      getMobile: 'global/settings/getMobile',
      getLazy: 'global/settings/getLazy',
      getAuthenticated: 'global/settings/getAuthenticated'
    })
  },
  methods: {
    ...mapMutations({
      toggleModalStatus: 'global/modal/toggleModalStatus',
      toggleTemplateModalStatus: 'global/templates/toggleTemplateModalStatus',
      toggleContentLoaderStatus: 'global/content-loader/toggleContentLoaderStatus',
      setLazy: 'global/settings/setLazy'
    }),
    inhouseAdsReporter (ad, link) {
      if (ad) {
        this.$axios.$get('https://geolocation-db.com/json/').then((response) => {
          let object = {
            ads_inhouse_pivot_id: ad.ads_inhouse_pivot_id,
            ip: response.IPv4,
            country: response.country_code,
            city: response.city
          }

          let key = encHex.parse('43e3b0f3405b2b7707e398f0171a91a2'),
              iv = encHex.parse('91bc845cbd4076fb9a0fdc2ad37e425d'),
              stringified_data = JSON.stringify(object),
              encrypted = aes.encrypt(
                stringified_data,
                key,
                {
                  iv: iv,
                  padding: padZeroPadding
                }
              ).toString()

          this.$axios.$post('web/ad/click', { dt: encrypted }).then((response) => {
            console.log(response);
          })
        })
        if (link) {
          let a_tag = document.createElement('a')
          a_tag.href = link
          a_tag.target = '_blank'
          a_tag.click()
        }
      }
    },
    getInhouseAds (payload, ad_type = 'global') {
      let results = (ad_type == 'global')
      ? {
        global: {}
      }
      : {
        module: {}
      },
        globals = ['leaderboard-ads', 'bottom-ads']
      
      /**
      * Global Ads */
      if (ad_type == 'global') {
        globals.forEach((identifier) => {
          results.global[identifier] = payload.global.filter((item) => {
            return item.ads_inhouse_module.slug == identifier &&
            item.ads_inhouse_client_active
          }).map((item) => item.ads_inhouse_client_active)

          results.global[identifier] = (results.global[identifier].length > 0) ? results.global[identifier] : null
          if (results.global[identifier]) {
            results.global[identifier] = results.global[identifier].map((item) => {
              let image_indexes = ['web_image', 'mobile_image', 'web_image_hover', 'mobile_image_hover'],
                images = {},
                record = {}

              switch (identifier) {
                case 'bottom-ads':
                  for (let i = 0, i_len = image_indexes.length; i < i_len; i++) {
                    images[image_indexes[i]] = item.images.filter((image) => {
                      return image.category == image_indexes[i]
                    }).map((image) => {
                      return {
                        path: image.path,
                        alt: image.alt,
                        title: image.title
                      }
                    })[0]
                  }
                  break
                case 'leaderboard-ads':
                  if (item.slider) {
                    let web_sliders = item.images.filter((image) => {
                        return image.category == 'web_sliders'
                      }),
                      mobile_sliders = item.images.filter((image) => {
                        return image.category == 'mobile_sliders'
                      })
                    
                    images = web_sliders.map((item, key) => {
                      return {
                        web_link: item.link,
                        mobile_link: mobile_sliders[key].link,
                        image: item.path,
                        mobile_image: mobile_sliders[key].path,
                        image_alt: item.alt,
                        mobile_image_alt: mobile_sliders[key].alt,
                        image_title: item.title,
                        mobile_image_title: mobile_sliders[key].title
                      }
                    })
                  }
                  break
              }

              delete item.images
              record = (identifier == 'bottom-ads') ? { ...item, ...images } : { ...item, images }

              return record
            })[0]
          }
        })
      } else {
        payload.modules.forEach((item) => {
          results.module[item.ads_inhouse_module.slug] = null
        })

        for (const identifier in results.module) {
          results.module[identifier] = payload.modules.filter((item) => {
            return item.ads_inhouse_module.slug == identifier &&
            item.ads_inhouse_client_active
          }).map((item) => item.ads_inhouse_client_active)

          results.module[identifier] = (results.module[identifier].length > 0) ? results.module[identifier] : null

          if (results.module[identifier]) {
            results.module[identifier] = results.module[identifier].map((item) => {
              let record = {},
                web_image = item.images.filter((image) => {
                  return image.category == 'web_image'
                }).map((image) => { return { ...image } })[0],
                mobile_image = item.images.filter((image) => {
                  return image.category == 'mobile_image'
                }).map((image) => { return { ...image } })[0]
  
              delete item.images
              record = {
                ...item,
                web_image,
                mobile_image
              }
              
              return record
            })[0]
          }
        }
      }

      return results
    },
    convertImageExtention (payload, extension) {
      let result = ''
      result = payload.replace('png', extension)

      return result
    },
    getAuthor (payload) {
      let result = '',
          exploded = payload.author.split(' '),
          last_name = exploded[exploded.length - 1]

      result = (this.getMobile)
      ? `${payload.author.charAt(0)}. ${last_name}`
      : payload.author

      return result
    },
    highlightCard (page) {
      let ls = new SecureLS(),
          compares = (ls.get('_compares_')) ? JSON.parse(ls.get('_compares_')) : [],
          result = false

      if (compares.length > 0 && page.payload) {
        compares.forEach(compare => {
          if (page.payload.id == compare.vehicle.id) {
            page.selected = compare
            result = true
          }
        })
      }

      page.compares_length = compares.length
      page.highlight = result
      this.$nuxt.$emit('recount')
    },
    removeToCompare (page) {
      let ls = new SecureLS(),
          compares = (ls.get('_compares_')) ? JSON.parse(ls.get('_compares_')) : []

      if (compares.length > 0) {
        compares.forEach((compare, key) => {
          if (page.payload.id == compare.vehicle.id) {
            compares.splice(key, 1)
          }
        })

        ls.remove('_compares_')
        ls.set('_compares_', JSON.stringify(compares))

        if (page.compare) {
          this.$nuxt.$emit('update-compared-vehicles')
        } else {
          page.proxyHighlight()
          this.$nuxt.$emit('populate-compare')
        }
      }
    },
    websiteChecker (items, page) {
      let ls = new SecureLS()

      items.map((item) => {
        if (!ls.get(`_${item}_`)) {
          page.toggle[item] = true
        } else {
          if (ls.get(`_${item}_expiration_`)) {
            let agreed_date = this.$moment(ls.get(`_${item}_expiration_`)).add((item == 'ads') ? 1 : 7, 'days'),
              expiration_date = this.$moment()
            if (agreed_date.diff(expiration_date) <= 0) {
              ls.remove(`_${item}_`)
              ls.remove(`_${item}_expiration_`)
              page.toggle[item] = true
            }
          }
        }
      })
    },
    formatBytes (bytes, decimals) {
      if (bytes == 0) return '0 Byte'
      let k = 1024,
        dm = decimals || 2,
        sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'],
        i = Math.floor(Math.log(bytes) / Math.log(k))

      return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i]
    },
    hasAlphabet (str) {
      let code = ''

      for (let i = 0, len = str.length; i < len; i++) {
        code = str.charCodeAt(i)
        if (code == 45 ||
            (code > 64 && code < 91) || // upper alpha (A-Z)
            (code > 96 && code < 123)) { // lower alpha (a-z)
          return false
        }
      }
      return true
    },
    animateElements (page, targets) {
      /**
       * Observer (IntersectionObserver)
       * @param {[array]} items [target elements] */
      let observer = new IntersectionObserver((items) => {
        items.forEach((item, key) => {
          if (item.isIntersecting) {
            setTimeout(() => {
              item.target.classList.add(page.attr.viewport)
              observer.unobserve(item.target)
            }, 150 * (key + 1))
          }
        })
      }, { rootMargin: '-10% 0% -10% 0%' })

      /**
       * Listing all the elements in order to observe */
      targets.forEach((target, key) => {
        let elements = document.querySelectorAll(target)
        elements.forEach((element, k) => {
          observer.observe(element)
        })
      })
    },
    parseInputToDate (target) {
      let result = '',
          value = target.split('-').join('')
      if (value.length > 0) {
        value = value.match(new RegExp('.{1,4}', 'g')).join('-')
        let array_checker = value.split('-')
        if (array_checker[1]) {
          if (array_checker[1].length > 0) {
            value = value.split('-')
            result = `${value[0]}-`
            result += value[1].match(new RegExp('.{1,2}', 'g')).join('-')
          }
        } else {
          result = value
        }
      }
      return result
    },
    sharer (type, link = null) {
      if (link == null) {
        link = window.location.href
      }

      let isMobile = (window.innerWidth <= 768) ? true : false
      let shareLink = null

      switch (type) {
        case 'facebook':
          shareLink = `https://${(isMobile) ? 'm' : 'www'}.facebook.com/sharer/sharer.php?u=${link}`
          break
        case 'twitter':
          shareLink = `https://${(isMobile) ? 'm' : 'www'}.twitter.com/share?url=${link}`
          break
        case 'linkedn':
          shareLink = `http://www.linkedin.com/shareArticle?mini=true&url=${link}&title=I wanted to share this great website with you`
          break
        case 'whatsapp':
          shareLink = `https://api.whatsapp.com/send?text=I%20wanted%20to%20share%20this%20great%20website%20with%20you%20${link}`
          break
        case 'telegram':
          shareLink = `https://t.me/share/url?url=${link}&text=I wanted to share this great website with you`
          break
      }
      window.open(shareLink, "shareWindow", "status=1,width=600,height=450")
    },
    randomString () {
      return Math.random().toString(36).substring(2)
    },
    totalCount (value, decimal = false) {
      let count = 0
      if (value) {
        count = (decimal) ? parseFloat(value).toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") : parseFloat(value).toFixed().toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
      }
      return (count == 0) ? value : count
    },
    toJSON (data) {
      if (data) {
        return JSON.parse(JSON.stringify(Object.fromEntries(data)))
      }
    },
    parser (data) {
      if (data) {
        return JSON.parse(data)
      }
    },
    replacer (data) {
      if (data) {
        return data.replace(/\-/g, ' ')
      }
    },
    convertToSlug (data) {
      let slug = ''
      if (data.match(/[!@#$%^&*(){}:;"’'<>?,./|“”]|\`|\~|\=|\-|\+|\_|\[|\]|\\/g)) {
        slug = data.toLowerCase().replace(/[!@#$%^&*(){}:;"'’<>?,./|“”]|\`|\~|\=|\-|\+|\_|\[|\]|\\/g, '').replace(/\s/g, '-')
      } else {
        slug = data.toLowerCase().replace(/\s/g, '-')
      }
      return slug
    }
  }
})
