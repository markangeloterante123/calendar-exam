<template lang="html">
  <section :class="attr['page']">
    <container-template>
      <calendar />
    </container-template>
  </section>
</template>

<script>
  export default {
    components: {
      Calendar: () => import('~/components/home/section/Calendar')
    }
  }
</script>
<style lang="stylus" module="attr">
  .page
    height: 100vh
    background-color: var(--theme_primary)
</style>
