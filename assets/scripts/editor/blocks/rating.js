/**
 * Formality block
 * Rating
 */

const blockName = 'formality/rating'

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
  SelectControl,
  Icon,
} = wp.components

const {
  InspectorControls,
} = wp.blockEditor

const {
  Fragment,
} = wp.element

import { iconRating as blockicon } from '../utility/icons.js'
import { glyphStar, glyphRhombus, glyphHeart } from '../utility/icons.js'

export let ratingBlock = () => {

  registerBlockType( blockName, {
    title: __('Rating', 'formality'),
    description: __('Ask your users for a rating. Score from one to ten.', 'formality'),
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
      icon: { type: 'string', default: 'star'},
      value_max: { type: 'string', default: '10'},
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
      let { name, label, placeholder, required, uid, value, rules, preview, icon, value_max } = props.attributes
      let focus = props.isSelected
      if ( preview ) { return getPreview(props.name) }

      let arrayrating = []
      let iconSvg = ''
      for (let radiovalue = 1; radiovalue <= value_max; radiovalue++) { arrayrating.push(radiovalue) }
      switch(icon) {
        case 'heart' : iconSvg = glyphHeart(uid); break;
        case 'star' : iconSvg = glyphStar(uid); break;
        case 'rhombus' : iconSvg = glyphRhombus(uid); break;
      }

      return ([
        <InspectorControls>
          <PanelBody title={__('Field options', 'formality')}>
            { mainOptions(props, true, true) }
            <PanelRow
              className='formality_panelrow formality_panelrow--half'
            >
              <SelectControl
                label={__('Icons', 'formality')}
                value={icon}
                options={[
                  { label: __('Stars', 'formality'), value: 'star' },
                  { label: __('Hearts', 'formality'), value: 'heart' },
                  { label: __('Rhombus', 'formality'), value: 'rhombus' },
                ]}
                onChange={(value) => editAttribute(props, 'icon', value)}
              />
              <TextControl
                type='number'
                label={__('Max value', 'formality')}
                value={value_max}
                onChange={(value) => editAttribute(props, 'value_max', value)}
              />
            </PanelRow>
          </PanelBody>
          { advancedPanel(props) }
        </InspectorControls>
        ,
        <div
          className={ 'fo__field fo__field--rating' + ( focus ? ' fo__field--focus' : '' ) + ( required ? ' fo__field--required' : '' ) + ( value ? ' fo__field--filled' : '' ) }
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
            <div className='fo__note'>{ placeholder }</div>
            {arrayrating.map(singlerating => {
              return <Fragment>
                  <input
                    type={ 'radio' }
                    value={ singlerating }
                    defaultChecked={ singlerating == value }
                    name={ uid }
                    id={ uid + '_' + singlerating  }
                  />
                  <label
                    className={ 'fo__label' }
                    htmlFor={ uid + '_' + singlerating  }
                  >
                    <svg width='36px' height='36px' viewBox='0 0 36 36' version='1.1' xmlns='http://www.w3.org/2000/svg'>
                      <defs>{ iconSvg }</defs>
                      <use href={ '#glyph_' + uid } className='border' x='0' y='0'/>
                      <use href={ '#glyph_' + uid } className='fill' x='0' y='0'/>
                    </svg>
                    { singlerating }
                  </label>
                </Fragment>
            })}
          </div>
        </div>,
      ])
    },
    save () {
      return null
    },
  })

}
