// Formality public script
import { cl, isConversational, getInput } from './modules/helpers'

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
    initLoader(form)
    addStepIndexes(form)
    loadFields(form, conversational)
    buildNavigation(form, conversational)
    submitForm(form)
    initMedia(form)
  })
  firstFocus()
  initHints()
  initEmbeds()
  dragNdrop()
}

document.addEventListener('initFormality', () => {
  initForms()
})

document.addEventListener('DOMContentLoaded', () => {
  const initEvent = new Event('initFormality');
  document.dispatchEvent(initEvent);
})
