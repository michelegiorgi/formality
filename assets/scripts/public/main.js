import { cl, isConversational, getInput } from './helpers'
import { initLoader } from './modules/loader'
import { inputFocus, inputPlaceholder, inputFilled, inputKeypress } from './modules/fields'
import { hasDbg } from './modules/dbg'
import { buildNavigation } from './modules/navigation'
import { liveUpdate, addStepIndexes } from './modules/validation'
import { submitForm } from './modules/submit'
import { initConditionalField } from './modules/conditional'
import { initMedia } from './fields/media'
import { fieldMultiple } from './fields/multiple'

export let initForm = (form) => {
  const conversational = isConversational(form)
  initLoader(form)
  addStepIndexes(form)
  loadFields(form, conversational)
  loadSections(form, conversational)
  submitForm(form)
  initMedia(form)
}

export let loadFields = (form, conversational = false) => {
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
  })
}

export let loadSections = (form, conversational = false) => {
  const sections = form.querySelectorAll(cl('section'))
  buildNavigation(form, sections, conversational)
}
