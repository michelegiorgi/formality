import { el, uid } from './helpers'
const { __ } = wp.i18n
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
    let valid = false;
    switch(rule) {
      case 'required':
        if(NodeList.prototype.isPrototypeOf(input)){
          Array.prototype.forEach.call(input, function(single, i){ if(single.checked) { valid = true; } })
        } else {
          valid = input.value !== ''
        }
        break;
      case 'email':
        valid = input.value.match(/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/);
        break;
      case 'checked':
        valid = input.checked;
        break;
      case 'notchecked':
        valid = !input.checked;
        break;
      case 'number':
        valid = !isNaN(input.value)
        console.log(valid, input.value)
        break;
      case 'min':
        valid = input.value >= input.min
        console.log(valid, input.value, input.min)
        break;
      case 'max':
        valid = input.value <= input.max
        console.log(valid, input.value, input.max)
        break
    }
    return valid;
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
      Array.prototype.forEach.call(rules, function(rule){
        if(valid && !validate.checkRule(input, rule)) {
          error = rule == 'required' ? __("This value is required", "formality") : fieldOptions[type]['rules'][rule];
          valid = false;
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
    Array.prototype.forEach.call(fields, function(field, i){
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
