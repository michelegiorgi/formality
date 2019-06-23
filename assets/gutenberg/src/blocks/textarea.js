/** 
 * Formality block
 * 
 */

const { __ } = wp.i18n;
const { 
  registerBlockType,
  createBlock,
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
  <g id="textarea" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
    <circle id="bg" fill="#EC407A" cx="12" cy="12" r="12"></circle>
    <g id="Group" transform="translate(5.000000, 9.000000)" fill="#FFFFFF">
      <rect id="Rectangle" x="0" y="0" width="8.55555556" height="2" rx="1"></rect>
      <rect id="Rectangle" x="3.88888889" y="3.88888889" width="10.1111111" height="2" rx="1"></rect>
      <rect id="Rectangle" x="10.1111111" y="0" width="3.88888889" height="2" rx="1"></rect>
      <rect id="Rectangle" x="0" y="3.88888889" width="2.33333333" height="2" rx="1"></rect>
    </g>
  </g>
</svg>
);

registerBlockType( 'formality/textarea', {
  title: __('Textarea', 'formality'),
  description: __('Standard text field, good for short answers and 1 line information', 'formality'), 
  icon: blockicon,
  category: 'formality',
  attributes: {
    uid: { type: 'string', default: '' },
    name: { type: 'string', default: ''},
    label: { type: 'string', default: ''},
    placeholder: { type: 'string', default: ''},
    required: { type: 'boolean', default: false },
    value: { type: 'string', default: ''},
  },
  supports: {
    html: false,
    customClassName: false,
  },
  transforms: {
    from: [{
      type: 'block',
      blocks: [ 'formality/text', 'formality/email', 'formality/select'  ],
      transform: function ( attributes ) { return createBlock( 'formality/textarea', attributes); },
    }]
  },
  edit(props) {
    let name = props.attributes.name
    let label = props.attributes.label
    let placeholder = props.attributes.placeholder
    let required = props.attributes.required
    let uid = props.attributes.uid
    let value = props.attributes.value
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
          <ToggleControl
            label={ required ? __('This is a required field', 'formality') : __('This is a not required field', 'formality') }
            checked={ required }
            onChange={() => editAttribute("required", true, true )}
          />
          <TextControl
            label={__('Label / Question', 'formality')}
            value={name}
            onChange={(value) => editAttribute("name", value)}
          />
          <TextControl
            label={__('Placeholder', 'formality')}
            help={__('Ex: "Type your answer here"', 'formality')}
            value={placeholder}
            onChange={(value) => editAttribute("placeholder", value)}
          />
        </PanelBody>
        <PanelBody
          title={__('Advanced', 'formality')}
          initialOpen={ false }
        >
          <TextControl
            label={__('Initial value', 'formality')}
            value={value}
            onChange={(value) => editAttribute("value", value)}
          />
          <TextControl
            label={__('Field ID/Name', 'formality')}
            value={uid}
            disabled
            help={__('You can set an initial variable value by using field ID as a query var. Ex: http://abc.com/form1/?', 'formality') + uid + '=test'}
          />
        </PanelBody>
      </InspectorControls>
      ,
      <div
        class={ "formality__field formality__field--textarea" + ( focus ? ' formality__field--focus' : '' ) + ( required ? ' formality__field--required' : '' ) + ( value ? ' formality__field--filled' : '' ) }
      >
        <label
          class="formality__label"
          for={ uid }
        >
          { name ? name : __('Field name', 'formality') }
        </label>
        <div
          class="formality__input"
          data-placeholder={ placeholder ? placeholder : __('Type your answer here', 'formality') }
        >
          <textarea
            type="text"
            id={ uid }
            name={ uid }
            placeholder={ placeholder ? placeholder : __('Type your answer here', 'formality') }
          >{ value }</textarea>
        </div>
      </div>
    ])
  }, 
  save ( props ) {
    return null
  },
});