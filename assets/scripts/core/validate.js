import el from '../utils/elements'
import 'parsleyjs'

export default {
	init() {
		$(el("section")).each(function(index, section) {
			$(section).find(':input').attr('data-parsley-group', 'step-' + index);
		});
		this.field_error()
		this.field_success()		
	},
	checkstep(index, newindex) {
		let valid = false;
		let options = this.parsley_options();
		if(index > newindex) {
			valid = true;
		} else {
			$(el("form")).parsley(options).whenValidate({
				group: 'step-' + index,
			}).done(function() {
				valid = true;
			});
		}
    return valid;
	},
	form() {
		let options = this.parsley_options();
		$(el("form")).parsley(options);
	},
	parsley_options() {
		let options = {
			classHandler: function (element) {
				return element.$element.closest(el("field"));
			},
			errorClass: el("field_error", false),
			successClass: el("field_success", false),
			errorsWrapper: '<ul class="'+el("input_errors", false)+'"></ul>',
		}
		return options;
	},
	field_error() {
		window.Parsley.on('field:error', function() {
			const id = $(this.$element).attr("id")
			$(el("nav_legend", true, ' li[data-name="' + id + '"]')).addClass("error")
		});
	},
	field_success() {
		window.Parsley.on('field:success', function() {
			const id = $(this.$element).attr("id")
			$(el("nav_legend", true, ' li[data-name="' + id + '"]')).removeClass("error")
		});
	},
};