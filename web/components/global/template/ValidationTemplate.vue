<template lang="html">
  <transition name="slideY">
    <span
      :class="[
        '__v',
        attr['validate'],
        attr[`validate${payload.adjusted}`]
      ]"
      v-if="payload.errors && payload.errors.length > 0"
    >
      {{ payload.errors[0] }}
    </span>
  </transition>
</template>

<script>
  export default {
    props: {
      payload: {
        type: Object,
        default: {
          errors: null,
          adjusted: ''
        }
      }
    }
  }
</script>

<style lang="stylus" module="attr">
  .validate
    position: absolute
    top: calc(100% + 5px)
    left: 0
    z-index: 1
    background-color: var(--theme_error)
    color: var(--theme_white)
    font-weight: var(--reg)
    font-size: 12px
    padding: 5px 10px
    border-radius: 5px
    user-select: none
    pointer-events: none
    &--textarea
      top: calc(89% + 5px)
    &--sizes
      top: calc(95% + 5px)
    &--captcha
      top: calc(84% + 5px)
      left: 50%
      transform: translateX(-50%)
    &::before
      content: ""
      position: absolute
      bottom: calc(100% - 1px)
      width: 0
      height: 0
      border-left: 7px solid transparent
      border-right: 7px solid transparent
      border-bottom: 7px solid var(--theme_error)
      left: 17px
      z-index: 1
</style>
