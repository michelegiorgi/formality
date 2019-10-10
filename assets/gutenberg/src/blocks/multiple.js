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
  RangeControl,
  Icon
} = wp.components;

const { 
  RichText,
  MediaUpload,
  InspectorControls,
  BlockControls
} = wp.blockEditor;

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
    },
    option_labels: { type: 'boolean', default: false },
    single: { type: 'boolean', default: false },
    option_grid: { type: 'integer', default: 1 },
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
    let option_labels = props.attributes.option_labels
    let option_grid = props.attributes.option_grid
    let rules = props.attributes.rules
    let uid = props.attributes.uid
    let value = props.attributes.value
    let single = props.attributes.single
    let focus = props.isSelected
        
    return ([
      <InspectorControls>
        <PanelBody title={__('Field options', 'formality')}>
          { mainOptions(props, true, true) }
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
                className={ option_labels ? "" : "components-base-control--hide" }
                placeholder="Label"
                value={value.label}
                onChange={(v) => { value.label = v; onChange(value) }}
              />
            ]
          }}</RepeaterControl>
          <ToggleControl
            label={ __('Assign different values and labels for each option', 'formality') }
            checked={ option_labels }
            onChange={() => editAttribute(props, "option_labels", true, true )}
          />
          <ToggleControl
            label={ __('Allow only single selection', 'formality') }
            checked={ single }
            onChange={() => editAttribute(props, "single", true, true )}
          />
          <PanelRow
            className="formality_optionsgrid"
          >
            <BaseControl
              label={ __( 'Options grid', 'formality' ) }
              help={ __( 'Distribute options in ', 'formality' ) + option_grid +  __( ' columns.', 'formality' ) }
            >
              <RangeControl
                value={ option_grid }
                onChange={(val) => { props.setAttributes({option_grid: val}); }}
                min={ 1 }
                max={ 3 }
                //beforeIcon="arrow-left"
                //afterIcon="arrow-right"
              />
            </BaseControl>
          </PanelRow>
        </PanelBody>
        { advancedPanel(props) }
      </InspectorControls>
      ,
      <div
        class={ "formality__field formality__field--multiple" + ( focus ? ' formality__field--focus' : '' ) + ( required ? ' formality__field--required' : '' ) + ( value ? ' formality__field--filled' : '' ) }
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
          <div class="formality__note">{ placeholder }</div>
          <div class={ "formality__input__grid formality__input__grid--" + option_grid }>
            {options.map(option => { 
              return <label class={ "formality__label formality__label--" + (single ? "radio" : "checkbox") }><i></i><span>{ option["label"] ? option["label"] : option["value"] }</span></label> })}
          </div>
        </div>
      </div>
    ])
  }, 
  save ( props ) {
    return null
  },
});