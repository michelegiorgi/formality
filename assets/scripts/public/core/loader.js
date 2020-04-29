import el from '../utils/elements'

export default {
  init() {
    $(window).on("load", function() {
      setTimeout(function() {
        $(el("form")).removeClass(el("form", false, "--first-loading"))
      }, 500)
    });
  },
}