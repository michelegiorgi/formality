export default function(name, dot = true) {
	const el = {
		form: "formality",
		field: "formality__field",
		field_focus: "formality__field--focus",
		input: "formality__input",
		inputs: "formality__input input",
		textarea: "formality__input textarea",
	}
	if(dot) {
		return "." + el[name]
	} else {
		return el[name]
	}
}