import el from '../utils/elements'
import uid from '../utils/uid'
const { __ } = wp.i18n
import 'parsleyjs'

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
      range: /* translators: validation */ __("This value should be between %1$s and %2$s", "formality"),
      minlength: /* translators: validation */ __("This value is too short. It should have %s characters or more", "formality"),
      maxlength: /* translators: validation */ __("This value is too long. It should have %s characters or fewer", "formality"),
      length: /* translators: validation */ __("This value length is invalid. It should be between %1$s and %2$s characters long", "formality"),
      check: /* translators: validation */ __("You must select between %1$s and %2$s choices", "formality"),
    });
    window.Parsley.setLocale('en');
  },
}