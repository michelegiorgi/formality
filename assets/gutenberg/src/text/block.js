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


registerBlockType( 'formality/text', {
  title: __('Text', 'formality'),
  description: __('Standard text field, good for short answers and 1 line information', 'formality'), 
  icon: 'universal-access-alt',
  category: 'formality',
  attributes: {
    input_uid: { type: 'string', default: '' },
    input_name: { type: 'string', default: ''},
    input_label: { type: 'string', default: ''},
    input_placeholder: { type: 'string', default: ''},
    input_required: { type: 'boolean', default: false },
    input_halfwidth: { type: 'boolean', default: false },
  },
  edit(props) {
    let input_name = props.attributes.input_name
    let input_label = props.attributes.input_label
    let input_placeholder = props.attributes.input_placeholder
    let input_required = props.attributes.input_required
    let input_halfwidth = props.attributes.input_halfwidth
    let input_uid = props.attributes.input_uid
    let focus = props.isSelected
    if(!input_uid) {
      props.setAttributes({input_uid: ([1e7]+1e11).replace(/[018]/g, c => (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)) })
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
            label={ input_required ? __('This is a required field', 'formality') : __('This is a not required field', 'formality') }
            checked={ input_required }
            onChange={() => editAttribute("input_required", true, true )}
          />
          <BaseControl
            label={__('Width', 'formality')}
          >
            <ButtonGroup>
              <Button
                isPrimary={ input_halfwidth ? true : false }
                isDefault={ input_halfwidth ? false : true }
                onClick={() => editAttribute("input_halfwidth", true)}
              >{__('Half width', 'formality')}</Button>
              <Button
                isPrimary={ input_halfwidth ? false : true }
                isDefault={ input_halfwidth ? true : false }
                onClick={() => editAttribute("input_halfwidth", false)}
              >{__('Full width', 'formality')}</Button>
            </ButtonGroup>
          </BaseControl>
          <TextControl
            label={__('Name', 'formality')}
            value={input_name}
            onChange={(value) => editAttribute("input_name", value)}
          />
          <TextControl
            label={__('Label', 'formality')}
            help={__('Field name if empty', 'formality')}
            value={input_label}
            onChange={(value) => editAttribute("input_label", value)}
          />
          <TextControl
            label={__('Placeholder', 'formality')}
            help={__('Ex: "Type your answer here"', 'formality')}
            value={input_placeholder}
            onChange={(value) => editAttribute("input_placeholder", value)}
          />
        </PanelBody>
      </InspectorControls>
      ,
      <div
        class={ "formality__field formality__field--text formality__field--width2" + ( focus ? ' formality__field--focus' : '' ) + ( input_required ? ' formality__field--required' : '' ) }
      >
        <label
          class="formality__label"
          for={ input_uid }
        >
          { input_label ? input_label : ( input_name ? input_name : __('Field name', 'formality')) }
        </label>
        <div
          class="formality__input"
          data-placeholder={ input_placeholder ? input_placeholder : __('Type your answer here', 'formality') }
        >
          <input
            type="text"
            id={ input_uid }
            name={ input_uid }
            required=""
            placeholder={ input_placeholder ? input_placeholder : __('Type your answer here', 'formality') }
          />
        </div>
      </div>
    ])
  }, 
  save ( props ) {
    return null
  },
});