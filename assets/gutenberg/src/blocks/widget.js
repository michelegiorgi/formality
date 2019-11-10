/** 
 * Formality widget block
 * 
 */

import { iconSelect as blockicon } from '../main/icons.js'

const { __ } = wp.i18n;

const { 
  registerBlockType,
  createBlock,
  source
} = wp.blocks;

const { 
  ColorPalette,
  PanelBody,
  PanelRow,
  Button,
  TextControl,
  SelectControl,
  ToggleControl,
  ButtonGroup,
  BaseControl,
  RepeaterControl,
  Icon
} = wp.components;

const {
	Component,
	Fragment,
	createElement
} = wp.element;

const { 
  RichText,
  MediaUpload,
  InspectorControls,
  BlockControls
} = wp.blockEditor;

const { serverSideRender } = wp; //WordPress form inputs and server-side renderer
const { select, withSelect } = wp.data;

registerBlockType( 'formality/widget', {
	title: __( 'Formality form' ), // Block title.
	category: 'widgets',
	attributes:  {
		id: { type: 'integer', default: 0, },
		include_bg: { type: 'boolean', default: false }
	},
	//display the post title
	edit: withSelect( function( select ) {
      return { forms_raw: select( 'core' ).getEntityRecords( 'postType', 'formality_form' ) };
    })( function( props ) {

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
			attributes: props.attributes
		})
		
		const blockInfo = () => {
  		if(props.forms_raw) {
        if(forms.length == 1) {
          return <span style={{ display: "block", marginTop: "12px" }}>
            { __("Currently you can't use this block, because you have no published Formality form.", 'formality') + ' ' }
            <a target="_blank" href={ formality.admin_url + 'post-new.php?post_type=formality_form' }>{__("Create the first one.", 'formality')}</a>
          </span>      
        } else if(formExist) {
          return <span style={{ display: "block", marginTop: "12px" }}>
            <a target="_blank" href={ formality.admin_url + 'post.php?action=edit&post=' + props.attributes.id }>{__("Edit this form", 'formality')}</a>
          </span>            
        }
      }
    };
		
		const noForm = (
      <Fragment>
        <div
          style={{ opacity: 0.6 }}
        >{ props.attributes.id && (!props.forms_raw) ? __('Loading your form...', 'formality') : __('Please select a form to embed, from the right sidebar', 'formality')}</div>
      </Fragment>
    )
    
    return ([
      <Fragment>{ props.attributes.id && formExist ? serverForm : noForm }</Fragment>,
      <InspectorControls>
        <PanelBody title={__('Options', 'formality')}>
          <SelectControl
            value={ props.attributes.id }
            options={ forms }
            label={__( 'Form', 'formality' )}
						onChange={(value) => { props.setAttributes({id: parseInt(value) }) }}
						help={ blockInfo() }
          />
          <ToggleControl
            label={ __('Include background', 'formality') }
            checked={ props.attributes.include_bg }
            onChange={(value) => { props.setAttributes({include_bg: value}) }}
          />
        </PanelBody>
      </InspectorControls>
    ])
	}),
	save(){
		return null;
	}
});