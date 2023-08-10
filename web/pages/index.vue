<template lang="html">
  <main
    :class="attr['page']"
    v-if="loaded"
  >
    <!-- Page Content -->
    <page-content />
  </main>
</template>

<script>
  import { computed } from 'vue'
  export default {
    components: {
      PageContent: () => import('~/components/home/PageContent')
    },
    data: () => ({
      loaded: false,
      records: null,
      page: {
        title: 'Calendar'
      }
    }),
    provide() {
      return {
        event_tasks: computed(() => {
          return this.records
        }),
        hours: [
          '6:AM',
          '7:AM',
          '8:AM',
          '9:AM',
          '10:AM',
          '11:AM',
          '12:AM',
          '1:PM',
          '2:PM',
          '3:PM',
          '4:PM',
          '5:PM',
          '6:PM',
          '7:PM',
          '8:PM',
          '9:PM',
          '10:PM',
          '11:PM',
          '12:MN',
          '1:AM',
          '2:AM',
          '3:AM',
          '4:AM',
          '5:AM'
        ]
      }
    },
    methods: {
      fetchTask() {
        this.$axios
          .$get('web/calendar')
          .then(({ records }) => {
            this.records = records
          })
          .catch(() => {
            this.error({ statusCode: 500 })
          })
      }
    },
    mounted() {
      setTimeout(() => {
        this.toggleModalStatus({
          type: 'loader',
          status: true,
          item: { start: false }
        })
        this.loaded = true
      }, 500)
    },
    created() {
      this.$nuxt.$on('update-calendar', payload => {
        this.fetchTask()
      })
    },
    beforeDestroy() {
      this.$nuxt.$off('update-calendar')
    },
    destroyed() {
      this.$nuxt.$off('update-calendar')
    },
    asyncData({ $axios, store, error }) {
      store.commit('global/modal/toggleModalStatus', {
        type: 'loader',
        status: true,
        item: { start: true }
      })

      return $axios
        .$get('web/calendar')
        .then(({ records }) => ({
          records: records
        }))
        .catch(() => {
          error({ statusCode: 500 })
        })
    },
    head() {
      let host = process.env.BASE_URL

      return {
        title: `calendar | ${this.page.title}`,
        link: [
          {
            rel: 'canonical',
            href: `${host}${this.$route.fullPath}`
          }
        ]
        // meta: [
        //   { hid: 'description', name: 'description', content: `${this.page.metadata.meta_description}` },
        //   { hid: 'og:title', property: 'og:title', content: `${this.page.metadata.meta_title}` },
        //   { hid: 'og:description', property: 'og:description', content: `${this.page.metadata.meta_description}` },
        //   { hid: 'og:url', property: 'og:url', content: `${host}${this.$route.fullPath}` },
        //   { hid: 'og:image', property: 'og:image', content: `${host}/logo.svg` },
        //   { hid: 'og:image:alt', property: 'og:image:alt', content: this.page.title },
        //   { hid: 'og:type', property: 'og:type', content: 'website' },
        //   { hid: 'og:site_name', property: 'og:site_name', content: 'C! Magazine' },
        // ]
      }
    }
  }
</script>
<style lang="stylus" module="attr">
  .page
    //
</style>
