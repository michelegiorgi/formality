// Formality public script
import { cl, isConversational, isLoaded, getInput, pushEvent } from './modules/helpers'

import { initLoader } from './modules/loader'
import { loadFields, firstFocus } from './modules/fields'
import { buildNavigation } from './modules/navigation'
import { addStepIndexes } from './modules/validation'
import { submitForm } from './modules/submit'
import { initHints } from './modules/hints'
import { initEmbeds } from './modules/embeds'

import { initMedia } from './fields/media'
import { dragNdrop } from './fields/upload'

let initForms = () => {
  const forms = document.querySelectorAll(cl('form'))
  forms.forEach((form) => {
    const conversational = isConversational(form)
    const loaded = isLoaded(form)
    if(!loaded) {
      initLoader(form)
      addStepIndexes(form)
      loadFields(form, conversational)
      buildNavigation(form, conversational)
      submitForm(form)
      initMedia(form)
    }
  })
  firstFocus()
  initHints()
  initEmbeds()
  dragNdrop()
}

window.addEventListener('foFormsInit', () => {
  initForms()
})

document.addEventListener('DOMContentLoaded', () => {
  pushEvent('FormsInit');
})
