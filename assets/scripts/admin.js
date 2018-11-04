// import external dependencies
import 'jquery';

// Load Events
jQuery(document).ready(() => {
	
/* eslint-disable no-undef */

	if($("body").hasClass("post-php") && $("body").hasClass("post-type-formality_form")) {
		acf.addAction('append_field', function(result){
			$(result.$el).closest(".layout").addClass("-collapsed");
		}); 
	}
	
/* eslint-enable no-undef */
	
});