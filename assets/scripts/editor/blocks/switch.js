/** 
 * Formality block
 * 
 */

const blockName = 'formality/switch'

import React from 'react'

import {
  checkUID,
  editAttribute,
  getBlockTypes,
  mainOptions,
  advancedPanel,
  hasRules,
} from '../main/utility.js'

const { __ } = wp.i18n;
const { 
  registerBlockType,
  createBlock,
} = wp.blocks;

const { 
  PanelBody,
  RadioControl,
  Icon,
} = wp.components;

const { 
  InspectorControls,
} = wp.blockEditor;

import { iconSwitch as blockicon } from '../main/icons.js'

registerBlockType( blockName, {
  title: __('Switch', 'formality'),
  description: __('Checkbox input, good for true/false answer or acceptance field.', 'formality'), 
  icon: blockicon,
  category: 'formality',
  attributes: {
    uid: { type: 'string', default: '' },
    name: { type: 'string', default: ''},
    label: { type: 'string', default: ''},
    placeholder: { type: 'string', default: ''},
    required: { type: 'boolean', default: false },
    halfwidth: { type: 'boolean', default: false },
    style: { type: 'string', default: 'switch' },
    value: { type: 'string', default: ''},
    rules: {
      type: 'string|array',
      attribute: 'rules',
      default: [],
    },
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
    let uid = props.attributes.uid
    let value = props.attributes.value
    let style = props.attributes.style
    let focus = props.isSelected
    let rules = props.attributes.rules

    return ([
      <InspectorControls>
        <PanelBody title={__('Field options', 'formality')}>
          { mainOptions(props, true, true) }
          <RadioControl
            label={__('Appearance', 'formality')}
            selected={ style }
            options={ [
              { label: 'Switch (default)', value: 'switch' },
              { label: 'Checkbox', value: 'checkbox' },
            ]}
            onChange={(value) => editAttribute(props, "style", value)}
          />
        </PanelBody>
        { advancedPanel(props) }
      </InspectorControls>
      ,
      <div
        className={ "formality__field formality__field--switch" + ( focus ? ' formality__field--focus' : '' ) + ( required ? ' formality__field--required' : '' ) + ( value ? ' formality__field--filled' : '' ) }
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
          <input
            type="checkbox"
            id={ uid }
            name={ uid }
            value="1"
            checked={value ? "checked" : ""}
          />
          <label
            className={"formality__label formality__label--" + style }
            htmlFor={ uid }
          >
            <i></i>
            <span>{ placeholder ? placeholder : __('Click to confirm', 'formality') }</span>
          </label>
        </div>
      </div>,
    ])
  }, 
  save () {
    return null
  },
});