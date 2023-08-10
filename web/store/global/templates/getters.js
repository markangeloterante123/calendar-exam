export default {
  getTemplateShowStatus: (state) => (type) => {
    return state.show[type]
  },
  getItem: (state) => {
    return state.item
  }
}
