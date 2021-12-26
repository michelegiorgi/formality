/**
 * Formality single form sidebar functions
 */

import React from 'react'

const {
  __,
  sprintf,
} = wp.i18n

const {
  PanelBody,
  Button,
  BaseControl,
  ToggleControl,
} = wp.components

const {
  Fragment,
} = wp.element

import templates from '../../../images/templates.json'
const formality_templates_url = formality.templates_url
const formality_api_url = formality.api
const formality_api_nonce = formality.nonce
const generalError = __('Something went wrong during the download process. You can retry or check your server logs for more informations about the error.', 'formality')
const sslError = __('SSL verification failed during the download process. These errors most commonly happen on localhost development environments, and/or on servers that do not fully support SSL. If possible, ask your web host to fix this issue ASAP. In the meantime, if you want to complete the download process now, you can temporary disable SSL verification.', 'formality')

//remove editor loading
let hideFormalityLoading = () => {
  let element = document.getElementsByClassName('edit-post-visual-editor')
  if(element.length) { element[0].classList.add('is-loaded') }
}

//update general form options function
let updateFormalityOptions = (name, value, parent) => {
  let value_id = 0
  let key_id = ''
  if(name=='_formality_logo'||name=='_formality_bg') {
    if(value) {
      value_id = value.id
      value = value.sizes.full.url
    }
    key_id = name+'_id'
  }
  let option_array = {}
  option_array[name] = value
  //reset template
  if(name=='_formality_bg') {
    option_array['_formality_template'] = ''
    option_array['_formality_credits'] = ''
    option_array['_formality_position'] = 'center center'
  }
  if(key_id) { option_array[key_id] = value_id }
  parent.setState(option_array, () => {
    wp.data.dispatch('core/editor').editPost({meta: option_array})
    applyFormalityStyles(parent.state)
  })
}

//apply styles to editor
let applyFormalityStyles = (state) => {
  let root = document.documentElement
  let editor = document.getElementsByClassName('edit-post-visual-editor')
  let editor_classes = editor.length ? editor[0].classList : ''
  let credits = state['_formality_custom_credits'] ? state['_formality_custom_credits'] : ''
  let credits_formality = __('Made with Formality', 'formality') + ( state['_formality_template'] ? ' — ' + /* translators: photo author */ sprintf(__('Photo by %s on Unsplash','formality'), state['_formality_credits']) : '')
  if(state['_formality_enable_credits']) { credits = credits ? ( credits + '\\A' + credits_formality ) : credits_formality }
  credits = credits ? '"' + credits + '"' : 'none'
  const stringopacity = state['_formality_overlay_opacity'] ? ( '0.' + ('0' + state['_formality_overlay_opacity']).slice(-2) ) : '0'
  root.style.setProperty('--formality_col1', state['_formality_color1'])
  root.style.setProperty('--formality_col1_alpha', hex2rgb(state['_formality_color1'], '0.3') )
  root.style.setProperty('--formality_col2', state['_formality_color2'])
  root.style.setProperty('--formality_col3', state['_formality_color3'])
  root.style.setProperty('--formality_logo', state['_formality_logo'] ? ( 'url(' + state['_formality_logo'] + ')' ) : 'none' )
  root.style.setProperty('--formality_logo_toggle', state['_formality_logo'] ? 'block' : 'none' )
  root.style.setProperty('--formality_logo_height', ((state['_formality_logo_height'] ? state['_formality_logo_height'] : 3) + 'em' ))
  root.style.setProperty('--formality_fontsize', (state['_formality_fontsize'] + 'px'))
  root.style.setProperty('--formality_bg', state['_formality_bg'] ? ( 'url(' + state['_formality_bg'] + ')' ) : 'none')
  root.style.setProperty('--formality_overlay', stringopacity == '0.00' ? '1' : stringopacity)
  root.style.setProperty('--formality_position', state['_formality_position'])
  root.style.setProperty('--formality_credits', credits)
  root.style.setProperty('--formality_radius', (state['_formality_border_radius'] + 'px'))
  root.style.setProperty('--formality_send_text', state['_formality_send_text'] ? '"' + state['_formality_send_text'] + '"' : '"' + __('Send','formality') + '"' )
  if(editor_classes) {
    const classes = {
      '_formality_type': 'conversational',
      '_formality_style': 'line',
      '_formality_style2': 'fill',
      '_formality_bg_layout': 'side',
    }
    for (const [key, value] of Object.entries(classes)) {
      editor_classes.toggle(value, state[key.replace(/[0-9]/g, '')]==value)
    }
  }
}

//hex to rgb conversion
let hex2rgb = (hexStr, a = 1) => {
  const hex = parseInt(hexStr.substring(1), 16)
  const r = (hex & 0xff0000) >> 16
  const g = (hex & 0x00ff00) >> 8
  const b = hex & 0x0000ff
  const rgba = 'rgba(' + r + ', ' + g + ', ' + b + ', ' + a + ')'
  return rgba
}

//load template
let loadFormalityTemplate = (item, parent) => {
  const entries = Object.entries(item)
  let option_array = {}
  for (let [key, value] of entries) {
    if(key=='name'||key=='description') {
      //exclude these keys
    } else if(key=='template'||key=='overlay_opacity'||key=='credits') {
      option_array[`_formality_${key}`] = value
    } else if(key=='bg') {
      value = (value=='none') ? '' : `${formality_templates_url}/${value}.jpg`
      option_array[`_formality_${key}`] = value
    } else if (value) {
      option_array[`_formality_${key}`] = value
    }
  }
  parent.setState(option_array, () => {
    wp.data.dispatch('core/editor').editPost({meta: option_array})
    applyFormalityStyles(parent.state)
  })
}

//download templates
let downloadFormalityTemplates = (parent, checkonly = false) => {
  parent.setState({'_formality_templates_progress': true })
  let retry = checkonly ? 1 : 3
  //get download progress
  const interval = setInterval(()=> {
    fetch(formality_api_url + 'formality/v1/templates/count/', {
      method: 'GET',
      mode: 'cors',
      cache: 'no-cache',
      headers: {
        'Access-Control-Allow-Origin': '*',
        'X-WP-Nonce': formality_api_nonce,
      },
    }).then(response => {
      const contentType = response.headers.get('content-type')
      if (contentType && contentType.indexOf('application/json') !== -1) {
        return response.json().then(data => {
          const newcount = parseInt(data)
          if(parent.state['_formality_templates_error'] && parent.state['_formality_templates_progress']) {
            if(retry) {
              if(newcount == parent.state['_formality_templates_count']) {
                retry--
              } else {
                retry = 3
              }
            } else {
              clearInterval(interval)
              parent.setState({'_formality_templates_progress': false})
            }
          }
          parent.setState({'_formality_templates_count': newcount })
          window.formality.templates_count = newcount
          const toscroll = document.querySelector('.edit-post-sidebar')
          if(toscroll !== null) { toscroll.scrollTop = toscroll.scrollHeight - toscroll.clientHeight }
          if(newcount == templates.length) {
            clearInterval(interval)
            parent.setState({'_formality_templates_progress': false})
          }
        })
      } else {
        clearInterval(interval)
        parent.setState({'_formality_templates_progress': false})
      }
    })
  }, 3000)
  //start download
  if(!checkonly) {
    const sslData = new FormData()
    sslData.append('disableSSL', parent.state['_formality_ssl_status'] == 2 ? 1 : 0 )
    fetch(formality_api_url + 'formality/v1/templates/download/', {
      method: 'POST',
      mode: 'cors',
      cache: 'no-cache',
      headers: {
        'Access-Control-Allow-Origin': '*',
        'X-WP-Nonce': formality_api_nonce,
      },
      body: sslData,
    }).then(response => {
      const contentType = response.headers.get('content-type')
      if (contentType && contentType.indexOf('application/json') !== -1) {
        return response.json().then(data => {
          clearInterval(interval)
          parent.setState({'_formality_templates_progress': false})
          if(data.hasOwnProperty('status')) {
            parent.setState({'_formality_templates_count': data.count })
            window.formality.templates_count = parseInt(data.count)
            if(data.status==200) {
              //
            } else if(data.status==501) {
              parent.setState({
                '_formality_ssl_status': parent.state['_formality_ssl_status'] == 2 ? 2 : 1,
                '_formality_templates_error': sslError,
              })
            } else {
              parent.setState({'_formality_templates_error': generalError })
            }
          }
        })
      } else {
        parent.setState({'_formality_templates_error': generalError })
      }
    })
  }
}

//build template selection input
let buildFormalityTemplates = (parent) => {
  let nodes = []
  const count = parent.state['_formality_templates_count']
  const progress = parent.state['_formality_templates_progress']
  const error = parent.state['_formality_templates_error']
  const sslStatus = parent.state['_formality_ssl_status']
  const fullLibrary = count == templates.length
  const errorStatus = count && !progress && !fullLibrary
  const button = (
    <Button
      isPrimary
      isBusy={ progress }
      disabled={ progress }
      onClick={() => downloadFormalityTemplates(parent)}
    >{ __('Download templates photos', 'formality') }</Button>
  )
  const introMessage = count && !progress ?
    __( 'Select one of our templates made with a selection of the best Unsplash photos.', 'formality' ) :
    sprintf( /* translators: templates total */ __( 'We have prepared %s templates made with a selection of the best Unsplash photos.', 'formality' ), templates.length)
  const introMessage2 = !count ? ' ' + __( 'To start using them, you first have to download these photos from Unsplash servers.', 'formality' ) : ''
  const errorMessage = error && !progress ? error : __( 'It seems your template library is incomplete. To fix this issue, you can retry the download process.', 'formality' )
  const disableSsl = (
    <ToggleControl
      label={ __('Disable SSL verification for unsplash.com domain until the download process finishes.', 'formality') }
      checked={ sslStatus == 2 }
      onChange={() => parent.setState({ '_formality_ssl_status': sslStatus == 2 ? 1 : 2 })}
    />
  )
  if(!fullLibrary){
    if(sslStatus) { nodes.push(disableSsl) }
    nodes.push(button)
  }
  if(count) {
    let options = []
    templates.forEach((item, index) => {
      if(index < count) {
        if(item.template == parent.state['_formality_template']) {
          window.dispatchEvent(new Event('resize'))
        }
        const thumb = `${formality_templates_url}/${item.bg}_thumb.jpg`
        const option = (
          <div
            className='components-radio-control__option'
          >
            <input
              className='components-radio-control__input'
              type='radio'
              name='formality_radio_templates'
              id={ 'formality_radio_templates_' + index }
              value={ item.template }
              onChange={ () => loadFormalityTemplate(item, parent) }
              checked={ item.template == parent.state['_formality_template']  }
            />
            <label
              htmlFor={ 'formality_radio_templates_' + index }
              style={{
                backgroundImage: (item.bg && item.bg != 'none') ? `url(${thumb})` : '',
                color: item.color1,
                backgroundColor: item.color2,
              }}
            >
              <strong>{ item.name }</strong>
              <i style={{
                opacity: ('0.' + ('0' + item.overlay_opacity).slice(-2)),
                backgroundColor: item.color2,
              }}></i>
            </label>
          </div>
        )
        options.push(option)
      }
    })
    nodes.push(options)
  }
  const panel = (
    <PanelBody
      className='formality_radio-templates'
      title={(<Fragment>{__('Templates', 'formality')}<span className='counter'>{ count + '/' + templates.length }</span></Fragment>)}
      initialOpen={ false }
    >
      <BaseControl>
        <label
          className='components-base-control__label'
        >
          { !errorStatus ? introMessage.split('Unsplash')[0] : errorMessage }
          { !errorStatus ? ( <a target='_blank' rel='noopener noreferrer' href='https://unsplash.com'>Unsplash</a> ) : '' }
          { !errorStatus ? ( introMessage.split('Unsplash')[1] + introMessage2 ) : '' }
        </label>
        {(nodes)}
        <div className='terms'>
          <a target='_blank' rel='noopener noreferrer' href='https://unsplash.com/terms'>{ __( 'Terms and conditions', 'formality' ) }</a>{ ' ' }
          <a target='_blank' rel='noopener noreferrer' href='https://unsplash.com/license'>{ __( 'License', 'formality' ) }</a>
        </div>
      </BaseControl>
    </PanelBody>
  )
  if(count > 2 && !fullLibrary && !progress) { downloadFormalityTemplates(parent, true) }
  return panel
}

//export all
export {
  hideFormalityLoading,
  updateFormalityOptions,
  applyFormalityStyles,
  hex2rgb,
  loadFormalityTemplate,
  buildFormalityTemplates,
}
