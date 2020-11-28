import { el } from './helpers'

export default {
  init() {
    const main = this;
    main.removeLoader()
    document.onreadystatechange = function () { main.removeLoader() }
  },
  removeLoader() {
    if(document.readyState === 'complete') {
      setTimeout(function() {
        $(el("form")).removeClass(el("form", false, "--first-loading"))
      }, 500)
    }
  },
}