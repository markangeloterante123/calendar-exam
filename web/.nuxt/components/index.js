export { default as Compliance } from '../../components/global/modal/Compliance.vue'
export { default as ContentLoader } from '../../components/global/modal/ContentLoader.vue'
export { default as Gallery } from '../../components/global/modal/Gallery.vue'
export { default as Loader } from '../../components/global/modal/Loader.vue'
export { default as ButtonTemplate } from '../../components/global/template/ButtonTemplate.vue'
export { default as HeaderTemplate } from '../../components/global/template/HeaderTemplate.vue'
export { default as ModalTemplate } from '../../components/global/template/ModalTemplate.vue'
export { default as ValidationTemplate } from '../../components/global/template/ValidationTemplate.vue'
export { default as ContainerTemplate } from '../../components/templates/wrapper/ContainerTemplate.vue'
export { default as Pagination } from '../../components/global/Pagination.vue'

// nuxt/nuxt.js#8607
function wrapFunctional(options) {
  if (!options || !options.functional) {
    return options
  }

  const propKeys = Array.isArray(options.props) ? options.props : Object.keys(options.props || {})

  return {
    render(h) {
      const attrs = {}
      const props = {}

      for (const key in this.$attrs) {
        if (propKeys.includes(key)) {
          props[key] = this.$attrs[key]
        } else {
          attrs[key] = this.$attrs[key]
        }
      }

      return h(options, {
        on: this.$listeners,
        attrs,
        props,
        scopedSlots: this.$scopedSlots,
      }, this.$slots.default)
    }
  }
}
