/** 
 * Formality block
 * 
 */

const blockName = 'formality/select'

import React from 'react'

import {
  checkUID,
  editAttribute,
  getBlockTypes,
  mainOptions,
  advancedPanel,
  hasRules,
} from '../main/utility.js'

import { iconSelect as blockicon } from '../main/icons.js'

const { __ } = wp.i18n;

const { 
  registerBlockType,
  createBlock,
} = wp.blocks;

const { 
  PanelBody,
  TextControl,
  ToggleControl,
  RepeaterControl,
  Icon,
} = wp.components;

const { 
  InspectorControls,
} = wp.blockEditor;

registerBlockType( blockName, {
  title: __('Select', 'formality'),
  description: __('Dropdown list with all available options that users can select.', 'formality'), 
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
      default: [],
    },
    options: {
      type: 'string|array',
      attribute: 'options',
      default: [],
    },
    option_labels: { type: 'boolean', default: false },
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
    }],
  },
  edit(props) {

    checkUID(props)
    let name = props.attributes.name
    let label = props.attributes.label
    let placeholder = props.attributes.placeholder
    let required = props.attributes.required
    let options = props.attributes.options
    let option_labels = props.attributes.option_labels
    let rules = props.attributes.rules
    let uid = props.attributes.uid
    let value = props.attributes.value
    let focus = props.isSelected
        
    return ([
      <InspectorControls>
        <PanelBody title={__('Field options', 'formality')}>
          { mainOptions(props) }
          <label
            className="components-base-control__label"
          >{__('Options', 'formality')}</label>
          <RepeaterControl
            addText={__('Add option', 'formality')}
            removeOnEmpty={true}
            value={options}
            onChange={(val) => { props.setAttributes({options: val}) }}
          >{(value, onChange) => {
            return [
              <TextControl
                placeholder={__('Value', 'formality')}
                value={value.value}
                onChange={(v) => { value.value = v; onChange(value)}}
              />,
              <TextControl
                className={ option_labels ? "" : "components-base-control--hide" }
                placeholder={__('Label', 'formality')}
                value={value.label}
                onChange={(v) => { value.label = v; onChange(value) }}
              />,
            ]
          }}</RepeaterControl>
          <ToggleControl
            label={ __('Assign different values and labels for each option', 'formality') }
            checked={ option_labels }
            onChange={() => editAttribute(props, "option_labels", true, true )}
          />
        </PanelBody>
        { advancedPanel(props) }
      </InspectorControls>
      ,
      <div
        className={ "formality__field formality__field--select" + ( focus ? ' formality__field--focus' : '' ) + ( required ? ' formality__field--required' : '' ) + ( value ? ' formality__field--filled' : '' ) }
      >
        <label
          className="formality__label"
          htmlFor={ uid }
        >
          { name ? name : __('Field name', 'formality') }
          <Icon icon={ hasRules(rules) ? "hidden" : "" } />
        </label>
        <div
          className="formality__input"
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
          <div className="formality__input__status" data-placeholder={ placeholder ? placeholder : __('Type your answer here', 'formality') }/>
        </div>
      </div>,
    ])
  }, 
  save () {
    return null
  },
});