/** 
 * Formality block
 * 
 */

const blockName = 'formality/email'

import React from 'react'

import {
  checkUID,
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
  Icon,
} = wp.components;

const { 
  InspectorControls,
} = wp.blockEditor;

import { iconEmail as blockicon } from '../main/icons.js'

registerBlockType( blockName, {
  title: __('E-mail', 'formality'),
  description: __('Text field that accepts only valid email address.', 'formality'), 
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
    let rules = props.attributes.rules
    let focus = props.isSelected

    return ([
      <InspectorControls>
        <PanelBody title={__('Field options', 'formality')}>
          { mainOptions(props) }
        </PanelBody>
        { advancedPanel(props) }
      </InspectorControls>
      ,
      <div
        className={ "formality__field formality__field--email" + ( focus ? ' formality__field--focus' : '' ) + ( required ? ' formality__field--required' : '' ) + ( value ? ' formality__field--filled' : '' ) }
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
            type="text"
            id={ uid }
            name={ uid }
            value={value}
            placeholder={ placeholder ? placeholder : __('Type your answer here', 'formality') }
          />
          <div className="formality__input__status" data-placeholder={ placeholder ? placeholder : __('Type your answer here', 'formality') }/>
        </div>
      </div>,
    ])
  }, 
  save () {
    return null
  },
});