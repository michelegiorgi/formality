/**
 * Formality block
 *
 */

const blockName = 'formality/number'

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
  PanelRow,
  TextControl,
  Icon,
} = wp.components;

const {
  InspectorControls,
} = wp.blockEditor;

import { iconNumber as blockicon } from '../utility/icons.js'

export function numberBlock() {

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
      rules: { type: 'string|array', attribute: 'rules', default: [], },
      dbg: { type: 'string|object', default: {}, },
      value_min: { type: 'string', default: ''},
      value_max: { type: 'string', default: ''},
      value_step: { type: 'string', default: '1'},
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
      let { name, label, placeholder, required, uid, value, rules, preview, value_min, value_max, value_step } = props.attributes
      let focus = props.isSelected
      if ( preview ) { return getPreview(props.name) }

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
