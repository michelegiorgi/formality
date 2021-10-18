import { el, uid } from './helpers'
const { __, sprintf } = wp.i18n
let fieldOptions = {
  text: {
    multiple: false,
  },
  textarea: {
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
      number_min: /* translators: validation */ __("This value should be greater than or equal to %s", "formality"),
      number_max: /* translators: validation */ __("This value should be lower than or equal to %s", "formality"),
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
    rules: {
      file: __("This is not a valid file", "formality"),
      file_ext: /* translators: validation */ __('%s file extension is not allowed', 'formality'),
      file_size: __('Your file exceeds the size limit', 'formality'),
    }
  },
}

export default {
  init() {
    //init validation
    let validate = this;
    const forms = document.querySelectorAll(el("form"))
    forms.forEach(function(form){
      validate.addStepIndexes(form)
      const inputs = form.querySelectorAll('input, select, textarea')
      inputs.forEach(function(input){
        validate.liveUpdate(input)
      })
    })
  },
  addStepIndexes(form) {
    const sections = form.querySelectorAll(el("section"))
    sections.forEach(function(section, index){
      const fields = section.querySelectorAll(el("field"))
      fields.forEach(function(field){
        field.setAttribute('data-step', index)
      })
    })
  },
  liveUpdate(input) {
    let validate = this;
    input.addEventListener('input', function(){
      let field = input.closest(el("field"))
      validate.validateField(field, true)
    })
  },
  validateStep(form, index, newindex) {
    if(index > newindex) { return true }
    const valid = this.validateForm(form, index)
    if(valid) {
      const sections = form.querySelectorAll(el("nav_section", "uid"))
      sections[index].classList.add(el("nav_section", false, "--validated"))
    }
    return valid
  },
  checkRule(input, rule) {
    let result = {
      valid: false,
      placeholder: '',
    }
    switch(rule) {
      case 'required':
        if(NodeList.prototype.isPrototypeOf(input)){
          input.forEach(function(single, i){ if(single.checked) { result.valid = true; } })
        } else {
          result.valid = input.value !== ''
        }
        break
      case 'email':
        result.valid = input.value.match(/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/)
        break
      case 'checked':
        result.valid = input.checked
        break
      case 'notchecked':
        result.valid = !input.checked
        break
      case 'number':
        result.valid = !isNaN(input.value)
        break
      case 'number_min':
        result.placeholder = input.min
        result.valid = parseFloat(input.value) >= result.placeholder
        break
      case 'number_max':
        result.placeholder = input.max
        result.valid = parseFloat(input.value) <= result.placeholder
        break
      case 'file':
        result.valid = input.files.length ? true : false
        break
      case 'file_ext':
        result.file = input.files.length ? input.files[0] : false
        if(result.file && result.file.type !== '') {
          result.formats = input.getAttribute('accept').split(", ")
          result.placeholder = ('.' + result.file.name.split('.').pop()).toLowerCase()
          result.valid = result.formats.indexOf(result.placeholder) !== -1
        }
        break
      case 'file_size':
        result.file = input.files.length ? input.files[0] : false
        if(result.file && result.file.size > 0) {
          result.valid = result.file.size <= parseInt(input.getAttribute('data-max-size'))
        }
        break
    }
    return result;
  },
  changeFieldStatus(field, name, valid=true, error='') {
    const form = field.closest(el('form'))
    const status = field.querySelector(el('input_status'))
    const legend = form.querySelector(el('nav_legend', true, ' li[data-name="' + name + '"]'))
    field.classList.toggle(el("field", false, "--error"), !valid);
    status.innerHTML = !error ? '' : ('<div class="' + el("input_errors", false) + '">' + error + '</div>')
    if(legend) { legend.classList.toggle("error", !valid) }
    if(!valid) {
      const section = form.querySelector(el("nav_section", "uid", "--active"))
      if(section) { section.classList.remove(el("nav_section", false, "--validated")) }
    }
    field.classList.add(el("field", false, "--validated"));
  },
  validateField(field, soft=false) {
    if(field.hasAttribute('data-excluded')) { return true }
    let validate = this;
    const type = field.getAttribute('data-type')
    const required = field.classList.contains(el("field", false, "--required"))
    const validated = field.classList.contains(el("field", false, "--validated"))
    let rules = 'rules' in fieldOptions[type] ? Object.keys(fieldOptions[type]['rules']) : []
    const multiple = fieldOptions[type]['multiple']
    if(required) { rules.unshift('required') }
    const input = multiple ? field.querySelectorAll('input, select, textarea') : field.querySelector('input, select, textarea')
    const name = multiple ? input[0].name : input.name
    let valid = true;
    let error = '';
    if(!rules.includes('required') && !multiple && !input.value) {
      //skip validation
    } else {
      rules.forEach(function(rule){
        if(valid) {
          const check = validate.checkRule(input, rule)
          if(!check.valid) {
            error = rule == 'required' ? __("This value is required", "formality") : sprintf(fieldOptions[type]['rules'][rule], check.placeholder);
            valid = false;
          }
        }
      })
    }
    if(!soft || (soft && validated)) {
      validate.changeFieldStatus(field, name, valid, error)
    }
    return valid;
  },
  validateForm(form, step=null) {
    let validate = this
    let errors = false
    const selector = step == null ? el("field") : el("field", true, '[data-step="'+step+'"]')
    let fields = form.querySelectorAll(selector)
    let firsterror = false
    fields.forEach(function(field, i){
      const error = !validate.validateField(field)
      if(!errors && error) {
        errors = true
        firsterror = field.querySelector('input, select, textarea')
      }
    })
    if(firsterror) { firsterror.focus() }
    return !errors
  }
}
