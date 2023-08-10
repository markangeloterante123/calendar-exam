export default {
  toggleModalStatus: (state, payload) => {
    state.show[payload.type] = payload.status
    if (payload.item) {
      state.item = payload.item
    }
    if (!payload.status) {
      document.body.classList.remove('no_scroll')
      if (payload.type == 'loader') {
        document.body.classList.remove('no_click')
      }
    }
  }
}
