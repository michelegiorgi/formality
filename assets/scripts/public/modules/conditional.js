import { cl, el } from '../helpers'

export let initConditionalField = (form, field) => {
  if(!field.hasAttribute('data-conditional')) return
  let query = ""
  const rule = JSON.parse(field.getAttribute("data-conditional"))
  for (const index in rule) { query += ( index == 0 ? "" : ", " ) + "[name=" + rule[index].field + "]" }
  if(query) {
    checkCondition(form, field, rule)
    const inputs = form.querySelectorAll(query)
    inputs.forEach((input) => {
      input.addEventListener('input', () => {
        checkCondition(form, field, rule)
      })
    })
  }
}

export let checkCondition = (form, field, rule, auto=true) => {
  let valid = false
  for (const index in rule) {
    let inputs = 'field' in rule[index] ? form.querySelectorAll('[name="' + rule[index].field + '"]') : '';
    if(inputs.length) {
      let check = false
      let inputValue = ''
      inputs.forEach((input)=> {
        const type = input.getAttribute('type')
        switch(type) {
          case "radio" : inputValue = input.checked ? input.value : '' ; break;
          case "checkbox" : inputValue = input.checked ? input.value : ''; break;
          case "file" : inputValue = input.hasAttribute('data-file') ? input.getAttribute('data-file') : ''; break;
          default : inputValue = input.value; break;
        }
      })
      const ruleValue = ("value" in rule[index]) ? rule[index].value : "";
      switch(rule[index].is) {
        case "==" : check = inputValue == ruleValue; break;
        case "!==" : check = inputValue !== ruleValue; break;
        case ">" : check = inputValue > ruleValue; break;
        case ">=" : check = inputValue >= ruleValue; break;
        case "<=" : check = inputValue <= ruleValue; break;
        case "<" : check = inputValue < ruleValue; break;
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
    toggleConditionalField(field, valid)
  } else {
    return valid;
  }
}

export let toggleConditionalField = (field, show) => {
  const disabled = field.classList.contains(el('field', '', 'disabled'))
  const video = field.querySelector('video')
  if(!field.classList.contains(el('field', '', 'fixed-height'))) {
    field.style.setProperty('--fixed-height', field.offsetHeight + "px")
    field.classList.add(el('field', '', 'fixed-height'))
  }
  if(show && disabled) {
    field.classList.remove(el('field', '', 'disabled'))
    toggleConditionalValidation(field, false)
  } else if(!show && !disabled) {
    field.classList.add(el('field', '', 'disabled'))
    toggleConditionalValidation(field, true)
  }
  if(video) {
    video.style.display = 'none'
    video.style.display = 'block'
  }
}

export let toggleConditionalValidation = (field, disable=true) => {
  const input = field.querySelector("[required]")
  if(disable) {
    field.setAttribute('data-excluded','')
  } else {
    field.removeAttribute('data-excluded')
  }
  if(input) {
    const navItem = document.querySelector(cl('nav', 'list li[data-name="' + input.name + '"]'))
    if(navItem) {
      navItem.classList.toggle('disabled', disable)
    }
  }
}

/*
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
    const $input = $field.find("[required]")
    if(disable) {
      $field[0].setAttribute('data-excluded','')
    } else {
      $field[0].removeAttribute('data-excluded')
    }
    $(el("nav_list", true, ' li[data-name="' + $input.attr("name") + '"]')).toggleClass("disabled", disable)
  },
}
*/
