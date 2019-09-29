/** 
 * Formality block
 * 
 */

const blockName = 'formality/number'

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
} = wp.blockEditor;

import { iconNumber as blockicon } from '../main/icons.js'

registerBlockType( blockName, {
  title: __('Number', 'formality'),
  description: __('Number field, accept integer or float number value', 'formality'), 
  icon: blockicon,
  category: 'formality',
  attributes: {
    uid: { type: 'string', default: '' },
    name: { type: 'string', default: ''},
    label: { type: 'string', default: ''},
    placeholder: { type: 'string', default: ''},
    required: { type: 'boolean', default: false },
    halfwidth: { type: 'boolean', default: false },
    value: { type: 'string', default: ''},
    rules: {
      type: 'string|array',
      attribute: 'rules',
      default: []
    },
    value_min: { type: 'string', default: ''},
    value_max: { type: 'string', default: ''},
    value_step: { type: 'string', default: '1'},
  },
  supports: {
    html: false,
    customClassName: false,
  },
  transforms: {
    from: [{
      type: 'block',
      blocks: getBlockTypes(blockName),
      transform: function ( attributes ) { return createBlock( blockName, attributes); },
    }]
  },
  edit(props) {
    
    checkUID(props)
    let name = props.attributes.name
    let label = props.attributes.label
    let placeholder = props.attributes.placeholder
    let required = props.attributes.required
    let halfwidth = props.attributes.halfwidth
    let uid = props.attributes.uid
    let value = props.attributes.value
    let value_min = props.attributes.value_min
    let value_max = props.attributes.value_max
    let value_step = props.attributes.value_step
    let focus = props.isSelected
    let rules = props.attributes.rules

    return ([
      <InspectorControls>
        <PanelBody title={__('Field options', 'formality')}>
          { mainOptions(props) }
          <PanelRow
            className="formality_panelrow"
          >
            <TextControl
              type="number"
              label={__('Min value', 'formality')}
              value={value_min}
              onChange={(value) => editAttribute(props, "value_min", value)}
            />
            <TextControl
              type="number"
              label={__('Max value', 'formality')}
              value={value_max}
              onChange={(value) => editAttribute(props, "value_max", value)}
            />
            <TextControl
              type="number"
              label={__('Interval', 'formality')}
              value={value_step}
              onChange={(value) => editAttribute(props, "value_step", value)}
            />
          </PanelRow>
        </PanelBody>
        { advancedPanel(props) }
      </InspectorControls>
      ,
      <div
        class={ "formality__field formality__field--text" + ( focus ? ' formality__field--focus' : '' ) + ( required ? ' formality__field--required' : '' ) + ( value ? ' formality__field--filled' : '' ) }
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
          <input
            type="text"
            id={ uid }
            name={ uid }
            value={value}
            placeholder={ placeholder ? placeholder : __('Type your answer here', 'formality') }
          />
        </div>
      </div>
    ])
  }, 
  save ( props ) {
    return null
  },
});