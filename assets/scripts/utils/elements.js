export default function(name, dot = true) {
	const el = {
		form: "formality",
		field: "formality__field",
		field_focus: "formality__field--focus",
	}
	if(dot) {
		return "." + el[name]
	} else {
		return el[name]
	}
}