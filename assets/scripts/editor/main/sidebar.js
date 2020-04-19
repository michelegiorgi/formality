/**
 * Internal block libraries
 */

import React from 'react'

const { __ } = wp.i18n;

const {
  //PluginSidebar,
  //PluginSidebarMoreMenuItem,
  PluginDocumentSettingPanel,
} = wp.editPost;

const { registerPlugin } = wp.plugins;

const { 
  ColorPicker,
  Panel,
  PanelBody,
  PanelRow,
  Button,
  TextControl,
  TextareaControl,
  ToggleControl,
  ButtonGroup,
  BaseControl,
  Dropdown,
  RangeControl,
  ClipboardButton,
  TabPanel,
} = wp.components;

const { 
  MediaUpload,
} = wp.blockEditor;

const {
  Component,
  Fragment,
} = wp.element;

import templates from './templates.js'

//const { withSelect } = wp.data;
//const { compose } = wp.compose;

class Formality_Sidebar extends Component {
  
  constructor() {
    super( ...arguments );
    
    //get post metas
    let formality_keys = wp.data.select('core/editor').getEditedPostAttribute('meta')
    const formality_pluginurl = formality.plugin_url

    //define default values    
    let default_keys = {
      '_formality_type': "standard",
      '_formality_style': "box",
      '_formality_color1': "#000000",
      '_formality_color2': "#ffffff",
      '_formality_fontsize': 20,
      '_formality_logo': '',
      '_formality_logo_id': 0,
      '_formality_logo_height': 3,
      '_formality_bg': '',
      '_formality_bg_id': 0,
      '_formality_overlay_opacity': 80,
      '_formality_template': '',
      '_formality_position': 'center center',
      '_formality_credits': '',
      '_formality_enable_credits': 0,
      '_formality_custom_credits': '',
      '_formality_credits_url': '',
      '_formality_thankyou': '',
      '_formality_thankyou_message': '',
      '_formality_error': '',
      '_formality_error_message': '',
      '_formality_email': '',
      '_formality_send_text' : '',
    }
    
    //check if formality keys are already defined
    if(!formality_keys) {
      formality_keys = default_keys
      wp.data.dispatch('core/editor').editPost({meta: formality_keys})
    } else if("_formality_type" in formality_keys) {
      if(!formality_keys["_formality_type"]) {
        formality_keys = default_keys
        wp.data.dispatch('core/editor').editPost({meta: formality_keys})
      }
    }
    
    //remove editor         
    this.hideFormalityLoading = function() {
      let element = document.getElementsByClassName("edit-post-visual-editor");
      element[0].classList.add("is-loaded");
    }
    
    //update general form options function
    this.updateFormalityOptions = function(name, value) {
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
      this.setState(option_array, () => {
        wp.data.dispatch('core/editor').editPost({meta: option_array})
        this.applyFormalityStyles()
      });
    }
    
    //apply styles to editor
    this.applyFormalityStyles = function() {
      let root = document.documentElement;
      let element = document.getElementsByClassName("edit-post-visual-editor");
      let credits = this.state['_formality_custom_credits'] ? this.state['_formality_custom_credits'] : ''
      let credits_formality = __('Made with Formality', 'formality') + ( this.state['_formality_template'] ? ' — ' + __('Photo by','formality') + ' ' + this.state['_formality_credits'] + ' ' + __('on Unsplash','formality') : '');
      if(this.state['_formality_enable_credits']) { credits = credits ? ( credits + '\\A' + credits_formality ) : credits_formality; }
      credits = credits ? '"' + credits + '"' : 'none';
      root.style.setProperty('--formality_col1', this.state['_formality_color1']);
      root.style.setProperty('--formality_col1_alpha', this.hex2rgb(this.state['_formality_color1'], "0.3") );
      root.style.setProperty('--formality_col2', this.state['_formality_color2']);
      root.style.setProperty('--formality_logo', this.state['_formality_logo'] ? ( "url(" + this.state['_formality_logo'] + ")" ) : "none" );
      root.style.setProperty('--formality_logo_toggle', this.state['_formality_logo'] ? "block" : "none" );
      root.style.setProperty('--formality_logo_height', ((this.state['_formality_logo_height'] ? this.state['_formality_logo_height'] : 3) + "em" ));
      root.style.setProperty('--formality_fontsize', (this.state['_formality_fontsize'] + "px"));
      root.style.setProperty('--formality_bg', this.state['_formality_bg'] ? ( "url(" + this.state['_formality_bg'] + ")" ) : "none");
      root.style.setProperty('--formality_overlay', this.state['_formality_overlay_opacity'] ? ( "0." + ("0" + this.state['_formality_overlay_opacity']).slice(-2) ) : "0");
      root.style.setProperty('--formality_position', this.state['_formality_position']);
      root.style.setProperty('--formality_credits', credits);
      root.style.setProperty('--formality_send_text', this.state['_formality_send_text'] ? '"' + this.state['_formality_send_text'] + '"' : '"' + __('Send','formality') + '"' );    
      if(this.state['_formality_type']=="conversational") {
        element[0].classList.add("conversational");
      } else {
        element[0].classList.remove("conversational");
      }
      if(this.state['_formality_style']=="line") {
        element[0].classList.add("line");
      } else {
        element[0].classList.remove("line");
      }
    }
    
    this.hex2rgb = function(hexStr, a = 1){
      const hex = parseInt(hexStr.substring(1), 16);
      const r = (hex & 0xff0000) >> 16;
      const g = (hex & 0x00ff00) >> 8;
      const b = hex & 0x0000ff;
      const rgba = "rgba(" + r + ", " + g + ", " + b + ", " + a + ")" 
      return rgba;
    }
    
    //load template
    this.loadFormalityTemplate = function(item) {
      const entries = Object.entries(item)
      let option_array = {}
      for (let [key, value] of entries) {
        if(key=="name"||key=="description") {
          //exclude these keys
        } else if(key=="template"||key=="overlay_opacity"||key=="credits") {
          option_array[`_formality_${key}`] = value
        } else if(key=="bg") {
          value = (value=="none") ? "" : (formality_pluginurl + 'public/templates/images/bg/' + value);
          option_array[`_formality_${key}`] = value
        } else if (value) {
          option_array[`_formality_${key}`] = value
        }
      }
      this.setState(option_array, () => {
        wp.data.dispatch('core/editor').editPost({meta: option_array})
        this.applyFormalityStyles()
      });
    }
    
    //build template selection input
    this.buildFormalityTemplates = function() {
      let parent = this; 
      let options = []      
      templates.forEach(function (item, index) {
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
              onChange={ () => parent.loadFormalityTemplate(item) }
              checked={ item.template == parent.state['_formality_template']  }
            />
            <label
              htmlFor={ "formality_radio_templates_" + index }
              style={{
                backgroundImage: (item.bg && item.bg != "none") ? ("url(" + formality_pluginurl + "public/templates/images/thumb/" + item.bg + ")") : "",
                color: item.color1,
                backgroundColor: item.color2,
              }}
            >
              <strong>{ item.name }</strong>
              <span>{ item.description }</span>
              <i style={{
                opacity: ("0." + ("0" + item.overlay_opacity).slice(-2)),
                backgroundColor: item.color2,
              }}></i>
            </label>
          </div>
        )
        options.push(option)
      });
      return options
    }
    
    //set state and remove loading layer
    this.state = formality_keys
    this.applyFormalityStyles()
    this.hideFormalityLoading()

  }

  render() {

    const postId = wp.data.select("core/editor").getCurrentPostId();
    const postPermalink = wp.data.select('core/editor').getPermalink();
    
    let tabAppearance = (
      <Fragment>
        <div
          className={"components-panel__body is-opened"}
        >
          <BaseControl
            label={__("Form type")}
            //help={ this.state['_formality_type']=="standard" ? 'Classic layout form' : 'Distraction free form' }
          >
            <ButtonGroup>
              <Button
                isPrimary={ this.state['_formality_type']=="standard" ? true : false }
                isDefault={ this.state['_formality_type']=="standard" ? false : true }
                onClick={() => this.updateFormalityOptions('_formality_type', 'standard')}
              >Standard</Button>
              <Button
                isPrimary={ this.state['_formality_type']=="conversational" ? true : false }
                isDefault={ this.state['_formality_type']=="conversational" ? false : true }
                onClick={() => this.updateFormalityOptions('_formality_type', 'conversational')}
              >Conversational</Button>
            </ButtonGroup>
          </BaseControl>
          <PanelRow
              className="formality_colorpicker"
            >
            <BaseControl
              label={ __("Primary color", "formality") }
              help={ __("Texts, Labels, Borders, etc.", "formality") }
              >
              <Dropdown
                className="components-color-palette__item-wrapper components-color-palette__custom-color components-circular-option-picker__option-wrapper"
                contentClassName="components-color-palette__picker"
                renderToggle={ ( { isOpen, onToggle } ) => (
                  <button
                    type="button"
                    style={{ background: this.state['_formality_color1'] }}
                    aria-expanded={ isOpen }
                    className="components-color-palette__item components-circular-option-picker__option"
                    onClick={ onToggle }
                  ></button>
                ) }
                renderContent={ () => (
                  <ColorPicker
                    color={ this.state['_formality_color1'] }
                    onChangeComplete={(value) => this.updateFormalityOptions('_formality_color1', value.hex)}
                    disableAlpha
                  />
                ) }
              />
            </BaseControl>
            <BaseControl
              label={ __("Secondary color", "formality") }
              help={ __("Backgrounds, Input suggestions, etc.", "formality") }
            >
              <Dropdown
                className="components-color-palette__item-wrapper components-color-palette__custom-color components-circular-option-picker__option-wrapper"
                contentClassName="components-color-palette__picker"
                renderToggle={ ( { isOpen, onToggle } ) => (
                  <button
                    type="button"
                    style={{ background: this.state['_formality_color2'] }}
                    aria-expanded={ isOpen }
                    className="components-color-palette__item components-circular-option-picker__option"
                    onClick={ onToggle }
                  ></button>
                ) }
                renderContent={ () => (
                  <ColorPicker
                    color={ this.state['_formality_color2'] }
                    onChangeComplete={(value) => this.updateFormalityOptions('_formality_color2', value.hex)}
                    disableAlpha
                  />
                ) }
              />
            </BaseControl>
          </PanelRow>
          <PanelRow
            className="formality_fontsize"
          >
            <BaseControl
              label={ __( 'Font size', 'formality' ) }
              help={ __( "Align this value to your theme's fontsize", 'formality' ) }
            >
              <RangeControl
                value={ this.state['_formality_fontsize'] }
                onChange={ ( newFontSize ) => this.updateFormalityOptions('_formality_fontsize', newFontSize) }
                min={ 16 }
                max={ 24 }
                beforeIcon="editor-textcolor"
                afterIcon="editor-textcolor"
              />
            </BaseControl>
          </PanelRow>
        </div>
        <PanelBody
          title={__('Advanced', 'formality')}
          initialOpen={ false }
        >
          <BaseControl
            label={ __( 'Logo', 'formality' ) }
            help={ __( "Set a custom logo", 'formality' ) }
          >
            <MediaUpload
              onSelect={(file) => this.updateFormalityOptions('_formality_logo', file)}
              type="image"
              value={ this.state['_formality_logo_id'] }
              render={({ open }) => (
                <Fragment>
                  <Button
                    className={ this.state['_formality_logo'] ? 'editor-post-featured-image__preview' : 'editor-post-featured-image__toggle' }
                    onClick={ open }
                    aria-label={ ! this.state['_formality_logo'] ? null : __( 'Edit or update logo', 'formality' ) }>
                    { this.state['_formality_logo'] ? <img src={ this.state['_formality_logo'] } alt="" /> : ''}
                    { this.state['_formality_logo'] ? '' : __('Upload logo image', 'formality' ) }
                  </Button>
                  { this.state['_formality_logo'] ? <Button onClick={() => this.updateFormalityOptions('_formality_logo', '')} isLink isDestructive>{ __('Remove custom logo', 'formality' )}</Button> : ''}
                </Fragment>
              )}
            />
          </BaseControl>
          { this.state['_formality_logo'] ? 
            <BaseControl
              label={ __( 'Logo height multiplier', 'formality' ) }
              help={ __( "Based on font-size setting:", 'formality' ) + " " + ((this.state['_formality_logo_height'] ? this.state['_formality_logo_height'] : 3) * this.state['_formality_fontsize']) + "px" }
            >
              <RangeControl
                value={ this.state['_formality_logo_height'] ? this.state['_formality_logo_height'] : 3 }
                onChange={( newHeight ) => this.updateFormalityOptions('_formality_logo_height', newHeight)}
                min={ 2 }
                max={ 10 }
              />
            </BaseControl> : ''
          }
          <BaseControl
            label={ __( 'Background image', 'formality' ) }
            help={ __( "Add background image", 'formality' ) }
          >
            <MediaUpload
              onSelect={(file) => this.updateFormalityOptions('_formality_bg', file)}
              type="image"
              value={ this.state['_formality_bg_id'] }
              render={({ open }) => (
                <Fragment>
                  <Button
                    className={ this.state['_formality_bg'] ? 'editor-post-featured-image__preview' : 'editor-post-featured-image__toggle' }
                    onClick={ open }
                    aria-label={ ! this.state['_formality_bg'] ? null : __( 'Edit or update the image', 'formality' ) }>
                    { this.state['_formality_bg'] ? <img src={ this.state['_formality_bg'] } alt="" /> : ''}
                    { this.state['_formality_bg'] ? '' : __('Set a background image', 'formality' ) }
                  </Button>
                  { this.state['_formality_bg'] ? <Button onClick={() => this.updateFormalityOptions('_formality_bg', '')} isLink isDestructive>{ __('Remove background image', 'formality' )}</Button> : ''}
                </Fragment>
              )}
            />
          </BaseControl>
          { this.state['_formality_bg'] ? 
            <BaseControl
              label={ __( 'Background overlay', 'formality' ) }
              help={ __( "Set background overlay opacity (%)", 'formality' ) }
            >
              <RangeControl
                value={ this.state['_formality_overlay_opacity'] }
                onChange={ ( newOpacity ) => this.updateFormalityOptions('_formality_overlay_opacity', newOpacity) }
                min={ 0 }
                max={ 100 }
              />
            </BaseControl> : ''
          }
          <BaseControl
            label={__("Input style", "formality")}
            help={ this.state['_formality_style']=="box" ? __('Boxed border input field', 'formality') : __('Single line border input field', 'formality') }
          >
            <ButtonGroup>
              <Button
                isPrimary={ this.state['_formality_style']=="box" ? true : false }
                isDefault={ this.state['_formality_style']=="box" ? false : true }
                onClick={() => this.updateFormalityOptions('_formality_style', 'box')}
              >{__('Boxed','formality')}</Button>
              <Button
                isPrimary={ this.state['_formality_style']=="line" ? true : false }
                isDefault={ this.state['_formality_style']=="line" ? false : true }
                onClick={() => this.updateFormalityOptions('_formality_style', 'line')}
              >{__('Line','formality')}</Button>
            </ButtonGroup>
          </BaseControl>
        </PanelBody>
        <PanelBody
          title={__('Templates', 'formality')}
          initialOpen={ false }
          //icon={ "hidden" }
        >
          <BaseControl
            className="formality_radio-templates"
          >
            <label
              className="components-base-control__label"
            >
              { __( 'Select one of our templates made with a selection of the best', 'formality' ) + ' ' }
              <a target="_blank" rel="noopener noreferrer" href="https://unsplash.com">Unplash</a>
              { ' ' + __( 'photos.', 'formality' ) }
            </label>
            { this.buildFormalityTemplates() }
          </BaseControl>
        </PanelBody>
      </Fragment>
    )
    
    let tabSettings = (
      <Fragment>
        <Panel>
          <PanelBody
            title={__('Embed & Share', 'formality')}
            initialOpen={ false }
          >
            <strong>Standalone version</strong>
            <p>This is an independent form, that are not tied to your posts or pages, and you can visit at this web address: <a className="formality-admin-info-permalink" target="_blank" href=""></a></p>
            <PanelRow
              className='components-panel__row--copyurl'
            >
              <TextControl
                value={ postPermalink }
                disabled
              />
              <ClipboardButton
                icon="admin-page"
                text={ postPermalink }
              >
              </ClipboardButton>
            </PanelRow>
            <strong>Embedded version</strong>
            <p>But you can also embed it, into your post or pages with Formality block or with this specific shortcode:</p>
            <PanelRow
              className='components-panel__row--copyurl'
            >
              <TextControl
                value={ '[formality id="' + postId + '"]' }
                disabled
              />
              <ClipboardButton
                icon="admin-page"
                text={ '[formality id="' + postId + '"]' }
              >
              </ClipboardButton>
            </PanelRow>
          </PanelBody>
          <PanelBody
            className={'formality-toggle-footer'}
            title={__('Form footer', 'formality')}
            initialOpen={ false }
          >
            <TextControl
              label={__('Send button label', 'formality')}
              placeholder={__('Send', 'formality')}
              value={ this.state['_formality_send_text'] }
              onChange={(value) => this.updateFormalityOptions('_formality_send_text', value)}
            />
            <TextareaControl
              label={__('Credits/copy text')}
              rows={ 3 }
              value={ this.state['_formality_custom_credits'] }
              onChange={(value) => this.updateFormalityOptions('_formality_custom_credits', value)}
            />
            <ToggleControl
              label={ __('Enable Formality credits', 'formality') }
              checked={ this.state['_formality_enable_credits'] }
              onChange={(value) => this.updateFormalityOptions('_formality_enable_credits', value)}
              help={ __('If you like this plugin, add a small Formality badge and template background credits', 'formality') }
            />
          </PanelBody>
          <PanelBody
            title={__('Notifications', 'formality')}
            initialOpen={ false }
          >
            <p>{ __('Formality automatically saves all the results in the Wordpress database, but if you want you can also activate e-mail notifications, by entering your address.', 'formality') }</p>
            <TextControl
              //label={__('Error message', 'formality')}
              placeholder={__('E-mail address', 'formality')}
              value={ this.state['_formality_email'] }
              onChange={(value) => this.updateFormalityOptions('_formality_email', value)}
            />
          </PanelBody>
          <PanelBody
            title={__('Submit status', 'formality')}
            initialOpen={ false }
          >
            <TextControl
              className={'components-base-control--nomargin'}
              label={__('Thank you message', 'formality')}
              placeholder={__('Thank you', 'formality')}
              value={ this.state['_formality_thankyou'] }
              onChange={(value) => this.updateFormalityOptions('_formality_thankyou', value)}
            />
            <TextareaControl
              placeholder={__('Your data has been successfully submitted. You are very important to us, all information received will always remain confidential. We will contact you as soon as possible.', 'formality')}
              value={ this.state['_formality_thankyou_message'] }
              onChange={(value) => this.updateFormalityOptions('_formality_thankyou_message', value)}
            />
            <TextControl
              className={'components-base-control--nomargin'}
              label={__('Error message', 'formality')}
              placeholder={__('Error', 'formality')}
              value={ this.state['_formality_error'] }
              onChange={(value) => this.updateFormalityOptions('_formality_error', value)}
            />
            <TextareaControl
              placeholder={__("Something went wrong and we couldn't save your data. Please retry later or contact us by e-mail or phone.", 'formality')}
              value={ this.state['_formality_error_message'] }
              onChange={(value) => this.updateFormalityOptions('_formality_error_message', value)}
            />
          </PanelBody>
        </Panel>
      </Fragment>
    )
    
    return (
      <Fragment>
        <TabPanel
          activeClass="active"
          onSelect={(tabName) => {
            const $panel = jQuery('.edit-post-sidebar > .components-panel');
            if(tabName=='appearance-tab') {
              $panel.removeClass('view-all');
            } else {
              $panel.addClass('view-all');
            }
          }}
          tabs={[
            { name: 'appearance-tab', title: 'Appearance', className: 'components-panel__body-toggle' },
            { name: 'settings-tab', title: 'Settings', className: 'components-panel__body-toggle formality-toggle-settings' },
          ]}>
          {( tab ) => <Fragment>{ tab.name == 'appearance-tab' ? tabAppearance : tabSettings }</Fragment> }
        </TabPanel>
      </Fragment>
    )
  }
}

const FormalitySidebarDocument = () => {
  if(wp.data.select("core/editor").getCurrentPostType() == "formality_form") {
    return (
      <PluginDocumentSettingPanel
        name="formality-sidebar"
        title="Form options"
        className="components-panel__body--formality"
        icon={""}
      >
        <Formality_Sidebar></Formality_Sidebar>
      </PluginDocumentSettingPanel>
    )
  }
  return ( <Fragment></Fragment> )
}

registerPlugin('formality-sidebar', { render: FormalitySidebarDocument });
