/** 
 * Formality block
 * 
 */

import {
  checkUID,
  editAttribute,
  getBlocks,
  mainOptions,
  advancedPanel,
  hasRules
} from '../main/utility.js'

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

import { iconTextarea as blockicon } from '../main/icons.js'

registerBlockType( 'formality/textarea', {
  title: __('Textarea', 'formality'),
  description: __('Standard text field, good for short answers and 1 line information', 'formality'), 
  icon: blockicon,
  category: 'formality',
  attributes: {
    uid: { type: 'string', default: '' },
    name: { type: 'string', default: ''},
    label: { type: 'string', default: ''},
    placeholder: { type: 'string', default: ''},
    required: { type: 'boolean', default: false },
    value: { type: 'string', default: ''},
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
  transforms: {
    from: [{
      type: 'block',
      blocks: [ 'formality/text', 'formality/email', 'formality/select'  ],
      transform: function ( attributes ) { return createBlock( 'formality/textarea', attributes); },
    }]
  },
  edit(props) {
    
    checkUID(props)
    let name = props.attributes.name
    let label = props.attributes.label
    let placeholder = props.attributes.placeholder
    let required = props.attributes.required
    let uid = props.attributes.uid
    let value = props.attributes.value
    let focus = props.isSelected
    let rules = props.attributes.rules

    return ([
      <InspectorControls>
        <PanelBody title={__('Field options', 'formality')}>
          { mainOptions(props, false) }
        </PanelBody>
        { advancedPanel(props) }
      </InspectorControls>
      ,
      <div
        class={ "formality__field formality__field--textarea" + ( focus ? ' formality__field--focus' : '' ) + ( required ? ' formality__field--required' : '' ) + ( value ? ' formality__field--filled' : '' ) }
      >
        <label
          class="formality__label"
          for={ uid }
        >
          { name ? name : __('Field name', 'formality') }
          <Icon icon={ hasRules(rules) ? "hidden" : "" } />
        </label>
        <div
          class="formality__input"
          data-placeholder={ placeholder ? placeholder : __('Type your answer here', 'formality') }
        >
          <textarea
            placeholder={ placeholder ? placeholder : __('Type your answer here', 'formality') }
          >{ value }</textarea>
        </div>
      </div>
    ])
  }, 
  save ( props ) {
    return null
  },
});