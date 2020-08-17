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

import {
  hideFormalityLoading,
  updateFormalityOptions,
  applyFormalityStyles,
  buildFormalityTemplates,
} from '../utility/sidebar.js'

//const { withSelect } = wp.data;
//const { compose } = wp.compose;

class Formality_Sidebar extends Component {
  
  constructor() {
    super( ...arguments );
    
    //get post metas
    let formality_keys = wp.data.select('core/editor').getEditedPostAttribute('meta')

    //define default values    
    let default_keys = {
      '_formality_type': 'standard',
      '_formality_style': 'box',
      '_formality_color1': '#000000',
      '_formality_color2': '#ffffff',
      '_formality_color3': '#ff0000',
      '_formality_fontsize': 20,
      '_formality_logo': '',
      '_formality_logo_id': 0,
      '_formality_logo_height': 3,
      '_formality_bg': '',
      '_formality_bg_id': 0,
      '_formality_bg_layout': 'standard',
      '_formality_overlay_opacity': 20,
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
    
    //set state and remove loading layer
    formality_keys['_formality_templates_count'] = parseInt(formality.templates_count)
    formality_keys['_formality_templates_progress'] = false
    this.state = formality_keys
    applyFormalityStyles(this.state)
    hideFormalityLoading()

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
            label={__("Form type", "formality")}
            //help={ this.state['_formality_type']=="standard" ? 'Classic layout form' : 'Distraction free form' }
          >
            <ButtonGroup>
              <Button
                isPrimary={ this.state['_formality_type']=="standard" ? true : false }
                isSecondary={ this.state['_formality_type']=="standard" ? false : true }
                onClick={() => updateFormalityOptions('_formality_type', 'standard', this)}
              >{ __("Standard", "formality") }</Button>
              <Button
                isPrimary={ this.state['_formality_type']=="conversational" ? true : false }
                isSecondary={ this.state['_formality_type']=="conversational" ? false : true }
                onClick={() => updateFormalityOptions('_formality_type', 'conversational', this)}
              >{ __("Conversational", "formality") }</Button>
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
                    onChangeComplete={(value) => updateFormalityOptions('_formality_color1', value.hex, this)}
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
                    onChangeComplete={(value) => updateFormalityOptions('_formality_color2', value.hex, this)}
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
                onChange={ ( newFontSize ) => updateFormalityOptions('_formality_fontsize', newFontSize, this) }
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
            //help={ __( "Set a custom logo", 'formality' ) }
          >
            <MediaUpload
              onSelect={(file) => updateFormalityOptions('_formality_logo', file, this)}
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
                  { this.state['_formality_logo'] ? <Button onClick={() => updateFormalityOptions('_formality_logo', '', this)} isLink isDestructive>{ __('Remove custom logo', 'formality' )}</Button> : ''}
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
                onChange={( newHeight ) => updateFormalityOptions('_formality_logo_height', newHeight, this)}
                min={ 2 }
                max={ 10 }
              />
            </BaseControl> : ''
          }
          <BaseControl
            label={ __( 'Background image', 'formality' ) }
            //help={ __( "Add background image", 'formality' ) }
          >
            <MediaUpload
              onSelect={(file) => updateFormalityOptions('_formality_bg', file, this)}
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
                  { this.state['_formality_bg'] ? <Button onClick={() => updateFormalityOptions('_formality_bg', '', this)} isLink isDestructive>{ __('Remove background image', 'formality' )}</Button> : ''}
                </Fragment>
              )}
            />
          </BaseControl>
          { this.state['_formality_bg'] ?
            <Fragment>
              <BaseControl
                label={ __( 'Background overlay', 'formality' ) }
                help={ __( "Set background overlay opacity (%)", 'formality' ) }
              >
                <RangeControl
                  value={ this.state['_formality_overlay_opacity'] }
                  onChange={ ( newOpacity ) => updateFormalityOptions('_formality_overlay_opacity', newOpacity, this) }
                  min={ 0 }
                  max={ 100 }
                  disabled={ this.state['_formality_bg_layout'] == "side" }
                />
              </BaseControl>
              <BaseControl
                label={__("Background layout", "formality")}
                help={ this.state['_formality_bg_layout']=="standard" ? __('Full screen background', 'formality') : __('Side background', 'formality') }
              >
                <ButtonGroup>
                  <Button
                    isPrimary={ this.state['_formality_bg_layout']=="standard" ? true : false }
                    isSecondary={ this.state['_formality_bg_layout']=="standard" ? false : true }
                    onClick={() => updateFormalityOptions('_formality_bg_layout', 'standard', this)}
                  >Standard</Button>
                  <Button
                    isPrimary={ this.state['_formality_bg_layout']=="side" ? true : false }
                    isSecondary={ this.state['_formality_bg_layout']=="side" ? false : true }
                    onClick={() => updateFormalityOptions('_formality_bg_layout', 'side', this)}
                  >Side</Button>
                </ButtonGroup>
              </BaseControl>
            </Fragment> : ''
          }
          <BaseControl
            label={__("Input style", "formality")}
            help={ this.state['_formality_style']=="box" ? __('Boxed border input field', 'formality') : __('Single line border input field', 'formality') }
          >
            <ButtonGroup>
              <Button
                isPrimary={ this.state['_formality_style']=="box" ? true : false }
                isSecondary={ this.state['_formality_style']=="box" ? false : true }
                onClick={() => updateFormalityOptions('_formality_style', 'box', this)}
              >{__('Boxed','formality')}</Button>
              <Button
                isPrimary={ this.state['_formality_style']=="line" ? true : false }
                isSecondary={ this.state['_formality_style']=="line" ? false : true }
                onClick={() => updateFormalityOptions('_formality_style', 'line', this)}
              >{__('Line','formality')}</Button>
            </ButtonGroup>
          </BaseControl>
          <span className="components-base-control__label">{ __("Error color", "formality") }</span>
          <PanelRow
            className="formality_colorpicker no-margin"
          >
            <BaseControl
              help={ __("Label and border color of the wrong inputs.", "formality") }
            >
              <Dropdown
                className="components-color-palette__item-wrapper components-color-palette__custom-color components-circular-option-picker__option-wrapper"
                contentClassName="components-color-palette__picker"
                renderToggle={ ( { isOpen, onToggle } ) => (
                  <button
                    type="button"
                    style={{ background: this.state['_formality_color3'] }}
                    aria-expanded={ isOpen }
                    className="components-color-palette__item components-circular-option-picker__option"
                    onClick={ onToggle }
                  ></button>
                ) }
                renderContent={ () => (
                  <ColorPicker
                    color={ this.state['_formality_color3'] }
                    onChangeComplete={(value) => updateFormalityOptions('_formality_color3', value.hex, this)}
                    disableAlpha
                  />
                ) }
              />
            </BaseControl>
          </PanelRow>
        </PanelBody>
        { buildFormalityTemplates(this) }
      </Fragment>
    )
    
    let tabSettings = (
      <Fragment>
        <Panel>
          <PanelBody
            title={__('Embed & Share', 'formality')}
            initialOpen={ false }
          >
            <strong>{__('Standalone version', 'formality')}</strong>
            <p>
              {__('This is an independent form, that are not tied to your posts or pages, and you can visit at this web address:', 'formality') + ' '}
              <a className="formality-admin-info-permalink" target="_blank" href=""></a>
            </p>
            <PanelRow
              className='components-panel__row--copyurl'
            >
              <TextControl
                value={ postPermalink }
                disabled
              />
              <ClipboardButton
                onCopy={ () => this.setState( { '_formality_hascopied_1': true } ) }
                onFinishCopy={ () => this.setState( { '_formality_hascopied_1': false } ) }
                icon={ this.state['_formality_hascopied_1'] ? 'yes' : 'admin-page' }
                text={ postPermalink }
              >
              </ClipboardButton>
            </PanelRow>
            <strong>{__('Embedded version', 'formality')}</strong>
            <p>{__('But you can also embed it, into your post or pages with Formality block or with this specific shortcode:', 'formality')}</p>
            <PanelRow
              className='components-panel__row--copyurl'
            >
              <TextControl
                value={ '[formality id="' + postId + '"]' }
                disabled
              />
              <ClipboardButton
                onCopy={ () => this.setState( { '_formality_hascopied_2': true } ) }
                onFinishCopy={ () => this.setState( { '_formality_hascopied_2': false } ) }
                icon={ this.state['_formality_hascopied_2'] ? 'yes' : 'admin-page' }
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
              onChange={(value) => updateFormalityOptions('_formality_send_text', value, this)}
            />
            <TextareaControl
              label={__('Credits/copy text')}
              rows={ 3 }
              value={ this.state['_formality_custom_credits'] }
              onChange={(value) => updateFormalityOptions('_formality_custom_credits', value, this)}
            />
            <ToggleControl
              label={ __('Enable Formality credits', 'formality') }
              checked={ this.state['_formality_enable_credits'] }
              onChange={(value) => updateFormalityOptions('_formality_enable_credits', value, this)}
              help={ __('Support us (and the photographer of the chosen template) with a single text line at the end of this form.', 'formality') }
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
              onChange={(value) => updateFormalityOptions('_formality_email', value, this)}
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
              onChange={(value) => updateFormalityOptions('_formality_thankyou', value, this)}
            />
            <TextareaControl
              placeholder={__('Your data has been successfully submitted. You are very important to us, all information received will always remain confidential. We will contact you as soon as possible.', 'formality')}
              value={ this.state['_formality_thankyou_message'] }
              onChange={(value) => updateFormalityOptions('_formality_thankyou_message', value, this)}
            />
            <TextControl
              className={'components-base-control--nomargin'}
              label={__('Error message', 'formality')}
              placeholder={__('Error', 'formality')}
              value={ this.state['_formality_error'] }
              onChange={(value) => updateFormalityOptions('_formality_error', value, this)}
            />
            <TextareaControl
              placeholder={__("Something went wrong and we couldn't save your data. Please retry later or contact us by e-mail or phone.", 'formality')}
              value={ this.state['_formality_error_message'] }
              onChange={(value) => updateFormalityOptions('_formality_error_message', value, this)}
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
            { name: 'appearance-tab', title: __('Appearance', 'formality'), className: 'components-panel__body-toggle' },
            { name: 'settings-tab', title: __('Settings', 'formality'), className: 'components-panel__body-toggle formality-toggle-settings' },
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
