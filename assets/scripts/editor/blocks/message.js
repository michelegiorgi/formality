/**
 * Formality block
 *
 */

const blockName = 'formality/message'

import React from 'react'

import {
  checkUID,
  getPreview,
  editAttribute,
  advancedPanel,
  hasRules,
} from '../utility/blocks.js'

const { __ } = wp.i18n;
const {
  registerBlockType,
} = wp.blocks;

const {
  Icon,
} = wp.components;

const {
  RichText,
  InspectorControls,
} = wp.blockEditor;

import { iconMessage as blockicon } from '../utility/icons.js'

registerBlockType( blockName, {
  title: __('Message', 'formality'),
  description: __('Custom message/information for your users.', 'formality'),
  icon: blockicon,
  category: 'formality_nav',
  attributes: {
    //uid: { type: 'string', default: '' },
    text: { type: 'string', default: ''},
    exclude: { type: 'integer', default: 99},
    rules: { type: 'string|array', attribute: 'rules', default: [], },
    preview: { type: 'boolean', default: false },
  },
  example: { attributes: { preview: true } },
  supports: {
    html: false,
    customClassName: false,
  },
  edit(props) {

    checkUID(props, 2)
    let { text, rules, preview } = props.attributes
    if ( preview ) { return getPreview(props.name) }

    return ([
      <InspectorControls>
        { advancedPanel(props, false) }
      </InspectorControls>,
      <div
        className="formality__message"
      >
        <Icon icon={ hasRules(rules) ? "hidden" : "" } />
        <RichText
          tagName="p"
          value={text}
          keepPlaceholderOnFocus={ true }
          onChange={(value) => editAttribute(props, "text", value)}
          placeholder={__('Enter your text here!', 'formality')}
        />
      </div>,
    ])
  },
  save () {
    return null
  },
});
