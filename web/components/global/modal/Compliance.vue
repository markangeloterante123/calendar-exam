<template lang="html">
  <div
    :class="[
      attr['compliance'],
      toggle.animate && attr['compliance--out']
    ]"
  >
    <div :class="attr['compliance__wrapper']">
      <p>
        This site uses cookies to store information in your computer.<br />
        By using our site, you accept our <a href="/terms-and-conditions" target="_blank">Terms &amp; Conditions</a> and
        <a href="/privacy-policy" target="_blank">Privacy Statement</a> pursuant to the Data Privacy Act of
        2012.
      </p>
      <div :class="attr['compliance__action']">
        <button-template
          :label="'OK, I Agree'"
          :button_type="'button'"
          :status="2"
          :minimal="true"
          @click.native="close()"
        />
      </div>
    </div>
  </div>
</template>

<script>
  import SecureLS from 'secure-ls'

  export default {
    data: () => ({
      toggle: {
        animate: false
      }
    }),
    methods: {
      close() {
        let ls = new SecureLS()

        ls.set('_compliance_', 1)
        ls.set('_compliance_expiration_', this.$moment())
        this.toggle.animate = true
        setTimeout(() => {
          this.$parent.toggle.compliance = false
        }, 500)
      }
    }
  }
</script>

<style lang="stylus" module="attr">
  .compliance
    position: fixed
    bottom: 20px
    left: 20px
    z-index: 1005
    width: 100%
    max-width: 525px
    border-radius: 5px
    background-color: rgba(0, 0, 0, .85)
    box-shadow: 0 3px 20px rgba(0, 0, 0, .65)
    animation: slide_in_x 1s ease-in-out
    &--out
      animation: slide_out_x 1s ease-in-out
    &__wrapper
      display: flex
      flex-flow: row wrap
      align-items: center
      justify-content: center
      text-align: center
      padding: 20px
      margin: 0 auto
      p
        flex: 0 0 100%
        font-size: 14px
        font-family: var(--med)
        color: var(--theme_white)
        line-height: 1.75
        a
          color: var(--theme_white)
          text-decoration: underline
          font-family: var(--bold)
          transition: .4s ease-in-out
          &:hover
            color: var(--theme_primary)
      & ^[0]__action
        margin-top: 20px
  /**
  * Animations */
  @keyframes slide_in_x
    0%
      left: -50%
      opacity: 0
    100%
      left: 20px
      opacity: 1
  @keyframes slide_out_x
    0%
      left: 20px
      opacity: 1
    100%
      left: -50%
      opacity: 0
  @keyframes slide_in_y
    0%
      bottom: -50%
      opacity: 0
    100%
      bottom: 100px
      opacity: 1
  @keyframes slide_out_y
    0%
      bottom: 100px
      opacity: 1
    100%
      bottom: -50%
      opacity: 0
  /**
  * Response */
  @media (max-width: 1300px) and (min-width: 1024px)
    .compliance
      bottom: 20px
      left: 0
      right: 0
      max-width: 500px
      margin: 0 auto
  @media (max-width: 1024px) and (min-width: 280px)
    .compliance
      bottom: 100px
      left: 0
      right: 0
      max-width: 450px
      margin: 0 auto
      animation: slide_in_y 1s ease-in-out
      &--out
        animation: slide_out_y 1s ease-in-out
      &__wrapper
        text-align: center
        p
          flex: 0 0 100%
          font-size: 14px
    @media (max-width: 450px) and (min-width: 280px)
      .compliance
        max-width: calc(100% - 40px)
</style>
