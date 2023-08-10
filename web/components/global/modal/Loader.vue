<template lang="html">
  <div
    :class="[attr['loader'], getItem.indicator && attr['loader--indicator']]"
  >
    <div :class="attr['loader__wrapper']">
      <template v-if="!getItem.indicator">
        <figure>
          <nuxt-img
            format="png"
            width="82"
            height="82"
            src="/assets/logo/adb-logo.jpg"
          />
        </figure>
      </template>
      <template v-else>
        <div :class="attr['loader__dots']">
          <div></div>
          <div></div>
          <div></div>
          <div></div>
          <div></div>
          <div></div>
          <div></div>
          <div></div>
        </div>
      </template>
      <template v-if="getItem && !getItem.indicator">
        <div :class="attr['loader__progress']">
          <div
            :class="attr['loader__overlay']"
            :style="`width: ${getItem.start ? progress : 100}%;`"
          ></div>
          <span>{{ getItem.start ? progress : 100 }}</span>
        </div>
      </template>
    </div>
  </div>
</template>

<script>
  import { mapGetters } from 'vuex'

  export default {
    data: () => ({
      progress: 0
    }),
    computed: {
      ...mapGetters({
        getItem: 'global/modal/getItem'
      })
    },
    mounted() {
      document.body.classList.add('no_click')
      if (this.getItem) {
        let interval = setInterval(() => {
          if (this.getItem.start) {
            if (this.progress < 99) this.progress += 1
          } else {
            clearInterval(interval)
            setTimeout(() => {
              this.toggleModalStatus({ type: 'loader', status: false })
            }, 500)
          }
        }, 100)
      }
    }
  }
</script>

<style lang="stylus" module="attr">
  .loader
    position: fixed
    top: 0
    left: 0
    bottom: 0
    right: 0
    z-index: 9901
    background-color: var(--theme_white)
    &--indicator
      background-color: #A8321740
    &__wrapper
      position: absolute
      top: 50%
      left: 0
      right: 0
      text-align: center
      margin: 0 auto
      width: 200px
      transform: translateY(-50%)
      img
        width: 80px
        height: auto
    &__progress
      position: relative
      width: 100%
      height: 30px
      margin-top: 10px
      border-radius: 30px
      overflow: hidden
      background-color: rgba(200, 200, 200, 0.5)
      border: 1px solid var(--theme_primary)
      span
        position: absolute
        top: 50%
        left: 0
        right: 0
        text-align: center
        z-index: 2
        font-weight: var(--bold)
        color: var(--theme_primary)
        transform: translateY(-50%)
    &__overlay
      position: absolute
      top: 0
      left: 0
      bottom: 0
      z-index: 1
      width: 0%
      border-radius: 30px
      background-color: var(--theme_white)
      transition: .4s ease-in-out
    &__dots
      display: inline-block
      position: relative
      width: 80px
      height: 80px
      div
        animation: rotate 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite
        transform-origin: 40px 40px
        &::after
          content: " "
          display: block
          position: absolute
          width: 7px
          height: 7px
          border-radius: 50%
          background-color: var(--theme_primary)
          margin: -4px 0 0 -4px
        &:nth-child(1)
          animation-delay: -0.036s
          &::after
            top: 63px
            left: 63px
        &:nth-child(2)
          animation-delay: -0.072s
          &::after
            top: 68px
            left: 56px
        &:nth-child(3)
          animation-delay: -0.108s
          &::after
            top: 71px
            left: 48px
        &:nth-child(4)
          animation-delay: -0.144s
          &::after
            top: 72px
            left: 40px
        &:nth-child(5)
          animation-delay: -0.18s
          &::after
            top: 71px
            left: 32px
        &:nth-child(6)
          animation-delay: -0.216s
          &::after
            top: 68px
            left: 24px
        &:nth-child(7)
          animation-delay: -0.252s
          &::after
            top: 63px
            left: 17px
        &:nth-child(8)
          animation-delay: -0.288s
          &::after
            top: 56px
            left: 12px
  /**
    * Animations */
  @keyframes rotate
    0%
      transform: rotate(0deg)
    100%
      transform: rotate(360deg)
</style>
