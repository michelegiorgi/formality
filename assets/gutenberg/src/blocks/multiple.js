/** 
 * Formality block
 * 
 */

const blockName = 'formality/multiple'

import {
  checkUID,
  editAttribute,
  getBlocks,
  getBlockTypes,
  mainOptions,
  advancedPanel,
  hasRules
} from '../main/utility.js'

import { iconMultiple as blockicon } from '../main/icons.js'

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
  RichText,
  MediaUpload,
  InspectorControls,
  BlockControls
} = wp.editor;

var el = wp.element.createElement;

registerBlockType( blockName, {
  title: __('Multiple choice', 'formality'),
  description: __('Standard text field, good for short answers and 1 line information', 'formality'), 
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
    options: {
      type: 'string|array',
      attribute: 'options',
      default: []
    }
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
    let options = props.attributes.options
    let rules = props.attributes.rules
    let uid = props.attributes.uid
    let value = props.attributes.value
    let focus = props.isSelected

    return ([
      <InspectorControls>
        <PanelBody title={__('Field options', 'formality')}>
          { mainOptions(props) }
          <label
            class="components-base-control__label"
          >Options</label>
          <RepeaterControl
            addText={__('Add option', 'formality')}
            removeOnEmpty={true}
            value={options}
            onChange={(val) => { props.setAttributes({options: val}); }}
          >{(value, onChange) => {
            return [
              <TextControl
                placeholder="Value"
                value={value.value}
                onChange={(v) => { value.value = v; onChange(value)}}
              />,
              <TextControl
                placeholder="Label"
                value={value.label}
                onChange={(v) => { value.label = v; onChange(value) }}
              />
            ]
          }}</RepeaterControl>          
        </PanelBody>
        { advancedPanel(props) }
      </InspectorControls>
      ,
      <div
        class={ "formality__field formality__field--select" + ( focus ? ' formality__field--focus' : '' ) + ( required ? ' formality__field--required' : '' ) + ( value ? ' formality__field--filled' : '' ) }
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
          data-placeholder={ placeholder ? placeholder : __('Select your choice', 'formality') }
        >
          <select
            id={ uid }
            name={ uid }
            required=""
            placeholder={ placeholder ? placeholder : __('Select your choice', 'formality') }
          >
            <option
              disabled
              selected
              value=""
            >{ value ? value : (placeholder ? placeholder : __('Select your choice', 'formality')) }</option>
          </select>
        </div>
      </div>
    ])
  }, 
  save ( props ) {
    return null
  },
});