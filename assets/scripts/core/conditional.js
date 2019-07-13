import el from '../utils/elements'

export default {
  init() {
    //init conditional field
    let conditional = this
    $(el("field", true, "[data-conditional]")).each(function() {
      let elements = ""
      const $field = $(this)
      const rule = JSON.parse($field.attr("data-conditional"));
      conditional.check(rule, $field)
      for (const index in rule) { elements += ( index == 0 ? "" : ", " ) + "#" + rule[index].field }
      if(elements) {
        $(elements).on("input", function(){
          conditional.check(rule, $field)
        })
      }
    })
  },
  check(rule, $field, auto=true) {
    //check if rule is true
    let valid = false
    let conditional = this
    for (const index in rule) {
      const $input = ("field" in rule[index]) ? $("#" + rule[index].field) : "";
      if($input) {
        let check = false
        let input = $input.val()
        const value = ("value" in rule[index]) ? rule[index].value : "";
        if(!input) { input = "" }
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
    if(auto) {
      conditional.toggle($field, valid)
    } else {
      return valid;
    }
  },
  toggle($field, show) {
    //show/hide conditional field
    const classes = el("field_disabled", false)
    const disabled = $field.hasClass(classes)
    if(show){
      if(disabled) { $field.removeClass(classes) }
    } else {
      if(!disabled) { $field.addClass(classes) }
    }
  },
}