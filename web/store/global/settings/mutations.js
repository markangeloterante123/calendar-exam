export default {
  enteredMobile (state, payload) {
    state.mobile = payload.status
  },
  setLazy (state, payload) {
    state.lazy = payload.status
  },
  userAuthentication (state, payload) {
    state.authenticated = payload.status
  },
  TOGGLE_SIDENAV (state, payload) {
    state.togged_sidenav = payload
  },
  ACTIVE_NAV (state, payload) {
    state.active_nav = payload
  },
}
