export default function($element) {
	/* eslint-disable no-unused-vars */
	if($element) {
		if($element.is("form")) {
			window.formalityID = $element.attr("data-uid");
		} else {
			window.formalityID = $element.closest("form.formality").attr("data-uid");
		}
	}
	return 'form.formality[data-uid="' + window.formalityID + '"]';
	/* eslint-enable no-unused-vars */
}