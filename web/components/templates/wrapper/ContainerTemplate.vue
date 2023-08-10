<template lang="html">
  <section :class="[attr['template'], theme && attr[`template--${theme}`]]">
    <div :class="attr['template-container']">
      <slot></slot>
    </div>
  </section>
</template>

<script>
  export default {
    props: {
      theme: {
        type: String,
        default: 'primary' //transparent, white
      }
    }
  }
</script>

<style lang="stylus" module="attr">
  .template
    &--transparent
      background-color: transparent
    &--white
      background-color: var(--theme_white)
    &-container
      width: 100%
      max-width: 1180px
      margin: 0 auto
  /**
   * Responsive
   */
  @media (max-width: 1366px) and (min-width: 425px)
    .template
      &-container
        max-width: calc(100% - 80px)
  @media (max-width: 425px) and (min-width: 280px)
    .template
      &-container
        max-width: calc(100% - 40px)
</style>
