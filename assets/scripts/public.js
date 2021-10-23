// Formality public script
import { cl, getElements } from './public/helpers'
import { initForm } from './public/main'
import { initUx } from './public/modules/ux'
import { initHints } from './public/modules/hints'

document.addEventListener('DOMContentLoaded', () => {
  const forms = getElements(cl('form'))
  forms.forEach((form) => { initForm(form) })
  initUx()
  initHints()
})
