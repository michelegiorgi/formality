import { el, cl } from '../helpers'

export const fieldNumber = (field) => {
  if(!field.classList.contains(el('field', '', 'number'))) return
  const input = field.querySelector('input')
  const arrowsHtml = `<div class="${ el('input', 'spinner') }"><a href="#"></a><a href="#"></a></div>`
  input.insertAdjacentHTML('afterend', arrowsHtml)
  input.addEventListener('keydown', (e) => {
    if(!e.code.search('Digit') && !e.code.search('Arrow')) {
      e.preventDefault()
    }
  })
  const links = field.querySelectorAll(cl('input', 'spinner a'))
  links.forEach((link, index) => {
    link.addEventListener('click', (e) => {
      e.preventDefault()
      const value = input.value
      if(!value) {
        input.value = 0
      } else if(index == 0 && input.max > value) {
        input.stepUp()
      } else if(index == 1 && input.min < value) {
        input.stepDown()
      }
      input.dispatchEvent(new Event('change'))
      input.focus()
    })
  })
}
