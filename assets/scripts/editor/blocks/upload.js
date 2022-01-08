/**
 * Formality block
 * Upload
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
  inlineName,
} from '../utility/blocks.js'

const { __ } = wp.i18n
const {
  registerBlockType,
  createBlock,
} = wp.blocks

const {
  PanelBody,
  Icon,
  RangeControl,
  BaseControl,
  CheckboxControl
} = wp.components

const {
  InspectorControls,
} = wp.blockEditor

import { iconUpload as blockicon } from '../utility/icons.js'

export let uploadBlock = () => {

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
      maxsize: { type: 'number', default: formality.upload_max > 3 ? 3 : formality.upload_max },
      formats: { type: 'string|array', default: ['jpg', 'jpeg', 'gif', 'png', 'pdf'], },
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
        transform: (attributes) => { return createBlock( blockName, attributes) },
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
            { mainOptions(props, false) }
            <RangeControl
              value={ maxsize }
              onChange={(val) => { props.setAttributes({maxsize: val }) }}
              min={ 1 }
              max={ formality.upload_max }
              label={ __( 'Size limit', 'formality' ) }
              help={ __( 'Max upload file size', 'formality' ) }
              className={ 'components-base-control--sizelimit' }
            />
            <BaseControl
              label={ __( 'Allowed types', 'formality' ) }
              help={ __( 'Enable/disable file formats', 'formality' ) }
              className={ 'components-base-control--formats' }
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
                    filtered = formats.filter((value, index, arr) => { return value !== format })
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
          className={ 'fo__field fo__field--upload' + ( focus ? ' fo__field--focus' : '' ) + ( required ? ' fo__field--required' : '' ) + ( value ? ' fo__field--filled' : '' ) }
        >
          <div
            className='fo__label'
          >
            { inlineName(props) }
            <Icon icon={ hasRules(rules) ? 'hidden' : '' } />
          </div>
          <div
            className='fo__input'
          >
            <input
              type='file'
              id={ uid }
              name={ uid }
              value={value}
            />
            <label class='fo__upload'>
              <div class='fo__upload__toggle'>
                <p>{ placeholder ? placeholder : __('Choose file or drag here', 'formality') }</p>
                <span>
                  { __('Size limit', 'formality') }
                  <strong>{ maxsize + 'MB' }</strong>
                </span>
                <span>
                  { __('Allowed types', 'formality') }
                  <strong>{ formats.join(', ') }</strong>
                </span>
              </div>
            </label>
            <div className='fo__input__status' data-placeholder={ placeholder ? placeholder : __('Choose file or drag here', 'formality') }/>
          </div>
        </div>,
      ])
    },
    save () {
      return null
    },
  })

}
