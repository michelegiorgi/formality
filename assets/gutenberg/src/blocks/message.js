/** 
 * Formality block
 * 
 */

const { __ } = wp.i18n;
const { 
  registerBlockType,
  source
} = wp.blocks;

const { 
  ColorPalette,
  PanelBody,
  PanelRow,
  Button,
  TextControl,
  ToggleControl,
  ButtonGroup,
  BaseControl
} = wp.components;

const { 
  RichText,
  MediaUpload,
  InspectorControls
} = wp.editor;


registerBlockType( 'formality/message', {
  title: __('Message', 'formality'),
  description: __('Custom message', 'formality'), 
  icon: 'universal-access-alt',
  category: 'formality',
  attributes: {
    uid: { type: 'string', default: '' },
    text: { type: 'string', default: ''}
  },
  supports: {
    html: false,
    customClassName: false,
  },
  edit(props) {
    let text = props.attributes.text
    let uid = props.attributes.uid
    let focus = props.isSelected
    if(!uid) {
      props.setAttributes({uid: ([1e7]+1e11).replace(/[018]/g, c => (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)) })
    }
    
    function editAttribute(key, value, toggle = false) {
      let tempArray = {}
      if(toggle){ value = props.attributes[key] ? false : true }
      tempArray[key] = value
      props.setAttributes(tempArray)
    }          

    return ([
      <InspectorControls>
      </InspectorControls>
      ,
      <div
        class="formality__message"
      >
        <RichText
          tagName="p"
          value={text}
          onChange={(value) => editAttribute("text", value)}
          placeholder="Enter your text here!"
        />
      </div>
    ])
  }, 
  save ( props ) {
    return null
  },
});