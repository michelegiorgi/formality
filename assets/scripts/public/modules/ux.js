import { el, cl, pushEvent } from '../helpers'
import { showHints, clearHints } from './hints'
import { updateDbg } from './dbg'

export let initUx = () => {
  window.addEventListener('foSidebarOpened', function(e) { focusFirst(600) }, false)
  if(document.body.classList.contains('single-formality_form')) { focusFirst(1000) }
}

export let inputFocus = (input, field, dbg = false) => {
  input.addEventListener('focus', () => {
    field.classList.add(el('field', '', 'focus'))
    showHints(field)
    if(dbg) { updateDbg(field) }
    pushEvent('FieldFocus', { el: field })
  })
  input.addEventListener('blur', () => {
    field.classList.remove(el('field', '', 'focus'))
    clearHints()
  })
}

export let inputPlaceholder = (input, field) => {
  const placeholder = input.getAttribute('placeholder')
  if(placeholder) {
    const wrap = field.querySelector(cl('input'))
    if(wrap) {
      wrap.insertAdjacentHTML('beforeend', `<div class="${ el("input", "status") }" data-placeholder="${ placeholder }"></div>`)
    }
  }
}

export let inputFilled = (input, field) => {
  input.addEventListener('change', () => {
    const val = input.type == 'checkbox' ? field.querySelector('input:checked') !== null : Boolean(input.value)
    field.classList.toggle(el('field', '', 'filled'), val)
    const navListItem = document.querySelector(cl('nav', 'list li[data-name="' + input.name + '"]'))
    if(navListItem) { navListItem.classList.toggle('active', val) }
    if(val){ pushEvent('FieldFill', { el: field }) }
  })
}

export let focusFirst = (delay = 10) => {
  const inputs = document.querySelectorAll(cl('section:first-child :input'))
  let focus = false
  inputs.forEach((input) => {
    if(!focus && input.value.length == 0) {
      focus = true
      setTimeout(() => { input.focus() }, delay)
    }
  })
}
