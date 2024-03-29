import { el, cl, isMobile, filterElements, animateScroll } from '../modules/helpers'
import { moveField } from '../modules/fields'

export const fieldSelect = (field, conversational) => {
  if(!field.classList.contains(el('field', '', 'select'))) return
  const input = field.querySelector(cl('input'))
  const select = input.querySelector('select')
  select.addEventListener('focus', () => {
    field.classList.add(el('field', '', 'open'))
  })
  select.addEventListener('blur', () => {
    setTimeout(()=> { field.classList.remove(el('field', '', 'open')) }, 100)
  })
  customSelect(field, input, select, conversational)
}

export let customSelect = (field, input, select, conversational) => {
  if(isMobile()) return
  const options = select.querySelectorAll('option:not([disabled])')
  let optionsHtml = ''
  options.forEach((option) => {
    const selected = option.hasAttribute('selected') ? ' class="selected"' : ''
    optionsHtml += `<li data-text="${ option.innerText }" data-value="${ option.value }"${ selected }>${ option.innerText }</li>`
  })
  const optionsClass = options.length < 6 ? ' options--' + options.length : '';
  input.insertAdjacentHTML('beforeend', `<div class="${ el('select', 'list') + optionsClass }"><ul>${ optionsHtml }</ul></div>`)
  field.classList.add(el('field', '', 'select-js'))
  select.insertAdjacentHTML('beforebegin', `<div class="${ el('select', 'fake') }"></div>`)
  const selectFake = field.querySelector(cl('select', 'fake'))
  selectFake.addEventListener('mousedown', (e) => {
    e.preventDefault()
    field.classList.toggle(el('field', '', 'open'))
    select.focus()
  })
  const selectItems = field.querySelectorAll(cl('select', 'list li'))
  selectItems.forEach((selectItem) => {
    selectItem.addEventListener('click', (e) => {
      e.preventDefault()
      const value = selectItem.getAttribute('data-value')
      selectOption(field, select, selectItems, value, true)
    })
  })
  select.addEventListener('keydown', (e) => {
    e.preventDefault()
    const focused = filterElements(selectItems, '.focus')
    if(e.key == 'ArrowUp') {
      moveOption(focused, 'prev', selectItems)
    } else if(e.key == 'ArrowDown') {
      moveOption(focused, 'next', selectItems)
    } else if(e.key == 'Enter' && focused) {
      selectOption(field, select, selectItems, focused.getAttribute('data-value'), true)
    } else if((/^[a-zA-Z0-9]{1}$/).test(e.key))  {
      const search = filterElements(selectItems, `[data-text^="${e.key}" i]`)
      if(search) {
        moveOption(focused, 'search', search)
      }
    }
  })
}

export let selectOption = (field, select, selectItems, value, focus = false) => {
  let selectedItem
  selectItems.forEach((selectItem) => {
    if(selectItem.getAttribute('data-value') == value) {
      selectedItem = selectItem
      selectItem.classList.add('selected', 'focus')
    } else {
      selectItem.classList.remove('selected', 'focus')
    }
  })
  select.value = value
  select.dispatchEvent(new Event('input'))
  select.dispatchEvent(new Event('change'))
  field.classList.remove(el('field', '', 'error'))
  if(focus) {
    select.focus()
    field.classList.remove(el('field', '', 'open'))
  }
}

export let moveOption = (focused, direction = 'next', selectItems) => {
  if(focused) {
    let nextprev = direction == 'search' ? selectItems : direction == 'next' ? focused.nextElementSibling : focused.previousElementSibling
    if(nextprev) {
      focused.classList.remove('focus')
      nextprev.classList.add('focus')
      focused = nextprev
    }
  } else {
    focused = direction == 'search' ? selectItems : selectItems[0]
    focused.classList.add('focus')
  }
  const optionsList = focused.closest('ul')
  const scrollPx = parseInt(Math.max(0, focused.offsetTop - (optionsList.offsetHeight/2) + (focused.offsetHeight/2)) )
  animateScroll(scrollPx, 100, optionsList)
}
