import { cl, getElements } from './helpers'
import { initLoader } from './modules/loader'
import { inputFocus, inputPlaceholder, inputFilled } from './modules/ux'
import { hasDbg } from './modules/dbg'

export let initForm = (form) => {
  initLoader(form)
  loadFields(form)
}

export let loadFields = (form) => {
  const fields = getElements(cl('field'), form)
  const dbg = hasDbg(form)
  fields.forEach((field) => {
    const inputs = getElements(cl('input :input'), field)
    inputs.forEach((input) => {
      inputFocus(input, field, dbg)
      inputPlaceholder(input, field)
      inputFilled(input, field)
    })
  })
}
