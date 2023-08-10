<template lang="html">
  <section :class="attr['section']">
    <div :class="attr['section__container']">
      <div :class="attr['section__container-left']">
        <calendar-note />
      </div>
      <div :class="attr['section__container-right']">
        <calendar-date />
      </div>
    </div>
  </section>
</template>

<script>
  export default {
    components: {
      CalendarDate: () => import('~/components/home/section/CalendarDate'),
      CalendarNote: () => import('~/components/home/section/CalendarNote')
    }
  }
</script>

<style lang="stylus" module="attr">
  .section
    position: relative
    padding: 120px 0
    &__container
      display: flex
      flex-flow: row wrap
      margin: 0 -10px
      &-left
        flex: 0 0 calc(30% - 20px)
        margin: 0 10px
      &-right
        flex: 0 0 calc(70% - 20px)
        margin: 0 10px
  @media (max-width: 1024px) and (min-width: 280px)
    .section
      padding: 80px 0
      &__container
        &-left,
        &-right
          flex: 0 0 calc(100% - 20px)
</style>
