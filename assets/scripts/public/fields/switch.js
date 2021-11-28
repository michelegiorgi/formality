import { el, cl } from '../helpers'

export const fieldSwitch = (field) => {
  if(!field.classList.contains(el('field', '', 'switch'))) return
  const input = field.querySelector('input')
  const label = input.nextElementSibling
  label.addEventListener('click', () => {
    input.focus()
  })
  input.addEventListener('keydown', (e) => {
    if(e.code !== 'Backspace') return
    if(input.checked) {
      input.checked = false
      e.preventDefault()
      e.stopImmediatePropagation();
    }
  }, true)
}
