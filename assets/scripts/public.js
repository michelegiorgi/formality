// Formality public script
import { el, getElements } from './public/helpers'
import { loadFields } from './public/fields'

import { removeLoader } from './public/modules/loader'

document.addEventListener('DOMContentLoaded', (event) => {
  const forms = getElements(el('form'))
  forms.forEach((form) => {
    removeLoader(form)
    document.addEventListener('readystatechange', () => { removeLoader(form) })
    loadFields(form)
  })
});
