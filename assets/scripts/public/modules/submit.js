import { el, cl, getUID, getInput, animateScroll, pushEvent } from '../helpers'
import { validateForm } from './validation'
const { __, sprintf } = wp.i18n

/* eslint-disable no-unused-vars */

export let submitForm = (form) => {
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    if(!validateForm(form)) { return false; }
    requestToken(form)
    pushEvent('FormSubmit', form)
  })
}

export let requestToken = (form, callbackSuccess=sendData, callbackError=printResult) => {
  if(form.classList.contains(cl('form', '', 'loading'))) { return }
  form.classList.add(el('form', '', 'loading'))
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
  }).then(handleErrors).then((response) => {
    if(response.status == 200) {
      callbackSuccess(form, response.token)
    } else {
      callbackError(form, response)
    }
  }).catch((error) => {
    callbackError(form, { status: 400 })
  })
}

export let sendData = (form, token) => {
  var fulldata = new FormData()
  fulldata.append('action', 'formality_send')
  fulldata.append('token', token)
  fulldata.append('id', form.getAttribute('data-id'))
  const inputs = getInput(form, true)
  inputs.forEach((input) => {
    if(['checkbox', 'radio'].indexOf(input.type) >-1 && !input.checked) return
    const value = input.type == 'file' ? input.getAttribute('data-file') : input.value
    fulldata.append('field_' + input.name, value)
  })
  fetch(window.formality.api + 'formality/v1/send/', {
    method: 'POST',
    mode: 'cors',
    cache: 'no-cache',
    credentials: 'same-origin',
    body: fulldata,
    headers: new Headers({ 'X-WP-Nonce' : window.formality.login_nonce })
  }).then(handleErrors).then((response) => {
    printResult(form, response)
  }).catch((error) => {
    printResult(form, {
      status: 400,
      error: ('responseText' in error) ? error.responseText : error
    })
  })
}

export let handleErrors = (response) => {
  if (!response.ok) { throw Error(response.statusText); }
  return response.json();
}

export let printResult = (form, response) => {
  const success = response.status == 200
  const result = form.querySelector(cl('result'))
  result.classList.add(el('result', '', 'visible'))
  form.classList.remove(el('form', '', 'loading'))
  document.activeElement.blur()
  form.classList.add(el('form', '', success ? 'sended' : 'error'))
  pushEvent(success ? 'FormSuccess' : 'FormError', { data: result, form: form })
  if(success) {
    const prevBtn = form.querySelector(cl('btn','','prev'))
    prevBtn.style.display = 'none'
  }
  const resultSuccess = form.querySelector(cl('result', 'success'))
  const resultError = form.querySelector(cl('result', 'error'))
  const actions = form.querySelector(cl('actions'))
  resultSuccess.classList.toggle('active', success)
  resultError.classList.toggle('active', !success)
  animateScroll(actions.offsetTop, 300)
}
