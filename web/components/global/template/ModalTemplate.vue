<template lang="html">
  <div
    :class="[
      attr['modal'],
      toggle.animate && attr['modal--out']
    ]"
  >
    <div :class="attr['modal__backdrop']"></div>
		<div :class="attr['modal__wrapper']">
			<div
        :class="[
          attr['modal__close'],
          attr['modal--pointer']
        ]"
        @click="close()"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          xmlns:xlink="http://www.w3.org/1999/xlink"
          viewBox="0 0 36 36"
          xml:space="preserve"
        >
          <path
            fill="#000"
            d="M33.12,5.92l-3.05-3.05L18,14.95L5.92,2.87L2.87,5.92l12.08,12.08L2.87,30.08l3.05,3.05L18,21.05l12.08,12.08l3.05-3.05L21.04,18.01L33.12,5.92z"
          />
        </svg>
      </div>
      <div :class="attr['modal__body']">
        <figure>
          <nuxt-img
            preload
            :src="getModalData('image')"
          />
        </figure>
        <!-- Header Template -->
        <header-template
          :payload="{
            title: getModalData('title'),
          }"
          :theme="getModalData('title_theme')"
          :padding="'--no-padding'"
        />
        <div :class="attr['modal__text']">
          {{ getModalData('description') }}
        </div>
        <!-- Button Template -->
        <button-template
          :status="2"
          :label="getModalData('button')"
          :template="'button--outline-black'"
          @click.native="close()"
        />
      </div>
    </div>
  </div>
</template>

<script>
  import { mapGetters } from 'vuex'

  export default {
    computed: {
      ...mapGetters({
        getItem: 'global/templates/getItem'
      })
    },
    data: () => ({
      toggle: {
        animate: false
      }
    }),
    methods: {
      close () {
        this.toggle.animate = true
        setTimeout(() => {
          this.toggleTemplateModalStatus({ type: 'modal', status: false })
          if (this.$route.path == '/unsubscribe') this.$router.push('/')
        }, 250)
      },
      getModalData (type) {
        let result = ''

        switch (type) {
          case 'image':
            result = (this.getItem.modal_type == 'success') ? '/assets/modal/modal-success.webp' : '/assets/modal/modal-error.webp'
            break
          case 'title':
            result = (this.getItem.modal_type == 'success') ? 'Success!' : 'Oops!'
            break
          case 'title_theme':
            result = (this.getItem.modal_type == 'success') ? '--green' : ''
            break
          case 'description':
            result = (this.getItem.modal_type == 'success') ? 'Congrats! Your inquiry was sent successfully.' : (this.getItem.message) ? this.getItem.message : 'Sorry, Something went wrong. Please try again.'
            break
          case 'button':
            result = (this.getItem.modal_type == 'success') ? 'Continue' : 'Try Again'
            break
        }

        return result
      }
    },
    mounted () {
      document.body.classList.add('no_scroll')
    }
  }
</script>

<style lang="stylus" module="attr">
  .modal
    position: fixed
    top: 0
    left: 0
    right: 0
    bottom: 0
    z-index: 9001
    &--out
      & ^[0]__backdrop
        animation: fade_out .5s ease-in-out
      & ^[0]__wrapper
        animation: slide_out_y .5s ease-in-out
    &--pointer
      cursor: pointer
    &__backdrop
      position: fixed
      top: 0
      left: 0
      z-index: 0
      width: 100vw
      height: 100vh
      background-color: rgba(0, 0, 0, 0.7)
      animation: fade_in .5s ease-in-out
    &__close
      position: absolute
      top: 15px
      right: 15px
      z-index: 2
      width: 20px
    &__wrapper
      position: fixed
      top: 50%
      left: 0
      right: 0
      width: 100%
      max-width: 400px
      margin: 0 auto
      border-radius: 18px
      background-color: var(--theme_white)
      transform: translateY(-50%)
      animation: slide_in_y .5s ease-in-out
    &__body
      text-align: center
      padding: 30px 50px 50px
      figure
        img
          width: 200px
    &__text
      margin-bottom: 40px
      letter-spacing: 0.4px
      color: #585858
  /** 
  * Animations */
  @keyframes fade_in
    0%
      opacity: 0
    100%
      opacity: 1
  @keyframes fade_out
    0%
      opacity: 1
    100%
      opacity: 0
  @keyframes slide_in_y
    0%
      transform: translateY(200%)
    100%
      transform: translateY(-50%)
  @keyframes slide_out_y
    0%
      transform: translateY(-50%)
    100%
      transform: translateY(200%)
  /**
  * Responsive */
  @media (max-width: 1024px) and (min-width: 280px)
    .modal
      &__wrapper
        max-width: calc(100% - 40px)
      &__body
        padding: 20px 40px 40px
        figure
          img
            width: 150px
</style>
