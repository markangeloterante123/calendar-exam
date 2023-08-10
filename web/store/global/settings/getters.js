export default {
  getMobile: (state) => {
    return state.mobile
  },
  getLazy: (state) => {
    return state.lazy
  },
  getAuthenticated: (state) => {
    return state.authenticated
  },
  getSideNavStatus: (state) => {
    return state.togged_sidenav
  },
  getActiveNavStatus: (state) => {
    return state.active_nav
  },
}
