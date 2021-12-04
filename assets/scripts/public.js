// Formality public script
import { cl } from './public/helpers'
import { initForm, initPage } from './public/main'

document.addEventListener('DOMContentLoaded', () => {
  const forms = document.querySelectorAll(cl('form'))
  forms.forEach((form) => { initForm(form) })
  initPage()
})
