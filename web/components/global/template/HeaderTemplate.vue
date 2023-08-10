<template lang="html">
  <header
    :class="[
      attr['header'],
      attr[`header${alignment}`],
      attr[`header${theme}`],
      attr[`header${padding}`]
    ]"
  >
    <h1 :class="attr['header__title']">{{ payload.title }}</h1>
    <template v-if="payload.subtitle">
      <h3 :class="attr['header__subtitle']">{{ payload.subtitle }}</h3>
    </template>
  </header>
</template>

<script>
  export default {
    props: {
      payload: {
        type: Object,
        default: () => {
          return {
            title: '',
            subtitle: ''
          }
        }
      },
      alignment: {
        type: String,
        default: null
      },
      theme: {
        type: String,
        default: null
      },
      padding: {
        type: String,
        default: null
      }
    }
  }
</script>

<style lang="stylus" module="attr">
  .header
    width: 100%
    max-width: 1280px
    margin: 0 auto
    padding: 40px 0
    text-align: center
    &--no-padding
      padding: 0
    &--padding-20
      padding: 0 0 20px
    &--left
      text-align: left
      & ^[0]__title
        &::before
          margin: 0
    &--white
      & ^[0]__title
        color: var(--theme_white)
        &::before
          background-color: var(--theme_white)
    &--green
      & ^[0]__title
        &::before
          background-color: var(--theme_success)
    &__title
      position: relative
      display: inline-block
      margin-bottom: 15px
      padding-bottom: 15px
      font-family: var(--merri)
      font-size: 35px
      color: var(--theme_black)
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
    &__subtitle
      margin: 0 auto
      width: 100%
      max-width: 600px
      color: #585858
      font-size: 16px
      letter-spacing: 0.4px
      line-height: 1.5
  /**
  * Responsive */
  @media (max-width: 1024px) and (min-width: 280px)
    .header
      padding: 20px
      &__title
        font-size: 25px
      &__subtitle
        max-width: 325px
      &--left
        text-align: center
        & ^[0]__title
          &::before
            margin: 0 auto
        & ^[0]__subtitle
          max-width: 100%
    @media (max-width: 1024px) and (min-width: 700px)
      .header
        &--left
          padding: 0
          text-align: left
          & ^[0]__title
            &::before
              margin: 0
    @media (max-width: 320px) and (min-width: 280px)
      .header
        &__subtitle
          max-width: 250px
</style>
