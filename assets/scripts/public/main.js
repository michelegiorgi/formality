import { cl, isConversational } from './helpers'
import { initLoader } from './modules/loader'
import { inputFocus, inputPlaceholder, inputFilled, inputKeypress } from './modules/fields'
import { hasDbg } from './modules/dbg'
import { buildNavigation } from './modules/navigation'

export let initForm = (form) => {
  const conversational = isConversational(form)
  initLoader(form)
  loadFields(form, conversational)
  loadSections(form, conversational)
}

export let loadFields = (form, conversational = false) => {
  const fields = form.querySelectorAll(cl('field'))
  const dbg = hasDbg(form)
  fields.forEach((field) => {
    const inputs = field.querySelectorAll(cl('input :input'))
    inputs.forEach((input) => {
      inputFocus(input, field, dbg)
      inputPlaceholder(input, field)
      inputFilled(input, field)
      inputKeypress(input, field, conversational)
    })
  })
}

export let loadSections = (form, conversational = false) => {
  const sections = form.querySelectorAll(cl('section'))
  buildNavigation(form, sections, conversational)
}
