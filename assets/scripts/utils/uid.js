export default function($element) {
	/* eslint-disable no-unused-vars */
	if($element) {
		if($element.is("form")) {
			window.formality.uid = $element.attr("data-uid");
		} else {
			window.formality.uid = $element.closest("form.formality").attr("data-uid");
		}
	}
	return 'form.formality[data-uid="' + window.formality.uid + '"]';
	/* eslint-enable no-unused-vars */
}