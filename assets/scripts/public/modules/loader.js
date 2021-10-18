import { el } from '../helpers'

export let removeLoader = (form) => {
  if(form && document.readyState === 'complete') {
    setTimeout(() => {
      form.classList.remove(el('form','','first-loading'))
    }, 500)
  }
}
