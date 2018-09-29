import el from '../utils/elements'
//import uid from '../utils/uid'

export default {
	init() {
		$(el("form")).submit(function(e){
			e.preventDefault();
			alert("send");
		});
	},
	send() {
		
	},
}