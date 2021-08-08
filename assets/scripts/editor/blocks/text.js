/**
 * Formality block
 *
 */

const blockName = 'formality/text'

import React from 'react'

import {
  checkUID,
  getPreview,
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
  Icon,
} = wp.components;

const {
  InspectorControls,
} = wp.blockEditor;

import { iconText as blockicon } from '../utility/icons.js'

export function textBlock() {

  registerBlockType( blockName, {
    title: __('Text', 'formality'),
    description: __('Standard text field, good for short answers and 1 line information.', 'formality'),
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
      rules: { type: 'string|array', attribute: 'rules', default: [], },
      dbg: { type: 'string|object', default: {}, },
      preview: { type: 'boolean', default: false },
    },
    supports: {
      html: false,
      customClassName: false,
    },
    example: { attributes: { preview: true } },
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
          className={ "formality__field formality__field--text" + ( focus ? ' formality__field--focus' : '' ) + ( required ? ' formality__field--required' : '' ) + ( value ? ' formality__field--filled' : '' ) }
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
              tabindex={ "-1" }
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
}
