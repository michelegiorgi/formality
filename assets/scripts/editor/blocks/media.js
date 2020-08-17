/** 
 * Formality block
 * 
 */

const blockName = 'formality/media'

import React from 'react'

import {
  checkUID,
  getPreview,
  //editAttribute,
  editAttributeMedia,
  advancedPanel,
  hasRules,
} from '../utility/blocks.js'

const { __ } = wp.i18n;
const { 
  registerBlockType,
} = wp.blocks;

const { 
  Icon,
  Button,
  BaseControl,
  PanelBody,
} = wp.components;

const {
  InspectorControls,
  MediaUpload,
} = wp.blockEditor;

const {
  Fragment,
} = wp.element;

import { iconMedia as blockicon } from '../utility/icons.js'
import { iconPlay } from '../utility/icons.js'

registerBlockType( blockName, {
  title: __('Media', 'formality'),
  description: __('Hero image, how-to video or any type of visual content for your users.', 'formality'), 
  icon: blockicon,
  category: 'formality_nav',
  attributes: {
    //uid: { type: 'string', default: '' },
    media: { type: 'string', default: ''},
    media_type: { type: 'string', default: ''},
    media_id: { type: 'integer', default: 0},
    exclude: { type: 'integer', default: 99},
    rules: {
      type: 'string|array',
      attribute: 'rules',
      default: [],
    },
    preview: { type: 'boolean', default: false },
  },
  example: { attributes: { preview: true } },
  supports: {
    html: false,
    customClassName: false,
  },
  edit(props) {

    checkUID(props, 2)
    let { media, media_id, media_type, rules, preview } = props.attributes
    if ( preview ) { return getPreview(props.name) }

    return ([
      <InspectorControls>
        <PanelBody
          title={__('Media options', 'formality')}
          initialOpen={ true }
        >
          <BaseControl
            label={ __( 'Media file', 'formality' ) }
            help={ __( "Select an image or video", 'formality' ) }
          >
            <MediaUpload
              onSelect={(file) => editAttributeMedia(props, "media", file, true)}
              allowedTypes={ [ 'image', 'video' ] }
              value={ media_id }
              render={({ open }) => (
                <Fragment>
                  <Button
                    className={ media ? 'editor-post-featured-image__preview' : 'editor-post-featured-image__toggle' }
                    onClick={ open }
                    aria-label={ ! media ? null : __( 'Edit or update media file', 'formality' ) }>
                    { media ? ( media_type == 'video' ? <video><source src={ media } type="video/mp4"/></video> : <img src={ media } alt="" /> ) : ''}
                    { media ? '' : __('Select or upload media', 'formality' ) }
                  </Button>
                  { media ? <Button onClick={() => editAttributeMedia(props, "media", '', true)} isLink isDestructive>{ __('Remove media', 'formality' )}</Button> : ''}
                </Fragment>
              )}
            />
          </BaseControl>
        </PanelBody>
        { advancedPanel(props, false) }
      </InspectorControls>,
      <div
        className="formality__media"
      >
        <Icon icon={ hasRules(rules) ? "hidden" : "" } />
        { media ? ( media_type == "video" ? <fragment><video><source src={ media } type="video/mp4"/></video><a href="#">{ iconPlay }</a></fragment> : <img src={ media } alt="" /> ) : __('Please select an image or video, from the right sidebar.', 'formality') }
      </div>,
    ])
  }, 
  save () {
    return null
  },
});