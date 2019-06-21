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

const blockicon = () => (
<svg width="24px" height="24px" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg">
  <g id="message" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
    <circle id="bg" fill="#AAAAAA" cx="12" cy="12" r="12"></circle>
    <path d="M4.5,16.0115647 L8.10972799,7 L9.32147413,7 L12.9120693,16.0115647 L11.1646038,16.0115647 L10.4439337,14.0536381 L6.99364601,14.0536381 L6.26659832,16.0115647 L4.5,16.0115647 Z M7.50385491,12.6696964 L9.93372481,12.6696964 L8.72197867,9.37884901 L7.50385491,12.6696964 Z M16.7703824,16.139117 C16.1071108,16.139117 15.3991959,15.9222782 14.9527632,15.3738036 L14.9527632,16.0115647 L13.3456051,16.0115647 L13.3456051,7 L14.9527632,7 L14.9527632,10.2780922 C15.3991959,9.72961763 16.0624675,9.50640123 16.7448719,9.50640123 C18.5369807,9.50640123 19.5,11.0497832 19.5,12.8227591 C19.5,14.5893574 18.5369807,16.139117 16.7703824,16.139117 Z M16.3813481,14.7041544 C17.4527868,14.7041544 17.9311076,13.8814426 17.9311076,12.8227591 C17.9311076,11.7640756 17.4527868,10.9158533 16.3813481,10.9158533 C15.3099094,10.9158533 14.8124557,11.7513204 14.8124557,12.8227591 C14.8124557,13.8941978 15.3099094,14.7041544 16.3813481,14.7041544 Z" id="Ab" fill="#FFFFFF"></path>
  </g>
</svg>
);

registerBlockType( 'formality/message', {
  title: __('Message', 'formality'),
  description: __('Custom message', 'formality'), 
  icon: blockicon,
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