/**
 * Formality block
 *
 */

const blockName = 'formality/switch'

import React from 'react'

import {
  checkUID,
  getPreview,
  editAttribute,
  getBlockTypes,
  mainOptions,
  advancedPanel,
  hasRules,
  inlineName,
} from '../utility/blocks.js'

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

import { iconSwitch as blockicon } from '../utility/icons.js'

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
    rules: { type: 'string|array', attribute: 'rules', default: [], },
    dbg: { type: 'string|object', default: {}, },
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
    let { name, label, placeholder, required, uid, value, rules, preview, style } = props.attributes
    let focus = props.isSelected
    if ( preview ) { return getPreview(props.name) }

    return ([
      <InspectorControls>
        <PanelBody title={__('Field options', 'formality')}>
          { mainOptions(props, true, true) }
          <RadioControl
            label={__('Appearance', 'formality')}
            selected={ style }
            options={ [
              { label: __('Switch (default)', 'formality'), value: 'switch' },
              { label: __('Checkbox', 'formality'), value: 'checkbox' },
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
        <div
          className="formality__label"
        >
          { inlineName(props) }
          <Icon icon={ hasRules(rules) ? "hidden" : "" } />
        </div>
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
