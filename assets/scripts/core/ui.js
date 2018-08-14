import el from '../utils/elements'

export default {
  focus() {
		$(el("field")).click(function() {
			$(el("field_focus")).removeClass(el("field_focus", false))
			$(this).addClass(el("field_focus", false))
			$(this, "input:first-child").focus()
		})
  },
};