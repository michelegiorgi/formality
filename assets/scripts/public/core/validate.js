import { el, uid } from './helpers'
import 'parsleyjs'
const { __ } = wp.i18n
let fieldOptions = {
  text: {
    multiple: false,
  },
  message: {
    multiple: false,
  },
  email: {
    multiple: false,
    rules: {
      email: __("This value should be a valid email", "formality"),
    }
  },
  number: {
    multiple: false,
    rules: {
      number: __("This value should be a valid number", "formality"),
      min: /* translators: validation */ __("This value should be greater than or equal to %s", "formality"),
      max: /* translators: validation */ __("This value should be lower than or equal to %s", "formality"),
    }
  },
  select: {
    multiple: false,
  },
  multiple: {
    multiple: true,
  },
  rating: {
    multiple: true,
  },
  switch: {
    multiple: false,
  },
  upload: {
    multiple: false,
  },
}

export default {
  init() {
    //init validation
    $(el("form")).each(function() {
      uid($(this))
      $(el("section", "uid")).each(function(index, section) {
        $(section).find(':input').attr('data-parsley-group', 'step-' + index)
      })
    })
    this.field_error()
    this.field_success()
    this.form_error()
    this.i18n()
  },
  checkstep(index, newindex) {
    //validate single step
    let valid = false
    let options = this.parsley_options()
    if(index > newindex) {
      valid = true
    } else {
      $(el("form", "uid")).parsley(options).whenValidate({
        group: 'step-' + index,
      }).done(function() {
        valid = true
        $(el("nav_section", "uid")).eq(index).addClass(el("nav_section", false, "--validated"))
      })
    }
    return valid
  },
  form() {
    //validate standard form (1 step)
    let options = this.parsley_options()
    $(el("form", "uid")).parsley(options)
  },
  parsley_options() {
    //create parsley options array
    let options = {
      classHandler: function (element) {
        return element.$element.closest(el("field"))
      },
      errorClass: el("field_error", false),
      errorsContainer: function(element) {
        return element.$element.closest(el("input")).find(el("input", true, "__status"))
      },
      successClass: el("field_success", false),
      errorsWrapper: '<ul class="'+el("input_errors", false)+'"></ul>',
    }
    return options
  },
  form_error() {
    window.Parsley.on('form:error', function() {

    })
  },
  field_error() {
    //field error event
    window.Parsley.on('field:error', function() {
      const id = $(this.$element).attr("id")
      uid($(this.$element))
      $(el("nav_legend", 'uid', ' li[data-name="' + id + '"]')).addClass("error")
      const index = $(el("nav_section", "uid")).index(el("nav_section", "uid", "--active"))
      $(el("nav_section", "uid")).eq(index).removeClass(el("nav_section", false, "--validated"))
    })
  },
  field_success() {
    //field success event
    window.Parsley.on('field:success', function() {
      const id = $(this.$element).attr("id")
      uid($(this.$element))
      $(el("nav_legend", "uid", ' li[data-name="' + id + '"]')).removeClass("error")
    })
  },
  i18n() {
    window.Parsley.addMessages('en', {
      defaultMessage: __("This value seems to be invalid", "formality"),
      type: {
        email: __("This value should be a valid email", "formality"),
        url: __("This value should be a valid url", "formality"),
        number: __("This value should be a valid number", "formality"),
        integer: __("This value should be a valid integer", "formality"),
        digits: __("This value should be digits", "formality"),
        alphanum: __("This value should be alphanumeric", "formality"),
      },
      required: __("This value is required", "formality"),
      pattern: __("This value seems to be invalid", "formality"),
      min: /* translators: validation */ __("This value should be greater than or equal to %s", "formality"),
      max: /* translators: validation */ __("This value should be lower than or equal to %s", "formality"),
      range: /* translators: validation */ __("This value should be between %s and %s", "formality"),
      minlength: /* translators: validation */ __("This value is too short. It should have %s characters or more", "formality"),
      maxlength: /* translators: validation */ __("This value is too long. It should have %s characters or fewer", "formality"),
      length: /* translators: validation */ __("This value length is invalid. It should be between %s and %s characters long", "formality"),
      check: /* translators: validation */ __("You must select between %s and %s choices", "formality"),
    });
    window.Parsley.setLocale('en');
  },
  checkRule(input, rule) {
    let valid = false;
    switch(rule) {
      case 'required':
        if(NodeList.prototype.isPrototypeOf(input)){
          Array.prototype.forEach.call(input, function(single, i){ if(single.checked) { valid = true; } })
        } else {
          valid = input.value !== ''
        }
        break;
      }
      case 'email': {
        valid = input.value.match(/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/);
        break;
      }
      case 'checked': {
        valid = input.checked;
        break;
      }
      case 'notchecked': {
        valid = !input.checked;
        break;
      }
    }
    return valid;
  },
  validateField(field) {
    const type = field.getAttribute('data-type')
    const required = field.classList.contains(el("field", false, "--required"))
    const rules = Object.keys(fieldRules[type]['rules'])
    const multiple = fieldRules[type]['multiple'];
    const input = multiple ? field.querySelectorAll('input, select, textarea') : field.querySelector('input, select, textarea')
    let valid = true;
    if(!rules.includes('required') && !input.value) {
      //skip validation
    } else {
      Array.prototype.forEach.call(rules, function(rule, i){
        if(valid && !checkRule(input, rule)) {
          valid = false;

        }
      })
    }
    return valid;
  },
  validate(form, group) {
    let errors = false;
    Array.prototype.forEach.call(inputs, function(input, i){

    })
    if(errors) {
      let firsterror = form.querySelector('.error input')
      firsterror.focus()
      return false
    }
  }
}
