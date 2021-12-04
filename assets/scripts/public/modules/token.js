import { el, handleFetch } from '../helpers'

export let requestToken = (fieldOrForm, callbackSuccess, callbackError) => {
  if(fieldOrForm.classList.contains(el('form'))) {
    if(fieldOrForm.classList.contains(el('form', '', 'loading'))) { return }
    fieldOrForm.classList.add(el('form', '', 'loading'))
  }
  fetch(window.formality.api + 'formality/v1/token/', {
    method: 'POST',
    mode: 'cors',
    cache: 'no-cache',
    credentials: 'same-origin',
    body: JSON.stringify({ nonce: window.formality.action_nonce }),
    headers: new Headers({
      'Content-Type': 'application/json;charset=UTF-8',
      'X-WP-Nonce' : window.formality.login_nonce
    })
  }).then(handleFetch).then((response) => {
    if(response.status == 200) {
      callbackSuccess(fieldOrForm, response.token)
    } else {
      callbackError(fieldOrForm, response)
    }
  }).catch((error) => {
    callbackError(fieldOrForm, { status: 400 })
  })
}
