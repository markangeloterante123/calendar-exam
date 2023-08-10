<template lang="html">
  <div
    :class="[
      attr['button'],
      attr[`button--${template}`],
      payload.with_icon && attr['button--icon'],
      payload.disabled && attr['button--disabled'],
      payload.compact && attr['button--compact'],
      payload.full_width && attr['button--full']
    ]"
  >
    <!-- Router -->
    <nuxt-link
      :to="payload.link"
      custom
      v-slot="{ href, navigate, isExactActive }"
      v-if="payload.action_type == 'router'"
    >
      <a
        :class="[
          attr['button__link'],
          isExactActive && attr['button__link--exact']
        ]"
        :href="href"
        @click="navigate"
      >
        <slot></slot>
      </a>
    </nuxt-link>

    <!-- Link Out -->
    <a
      :href="payload.link"
      target="_blank"
      :class="attr['button__link']"
      v-else-if="payload.action_type == 'link_out'"
    >
      <slot></slot>
    </a>

    <!-- Download -->
    <a
      :href="payload.link"
      download
      target="_blank"
      :class="attr['button__link']"
      v-else-if="payload.action_type == 'download'"
    >
      <slot></slot>
    </a>

    <!-- Button -->
    <button
      :type="payload.button_type"
      :class="attr['button__link']"
      v-else-if="payload.action_type == 'button'"
    >
      <slot></slot>
    </button>
  </div>
</template>

<script>
  export default {
    props: {
      payload: {
        type: Object,
        default: () => ({
          link: '',
          action_type: '', //router, link_out, button, download
          button_type: '', //button, submit
          disabled: false,
          full_width: false,
          with_icon: false,
          compact: false
        })
      },
      template: {
        type: String,
        default: '' //primary
      }
    }
  }
</script>

<style lang="stylus" module="attr">
  .button
    position: relative
    z-index: 1
    display: inline-block
    border-radius: 30px
    text-align: center
    &__link
      display: block
      width: 100%
      border-radius: 25px
      padding: 11px 25px
      font-weight: var(--med)
      font-size: 18px
      color: var(--theme_white)
      cursor: pointer
      transition: .3s ease-in-out
      background-color: transparent
      border: 1px solid var(--theme_white)
    &:hover
      & ^[0]__link
        background-color: var(--theme_primary)
        border: 1px solid var(--theme_primary)
        svg
          transform: rotate(45deg)
    &--icon
      & ^[0]__link
        display: flex
        flex-flow: row wrap
        align-items: center
        justify-content: space-between
        svg
          flex: 0 0 auto
          margin-left: 20px
          margin-bottom: 5px
          transition: 0.4s ease-in-out
        p
          flex: 0 0 auto
          padding-right: 15px
    &--disabled
      pointer-events: none
    &--full
      display: block
      & ^[0]__link
        justify-content: center
  /**
  * Responsive */
  @media (max-width: 1024px) and (min-width: 280px)
    @media (max-width: 375px) and (min-width: 280px)
      .button
        display: block
        &__link
          font-size: 14px
</style>
