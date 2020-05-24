/** 
 * Formality widget block
 * 
 */

import { iconWidget as blockicon } from '../main/icons.js'
import { getPreview } from '../main/utility.js'
import React from 'react'

const {
  __,
  sprintf,
} = wp.i18n;

const { 
  registerBlockType,
} = wp.blocks;

const { 
  PanelBody,
  Button,
  TextControl,
  SelectControl,
  ToggleControl,
  ButtonGroup,
  BaseControl,
  PanelRow,
  ClipboardButton,
} = wp.components;

const {
  Fragment,
  createElement,
} = wp.element;

const { 
  InspectorControls,
} = wp.blockEditor;

const { serverSideRender } = wp; //WordPress form inputs and server-side renderer
const { withSelect } = wp.data;

registerBlockType( 'formality/widget', {
  title: __( 'Formality form' ), // Block title.
  description: __('Embed Formality forms in your posts or pages.', 'formality'), 
  icon: blockicon,
  category: 'widgets',
  supports: { align: true },
  attributes: {
    id: { type: 'integer', default: 0 },
    remove_bg: { type: 'boolean', default: false },
    is_sidebar: { type: 'boolean', default: false },
    hide_title: { type: 'boolean', default: false },
    invert_colors: { type: 'boolean', default: false },
    disable_button: { type: 'boolean', default: false },
    cta_label: { type: 'string', default: __('Call to action', 'formality') },
    preview: { type: 'boolean', default: false },
  },
  example: { attributes: { preview: true } },
  getEditWrapperProps() {
    return { 'data-align': '' };
  },
  //display the post title
  edit: withSelect( function( select ) {
      return { forms_raw: select( 'core' ).getEntityRecords( 'postType', 'formality_form' ) };
    })( function( props ) {
    
    if ( props.attributes.preview ) { return getPreview(props.name) }    
    
    let forms = [];
    let formExist = false;
    if(props.forms_raw) {
      forms.push( { value: 0, label: __('Select a form to embed', 'formality'), disabled: true } );
      props.forms_raw.forEach((form) => {
        forms.push({value:form.id, label:form.title.rendered})
        if(form.id==props.attributes.id) { formExist = true }
      });
    } else {
      forms.push( { value: 0, label: __('Loading your forms...', 'formality'), disabled: true } )
    }

    const serverForm = createElement( serverSideRender, {
      block: 'formality/widget',
      attributes: props.attributes,
    })
    
    const editForm = (
      <Fragment>
        <div className="formality_widget_block__edit">
          <a target="_blank" rel="noopener noreferrer" href={ formality.admin_url + 'post.php?action=edit&post=' + props.attributes.id }>{__("Edit this form", 'formality')}</a>
        </div>
      </Fragment>
    )
    
    const widgetForm = (
      <Fragment>
        { props.attributes.is_sidebar ? "" : editForm }
        { serverForm }
      </Fragment>
    )
    
    const blockInfo = () => {
      if(props.forms_raw) {
        if(forms.length == 1) {
          return <span style={{ display: "block", marginTop: "12px" }}>
            { __("Currently you can't use this block, because you have no published Formality form.", 'formality') + ' ' }
            <a target="_blank" rel="noopener noreferrer" href={ formality.admin_url + 'post-new.php?post_type=formality_form' }>{__("Create the first one.", 'formality')}</a>
          </span>      
        } else if(formExist) {
          return <span style={{ display: "block", marginTop: "12px" }}>
            <a target="_blank" rel="noopener noreferrer" href={ formality.admin_url + 'post.php?action=edit&post=' + props.attributes.id }>{__("Edit this form", 'formality')}</a>
          </span>            
        }
      }
    };

    const fieldsEmbed = (
      <Fragment>
        <ToggleControl
          label={ __('Remove background', 'formality') }
          checked={ props.attributes.remove_bg }
          onChange={(value) => { props.setAttributes({remove_bg: value}) }}
        />
        <ToggleControl
          label={ __('Hide form title', 'formality') }
          checked={ props.attributes.hide_title }
          onChange={(value) => { props.setAttributes({hide_title: value}) }}
        />
      </Fragment>
    )
    
    const fieldsSidebar = (
      <Fragment>
        <ToggleControl
          label={ __('Disable button', 'formality') }
          checked={ props.attributes.disable_button }
          onChange={(value) => { props.setAttributes({disable_button: value}) }}
          help={ __('Remove button and open this form with a simple text link', 'formality') }
        />
        { props.attributes.disable_button ? <Fragment>
            <p>{__('Manually set this hashtag as url link (href) in your text content', 'formality') }</p>
            <PanelRow className='components-panel__row--copyurl'>
              <TextControl value={ '#formality-open-' + props.attributes.id  } disabled />
              <ClipboardButton
                icon="admin-page"
                text={ '#formality-open-' + props.attributes.id }
                //onCopy={ }
                //onFinishCopy={ }
              >
              </ClipboardButton>
            </PanelRow>
          </Fragment> : <Fragment>
            <TextControl
              label={__('Button label', 'formality')}
              placeholder={__('Call to action', 'formality')}
              value={ props.attributes.cta_label }
              onChange={(value) => { props.setAttributes({cta_label: value}) }}
            />
            <ToggleControl
              label={ __('Invert form colors for this button', 'formality') }
              checked={ props.attributes.invert_colors }
              onChange={(value) => { props.setAttributes({invert_colors: value}) }}
            />
          </Fragment>
        }
      </Fragment>
    )
    
    const noForm = (
      <Fragment>
        <div
          style={{ opacity: 0.6 }}
        >{ props.attributes.id && (!props.forms_raw) ? __('Loading your form...', 'formality') : __('Please select a form to embed, from the right sidebar', 'formality')}</div>
      </Fragment>
    )

    const hiddenWidget = (
      <Fragment>
        <div
          style={{ opacity: 0.6 }}
        >{ sprintf( /* translators: link hashtag */ __( 'Manually set "%s" as url link (href) in your text content', 'formality' ), '#formality-open-' + props.attributes.id ) }</div>
      </Fragment>
    )
    
    return ([
      <Fragment>{ props.attributes.id && formExist ? ( props.attributes.disable_button ? hiddenWidget : widgetForm ) : noForm }</Fragment>,
      <InspectorControls>
        <PanelBody title={__('Options', 'formality')}>
          <SelectControl
            value={ props.attributes.id }
            options={ forms }
            label={__( 'Form', 'formality' )}
            onChange={(value) => { props.setAttributes({id: parseInt(value) }) }}
            help={ blockInfo() }
          />
          <BaseControl
            help={ props.attributes.is_sidebar ? __('Add a button link to your form. Your form will be opened in an onscreen sidebar.', 'formality') : __('Include this form in your post content.', 'formality') }
          >
            <ButtonGroup
              className={ 'components-button-group--wide' }
            >
              <Button
                isPrimary={ props.attributes.is_sidebar ? false : true }
                isDefault={ props.attributes.is_sidebar ? true : false }
                onClick={() => { props.setAttributes({is_sidebar: false}) }}
              >{ __( 'Embed', 'formality' ) }</Button>
              <Button
                isPrimary={ props.attributes.is_sidebar ? true : false }
                isDefault={ props.attributes.is_sidebar ? false : true }
                onClick={() => { props.setAttributes({is_sidebar: true}) }}
              >{ __( 'Button', 'formality' ) }</Button>
            </ButtonGroup>
          </BaseControl>
          { props.attributes.is_sidebar ? fieldsSidebar : fieldsEmbed }
        </PanelBody>
      </InspectorControls>,
    ])
  }),
  save(){
    return null;
  },
});