/**
 * Formality block
 *
 */

const blockName = 'formality/multiple'

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

import { iconMultiple as blockicon } from '../utility/icons.js'

const {
  __,
  sprintf,
} = wp.i18n;

const {
  registerBlockType,
  createBlock,
} = wp.blocks;

const {
  PanelBody,
  PanelRow,
  TextControl,
  ToggleControl,
  BaseControl,
  RepeaterControl,
  RangeControl,
  RadioControl,
  Icon,
} = wp.components;

const {
  InspectorControls,
} = wp.blockEditor;

const {
  Fragment,
} = wp.element;

registerBlockType( blockName, {
  title: __('Multiple choice', 'formality'),
  description: __('Checkbox grid with all available options that users can select.', 'formality'),
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
    style: { type: 'string', default: 'default' },
    rules: { type: 'string|array', attribute: 'rules', default: [], },
    dbg: { type: 'string|object', default: {}, },
    options: { type: 'string|array', attribute: 'options', default: [], },
    option_labels: { type: 'boolean', default: false },
    single: { type: 'boolean', default: false },
    option_grid: { type: 'integer', default: 2 },
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
    let { name, label, placeholder, required, uid, value, rules, preview, options, option_labels, option_grid, single, style } = props.attributes
    let focus = props.isSelected
    if ( preview ) { return getPreview(props.name) }

    if(!option_grid && style=="default") { option_grid = 1; }

    return ([
      <InspectorControls>
        <PanelBody title={__('Field options', 'formality')}>
          { mainOptions(props, true, true) }
          <label
            className="components-base-control__label"
          >{__('Options', 'formality')}</label>
          <RepeaterControl
            addText={__('Add option', 'formality')}
            removeOnEmpty={true}
            value={options}
            onChange={(val) => { props.setAttributes({options: val}); }}
          >{(value, onChange) => {
            return [
              <TextControl
                placeholder={__('Value', 'formality')}
                value={value.value}
                onChange={(v) => { value.value = v; onChange(value)}}
              />,
              <TextControl
                className={ option_labels ? "" : "components-base-control--hide" }
                placeholder={__('Label', 'formality')}
                value={value.label}
                onChange={(v) => { value.label = v; onChange(value) }}
              />,
            ]
          }}</RepeaterControl>
          <ToggleControl
            label={ __('Assign different values and labels for each option', 'formality') }
            checked={ option_labels }
            onChange={() => editAttribute(props, "option_labels", true, true )}
          />
          <ToggleControl
            label={ __('Allow only single selection', 'formality') }
            checked={ single }
            onChange={() => editAttribute(props, "single", true, true )}
          />
          <RadioControl
            label={__('Appearance', 'formality')}
            selected={ style }
            options={ [
              { label: __('Radio/checkbox (default)', 'formality'), value: 'default' },
              { label: __('Buttons', 'formality'), value: 'buttons' },
            ]}
            onChange={(value) => editAttribute(props, "style", value)}
          />
          <PanelRow
            className="formality_optionsgrid"
          >
            <BaseControl
              label={ __( 'Options grid', 'formality' ) }
              help={ option_grid ? sprintf( /* translators: option grid columns */ __( 'Distribute options in %s columns.', 'formality' ), option_grid) : __( 'Display inline options.', 'formality' ) }
            >
              <RangeControl
                value={ option_grid }
                onChange={(val) => { props.setAttributes({option_grid: val}); }}
                min={ style == "buttons" ? 0 : 1 }
                max={ 3 }
                //beforeIcon="arrow-left"
                //afterIcon="arrow-right"
              />
            </BaseControl>
          </PanelRow>
        </PanelBody>
        { advancedPanel(props) }
      </InspectorControls>
      ,
      <div
        className={ "formality__field formality__field--multiple" + ( focus ? ' formality__field--focus' : '' ) + ( required ? ' formality__field--required' : '' ) + ( value ? ' formality__field--filled' : '' ) }
      >
        <div
          className="formality__label"
        >
          { inlineName(props) }
          <Icon icon={ hasRules(rules) ? "hidden" : "" } />
        </div>
        <div
          className="formality__input"
          data-placeholder={ placeholder ? placeholder : __('Select your choice', 'formality') }
        >
          <div className="formality__note">{ placeholder }</div>
          <div className={ "formality__input__grid formality__input__grid--" + style + " formality__input__grid--" + option_grid }>
            {options.map(option => {
              return <Fragment>
                <input
                  type={ single ? "radio" : "checkbox" }
                  value={ option["value"] }
                  defaultChecked={ option["value"] == value }
                  name={ uid + ( (!single) ? "[]" : "" ) }
                  id={ uid + "_" + option["_key"]  }
                />
                <label
                  className={ "formality__label formality__label--" + (single ? "radio" : "checkbox") }
                  htmlFor={ uid + "_" + option["_key"]  }
                >
                  <i></i><span>{ option["label"] ? option["label"] : option["value"] }</span>
                </label>
              </Fragment>
            })}
          </div>
        </div>
      </div>,
    ])
  },
  save () {
    return null
  },
});
