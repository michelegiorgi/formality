// import external dependencies
import 'jquery';

// Load Events
jQuery(document).ready(() => {
	
/* eslint-disable no-undef */

	acf.addAction('append_field', function(result){
		$(result.$el).closest(".layout").addClass("-collapsed");
	}); 

/* eslint-enable no-undef */
	
});