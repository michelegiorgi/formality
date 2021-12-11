import { el } from '../modules/helpers'

export const fieldMultiple = (field) => {
  if(!field.classList.contains(el('field', '', 'multiple'))) return
  const labels = field.querySelectorAll('input + label')
  labels.forEach((label) => {
    label.addEventListener('click', () => {
      label.previousElementSibling.focus()
    })
  })
}
