export default {
  backToTop ({ dispatch, commit }) {
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}
