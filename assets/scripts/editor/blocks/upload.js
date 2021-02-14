/**
 * Formality block
 *
 */

const blockName = 'formality/upload'

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
  RangeControl,
  BaseControl,
  CheckboxControl
} = wp.components;

const {
  InspectorControls,
} = wp.blockEditor;

import { iconUpload as blockicon } from '../utility/icons.js'

registerBlockType( blockName, {
  title: __('Upload', 'formality'),
  description: __('Let your users upload files to your form', 'formality'),
  icon: blockicon,
  category: 'formality',
  attributes: {
    uid: { type: 'string', default: '' },
    name: { type: 'string', default: ''},
    label: { type: 'string', default: ''},
    placeholder: { type: 'string', default: ''},
    required: { type: 'boolean', default: false },
    halfwidth: { type: 'boolean', default: false },
    maxsize: { type: 'number', default: 3},
    formats: { type: 'string|array', default: [], },
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
    let { name, label, placeholder, required, uid, value, rules, preview, maxsize, formats } = props.attributes
    let focus = props.isSelected
    const wpformats = formality.upload_formats
    if(preview) { return getPreview(props.name) }

    return ([
      <InspectorControls>
        <PanelBody title={__('Field options', 'formality')}>
          { mainOptions(props) }
          <RangeControl
            value={ maxsize }
            onChange={(val) => { props.setAttributes({maxsize: val }) }}
            min={ 1 }
            max={ formality.upload_max }
            label={ __( 'Max size', 'formality' ) }
            help={ __( "nkjdsnjkfkdjsn", 'formality' ) }
          />
          <BaseControl
            label={ __( 'Allowed formats', 'formality' ) }
            help={ __( "Enable/disable file formats", 'formality' ) }
          >
          { wpformats.map((format) => (
            <CheckboxControl
              label={ format }
              checked={ formats.includes(format) }
              onChange={(check) => {
                let filtered = [...formats]
                if(check) {
                  filtered.push(format)
                } else {
                  filtered = formats.filter(function(value, index, arr){ return value !== format; });
                }
                props.setAttributes({ formats: filtered })
              }}
            />
          ))}
          </BaseControl>
        </PanelBody>
        { advancedPanel(props) }
      </InspectorControls>
      ,
      <div
        className={ "formality__field formality__field--text" + ( focus ? ' formality__field--focus' : '' ) + ( required ? ' formality__field--required' : '' ) + ( value ? ' formality__field--filled' : '' ) }
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
