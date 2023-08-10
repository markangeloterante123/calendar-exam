export default {
  getContentLoaderStatus: (state) => (type) => {
    return state.show[type]
  }
}
