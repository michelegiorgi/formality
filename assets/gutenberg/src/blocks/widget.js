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
  ServerSideRender,
  Icon
} = wp.components;

const { 
  RichText,
  MediaUpload,
  InspectorControls,
  BlockControls
} = wp.blockEditor;

const { serverSideRender } = wp; //WordPress form inputs and server-side renderer
const { select, withSelect } = wp.data;
const { createElement } = wp.element; //React.createElement

registerBlockType( 'formality/widget', {
	title: __( 'Formality form' ), // Block title.
	category: 'widgets',
	attributes:  {
		id: { type: 'integer', default: 0, },
		heading: { type: 'string', default: 'h2' }
	},
	//display the post title
	edit: withSelect( function( select ) {
      return { forms_raw: select( 'core' ).getEntityRecords( 'postType', 'formality_form' ) };
    })( function( props ) {

    let forms = [];
		if(props.forms_raw) {
			forms.push( { value: 0, label: 'Select something', disabled: true } );
			props.forms_raw.forEach((form) => {
				forms.push({value:form.id, label:form.title.rendered});
			});
		} else {
			forms.push( { value: 0, label: 'Loading...', disabled: true } )
		}
    
    return ([
      <ServerSideRender
        block="formality/widget"
        attributes={ props.attributes }
      />,
      <InspectorControls>
        <PanelBody title={__('Field options', 'formality')}>
          <SelectControl
            label="Size"
            value={ props.attributes.id }
            options={ forms }
            label={__( 'Select a form to embed', 'formality' )}
						onChange={(value) => { props.setAttributes({id: parseInt(value) }) }}
          />
          <TextControl
            label={__('Label / Question', 'formality')}
            value={ props.attributes.heading }
            onChange={(value) => { props.setAttributes({heading: value}) }}
          />
        </PanelBody>
      </InspectorControls>
    ])
	}),
	save(){
		return null;
	}
});