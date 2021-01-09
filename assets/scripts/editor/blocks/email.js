/**
 * Formality block
 *
 */

const blockName = 'formality/email'

import React from 'react'

import {
  checkUID,
  getPreview,
  getBlockTypes,
  mainOptions,
  advancedPanel,
  hasRules,
} from '../utility/blocks.js'

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

import { iconEmail as blockicon } from '../utility/icons.js'

registerBlockType( blockName, {
  title: __('E-mail', 'formality'),
  description: __('Text field that accepts only valid email address.', 'formality'),
  icon: blockicon,
  category: 'formality',
  attributes: {
    uid: { type: 'string', default: '' },
    name: { type: 'string', default: ''},
    label: { type: 'string', default: ''},
    halfwidth: { type: 'boolean', default: false },
    placeholder: { type: 'string', default: ''},
    required: { type: 'boolean', default: false },
    value: { type: 'string', default: ''},
    rules: {
      type: 'string|array',
      attribute: 'rules',
      default: [],
    },
    dbg: {
      type: 'string|array',
      attribute: 'dbg',
      default: [],
    },
    preview: { type: 'boolean', default: false },
  },
  example: { attributes: { preview: true } },
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
    let { name, label, placeholder, required, uid, value, rules, preview } = props.attributes
    let focus = props.isSelected
    if ( preview ) { return getPreview(props.name) }

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
