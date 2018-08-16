import el from '../utils/elements'

export default {
  focus() {
		$(el("field")).click(function() { focusin(this); })
		$(el("field", true, "input") + ", " + el("field", true, "textarea")).on("focus", function() { focusin(this, true); })
		function focusin(field, input = false) {
			$(el("field_focus")).removeClass(el("field_focus", false))
			if(input){
				$(field).closest(el("field")).addClass(el("field_focus", false))
			} else {
				$(field).addClass(el("field_focus", false))
				$(field, "input:first-child").focus()
			}
		}
  },
  placeholder() {
		$(el("input") + " *[placeholder]").each(function(){
			let placeholder = $(this).attr("placeholder")
			$(this).closest(el("input")).attr("data-placeholder", placeholder)
		})
	},
};