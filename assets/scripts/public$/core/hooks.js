import { el, uid } from './helpers'

export default {
  init() {
    //
  },
  event(name, options = {}, target = window) {
    options['form'] = window.formality.uid;
    const event = new CustomEvent('fo' + name, {
      view: window,
      bubbles: true,
      detail: options
    })
    target.dispatchEvent(event)
  }
}
