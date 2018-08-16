import el from '../utils/elements'

export default {
  focus() {
		$(el("field")).click(function() { focusToggle(this); })
		$(el("field", true, " :input")).on("focus", function() { focusToggle(this, true); })
		function focusToggle(field, input = false) {
			$(el("field_focus")).removeClass(el("field_focus", false))
			if(input){
				$(field).closest(el("field")).addClass(el("field_focus", false))
			} else {
				$(field).addClass(el("field_focus", false))
				$(field).find("input, textarea").focus()
			}
		}
		//autofocus first input
		setTimeout(function(){
			$(el("field") + ":first-child").click();
		}, 1000);
		$(el("field", true, " :input")).on("keyup", function(e) {
			if($(this).val()) {
				$(this).addClass("filled");
			} else {
				if($(this).hasClass("filled")) {
					$(this).removeClass("filled");
				} else if(e.keyCode == 8) {
					$(this).prev("input").focus();
				}
			}
		});
  },
  placeholder() {
		$(el("input") + " :input[placeholder]").each(function(){
			let placeholder = $(this).attr("placeholder")
			$(this).closest(el("input")).attr("data-placeholder", placeholder)
		})
	},
	filled() {
		$(el("field", true, " :input")).on("change", function() { fillToggle(this); })
		function fillToggle(field) {
			let val = $(field).val();
			if(val) {
				$(field).closest(el("field")).addClass(el("field_filled", false))
			} else {
				$(field).closest(el("field")).removeClass(el("field_filled", false))
			}
		}
	},
};