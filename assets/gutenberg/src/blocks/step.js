/** 
 * Formality block
 * 
 */

import {
  checkUID,
  editAttribute
} from '../main/utility.js'

const { __ } = wp.i18n;
const { 
  registerBlockType,
  source
} = wp.blocks;

const { 
  ColorPalette,
  PanelBody,
  PanelRow,
  Button,
  TextControl,
  ToggleControl,
  ButtonGroup,
  BaseControl
} = wp.components;

const { 
  RichText,
  MediaUpload,
  InspectorControls
} = wp.editor;

import { iconStep as blockicon } from '../main/icons.js'

registerBlockType( 'formality/step', {
  title: __('Step', 'formality'),
  description: __('Group your fields into multiple sections, with custom heading.', 'formality'), 
  icon: blockicon,
  category: 'formality_nav',
  attributes: {
    uid: { type: 'string', default: '' },
    name: { type: 'string', default: ''},
    description: { type: 'string', default: ''},
    exclude: { type: 'integer', default: 1},
  },
  supports: {
    html: false,
    customClassName: false,
  },
  edit(props) {
    
    checkUID(props)
    let name = props.attributes.name
    let description = props.attributes.description
    let uid = props.attributes.uid
    let focus = props.isSelected
    
    return ([
      <InspectorControls>
        <PanelBody title={__('Field options', 'formality')}>
          <TextControl
            label={__('Step title', 'formality')}
            value={name}
            onChange={(value) => editAttribute(props, "name", value)}
          />
          <TextControl
            label={__('Description', 'formality')}
            value={description}
            onChange={(value) => editAttribute(props, "description", value)}
          />
        </PanelBody>
      </InspectorControls>
      ,
      <div
        class="formality__section__header"
      >
        <h4>
          { name ? name : __('Step name', 'formality') }
        </h4>
        <p>
          { description }
        </p>
      </div>
    ])
  }, 
  save ( props ) {
    return null
  },
});