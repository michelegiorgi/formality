// Formality public script
import { cl, isConversational, getInput } from './modules/helpers'

import { initLoader } from './modules/loader'
import { inputFocus, inputPlaceholder, inputFilled, inputKeypress, firstFocus } from './modules/fields'
import { hasDbg } from './modules/dbg'
import { buildNavigation } from './modules/navigation'
import { liveUpdate, addStepIndexes } from './modules/validation'
import { submitForm } from './modules/submit'
import { initConditionalField } from './modules/conditional'
import { initHints } from './modules/hints'
import { initEmbeds } from './modules/embeds'

import { initMedia } from './fields/media'
import { fieldMultiple } from './fields/multiple'
import { fieldNumber } from './fields/number'
import { fieldRating } from './fields/rating'
import { fieldSwitch } from './fields/switch'
import { fieldTextarea } from './fields/textarea'
import { fieldUpload, dragNdrop } from './fields/upload'
import { fieldSelect } from './fields/select'

let initForm = (form) => {
  const conversational = isConversational(form)
  initLoader(form)
  addStepIndexes(form)
  loadFields(form, conversational)
  buildNavigation(form, conversational)
  submitForm(form)
  initMedia(form)
}

let loadFields = (form, conversational = false) => {
  const fields = form.querySelectorAll(cl('field'))
  const dbg = hasDbg(form)
  fields.forEach((field) => {
    initConditionalField(form, field)
    const inputs = getInput(field, true)
    inputs.forEach((input) => {
      inputFocus(input, field, dbg)
      inputPlaceholder(input, field)
      inputFilled(input, field)
      inputKeypress(input, field, conversational)
      liveUpdate(input)
    })
    fieldMultiple(field)
    fieldNumber(field)
    fieldRating(field)
    fieldSwitch(field)
    fieldTextarea(field)
    fieldUpload(field)
    fieldSelect(field, conversational)
  })
}

document.addEventListener('DOMContentLoaded', () => {
  const forms = document.querySelectorAll(cl('form'))
  forms.forEach((form) => { initForm(form) })
  firstFocus()
  initHints()
  initEmbeds()
  dragNdrop()
})
