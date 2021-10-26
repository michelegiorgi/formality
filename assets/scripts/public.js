// Formality public script
import { cl } from './public/helpers'
import { initForm } from './public/main'
import { firstFocus } from './public/modules/fields'
import { initHints } from './public/modules/hints'

document.addEventListener('DOMContentLoaded', () => {
  const forms = document.querySelectorAll(cl('form'))
  forms.forEach((form) => { initForm(form) })
  firstFocus()
  initHints()
})
