import el from '../utils/elements'
import 'parsleyjs'

export default {
	init() {
		$(el("section")).each(function(index, section) {
			$(section).find(':input').attr('data-parsley-group', 'section-' + index);
		});
	},
	check(index, newindex) {
		let valid = false;
		if(index > newindex) {
			valid = true;
		} else {
			$(el("form")).parsley({
				classHandler: function (element) {
					return element.$element.closest(el("field"));
				},
				errorClass: el("field_error", false),
				successClass: el("field_success", false),
				errorsWrapper: '<ul class="'+el("input_errors", false)+'"></ul>',
			}).whenValidate({
				group: 'section-' + index,
			}).done(function() {
				valid = true;
			});
		}
    return valid;
	},
};