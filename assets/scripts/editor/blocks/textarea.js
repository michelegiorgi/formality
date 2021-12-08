/**
 * Formality block
 * Textarea
 */

const blockName = 'formality/textarea'

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

const { __ } = wp.i18n
const {
  registerBlockType,
  createBlock,
} = wp.blocks

const {
  PanelBody,
  PanelRow,
  TextControl,
  Icon,
} = wp.components

const {
  InspectorControls,
} = wp.blockEditor

import { iconTextarea as blockicon } from '../utility/icons.js'

export let textareaBlock = () => {

  registerBlockType( blockName, {
    title: __('Textarea', 'formality'),
    description: __('Multi-line area, good for texts or long answers.', 'formality'),
    icon: blockicon,
    category: 'formality',
    attributes: {
      uid: { type: 'string', default: '' },
      name: { type: 'string', default: ''},
      label: { type: 'string', default: ''},
      placeholder: { type: 'string', default: ''},
      required: { type: 'boolean', default: false },
      value: { type: 'string', default: ''},
      rows: { type: 'string', default: '3'},
      max_length: { type: 'string', default: '500'},
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
        transform: (attributes) => { return createBlock( blockName, attributes) },
      }],
    },
    edit(props) {

      checkUID(props)
      let { name, label, placeholder, required, uid, value, rules, preview, rows, max_length } = props.attributes
      let focus = props.isSelected
      if ( preview ) { return getPreview(props.name) }

      return ([
        <InspectorControls>
          <PanelBody title={__('Field options', 'formality')}>
            { mainOptions(props, false) }
            <PanelRow
              className='formality_panelrow'
            >
              <TextControl
                type='number'
                min='2'
                step='1'
                label={__('Rows', 'formality')}
                value={ rows }
                onChange={(value) => editAttribute(props, 'rows', value)}
              />
              <TextControl
                type='number'
                label={__('Max length', 'formality')}
                value={max_length}
                onChange={(value) => editAttribute(props, 'max_length', value)}
              />
            </PanelRow>
          </PanelBody>
          { advancedPanel(props) }
        </InspectorControls>
        ,
        <div
          className={ 'formality__field formality__field--textarea' + ( focus ? ' formality__field--focus' : '' ) + ( required ? ' formality__field--required' : '' ) + ( value ? ' formality__field--filled' : '' ) }
        >
          <div
            className='formality__label'
          >
            { inlineName(props) }
            <Icon icon={ hasRules(rules) ? 'hidden' : '' } />
          </div>
          <div
            className='formality__input'
          >
            <div
              className='formality__textarea__counter'
            >{ value.length + ' / ' + max_length }</div>
            <textarea
              placeholder={ placeholder ? placeholder : __('Type your answer here', 'formality') }
              rows={ rows }
              value={ value }
            ></textarea>
            <div className='formality__input__status' data-placeholder={ placeholder ? placeholder : __('Type your answer here', 'formality') }/>
          </div>
        </div>,
      ])
    },
    save () {
      return null
    },
  })

}
