import { cl, el } from './helpers'

export let initConditionalField = (form, field) => {
  if(!field.hasAttribute('data-conditional')) return
  let query = ''
  const rule = JSON.parse(field.getAttribute('data-conditional'))
  for(const index in rule) { query += ( index == 0 ? '' : ', ' ) + '[name="' + rule[index].field + '"]' }
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
      inputs.forEach((input) => {
        const type = input.getAttribute('type')
        switch(type) {
          case 'radio':
          case 'checkbox':
            if(input.checked) { inputValue = input.value }
            break
          case 'file':
            inputValue = input.hasAttribute('data-file') ? input.getAttribute('data-file') : ''
            break
          default:
            inputValue = input.value
            break
        }
      })
      const ruleValue = ('value' in rule[index]) ? rule[index].value : '';
      switch(rule[index].is) {
        case '==' : check = inputValue == ruleValue; break;
        case '!==' : check = inputValue !== ruleValue; break;
        case '>' : check = inputValue > ruleValue; break;
        case '>=' : check = inputValue >= ruleValue; break;
        case '<=' : check = inputValue <= ruleValue; break;
        case '<' : check = inputValue < ruleValue; break;
      }
      if(check) {
        valid = true;
      } else if (index == 0 && (typeof rule[1] !== 'undefined') && rule[1].operator == '&&' ) {
        valid = false;
        break;
      } else if((rule[index].operator=='&&') && (rule[index]._key > 1)) {
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
    field.style.setProperty('--fixed-height', field.offsetHeight + 'px')
    field.classList.add(el('field', '', 'fixed-height'))
  }
  if(show && disabled) {
    field.classList.remove(el('field', '', 'disabled'))
    toggleConditionalValidation(field, false)
    toggleConditionalNavbar(field, true)
  } else if(!show && !disabled) {
    field.classList.add(el('field', '', 'disabled'))
    toggleConditionalValidation(field, true)
    toggleConditionalNavbar(field, false)
  }
  if(video) {
    video.style.display = 'none'
    video.style.display = 'block'
  }
}

export let toggleConditionalNavbar = (field, show) => {
  const input = field.querySelector('input, select, textarea')
  if(input) {
    const navItem = document.querySelector(cl('nav', 'list li[data-name="' + input.name + '"]'))
    if(navItem) { navItem.classList.toggle('disabled', !show) }
  }
}

export let toggleConditionalValidation = (field, disable=true) => {
  if(disable) {
    field.setAttribute('data-excluded','')
  } else {
    field.removeAttribute('data-excluded')
  }
}
