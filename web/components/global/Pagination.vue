<template>
  <div :class="attr['pagination']" v-if="last > 1">
    <div
      :class="[
        attr['pagination__next'],
        attr['pagination--pointer'],
        (current == 1) && attr['pagination__next--disabled']
      ]"
      @click="getPage(null, current, 'prev')"
    >
      <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24" fill="none" stroke="#FFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="attr['pagination__icon']"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
    </div>

    <div :class="attr['pagination__text']">Page</div>

    <div :class="attr['pagination__number']">
      <input
        :class="attr['pagination__input']"
        :max="last"
        type="number"
        v-model="page_number"
        @blur="getPage($event)"
      >
    </div>

    <div :class="attr['pagination__text']">of {{ last }}</div>

    <div
      :class="[
        attr['pagination__prev'],
        attr['pagination--pointer'],
        (current == last) && attr['pagination__prev--disabled']
      ]"
      @click="getPage(null, current, 'next')"
    >
      <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24" fill="none" stroke="#FFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="attr['pagination__icon']"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
    </div>
  </div>
</template>

<script>
  export default {
    props: {
      emitter: {
        type: String,
        default: null
      },
      emitter_params: {
        type: Array/Object,
        default: null
      },
      current: {
        type: Number,
        default: 1
      },
      last: {
        type: Number,
        default: 1
      },
      query_params: {
        type: String,
        default: null
      }
    },
    data: ({ current }) => ({
      page_number: Math.abs(current)
    }),
    methods: {
      getPage (event = null, current = null, type = null) {
        let call_api = true

        if (!event) {
          this.page_number = (type == 'prev') ? this.page_number - 1 : this.page_number + 1
          if (this.page_number > this.last) {
            call_api = false
          }
        } else {
          let target = parseInt(event.target.value)
          if (target != 0) {
            if (target <= this.last) {
              if (current != target) {
                this.page_number = parseInt(event.target.value)
              } else {
                call_api = false
              }
            } else {
              call_api = false
            }
          } else {
            call_api = false
          }
        }

        this.page_number = parseInt(this.page_number)

        let url = (this.query_params)
          ? `${this.$route.path}?p=${this.page_number}${this.query_params}`
          : `${this.$route.path}?p=${this.page_number}`
        
        if (call_api) {
          this.$router.push(url)
          setTimeout(() => {
            if (this.emitter_params) {
              this.$nuxt.$emit(this.emitter, this.emitter_params)
            } else {
              this.$nuxt.$emit(this.emitter)
            }
          }, 250)
        }
      }
    }
  }
</script>

<style lang="stylus" module="attr">
  .pagination
    display: flex
    flex-flow: row wrap
    align-items: center
    justify-content: center
    padding: 20px 0 50px
    user-select: none
    &--pointer
      cursor: pointer
    &__text
      font-weight: var(--reg)
    &__number
      margin: 0 5px
      & ^[0]__input
        width: 50px
        font-weight: var(--reg)
        background-color: transparent
        border: none
        padding: 6px 5px
        border: 1px solid var(--theme_primary)
        outline: none
        border-radius: 0
        box-shadow: none
        font-weight: var(--reg)
        color: var(--theme_black)
        appearance: none
    &__next
      margin-right: 10px
    &__prev
      margin-left: 10px
    &__next,
    &__prev
      display: flex
      flex-flow: row wrap
      align-items: center
      background-color: var(--theme_primary)
      border: 1px solid var(--theme_primary)
      padding: 5px
      transition: .4s ease-in-out
      & ^[0]__icon
        stroke: var(--theme_white)
        fill: transparent
      &:hover,
      &:focus
        background-color: var(--theme_white)
        & ^[0]__icon
          stroke: var(--theme_primary)
      &--disabled
        pointer-events: none
        background-color: var(--theme_gray)
        border: 1px solid var(--theme_gray)
        & ^[0]__icon
          stroke: var(--theme_white)
</style>