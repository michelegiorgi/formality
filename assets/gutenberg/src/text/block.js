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
  title: __('Text'),
  description: __('Standard text field, good for short answers and 1 line information'), 
  icon: 'universal-access-alt',
  category: 'formality',
  attributes: {
    input_uid: {
      type: 'string',
      default: ''
    },
    input_name: { 
      type: 'string',
      default: ''
    },            
    input_label: { 
      type: 'string',
      default: ''
    },
    input_placeholder: { 
      type: 'string',
      default: ''
    },
    input_required: { 
      type: 'boolean',
      default: false
    },
    input_halfwidth: { 
      type: 'boolean',
      default: false
    },            
  },
  edit(props) {
    var input_name = props.attributes.input_name
    var input_label = props.attributes.input_label
    var input_placeholder = props.attributes.input_placeholder
    var input_required = props.attributes.input_required
    var input_halfwidth = props.attributes.input_halfwidth
    var input_uid = props.attributes.input_uid
    var focus = props.isSelected
    if(!input_uid) {
      props.setAttributes({input_uid: ([1e7]+1e11).replace(/[018]/g, c => (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)) })
    }

    function onChangeInputName ( content ) {
      props.setAttributes({input_name: content})
    } 

    function onChangeInputLabel ( content ) {
      props.setAttributes({input_label: content})
    } 

    function onChangeInputPlaceholder ( content ) {
      props.setAttributes({input_placeholder: content})
    }
    
    function onChangeInputRequired ( content ) {
      input_required = input_required ? false : true;
      props.setAttributes({input_required: input_required})
    }
    
    function onChangeInputHalfWidth ( content ) {
      props.setAttributes({input_halfwidth: true})
    }
    function onChangeInputFullWidth ( content ) {
      props.setAttributes({input_halfwidth: false})
    }           

    return ([
      <InspectorControls>
        <PanelBody title={__('Basic')}>
          <ToggleControl
            label={ input_required ? __('This is a required field') : __('This is a not required field') }
            checked={ input_required }
            onChange={ onChangeInputRequired }
          />
          <BaseControl
            label={__("Width")}
          >
            <ButtonGroup>
              <Button
                isPrimary={ input_halfwidth ? true : false }
                isDefault={ input_halfwidth ? false : true }
                onClick={onChangeInputHalfWidth}
              >Half width</Button>
              <Button
                isPrimary={ input_halfwidth ? false : true }
                isDefault={ input_halfwidth ? true : false }
                onClick={onChangeInputFullWidth}
              >Full width</Button>
            </ButtonGroup>
          </BaseControl>
          <TextControl
            label={__("Name")}
            value={input_name}
            onChange={onChangeInputName}
          />
          <TextControl
            label={__("Label")}
            help={__("Field name if empty")}
            value={input_label}
            onChange={onChangeInputLabel}
          />
          <TextControl
            label={__("Placeholder")}
            help={__('Ex: "Type your answer here"')}
            value={input_placeholder}
            onChange={onChangeInputPlaceholder}
          />
        </PanelBody>
      </InspectorControls>
      ,
      <div class={ "formality__field formality__field--text formality__field--width2" + ( focus ? ' formality__field--focus' : '' ) + ( input_required ? ' formality__field--required' : '' ) }>
        <label class="formality__label" for={ input_uid }>{ input_label ? input_label : ( input_name ? input_name : __('Field name')) }</label>
        <div class="formality__input" data-placeholder={ input_placeholder ? input_placeholder : __('Type your answer here') }>
          <input type="text" id={ input_uid } name={ input_uid } required="" placeholder={ input_placeholder ? input_placeholder : "Type your answer here" } />
        </div>
      </div>
    ])
  }, 
  save ( props ) {
    return null
  },
});