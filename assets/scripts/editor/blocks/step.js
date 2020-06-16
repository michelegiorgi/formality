/** 
 * Formality block
 * 
 */

const blockName = 'formality/step'

import React from 'react'

import {
  checkUID,
  getPreview,
  editAttribute,
} from '../main/utility.js'

const { __ } = wp.i18n;
const { 
  registerBlockType,
} = wp.blocks;

const { 
  PanelBody,
  TextControl,
} = wp.components;

const { 
  InspectorControls,
} = wp.blockEditor;

import { iconStep as blockicon } from '../main/icons.js'

registerBlockType( blockName, {
  title: __('Step', 'formality'),
  description: __('Group your fields into multiple sections, with custom heading.', 'formality'), 
  icon: blockicon,
  category: 'formality_nav',
  attributes: {
    uid: { type: 'string', default: '' },
    name: { type: 'string', default: ''},
    description: { type: 'string', default: ''},
    exclude: { type: 'integer', default: 99},
    preview: { type: 'boolean', default: false },
  },
  example: { attributes: { preview: true } },
  supports: {
    html: false,
    customClassName: false,
  },
  edit(props) {
    
    checkUID(props, 1)
    let { name, description, preview } = props.attributes
    if ( preview ) { return getPreview(props.name) }

    return ([
      <InspectorControls>
        <PanelBody title={__('Field options', 'formality')}>
          <p>{ __('Place this block before the first field you want to group. This step section will be closed automatically before the next step block (or at the end of the form).', 'formality')}<br/><hr/></p>
          <TextControl
            label={__('Step title', 'formality')}
            placeholder={__('Step name', 'formality')}
            value={name}
            onChange={(value) => editAttribute(props, "name", value)}
          />
          <TextControl
            label={__('Description', 'formality')}
            value={description}
            onChange={(value) => editAttribute(props, "description", value)}
          />
        </PanelBody>
      </InspectorControls>
      ,
      <div
        className="formality__section__header"
      >
        <h4>{ name ? name : __('Step name', 'formality') }</h4>
        <p>{ description }</p>
      </div>,
    ])
  }, 
  save () {
    return null
  },
});