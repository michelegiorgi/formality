import { el } from './helpers'

export default {
  init() {
    const main = this;
    if(document.readyState == 'complete') {
      main.removeLoader();
    }
    document.onreadystatechange = function () {
      if(document.readyState === 'complete') {
        main.removeLoader();
      }
    }
  },
  removeLoader() {
    setTimeout(function() {
      $(el("form")).removeClass(el("form", false, "--first-loading"))
    }, 500)
  },
}