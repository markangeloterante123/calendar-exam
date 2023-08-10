export default {
  toggleContentLoaderStatus: (state, payload) => {
    state.show[payload.type] = payload.status
    if (!payload.status) {
      document.body.classList.remove('no_scroll')
      if (payload.type == 'loader') {
        document.body.classList.remove('no_click')
      }
    }
  }
}
