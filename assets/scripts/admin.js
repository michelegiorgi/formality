// import external dependencies
import 'jquery';

// Load Events
jQuery(document).ready(() => {
	
	$(document).on('keypress', '.acf-field-flexible-content[data-name="formality_fields"] .acf-field[data-name="name"] input', function (event) {
    var regex = new RegExp("^[a-zA-Z0-9_]+$");
    var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
    if (!regex.test(key)) {
      event.preventDefault();
      return false;
    }
	});
	
});