export default function(name, dot = true, child = "") {
	const el = {
		form: "formality",
		section: "formality__section",
		section_header: "formality__section__header",
		nav: "formality__nav",
		nav_list: "formality__nav__list",
		field: "formality__field",
		field_focus: "formality__field--focus",
		field_filled: "formality__field--filled",
		input: "formality__input",
		inputs: "formality__input input",
		textarea: "formality__input textarea",
	}
	if(dot) {
		return "." + el[name] + child
	} else {
		return el[name] + child
	}
}