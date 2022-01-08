import { el } from './helpers'

export let initLoader = (form) => {
  removeLoader(form)
  document.addEventListener('readystatechange', () => { removeLoader(form) })
}

export let removeLoader = (form, delay=500) => {
  if(form && document.readyState === 'complete') {
    setTimeout(() => {
      form.classList.remove(el('form','','first-loading'))
    }, delay)
  }
}
