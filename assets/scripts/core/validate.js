import el from '../utils/elements'
import uid from '../utils/uid'
import 'parsleyjs'

export default {
	init() {
		//init validation
		$(el("form")).each(function() {
			uid($(this));
			$(el("section", "uid")).each(function(index, section) {
				$(section).find(':input').attr('data-parsley-group', 'step-' + index);
			});
		})
		this.field_error()
		this.field_success()			
	},
	checkstep(index, newindex) {
		//validate single step
		let valid = false;
		let options = this.parsley_options();
		if(index > newindex) {
			valid = true;
		} else {
			$(el("form", "uid")).parsley(options).whenValidate({
				group: 'step-' + index,
			}).done(function() {
				valid = true;
			});
		}
    return valid;    
	},
	form() {
		//validate standard form (1 step)
		let options = this.parsley_options();
		$(el("form", "uid")).parsley(options);
	},
	parsley_options() {
		//create parsley options array
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
		//field error event
		window.Parsley.on('field:error', function() {
			const id = $(this.$element).attr("id")
			uid($(this.$element));
			$(el("nav_legend", 'uid', ' li[data-name="' + id + '"]')).addClass("error")
		});
	},
	field_success() {
		//field success event
		window.Parsley.on('field:success', function() {
			const id = $(this.$element).attr("id")
			uid($(this.$element));
			$(el("nav_legend", "uid", ' li[data-name="' + id + '"]')).removeClass("error")
		});
	},
};