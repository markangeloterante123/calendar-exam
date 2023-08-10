<template>
  <div id="__ppn">
    <transition name="page">
      <Nuxt />
    </transition>

    <!-- Content Loader -->
    <transition name="fade">
      <template v-if="getContentLoaderStatus('loader')">
        <content-loader />
      </template>
    </transition>

    <!-- Modal Template -->
    <template v-if="getTemplateShowStatus('modal')">
      <modal-template />
    </template>
  </div>
</template>

<script>
  import { mapMutations, mapGetters } from 'vuex'

  export default {
    data: () => ({
      style: 0,
      prev_scroll: 0,
      toggle: {
        compliance: false
      }
    }),
    watch: {
      $route() {
        this.routeChecklist()
      }
    },
    computed: {
      ...mapGetters({
        toggled_side_nav: `global/settings/getSideNavStatus`,
        active_nav: `global/settings/getActiveNavStatus`
      })
    },
    methods: {
      ...mapMutations({
        enteredMobile: 'global/settings/enteredMobile',
        setNavAction: 'global/settings/ACTIVE_NAV'
      }),
      routeChecklist() {
        setTimeout(() => {
          this.autoPadding()
          this.onResize()
        }, 250)
      },
      onResize() {
        if (document.documentElement && document.documentElement.clientWidth) {
          this.enteredMobile({
            status: document.documentElement.clientWidth <= 1024 ? true : false
          })
        }
        document.querySelector('html').style.backgroundColor = '#FFF'
        this.autoPadding()
      },
      onScroll() {
        let current_scroll = window.pageYOffset | document.body.scrollTop

        if (current_scroll > 700) {
          this.setNavAction(true)
        } else {
          this.setNavAction(false)
        }
        console.log('dataas status', this.active_nav)
      },
      autoPadding() {
        let topbar = document.querySelector('.__tb')

        if (topbar) {
          let padding_top = topbar.offsetHeight

          this.style = `padding-top: ${padding_top}px`
        }
      },
      windowScroll() {
        let current_scroll = window.pageYOffset | document.body.scrollTop

        this.$nuxt.$emit(
          'toggle-top-bar',
          current_scroll > this.prev_scroll ? false : true
        )
        this.prev_scroll = current_scroll
      },
      initialize() {
        setTimeout(() => {
          this.onResize()
          this.autoPadding()
          this.websiteChecker(['compliance'], this)
        }, 500)
      }
    },
    mounted() {
      document.addEventListener('contextmenu', event => event.preventDefault())
      window.addEventListener('load', this.onResize)
      window.addEventListener('resize', this.onResize)
      window.addEventListener('scroll', this.windowScroll)
      window.addEventListener('scroll', this.onScroll)

      this.initialize()
      console.log(
        '%c What are you doing?! Close this!',
        'font-weight: bold; font-size: 40px;color: white; text-shadow: 3px 3px 0 rgb(217,31,38) , 6px 6px 0 rgb(226,91,14) , 9px 9px 0 rgb(245,221,8) , 12px 12px 0 rgb(5,148,68) , 15px 15px 0 rgb(2,135,206) , 18px 18px 0 rgb(4,77,145) , 21px 21px 0 rgb(42,21,113)'
      )
    },
    destroyed() {
      window.removeEventListener('load', this.onResize)
      window.removeEventListener('resize', this.onResize)
      window.removeEventListener('scroll', this.windowScroll)
      window.removeEventListener('scroll', this.onScroll)
    },
    head() {
      return {
        title: 'Propan | Welcome'
      }
    }
  }
</script>

<style lang="stylus">
  :root
    --theme_white: #FFF
    --theme_white_v2: #F1F1F1
    --theme_gray: #6E6E74
    --theme_black: #232323
    --theme_primary: #007DB7
    --theme_secondary: #007DB7
    --theme_tertiary: #FDB515
    --theme_success: #3AC142
    --theme_orange: #EC7526
    --theme_error: #D13744
    --theme_link: #0161AE
    --source_sans: 'Source Sans Pro'
    --reg: 400
    --bold: 700

  #__ppn
    transition: .3s cubic-bezier(.17,.67,.17,1)

  html
    -webkit-backface-visibility: hidden
    -moz-backface-visibility: hidden
    -ms-backface-visibility: hidden
    -webkit-text-size-adjust: none
    -webkit-font-smoothing: subpixel-antialiased
    backface-visibility: hidden
    font-family: 'Source Sans Pro'
    font-variant-numeric: lining-nums

  *,
  *:before,
  *:after
    user-select: none
    box-sizing: border-box
    margin: 0
    word-break: break-word !important
    -webkit-user-drag: none
    font-weight: var(--reg)

  body
    position: relative
    overflow-y: overlay
    overflow-x: hidden
    font-size: 16px
    &.no_click
      pointer-events: none
    &.no_scroll
      overflow-y: hidden
    &::-webkit-scrollbar
      width: 10px
      background-color: rgba(0, 0, 0, 0.15)
    &::-webkit-scrollbar-track
      box-shadow: none
      background-color: transparent
    &::-webkit-scrollbar-thumb
      border-radius: 8px
      background-repeat: no-repeat
      background-clip: padding-box
      background-color: var(--theme_primary)

  ::-webkit-scrollbar-track
    box-shadow: none
    background-color: transparent
  ::-webkit-scrollbar
    width: 7px
    height: 5px
    background-color: rgba(0, 0, 0, 0.15)
  ::-webkit-scrollbar-thumb
    border-radius: 8px
    background-repeat: no-repeat
    background-clip: padding-box
    background-color: var(--theme_primary)

  ::selection
    color: var(--theme_white)
    background-color: var(--theme_primary)

  button
    padding: 0
    border: none
    outline: none
    box-shadow: none
    background-color: transparent

  textarea
    font-family: var(--source_sans)
    resize: vertical
    vertical-align: middle

  input,
  input:before,
  input:after,
  textarea,
  textarea:before,
  textarea:after,
  select,
  select:before,
  select:after
    -webkit-appearance: none
    border: none
    outline: none
    box-shadow: none
    user-select: initial
    background-color: transparent
    font-family: var(--source_sans)

  ::-webkit-input-placeholder
    color: #B1B1B1
    font-weight: var(--reg)
    text-transform: none

  :-ms-input-placeholder
    color: #B1B1B1
    font-weight: var(--reg)
    text-transform: none

  ::-webkit-datetime-edit
    color: #B1B1B1
    font-weight: var(--reg)
    text-transform: none

  ::placeholder
    color: #B1B1B1
    font-weight: var(--reg)
    text-transform: none

  input::-webkit-outer-spin-button,
  input::-webkit-inner-spin-button
    -webkit-appearance: none
    margin: 0

  iframe,
  video
    border: none
    outline: none

  input[type=number]
    -moz-appearance: textfield

  picture
    display: block

  h1,
  h2,
  h3,
  h4,
  h5
    font-weight: normal

  table
    width: 100%
    border-collapse: collapse

  svg,
  span,
  iframe
    vertical-align: middle

  img
    width: 100%
    height: auto
    vertical-align: middle

  a
    text-decoration: none

  p
    &:empty
      &::before
        content: ' '
        white-space: pre

  .isLoading
    width: 100px !important
    height: auto !important

  .ql-size-huge
    float: left
    padding-right: 10px
    font-family: var(--merri)
    font-size: 70px
    line-height: 1
    color: var(--theme_primary) !important
    transform: translateY(-5px)

  .__swpr
    .swiper-pagination-bullet
      background: var(--theme_white)
      opacity: 1
      transition: .3s ease-in-out
      &.swiper-pagination-bullet-active
        background: var(--theme_primary)
        opacity: 1
    .swiper-button-prev,
    .swiper-button-next
      margin-top: 0
      width: 50px
      height: 50px
      transform: translateY(-50%)
      transition: .4s ease-in-out
      &::after
        content: ''
        font-size: 0
        width: 50px
        height: 50px
        background-position: center
        background-repeat: no-repeat
        background-size: contain
    .swiper-button-prev
      left: 0
      &::after
        background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB3aWR0aD0iNTAiIGhlaWdodD0iNTAiIHZpZXdCb3g9IjAgMCA1MCA1MCI+CiAgPGRlZnM+CiAgICA8ZmlsdGVyIGlkPSJFbGxpcHNlXzQyOSIgeD0iMCIgeT0iMCIgd2lkdGg9IjUwIiBoZWlnaHQ9IjUwIiBmaWx0ZXJVbml0cz0idXNlclNwYWNlT25Vc2UiPgogICAgICA8ZmVPZmZzZXQgZHk9IjMiIGlucHV0PSJTb3VyY2VBbHBoYSIvPgogICAgICA8ZmVHYXVzc2lhbkJsdXIgc3RkRGV2aWF0aW9uPSIzIiByZXN1bHQ9ImJsdXIiLz4KICAgICAgPGZlRmxvb2QgZmxvb2Qtb3BhY2l0eT0iMC4xNjEiLz4KICAgICAgPGZlQ29tcG9zaXRlIG9wZXJhdG9yPSJpbiIgaW4yPSJibHVyIi8+CiAgICAgIDxmZUNvbXBvc2l0ZSBpbj0iU291cmNlR3JhcGhpYyIvPgogICAgPC9maWx0ZXI+CiAgPC9kZWZzPgogIDxnIGlkPSJHcm91cF85MTQwIiBkYXRhLW5hbWU9Ikdyb3VwIDkxNDAiIHRyYW5zZm9ybT0idHJhbnNsYXRlKC0yMDYgLTE3OCkiPgogICAgPGcgdHJhbnNmb3JtPSJtYXRyaXgoMSwgMCwgMCwgMSwgMjA2LCAxNzgpIiBmaWx0ZXI9InVybCgjRWxsaXBzZV80MjkpIj4KICAgICAgPGNpcmNsZSBpZD0iRWxsaXBzZV80MjktMiIgZGF0YS1uYW1lPSJFbGxpcHNlIDQyOSIgY3g9IjE2IiBjeT0iMTYiIHI9IjE2IiB0cmFuc2Zvcm09InRyYW5zbGF0ZSg5IDYpIiBmaWxsPSIjZmZmIi8+CiAgICA8L2c+CiAgICA8cGF0aCBpZD0iSWNvbl9tYXRlcmlhbC1rZXlib2FyZC1hcnJvdy1kb3duIiBkYXRhLW5hbWU9Ikljb24gbWF0ZXJpYWwta2V5Ym9hcmQtYXJyb3ctZG93biIgZD0iTTEuNTIsMCw2LjQ2OSw0LjkzOCwxMS40MTcsMGwxLjUyLDEuNTJMNi40NjksNy45ODksMCwxLjUyWiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMjM0Ljk5NCAxOTMuNTMxKSByb3RhdGUoOTApIiBmaWxsPSIjZTEyMzI4Ii8+CiAgPC9nPgo8L3N2Zz4K')
    .swiper-button-next
      right: 0
      &::after
        background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB3aWR0aD0iNTAiIGhlaWdodD0iNTAiIHZpZXdCb3g9IjAgMCA1MCA1MCI+CiAgPGRlZnM+CiAgICA8ZmlsdGVyIGlkPSJFbGxpcHNlXzQyNSIgeD0iMCIgeT0iMCIgd2lkdGg9IjUwIiBoZWlnaHQ9IjUwIiBmaWx0ZXJVbml0cz0idXNlclNwYWNlT25Vc2UiPgogICAgICA8ZmVPZmZzZXQgZHk9IjMiIGlucHV0PSJTb3VyY2VBbHBoYSIvPgogICAgICA8ZmVHYXVzc2lhbkJsdXIgc3RkRGV2aWF0aW9uPSIzIiByZXN1bHQ9ImJsdXIiLz4KICAgICAgPGZlRmxvb2QgZmxvb2Qtb3BhY2l0eT0iMC4xNjEiLz4KICAgICAgPGZlQ29tcG9zaXRlIG9wZXJhdG9yPSJpbiIgaW4yPSJibHVyIi8+CiAgICAgIDxmZUNvbXBvc2l0ZSBpbj0iU291cmNlR3JhcGhpYyIvPgogICAgPC9maWx0ZXI+CiAgPC9kZWZzPgogIDxnIGlkPSJHcm91cF85MTM5IiBkYXRhLW5hbWU9Ikdyb3VwIDkxMzkiIHRyYW5zZm9ybT0idHJhbnNsYXRlKC0xMDI0IC0xNzgpIj4KICAgIDxnIHRyYW5zZm9ybT0ibWF0cml4KDEsIDAsIDAsIDEsIDEwMjQsIDE3OCkiIGZpbHRlcj0idXJsKCNFbGxpcHNlXzQyNSkiPgogICAgICA8Y2lyY2xlIGlkPSJFbGxpcHNlXzQyNS0yIiBkYXRhLW5hbWU9IkVsbGlwc2UgNDI1IiBjeD0iMTYiIGN5PSIxNiIgcj0iMTYiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDQxIDM4KSByb3RhdGUoMTgwKSIgZmlsbD0iI2ZmZiIvPgogICAgPC9nPgogICAgPHBhdGggaWQ9Ikljb25fbWF0ZXJpYWwta2V5Ym9hcmQtYXJyb3ctZG93biIgZGF0YS1uYW1lPSJJY29uIG1hdGVyaWFsLWtleWJvYXJkLWFycm93LWRvd24iIGQ9Ik0xLjUyLDAsNi40NjksNC45MzgsMTEuNDE3LDBsMS41MiwxLjUyTDYuNDY5LDcuOTg5LDAsMS41MloiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDEwNDUuMDA2IDIwNi40NjkpIHJvdGF0ZSgtOTApIiBmaWxsPSIjZTEyMzI4Ii8+CiAgPC9nPgo8L3N2Zz4K')
    &.__swpr--alt
      .swiper-button-prev,
      .swiper-button-next
        width: 100px
        height: 100px
        transform: translateY(-50%)
        &.swiper-button-disabled
          opacity: 0
        &::after
          width: 100px
          height: 100px
    /**
    * Responsive */
    @media (max-width: 1024px) and (min-width: 280px)
      .swiper-button-prev,
      .swiper-button-next
        width: 30px
        height: 30px
        &::after
          width: 30px
          height: 30px
      .swiper-button-prev
        left: 5px
      .swiper-button-next
        right: 5px

  /*
  *** Animations
  */
  .fade-enter-active,
  .fade-leave-active
    transition: .4s ease-in-out

  .fade-enter,
  .fade-leave-to
    opacity: 0

  .searchSlide-enter-active,
  .searchSlide-leave-active
    transition: .4s ease-in-out

  .searchSlide-enter,
  .searchSlide-leave-to
    opacity: 0
    transform: translateY(-100%)

  .slideX-enter-active,
  .slideX-leave-active
    transition: .4s ease-in-out

  .slideX-enter,
  .slideX-leave-to
    opacity: 0
    transform: translateX(100%)

  .slideXAlt-enter-active,
  .slideXAlt-leave-active
    transition: .4s ease-in-out

  .slideXAlt-enter,
  .slideXAlt-leave-to
    opacity: 0
    transform: translateX(-100%)

  .slideY-enter-active,
  .slideY-leave-active
    transition: .4s ease-in-out

  .slideY-enter,
  .slideY-leave-to
    opacity: 0
    transform: translateY(-20px)

  .page-enter-active,
  .page-leave-active
    transition: .4s ease-in-out

  .page-enter,
  .page-leave-to
    opacity: 0
    transform: translateY(50px)
</style>
