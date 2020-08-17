import React from 'react'

const { 
  __,
  sprintf,
} = wp.i18n;

const { 
  PanelBody,
  Button,
  BaseControl,
} = wp.components;

const {
  Fragment,
} = wp.element;

import templates from '../../../images/templates.json'
const formality_templates_url = formality.templates_url

//remove editor         
  let hideFormalityLoading = () => {
    let element = document.getElementsByClassName("edit-post-visual-editor");
    element[0].classList.add("is-loaded");
  }
  
//update general form options function
  let updateFormalityOptions = (name, value, parent) => {
    let value_id = 0;
    let key_id = "";
    if(name=="_formality_logo"||name=="_formality_bg") {
      if(value) {
        value_id = value.id;
        value = value.sizes.full.url
      }
      key_id = name+"_id";
    }
    let option_array = {}
    option_array[name] = value;
    //reset template
    if(name=="_formality_bg") {
      option_array['_formality_template'] = '';
      option_array['_formality_credits'] = '';
      option_array['_formality_position'] = 'center center';
    }
    if(key_id) { option_array[key_id] = value_id; }
    parent.setState(option_array, () => {
      wp.data.dispatch('core/editor').editPost({meta: option_array})
      applyFormalityStyles(parent.state)
    });
  }
  
//apply styles to editor
  let applyFormalityStyles = (state) => {
    let root = document.documentElement
    let editor_classes = document.getElementsByClassName("edit-post-visual-editor")[0].classList
    let credits = state['_formality_custom_credits'] ? state['_formality_custom_credits'] : ''
    let credits_formality = __('Made with Formality', 'formality') + ( state['_formality_template'] ? ' — ' + __('Photo by','formality') + ' ' + state['_formality_credits'] + ' ' + __('on Unsplash','formality') : '')
    if(state['_formality_enable_credits']) { credits = credits ? ( credits + '\\A' + credits_formality ) : credits_formality }
    credits = credits ? '"' + credits + '"' : 'none'
    const stringopacity = state['_formality_overlay_opacity'] ? ( "0." + ("0" + state['_formality_overlay_opacity']).slice(-2) ) : "0"
    root.style.setProperty('--formality_col1', state['_formality_color1'])
    root.style.setProperty('--formality_col1_alpha', hex2rgb(state['_formality_color1'], "0.3") )
    root.style.setProperty('--formality_col2', state['_formality_color2'])
    root.style.setProperty('--formality_col3', state['_formality_color3'])
    root.style.setProperty('--formality_logo', state['_formality_logo'] ? ( "url(" + state['_formality_logo'] + ")" ) : "none" )
    root.style.setProperty('--formality_logo_toggle', state['_formality_logo'] ? "block" : "none" )
    root.style.setProperty('--formality_logo_height', ((state['_formality_logo_height'] ? state['_formality_logo_height'] : 3) + "em" ))
    root.style.setProperty('--formality_fontsize', (state['_formality_fontsize'] + "px"))
    root.style.setProperty('--formality_bg', state['_formality_bg'] ? ( "url(" + state['_formality_bg'] + ")" ) : "none")
    root.style.setProperty('--formality_overlay', stringopacity == "0.00" ? "1" : stringopacity)
    root.style.setProperty('--formality_position', state['_formality_position'])
    root.style.setProperty('--formality_credits', credits)
    root.style.setProperty('--formality_send_text', state['_formality_send_text'] ? '"' + state['_formality_send_text'] + '"' : '"' + __('Send','formality') + '"' )
    const classes = {
      '_formality_type': 'conversational',
      '_formality_style': 'line',
      '_formality_bg_layout': 'side',
    }
    for (const [key, value] of Object.entries(classes)) {
      if(state[key]==value) { editor_classes.add(value) } else { editor_classes.remove(value) }
    }
  }
  
  //hex to rgb conversion
  let hex2rgb = (hexStr, a = 1) => {
    const hex = parseInt(hexStr.substring(1), 16);
    const r = (hex & 0xff0000) >> 16;
    const g = (hex & 0x00ff00) >> 8;
    const b = hex & 0x0000ff;
    const rgba = "rgba(" + r + ", " + g + ", " + b + ", " + a + ")" 
    return rgba;
  }
  
  //load template
  let loadFormalityTemplate = (item, parent) => {
    const entries = Object.entries(item)
    let option_array = {}
    for (let [key, value] of entries) {
      if(key=="name"||key=="description") {
        //exclude these keys
      } else if(key=="template"||key=="overlay_opacity"||key=="credits") {
        option_array[`_formality_${key}`] = value
      } else if(key=="bg") {
        value = (value=="none") ? "" : `${formality_templates_url}/${value}.jpg`;
        option_array[`_formality_${key}`] = value
      } else if (value) {
        option_array[`_formality_${key}`] = value
      }
    }
    parent.setState(option_array, () => {
      wp.data.dispatch('core/editor').editPost({meta: option_array})
      applyFormalityStyles(parent.state)
    });
  }
  
  //build template selection input
  let buildFormalityTemplates = (parent) => {
    const count = parent.state['_formality_templates_count']
    let nodes = []
    const button = (
      <Button
        isPrimary
        isBusy={ parent.state['_formality_templates_progress'] }
        disabled={ parent.state['_formality_templates_progress'] }
        onClick={
          () => {
            parent.setState({'_formality_templates_progress': true })
            let interval = setInterval(()=> {
              fetch('http://formality.local/wp-json/formality/v1/templates/count/').then(response => {
                const contentType = response.headers.get("content-type")
                if (contentType && contentType.indexOf("application/json") !== -1) {
                  return response.json().then(data => {
                    parent.setState({'_formality_templates_count': data })
                  })
                } else {
                  clearInterval(interval)
                }
              })
            }, 3000);
            fetch('http://formality.local/wp-json/formality/v1/templates/download/').then(response => {
              clearInterval(interval)
              parent.setState({'_formality_templates_progress': false})
              const contentType = response.headers.get("content-type")
              if (contentType && contentType.indexOf("application/json") !== -1) {
                return response.json().then(data => {
                  if(data.status==200) {
                    parent.setState({'_formality_templates_count': data.count })
                  }
                })
              }
            })
          }
        }
      >{ __('Download template photos', 'formality') }</Button>
    )
    if((!count) || parent.state['_formality_templates_progress']){ nodes.push(button) }    
    if(count) {
      let options = []
      templates.forEach(function (item, index) {
        if(index < count) {
          const thumb = `${formality_templates_url}/${item.bg}_thumb.jpg`;
          const option = (
            <div
              className="components-radio-control__option"
            >
              <input
                className="components-radio-control__input"
                type="radio"
                name="formality_radio_templates"
                id={ "formality_radio_templates_" + index }
                value={ item.template }
                onChange={ () => loadFormalityTemplate(item, parent) }
                checked={ item.template == parent.state['_formality_template']  }
              />
              <label
                htmlFor={ "formality_radio_templates_" + index }
                style={{
                  backgroundImage: (item.bg && item.bg != "none") ? `url(${thumb})` : "",
                  color: item.color1,
                  backgroundColor: item.color2,
                }}
              >
                <strong>{ item.name }</strong>
                <i style={{
                  opacity: ("0." + ("0" + item.overlay_opacity).slice(-2)),
                  backgroundColor: item.color2,
                }}></i>
              </label>
            </div>
          )
          options.push(option)
        }
      });
      nodes.push(options)
    }
    if(count && !parent.state['_formality_templates_progress'] && parseInt(parent.state['_formality_templates_count']) !== templates.length) {
      const message = (<label className="components-base-control__label incomplete">{ __( "It seems your template library is incomplete. To fix this issue, you can retry the download process.", 'formality' ) }</label>)
      nodes.push(message)
      nodes.push(button)
    }
    const panel = (
      <PanelBody
        className="formality_radio-templates"
        title={(<Fragment>{__('Templates', 'formality')}<span className="counter">{ parent.state['_formality_templates_count'] + '/' + templates.length }</span></Fragment>)}
        initialOpen={ false }
      >
        <BaseControl>
          <label className="components-base-control__label">
            { count && !parent.state['_formality_templates_progress'] ? __( 'Select one of our templates made with a selection of the best', 'formality' ) : sprintf( __( 'We have prepared %s templates made with a selection of the best', 'formality' ), templates.length) }
            { ' ' }<a target="_blank" rel="noopener noreferrer" href="https://unsplash.com">Unsplash</a>{ ' ' + __( 'photos.', 'formality' ) + ' ' }
            { !count ? __( 'To start using them, you first have to download these photos from Unsplash servers.', 'formality' ) : '' }
          </label>
          {(nodes)}
          <div className="terms">
            <a target="_blank" rel="noopener noreferrer" href="https://unsplash.com/terms">Terms and conditions</a>{ ' ' }
            <a target="_blank" rel="noopener noreferrer" href="https://unsplash.com/license">License</a>
          </div>
        </BaseControl>
      </PanelBody>
    )
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
