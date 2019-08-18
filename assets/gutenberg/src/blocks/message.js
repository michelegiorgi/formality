/** 
 * Formality block
 * 
 */

const blockName = 'formality/message'

import {
  checkUID,
  editAttribute,
  getBlocks,
  getBlockTypes,
  mainOptions,
  advancedPanel,
  hasRules
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
  BaseControl,
  Icon
} = wp.components;

const { 
  RichText,
  MediaUpload,
  InspectorControls
} = wp.editor;

import { iconMessage as blockicon } from '../main/icons.js'

registerBlockType( blockName, {
  title: __('Message', 'formality'),
  description: __('Custom message/information for your users.', 'formality'), 
  icon: blockicon,
  category: 'formality_nav',
  attributes: {
    uid: { type: 'string', default: '' },
    text: { type: 'string', default: ''},
    exclude: { type: 'integer', default: 99},
    rules: {
      type: 'string|array',
      attribute: 'rules',
      default: []
    },
  },
  supports: {
    html: false,
    customClassName: false,
  },
  edit(props) {

    checkUID(props, 2)
    let text = props.attributes.text
    let uid = props.attributes.uid
    let focus = props.isSelected
    let rules = props.attributes.rules

    return ([
      <InspectorControls>
        { advancedPanel(props, false) }
      </InspectorControls>
      ,
      <div
        class="formality__message"
      >
        <Icon icon={ hasRules(rules) ? "hidden" : "" } />
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