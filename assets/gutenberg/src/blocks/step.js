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
  <g id="step" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
    <circle id="bg" fill="#AAAAAA" cx="12" cy="12" r="12"></circle>
    <path d="M12,19 C8.13400675,19 5,15.8659932 5,12 C5,8.13400675 8.13400675,5 12,5 C15.8659932,5 19,8.13400675 19,12 C19,15.8659932 15.8659932,19 12,19 Z M13.2751385,15.7986667 L13.2751385,7.72222222 L11.651637,7.72222222 C11.4742797,8.64993734 10.5738504,9.37300942 9.27777778,9.38665229 L9.27777778,10.6554391 L11.4060654,10.6554391 L11.4060654,15.7986667 L13.2751385,15.7986667 Z" id="Combined-Shape" fill="#FFFFFF"></path>
  </g>
</svg>
);

registerBlockType( 'formality/step', {
  title: __('Step', 'formality'),
  description: __('Standard text field, good for short answers and 1 line information', 'formality'), 
  icon: blockicon,
  category: 'formality',
  attributes: {
    uid: { type: 'string', default: '' },
    name: { type: 'string', default: ''},
    description: { type: 'string', default: ''},
  },
  supports: {
    html: false,
    customClassName: false,
  },
  edit(props) {
    let name = props.attributes.name
    let description = props.attributes.description
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
        <PanelBody title={__('Field options', 'formality')}>
          <TextControl
            label={__('Step title', 'formality')}
            value={name}
            onChange={(value) => editAttribute("name", value)}
          />
          <TextControl
            label={__('Description', 'formality')}
            value={description}
            onChange={(value) => editAttribute("description", value)}
          />
        </PanelBody>
      </InspectorControls>
      ,
      <div
        class="formality__section__header"
      >
        <h4>
          { name ? name : __('Step name', 'formality') }
        </h4>
        <p>
          { description }
        </p>
      </div>
    ])
  }, 
  save ( props ) {
    return null
  },
});