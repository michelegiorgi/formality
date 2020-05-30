import { el, uid } from './helpers'
import 'parsleyjs'

export default {
  init() {
    //init conditional field
    let conditional = this
    $(el("section", true, " > [data-conditional]")).each(function() {
      let elements = ""
      const $field = $(this)
      const rule = JSON.parse($field.attr("data-conditional"));
      conditional.check(rule, $field)
      for (const index in rule) { elements += ( index == 0 ? "" : ", " ) + "[name=" + rule[index].field + "]" }
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
      let $input = ("field" in rule[index]) ? $("[name=" + rule[index].field+ "]") : "";
      if($input) {
        let check = false
        const type = $input.attr("type")
        if(type=="radio"||type=="checkbox") { $input = $input.filter(':checked') }
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
    let conditional = this
    const classes = el("field_disabled", false)
    const disabled = $field.hasClass(classes)
    const video = $field.find('video')
    if(!$field.hasClass(el("field", false, "--fixed-height"))) {
      const height = $field.outerHeight()
      $field.attr("style", "--fixed-height:"+height+"px").addClass(el("field", false, "--fixed-height"))
    }
    if(show) {
      if(disabled) {
        //$field.slideDown(200, function() { });
        $field.removeClass(classes)
        conditional.validation($field, false)
      }
    } else {
      if(!disabled) {
        //$field.slideUp(200, function() { });
        $field.addClass(classes)
        conditional.validation($field, true)
      }
    }
    if(video) {
      video.hide().show()
    }
  },
  validation($field, disable=true) {
    //reset validation if required
    const $required = $field.find("[required]")
    if($required) {
      const navlink = ' li[data-name="' + $required.attr("id") + '"]'
      if(disable) {
        $required.attr("data-parsley-excluded", "true")
        $(el("nav_list", true, navlink)).addClass("disabled")
      } else {
        $required.attr("data-parsley-excluded", "false")
        $(el("nav_list", true, navlink)).removeClass("disabled")
      }
      uid($field)
      $(el("form", "uid")).parsley().refresh()
    }
  },
}