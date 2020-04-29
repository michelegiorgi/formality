export default function($element, form = true) {
	/* eslint-disable no-unused-vars */
	if($element) {
		if($element.is("form")) {
			window.formality.uid = $element.attr("data-uid")
		} else {
			window.formality.uid = $element.closest("form.formality").attr("data-uid")
		}
	}
	if(form) {
		return 'form.formality[data-uid="' + window.formality.uid + '"]'
	} else {
		return window.formality.uid
	}
	/* eslint-enable no-unused-vars */
}