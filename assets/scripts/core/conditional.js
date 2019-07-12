import el from '../utils/elements'

export default {
	init() {
		//init
		$(el("field", true, "[data-conditional]")).each(function() {
			let elements = "";
			const rule = JSON.parse($(this).attr("data-conditional"));
			for (const index in rule) {
        elements += ( index == 0 ? "" : ", " ) + "#" + rule[index].field
      }
			$(elements).on("input", function(){
        let valid = false;
        for (const index in rule) {
          const $input = ("field" in rule[index]) ? $("#" + rule[index].field) : "";
          if($input) {
            let input = $input.val()
            if(!input) { input = "" }
            const value = ("value" in rule[index]) ? rule[index].value : "";
            let check = false
            console.log(rule[index].operator, input, rule[index].is, value)
            switch(rule[index].is) {
              case "==" : check = input == value; break;
              case "!==" : check = input !== value; break;
              case ">" : check = input > value; break;
              case ">=" : check = input >= value; break;
              case "<=" : check = input <= value; break;
              case "<" : check = input < value; break;
            }
            if(check) {
              valid = true;
            } else if (index == 0 && (typeof rule[1] !== 'undefined') && rule[1].operator == "&&" ) {
              valid = false;
              break;              
            } else if((rule[index].operator=="&&") && (rule[index]._key > 1)) {
              valid = false;
              break;
            }
          }
        }
        console.log(valid);
			})
		})
	},
}