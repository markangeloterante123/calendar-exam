<template>
  <main
    :class="[
      (error.statusCode == 42069) ? attr['error--soon'] : '',
      attr['error']
    ]"
    v-if="loaded"
  >
    <div :class="attr['error__wrapper']">
      <template v-if="label.image_png">
        <picture>
          <source
            :srcset="label.image_png"
          />
          <nuxt-img
            format="webp"
            loading="lazy"
            preload
            width="927"
            height="450"
            :src="label.image_webp"
          />
        </picture>
      </template>
      <h1 :class="attr['error__title']">{{ label.title }}</h1>
      <h2 :class="attr['error__subtitle']">{{ label.subtitle }}</h2>
      <template v-if="label.button">
        <button-template
          :label="label.button.label"
          :link="label.button.link"
          :template="'button--outline-black'"
        />
      </template>
    </div>
  </main>
</template>

<script>
  export default {
    props: ['error'],
    layout: 'error',
    data: () => ({
      loaded: false,
      page_title: 'Propan | Home',
      label: {
        title: '',
        subtitle: '',
        button: {
          label: '',
          link: ''
        }
      }
    }),
    methods: {
      initialization () {
        this.$nuxt.$emit('toggle-footer', 'image', false)

        switch (this.error.statusCode) {
          case 404:
            this.page_title = 'Propan | Page Not Found'
            this.label = {
              title: 'Oops!',
              subtitle: 'We can\'t seem to find the page you\'re looking for.',
              image_png: '/assets/error/error-404.png',
              image_webp: '/assets/error/error-404.webp',
              button: {
                label: 'Back to Home',
                link: '/'
              }
            }
            break
          case 42069:
            this.page_title = 'Propan | Coming Soon'
            this.label = {
              title: 'Coming Soon',
              subtitle: 'We are going to launch this page very soon. Stay Tune!',
              button: null
            }
            break
          case 403:
            this.page_title = 'Propan | Access Denied'
            this.label = {
              title: 'Oops!',
              subtitle: 'You don\'t have permission to access this page.',
              image_png: '/assets/error/error-403.png',
              image_webp: '/assets/error/error-403.webp',
              button: {
                label: 'Back to Home',
                link: '/'
              }
            }
            break
          case 401:
            this.page_title = 'Propan | Unauthorized'
            this.label = {
              title: 'Error 401',
              subtitle: 'Oops! Looks like your token has been expired!',
              button: {
                label: 'Re-login',
                link: '/login'
              }
            }
            break
          default:
            this.page_title = 'Propan | Something Went Wrong'
            this.label = {
              title: 'Oops!',
              subtitle: 'Something Went Wrong.',
              image_png: '/assets/error/error-500.png',
              image_webp: '/assets/error/error-500.webp',
              button: {
                label: 'Back to Home',
                link: '/'
              }
            }
            break
        }

        setTimeout( () => {
          this.loaded = true
          this.toggleModalStatus({ type: 'loader', status: true, item: { start: false } })
          document.querySelector('html').style.backgroundColor = '#F1F1F1'
        }, 500)
      }
    },
    mounted () {
      this.initialization()
    },
    head () {
      return {
        title: this.page_title
      }
    }
  }
</script>

<style lang="stylus" module="attr">
  .error
    position: relative
    display: flex
    flex-flow: row wrap
    align-items: center
    justify-content: center
    width: 100%
    max-width: 1280px
    height: 900px
    margin: 0 auto
    padding: 5% 0 10%
    text-align: center
    &__wrapper
      position: relative
      z-index: 1
      display: flex
      flex-flow: column wrap
      align-items: center
      justify-content: center
      img
        height: 250px
      & ^[0]__title
        position: relative
        font-family: var(--merri_sans)
        font-size: 45px
        margin: 30px 0 20px
        padding-bottom: 20px
        &::before
          content: ''
          position: absolute
          bottom: 0
          left: 0
          right: 0
          width: 55px
          height: 2px
          margin: 0 auto
          background-color: var(--theme_primary)
      & ^[0]__subtitle
        width: 100%
        max-width: 300px
        margin: 0 auto
        letter-spacing: 0.4px
        margin-bottom: 30px
        font-size: 16px
        line-height: 2
        color: #585858
  /**
  * Responsive */
  @media (max-width: 1024px) and (min-width: 280px)
    .error
      max-width: 100%
      padding: 10em 20px
      &__wrapper
        img
          height: 150px
        & ^[0]__title
          font-size: 35px
        & ^[0]__subtitle
          font-size: 14px
</style>
