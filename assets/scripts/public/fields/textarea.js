import { el, isSafari } from '../modules/helpers'
import DOMPurify from 'dompurify'; // Import DOMPurify for sanitization

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
  const maxLengthLabel = textarea.getAttribute('maxlength') ? ' / ' + parseInt(textarea.getAttribute('maxlength')) : ''
  const counterHtml = maxLengthLabel ? `<div class="${ el('textarea', 'counter') }">${ DOMPurify.sanitize(savedValue.length + maxLengthLabel) }</div>` : '' // Sanitize the HTML content
  textarea.insertAdjacentHTML('beforebegin', counterHtml)
  textarea.addEventListener('input', (e) => {
    const counter = textarea.previousElementSibling
    const currentValue = textarea.value
    const newLines = currentValue.match(/(\r\n|\n|\r)/g)
    const realLength = newLines != null && isSafari() ? newLines.length + currentValue.length : currentValue.length
    if(counter) {
      counter.innerText = realLength + maxLengthLabel
    }
    textarea.rows = minRows
    const newRows = Math.ceil((textarea.scrollHeight - baseScrollHeight) / lineHeight)
    textarea.rows = minRows + newRows
  })
}
