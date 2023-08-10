export default {
  getShowStatus: (state) => (type) => {
    return state.show[type]
  },
  getItem: (state) => {
    return state.item
  }
}
