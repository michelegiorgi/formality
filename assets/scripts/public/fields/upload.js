import { el, cl, handleFetch } from '../modules/helpers'
import { validateField } from '../modules/validation'
import { requestToken } from '../modules/token'
const { __ } = wp.i18n

export const fieldUpload = (field) => {
  if(!field.classList.contains(el('field', '', 'upload'))) return
  const labels = field.querySelectorAll('label')
  const input = field.querySelector('input')
  labels.forEach((label) => {
    label.addEventListener('click', () => {
      field.classList.remove(el('field', '', 'error'))
      if(label.classList.contains(el('upload'))) input.focus()
    })
  })
  input.addEventListener('change', () => {
    let errors = []
    const file = input.files.length ? input.files[0] : false
    field.classList.remove(el('field', '', 'uploaded'))
    if(validateField(field) && file) {
      let fileinfo = field.querySelector(cl('upload', 'info'))
      fileinfo.innerHTML = `<i></i><span><strong>${ __('Checking file', 'formality') }</strong>${ __('Please wait', 'formality') }</span>`
      var reader = new FileReader()
      const previewFormats = ['jpeg', 'jpg', 'png', 'gif', 'svg', 'webp']
      reader.fileName = file.name
      reader.fileSize = file.size
      reader.fileFormat = file.name.split('.').pop().toLowerCase()
      reader.onload = (e) => {
        fileinfo.innerHTML = `<i${ previewFormats.indexOf(e.target.fileFormat) !== -1 ? ' style="background-image:url('+e.target.result+')"' : '' }></i><span><strong>${e.target.fileName}</strong>${formatBytes(e.target.fileSize)}</span><a class="formality__upload__remove" href="#"></a>`
        const removeButton = field.querySelector(cl('upload', 'remove'))
        removeButton.addEventListener('click', (e) => {
          e.preventDefault()
          input.value = ''
          input.dispatchEvent(new Event('change'))
        })
        requestToken(field, uploadFile, uploadResult)
      }
      reader.readAsDataURL(file)
    } else {
      input.value = ''
      input.focus()
      field.classList.remove(el('field', '', 'filled'))
    }
  })
  input.addEventListener('blur', () => {
    field.classList.remove(el('field', '', 'error'))
  })
  input.addEventListener('keydown', (e) => {
    if(input.value && e.key == 'Backspace') {
      input.value = ''
      input.dispatchEvent(new Event('change'))
    }
  })
  dragNdrop(field, input)
}

export const formatBytes = (a, b) => {
  if(0==a) return "0 Bytes"
  var c=1024,d=b||2,e=["Bytes","KB","MB","GB"],f=Math.floor(Math.log(a)/Math.log(c));
  return parseFloat((a/Math.pow(c,f)).toFixed(d))+" "+e[f]
}

export const uploadResult = (field, data) => {
  const input = field.querySelector('input')
  if(data.status == 200 && data.field){
    input.setAttribute('data-file', data.file)
    field.classList.add(el('field', '', 'uploaded'))
  } else {
    input.value = ''
    field.classList.remove(el('field', '', 'filled'))
    field.classList.add(el('field', '', 'error'))
    let inputStatus = field.querySelector(cl('input', 'status'))
    inputStatus.innerHTML = `<div class="formality__input__errors">${ data.error }</ul>`
  }
}

export const uploadFile = (field, token) => {
  let fulldata = new FormData()
  const form = field.closest(cl('form'))
  const input = field.querySelector('input')
  const oldfile = input.getAttribute('data-file')
  fulldata.append('action', 'formality_upload')
  fulldata.append('nonce', window.formality.action_nonce)
  fulldata.append('token', token)
  fulldata.append('id', form.getAttribute('data-id'))
  fulldata.append('field', input.id)
  fulldata.append('field_' + input.id, input.files[0])
  if(oldfile) { fulldata.append('old', oldfile) }
  fetch(window.formality.api + 'formality/v1/upload/', {
    method: 'POST',
    mode: 'cors',
    cache: 'no-cache',
    credentials: 'same-origin',
    body: fulldata,
    headers: new Headers({ 'X-WP-Nonce' : window.formality.login_nonce })
  }).then(handleFetch).then((response) => {
    uploadResult(field, response)
  }).catch((error) => {
    uploadResult(field, {
      status: 400,
      debug: ('responseText' in error) ? error.responseText : error,
      error: 'Internal server error',
      field: input.id
    })
  })
}

export const dragNdrop = (field = false, input = false) => {
  if(field && input) {
    input.addEventListener('dragenter', () => {
      input.focus()
      field.classList.add(el('field', '', 'dragging'))
    })
    input.addEventListener('dragleave', () => {
      input.blur()
      field.classList.remove(el('field', '', 'dragging'))
    })
  } else {
    let drag_timer
    document.addEventListener('dragover', (e) => {
      let dt = e.dataTransfer;
      if(dt.types && (dt.types.indexOf ? dt.types.indexOf('Files') != -1 : dt.types.contains('Files'))){
        const forms = document.querySelectorAll(cl('form'))
        forms.forEach((form) => {
          form.classList.add(el('form', '', 'dragging'))
          const uploadErrors = form.querySelectorAll(cl('field', '', 'upload') + cl('field', '', 'error'))
          uploadErrors.forEach((uploadError) => {
            uploadError.classList.remove(el('field', '', 'error'))
          })
        })
        clearTimeout(drag_timer)
        drag_timer = setTimeout(() => {
          forms.forEach((form) => {
            form.classList.remove(el('form', '', 'dragging'))
          })
        }, 200)
      }
    })
  }
}
