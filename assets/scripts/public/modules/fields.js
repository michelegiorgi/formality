import { el, cl, pushEvent, nextEl, prevEl, getInput, isMobile, animateScroll, isVisible } from './helpers'
import { showHints, clearHints } from './hints'
import { moveStep } from './navigation'
import { updateDbg } from './dbg'

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
      wrap.insertAdjacentHTML('beforeend', `<div class="${ el('input', 'status') }" data-placeholder="${ placeholder }"></div>`)
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

export let inputKeypress = (input, field, conversational) => {
  input.addEventListener('keydown', (e) => {
    const validprev = !input.value || ['checkbox', 'radio', 'select-one'].includes(input.type)
    if(validprev && e.key == 'Backspace') {
      moveField(input, field, 'prev', e, conversational)
    } else if(e.key == 'Enter' && input.type !== 'textarea') {
      moveField(input, field, 'next', e, conversational)
    } else if(e.key == 'Tab') {
      moveField(input, field, 'next', e, conversational)
    }
  })
}

export let moveField = (input, field, direction='next', e, conversational=false) => {
  let element = false
  const visible = cl('field:not(' + cl('field', 'disabled') + ')')
  switch (direction) {
    case 'next':
      element = nextEl(field, visible)
      break
    case 'prev':
      element = prevEl(field, visible)
      break
    default:
      element = field
  }
  if(element) {
    if(conversational && !isMobile()) {
      let offset = 0
      if(document.body.classList.contains('body-formality')) {
        offset = window.innerHeight / 3
        const win = element.ownerDocument.defaultView
        if(field && field.classList.contains(el('field', '', 'select-js')) && direction =='next') {
          input.blur()
          const jsSelect = field.querySelector(cl('select', 'list'))
          offset += jsSelect.offsetHeight
        }
        animateScroll(element.getBoundingClientRect().top + win.pageYOffset - offset, 300)
      } else {
        const main = field.closest(cl('main'))
        offset = main.offsetHeight / 3
        animateScroll(main.scrollTop + element.offsetTop - offset, 300, main)
      }
    } else {
      const elementInput = getInput(element)
      elementInput.focus()
    }
    if(e) { e.preventDefault() }
  } else {
    const form = field.closest(cl('form'))
    if(field.matches(':first-child, :nth-child(2)') && direction == 'prev'){
      const prevButton = form.querySelector(cl('btn', '', 'prev'))
      if(prevButton && isVisible(prevButton)) {
        if(e) { e.preventDefault() }
        moveStep('prev', form)
      }
    } else if(field.matches(':last-child') && direction == 'next') {
      if(e) { e.preventDefault() }
      const nextButton = form.querySelector(cl('btn', '', 'next'))
      if(nextButton && isVisible(nextButton)) {
        moveStep('next', form)
      } else {
        let event = new Event('submit', { 'bubbles': true, 'cancelable': true });
        form.dispatchEvent(event);
      }
    }
  }
}

export let firstFocus = () => {
  window.addEventListener('foSidebarOpened', (e) => { focusFirst(600) }, false)
  if(document.body.classList.contains('single-formality_form')) { focusFirst(1000) }
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
