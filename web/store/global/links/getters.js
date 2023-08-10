export default {
  getLinks: (state) => (type) => {
    return state.links[type]
  },
  getSocialLinks: (state) => (type) => {
    return state.socials[type]
  }
}
