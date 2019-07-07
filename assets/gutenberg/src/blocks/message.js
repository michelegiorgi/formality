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

import { iconMessage as blockicon } from '../main/icons.js'

registerBlockType( 'formality/message', {
  title: __('Message', 'formality'),
  description: __('Custom message/information for your users.', 'formality'), 
  icon: blockicon,
  category: 'formality_nav',
  attributes: {
    uid: { type: 'string', default: '' },
    text: { type: 'string', default: ''},
    exclude: { type: 'integer', default: 2},
  },
  supports: {
    html: false,
    customClassName: false,
  },
  edit(props) {

    checkUID(props)
    let text = props.attributes.text
    let uid = props.attributes.uid
    let focus = props.isSelected
    
    return ([
      <InspectorControls>
      </InspectorControls>
      ,
      <div
        class="formality__message"
      >
        <RichText
          tagName="p"
          value={text}
          onChange={(value) => editAttribute(props, "text", value)}
          placeholder="Enter your text here!"
        />
      </div>
    ])
  }, 
  save ( props ) {
    return null
  },
});