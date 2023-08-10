import Vue from 'vue'
import { ValidationObserver, ValidationProvider, extend } from 'vee-validate'
import * as rules from 'vee-validate/dist/rules'
import { messages } from 'vee-validate/dist/locale/en.json'

Object.keys(rules).forEach((rule) => {
  extend(rule, {
    ...rules[rule],
    message: messages[rule]
  })
})

// extend('phone_number', (value) => {
//   if (
//     value.match(
//       /^\s*(?:\+?(\d{1,3}))?[-. (]*(\d{4})[-. )]*(\d{3})[-. ]*(\d{4})(?: *x(\d+))?\s*$/
//     )
//   )
//     return true

//   return 'Phone number is not valid.'
// })

Vue.component('ValidationProvider', ValidationProvider)
Vue.component('ValidationObserver', ValidationObserver)
