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
    <path d="M4,16.1123357 L7.85037652,6.5 L9.14290574,6.5 L12.9728739,16.1123357 L11.1089107,16.1123357 L10.340196,14.0238806 L6.65988908,14.0238806 L5.88437155,16.1123357 L4,16.1123357 Z M7.20411191,12.5476762 L9.79597313,12.5476762 L8.50344391,9.03743894 L7.20411191,12.5476762 Z M17.0884079,16.2483914 C16.3809182,16.2483914 15.625809,16.0170967 15.149614,15.4320572 L15.149614,16.1123357 L13.4353121,16.1123357 L13.4353121,6.5 L15.149614,6.5 L15.149614,9.99663168 C15.625809,9.41159214 16.3332987,9.17349465 17.0611967,9.17349465 C18.9727794,9.17349465 20,10.8197687 20,12.710943 C20,14.5953146 18.9727794,16.2483914 17.0884079,16.2483914 Z M16.673438,14.7177647 C17.8163059,14.7177647 18.3265148,13.8402054 18.3265148,12.710943 C18.3265148,11.5816807 17.8163059,10.6769102 16.673438,10.6769102 C15.53057,10.6769102 14.9999528,11.5680751 14.9999528,12.710943 C14.9999528,13.853811 15.53057,14.7177647 16.673438,14.7177647 Z" id="Ab" fill="#FFFFFF"></path>
  </g>
</svg>
);

registerBlockType( 'formality/message', {
  title: __('Message', 'formality'),
  description: __('Custom message/information for your users.', 'formality'), 
  icon: blockicon,
  category: 'formality_nav',
  attributes: {
    uid: { type: 'string', default: '' },
    text: { type: 'string', default: ''},
    exclude: { type: 'integer', default: 2},
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