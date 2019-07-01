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
    <g id="Group" transform="translate(5.000000, 6.500000)" fill="#FFFFFF">
      <path d="M7.63196692,2 L12.811111,2 C13.3633957,2 13.811111,2.44771525 13.811111,3 C13.811111,3.55228475 13.3633957,4 12.811111,4 L6.7,4 C6.40654976,4 6.14262165,3.87360068 5.95969028,3.67227663 L7.63196692,2 Z" id="Combined-Shape"></path>
      <path d="M6.7,7.4 L12.811111,7.4 C13.3633957,7.4 13.811111,7.84771525 13.811111,8.4 L13.811111,8.4 C13.811111,8.95228475 13.3633957,9.4 12.811111,9.4 L6.7,9.4 C6.14771525,9.4 5.7,8.95228475 5.7,8.4 L5.7,8.4 C5.7,7.84771525 6.14771525,7.4 6.7,7.4 Z" id="Rectangle"></path>
      <rect id="Rectangle" x="1" y="6.6" width="3.4" height="3.4" rx="1.5"></rect>
      <path d="M3.16861775,4.98076629 C2.85125052,5.29813352 2.33629461,5.29813352 2.01908055,4.98076629 L0.238025426,3.19971117 C-0.0793418087,2.8824971 -0.0793418087,2.36754119 0.238025426,2.05032713 C0.555239491,1.73295989 1.0701954,1.73295989 1.38756264,2.05032713 L2.44872103,3.11133236 C2.52882869,3.19128684 2.65886961,3.19128684 2.73913043,3.11133236 L5.61243736,0.238025426 C5.92965143,-0.0793418087 6.44460734,-0.0793418087 6.76197457,0.238025426 C6.91437824,0.390429093 7,0.597207939 7,0.812717446 C7,1.02822695 6.91437824,1.2350058 6.76197457,1.38740947 L3.16861775,4.98076629 Z" id="Path"></path>
    </g>
  </g>
</svg>
);

registerBlockType( 'formality/step', {
  title: __('Step', 'formality'),
  description: __('Group your fields into multiple sections, with custom heading.', 'formality'), 
  icon: blockicon,
  category: 'formality_nav',
  attributes: {
    uid: { type: 'string', default: '' },
    name: { type: 'string', default: ''},
    description: { type: 'string', default: ''},
    exclude: { type: 'integer', default: 1},
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