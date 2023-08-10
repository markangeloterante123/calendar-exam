<template>
  <div
    :class="[
      '__g',
      attr['gallery'],
      (opened) ? attr['gallery--opened'] : '',
      (toggled) ? attr['gallery--toggled'] : ''
    ]"
  >
    <div
      :class="[
        'main',
        attr['gallery__main']
      ]"
    >
      <template v-if="!getMobile">
        <div :class="attr['gallery__main-overlay']">
          <button
            type="button"
            :class="[
              attr['gallery--pointer'],
              attr['gallery__overlay-left-arrow']
            ]"
            @click="prev()"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 32 32">
              <g transform="translate(-108 -384)">
                <circle cx="16" cy="16" r="16" transform="translate(108 384)" fill="#171717"/>
                <path d="M1.52,0,6.469,4.938,11.417,0l1.52,1.52L6.469,7.989,0,1.52Z" transform="translate(126.994 393.531) rotate(90)" fill="#fff"/>
              </g>
            </svg>
          </button>
          <div :class="[ '__m', attr['gallery__main-image'] ]">
            <img id="__m" :class="attr['gallery__image-target']" :src="payload.images[active_thumb].path" />
            <div id="__t" :class="attr['gallery__image-title']">{{ payload.images[active_thumb].caption }}</div>
          </div>
          <button
            type="button"
            :class="[
              attr['gallery--pointer'],
              attr['gallery__overlay-right-arrow']
            ]"
            @click="next()"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 32 32">
              <g transform="translate(-723 -351.765)">
                <circle cx="16" cy="16" r="16" transform="translate(755 383.765) rotate(180)" fill="#171717"/>
                <path d="M1.52,0,6.469,4.938,11.417,0l1.52,1.52L6.469,7.989,0,1.52Z" transform="translate(736.006 374.234) rotate(-90)" fill="#fff"/>
              </g>
            </svg>
          </button>
        </div>
      </template>
      <template v-else>
        <div
          :class="[
            attr['gallery__main-overlay'],
            attr['gallery__main-overlay--mobile']
          ]"
        >
          <client-only>
            <swiper :options="slider_options">
              <div class="swiper-pagination" slot="pagination"></div>
              <swiper-slide
                :class="attr['gallery__main-slide']"
                v-for="(data, key) in payload.images"
                :key="key"
                v-if="getMobile"
              >
                <div
                  :class="[
                    '__m',
                    attr['gallery__main-image']
                  ]"
                >
                  <img id="__m" :class="attr['gallery__image-target']" :src="data.path" />
                  <div id="__t" :class="attr['gallery__image-title']">{{ data.caption }}</div>
                </div>
              </swiper-slide>
            </swiper>
          </client-only>
        </div>
      </template>
      <div :class="attr['gallery__control']">
        <button
          type="button"
          :class="[
            attr['gallery--pointer'],
            attr['gallery__control-toggle']
          ]"
          @click="toggler()"
          v-if="!getMobile"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14">
            <g transform="translate(-926 -12)">
              <rect width="6" height="6" transform="translate(926 12)" fill="#fff"/>
              <rect width="6" height="6" transform="translate(926 20)" fill="#fff"/>
              <rect width="6" height="6" transform="translate(934 20)" fill="#fff"/>
              <rect width="6" height="6" transform="translate(934 12)" fill="#fff"/>
            </g>
          </svg>
        </button>
        <button
          type="button"
          :class="[
            attr['gallery--pointer'],
            attr['gallery__control-close']
          ]"
          @click="close()"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="14.463" height="14.463" viewBox="0 0 14.463 14.463">
            <path d="M21.963,8.957,20.507,7.5l-5.775,5.775L8.957,7.5,7.5,8.957l5.775,5.775L7.5,20.507l1.457,1.457,5.775-5.775,5.775,5.775,1.457-1.457-5.775-5.775Z" transform="translate(-7.5 -7.5)" fill="#fff"/>
          </svg>
        </button>
      </div>
    </div>
    <template v-if="!getMobile">
      <div
        :class="[
          'thumb',
          attr['gallery__thumb']
        ]"
      >
        <div :class="attr['gallery__thumb-wrapper']">
          <div
            :class="[
              '__tm',
              attr['gallery__thumb-image'],
              (key == active_thumb) ? attr['gallery__thumb-image--active'] : ''
            ]"
            v-for="(data, key) in payload.images"
            :key="key"
          >
            <img :class="attr['gallery--pointer']" :src="data.path_resized" @click="getThumb(key)" />
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
  export default {
    props: {
      payload: {
        type: Object/Array,
        default: null
      }
    },
    data: ({ payload }) => ({
      active_thumb: payload.active_key,
      opened: false,
      toggled: false,
      slider_options: {
        autoHeight: true,
        slidesPerView: 2,
        spaceBetween: 40,
        pagination: {
          el: '.swiper-pagination',
          clickable: true
        },
        breakpoints: {
          1024: {
            slidesPerView: 1,
            spaceBetween: 20
          },
          768: {
            slidesPerView: 1,
            spaceBetween: 20
          },
          640: {
            slidesPerView: 1,
            spaceBetween: 20
          },
          320: {
            slidesPerView: 1,
            spaceBetween: 0
          }
        }
      }
    }),
    methods: {
      toggler () {
        this.toggled ^= true
      },
      close () {
        this.opened = false
        this.toggled = false
        document.body.classList.remove('no_scroll')
        setTimeout( () => {
          this.$parent.toggle.gallery = false
        }, 200)
      },
      next () {
        let thumbs = document.querySelectorAll('.thumb .__tm'),
          thumb_key = this.active_thumb + 1,
          length = this.payload.images.length
        if (thumb_key != length) {
          this.payload.images.forEach((image, key) => {
            if (thumb_key == key) {
              this.active_thumb = key
            }
          })
        } else {
          this.active_thumb = 0
        }
      },
      prev () {
        let thumbs = document.querySelectorAll('.thumb .__tm'),
          thumb_key = this.active_thumb - 1,
          length = this.payload.images.length
        if (thumb_key < 0) {
          this.active_thumb = length - 1
        } else {
          this.payload.images.forEach((image, key) => {
            if (thumb_key == key) {
              this.active_thumb = key
            }
          })
        }
      },
      getThumb (key) {
        let thumbs = document.querySelectorAll('.thumb .__tm')
        document.querySelector('.main #__t').innerHTML = this.payload.images[key].title
        document.querySelector('.main #__m').src = this.payload.images[key].path
        this.active_thumb = key
        thumbs.forEach((thumb, index) => {
          if (key == index) {
            thumb.classList.add('active')
          } else {
            thumb.classList.remove('active')
          }
        })
      }
    }
  }
</script>

<style lang="stylus">
  .__g
    .swiper-container
      .swiper-pagination
        bottom: 0
        .swiper-pagination-bullet
          background-color: rgba(255, 255 ,255, 0.5) !important
          border: 1px solid rgba(255, 255 ,255, 0.5) !important
        .swiper-pagination-bullet-active
          background-color: #fff !important
          border: 1px solid #fff !important
</style>

<style lang="stylus" module="attr">
  .gallery
    position: fixed
    top: 0%
    width: 100%
    height: 100vh
    z-index: -1
    display: flex
    flex-flow: row wrap
    background-color: rgba(0, 0, 0, 0.80)
    opacity: 0
    visibility: hidden
    transition: .4s ease-in-out
    &--pointer
      cursor: pointer
    &--opened
      z-index: 99999
      opacity: 1
      visibility: visible
      & ^[0]__main
        left: 0
      & ^[0]__thumb
        right: 0
    &--toggled
      align-items: center
      & ^[0]__main
        flex: 0 0 100%
        height: 100%
        & ^[0]__main-overlay
          max-width: calc(55% - 80px)
          & ^[0]__main-image
            padding: 100px 0
      & ^[0]__thumb
        right: -20%
        opacity: 0
        visibility: hidden
        transition: .4s ease-in-out
    &__main
      position: relative
      flex: 0 0 calc(100% - 300px)
      left: -100px
      transition: .4s ease-in-out
      & ^[0]__main-overlay
        position: absolute
        display: flex
        flex-flow: row wrap
        align-items: center
        justify-content: space-between
        top: 50%
        left: 0
        right: 0
        margin: 0 auto
        width: 100%
        max-width: 75%
        transform: translateY(-50%)
        transition: .4s ease-in-out
        & ^[0]__overlay-left-arrow
          margin-right: 40px
        & ^[0]__overlay-right-arrow
          margin-left: 40px
        & ^[0]__overlay-right-arrow
          flex: 0 0 50px
          opacity: .65
          transition: .4s ease-in-out
          &:hover,
          &:focus
            opacity: 1
        & ^[0]__main-image
          flex: 1 0 5%
          padding: 40px 0
          text-align: center
          transition: .4s ease-in-out
          & ^[0]__image-title
            font-family: var(--merri_sans)
            font-weight: var(--reg)
            font-size: 12px
            letter-spacing: 0.3px
            color: var(--theme_white)
            margin-top: 20px
          & ^[0]__image-target
            width: auto
            max-width: 100%
            max-height: 800px
            object-fit: cover
        &--mobile
          justify-content: center
          & ^[0]__main-image
            flex: 0 0 100%
            text-align: center
            position: relative
            & ^[0]__image-target
              width: auto
              max-width: 100%
              max-height: 400px
              object-fit: cover
      & ^[0]__control
        position: absolute
        display: flex
        flex-flow: row wrap
        align-items: center
        justify-content: space-around
        flex: 0 0 100%
        top: 0
        right: 30px
        background-color: #171717
        padding: 0 5px
        & ^[0]__control-toggle,
        & ^[0]__control-close
          position: relative
          margin: 10px 0
          padding: 0 10px
    &__thumb
      position: absolute
      width: 300px
      height: 100%
      right: -100px
      background-color: #171717
      overflow-y: auto
      user-select: none
      opacity: 1
      visibility: visible
      transition: .4s ease-in-out
      & ^[0]__thumb-wrapper
        display: flex
        flex-flow: row wrap
        justify-content: space-between
        padding: 10px
        & ^[0]__thumb-image
          flex: 0 0 calc(50% - 5px)
          margin-top: 10px
          img
            width: 100%
            height: 80px
            object-fit: cover
            border: 3px solid var(--theme_white)
            opacity: .5
            transition: .4s ease-in-out
          &--active
          &:hover,
          &:focus
            img
              opacity: 1
              border: 3px solid var(--theme_primary)
          &:nth-child(-n+2)
            margin-top: 0
  /**
  * Responsive */
  @media (max-width: 1024px) and (min-width: 280px)
    .gallery
      &--toggled
        & ^[0]__main
          & ^[0]__main-overlay
            & ^[0]__overlay-image
              padding: 30px
      &__main
        flex: 0 0 100%
        & ^[0]__main-overlay
          max-width: calc(100% - 40px)
        & ^[0]__control
          right: 0
          padding: 0
      &__thumb
        bottom: 0
        left: 0
        width: 100%
        height: auto
        & ^[0]__thumb-wrapper
          justify-content: flex-start
          & ^[0]__thumb-image
            flex: 0 0 20%
</style>
