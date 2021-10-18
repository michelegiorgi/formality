import { el, getElements } from './helpers'
import { inputFocus } from './modules/ux'

export let loadFields = (form) => {
  const fields = getElements(el('field'), form)
  //const hasDbg = getElements("[data-dbg-image], [data-dbg-color]", form)
  fields.forEach((field) => {
    console.log(el('input :input'))
    const inputs = getElements(el('input :input'), field)
    inputs.forEach((input) => {
      console.log(input)
    })
  })
}
