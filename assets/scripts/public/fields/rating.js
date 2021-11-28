import { el, cl } from '../helpers'

export const fieldRating = (field) => {
  if(!field.classList.contains(el('field', '', 'rating'))) return
  const labels = field.querySelectorAll('input + label')
  labels.forEach((label) => {
    label.addEventListener('click', () => {
      label.previousElementSibling.focus()
    })
  })
}
