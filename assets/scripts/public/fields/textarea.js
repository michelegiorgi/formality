import { el } from '../modules/helpers'

export const fieldTextarea = (field) => {
  if(!field.classList.contains(el('field', '', 'textarea'))) return
  const textarea = field.querySelector('textarea')
  let savedValue = textarea.value
  textarea.value = ''
  const baseScrollHeight = textarea.scrollHeight
  const style = window.getComputedStyle(textarea)
  let lineHeight = style.getPropertyValue('line-height')
  lineHeight = parseInt(lineHeight.replace('px', ''))
  textarea.value = savedValue
  const minRows = textarea.rows
  const maxLength = textarea.getAttribute('maxlength') ? ' / ' + parseInt(textarea.getAttribute('maxlength')) : ''
  const counterHtml = maxLength ? `<div class="${ el('textarea', 'counter') }">${ savedValue.length + maxLength }</div>` : ''
  textarea.insertAdjacentHTML('beforebegin', counterHtml)
  textarea.addEventListener('input', (e) => {
    const counter = textarea.previousElementSibling
    if(counter) {
      counter.innerText = textarea.value.length + maxLength
    }
    textarea.rows = minRows
    const newRows = Math.ceil((textarea.scrollHeight - baseScrollHeight) / lineHeight)
    textarea.rows = minRows + newRows
  })
}
