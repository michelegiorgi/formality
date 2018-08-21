import el from '../utils/elements'
import 'parsleyjs'

export default {
	init() {
		$(el("section")).each(function(index, section) {
			$(section).find(':input').attr('data-parsley-group', 'section-' + index);
		});
	},
	check(index) {
		let valid = false;
		$(el("form")).parsley().whenValidate({
      group: 'section-' + index,
    }).done(function() {
      valid = true;
    });
    return valid;
	},
};