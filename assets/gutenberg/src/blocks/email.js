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
  <g id="email" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
    <circle id="bg" fill="#7E57C2" cx="12" cy="12" r="12"></circle>
    <path d="M14.0833333,8.48333333 L15.35,8.48333333 L14.95,12.7 C14.9333333,12.8333333 14.9333333,12.9833333 14.9333333,12.9833333 C14.9333333,13.4333333 15.2,13.6333333 15.5833333,13.6333333 C16.55,13.6333333 16.8333333,12.5 16.8333333,11.1 C16.8333333,7.8 15.1166667,6.53333333 12.2333333,6.53333333 C9.71666667,6.53333333 7.25,8.28333333 7.25,12 C7.25,15.3833333 9.71666667,17 12.2166667,17 C13.3833333,17 14.4833333,17 16.2333333,16.1166667 L16.8166667,17.5333333 C15.1,18.3 14.2333333,18.5333333 12.2166667,18.5333333 C8.26666667,18.5333333 5.5,16.0333333 5.5,11.95 C5.5,7.66666667 8.36666667,5 12.3166667,5 C16.3166667,5 18.5,7.28333333 18.5,11.1 C18.5,13.75 17.3,15.1666667 15.5166667,15.1666667 C14.6833333,15.1666667 13.9166667,14.7333333 13.6333333,14.05 C13.1333333,14.8166667 12.3666667,15.1666667 11.4666667,15.1666667 C9.55,15.1666667 8.55,13.7666667 8.55,12.05 C8.55,9.96666667 9.98333333,8.36666667 12.1,8.36666667 C12.9666667,8.36666667 13.55,8.66666667 13.9,9.2 L14.0833333,8.48333333 Z M11.9833333,9.9 C10.8833333,9.9 10.1833333,10.7833333 10.1833333,12.1 C10.1833333,12.95 10.6666667,13.6333333 11.5833333,13.6333333 C12.9833333,13.6333333 13.4666667,12.4666667 13.4666667,11.4333333 C13.4666667,10.4166667 12.8666667,9.9 11.9833333,9.9 Z" id="@" fill="#FFFFFF"></path>
  </g>
</svg>
);

registerBlockType( 'formality/email', {
  title: __('E-mail', 'formality'),
  description: __('Standard text field, good for short answers and 1 line information', 'formality'), 
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
  },
  supports: {
    html: false,
    customClassName: false,
  },
  transforms: {
    from: [{
      type: 'block',
      blocks: [ 'formality/text', 'formality/select', 'formality/textarea'  ],
      transform: function ( attributes ) { return createBlock( 'formality/email', attributes); },
    }]
  },
  edit(props) {
    let name = props.attributes.name
    let label = props.attributes.label
    let placeholder = props.attributes.placeholder
    let required = props.attributes.required
    let halfwidth = props.attributes.halfwidth
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
          <BaseControl
            label={__('Width', 'formality')}
          >
            <ButtonGroup>
              <Button
                isPrimary={ halfwidth ? true : false }
                isDefault={ halfwidth ? false : true }
                onClick={() => editAttribute("halfwidth", true)}
              >{__('Half width', 'formality')}</Button>
              <Button
                isPrimary={ halfwidth ? false : true }
                isDefault={ halfwidth ? true : false }
                onClick={() => editAttribute("halfwidth", false)}
              >{__('Full width', 'formality')}</Button>
            </ButtonGroup>
          </BaseControl>
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
        class={ "formality__field formality__field--email" + ( focus ? ' formality__field--focus' : '' ) + ( required ? ' formality__field--required' : '' ) + ( value ? ' formality__field--filled' : '' ) }
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
          <input
            type="text"
            id={ uid }
            name={ uid }
            value={value}
            placeholder={ placeholder ? placeholder : __('Type your answer here', 'formality') }
          />
        </div>
      </div>
    ])
  }, 
  save ( props ) {
    return null
  },
});