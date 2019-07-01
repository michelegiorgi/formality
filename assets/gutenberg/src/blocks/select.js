/** 
 * Formality block
 * 
 */

//import { RepeaterControl } from '../main/repeater.js'

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
  SelectControl,
  ToggleControl,
  ButtonGroup,
  BaseControl,
  RepeaterControl
} = wp.components;

const { 
  RichText,
  MediaUpload,
  InspectorControls,
  BlockControls
} = wp.editor;

var el = wp.element.createElement;

const blockicon = () => (
<svg width="24px" height="24px" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg">
  <g id="select" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
    <circle id="bg" fill="#F3C635" cx="12" cy="12" r="12"></circle>
    <path d="M14.3073639,12.1316314 L10.5411741,8.37104614 C10.402477,8.22470089 10.2292825,8.15155862 10.0212469,8.15155862 C9.81321124,8.15155862 9.64001681,8.22478182 9.50131964,8.37104614 L9.06230413,8.80435594 C8.91978292,8.94685692 8.84852231,9.12007158 8.84852231,9.32418203 C8.84852231,9.52442797 8.91976269,9.6997064 9.06230413,9.84985545 L11.8696928,12.6515789 L9.06218273,15.4590687 C8.91970199,15.6015696 8.84844138,15.7747843 8.84844138,15.978915 C8.84844138,16.1791407 8.91968176,16.3544798 9.06218273,16.5045682 L9.50123871,16.9378173 C9.64373968,17.0803182 9.81711621,17.1515586 10.021166,17.1515586 C10.2252966,17.1515586 10.3986327,17.0803182 10.5410932,16.9378173 L14.3073639,13.177232 C14.4499256,13.0270425 14.5212469,12.8518046 14.5212469,12.6515586 C14.5212671,12.4474482 14.4499256,12.2741323 14.3073639,12.1316314 Z" id="Path" fill="#FFFFFF" transform="translate(11.684844, 12.651559) rotate(-270.000000) translate(-11.684844, -12.651559) "></path>
  </g>
</svg>
);

registerBlockType( 'formality/select', {
  title: __('Select', 'formality'),
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
    options: {
      type: 'string|array',
      attribute: 'options',
      default: []
    },
    rules: {
      type: 'string|array',
      attribute: 'rules',
      default: []
    }
  },
  supports: {
    html: false,
    customClassName: false,
  },
  transforms: {
    from: [{
      type: 'block',
      blocks: [ 'formality/text', 'formality/email', 'formality/textarea'  ],
      transform: function ( attributes ) { return createBlock( 'formality/select', attributes); },
    }]
  },
  edit(props) {
    let name = props.attributes.name
    let label = props.attributes.label
    let placeholder = props.attributes.placeholder
    let required = props.attributes.required
    let halfwidth = props.attributes.halfwidth
    let options = props.attributes.options
    let rules = props.attributes.rules
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
    
    function getBlocks() {
      let blocks = wp.data.select('core/editor').getBlocks();
      let options = [{ label: __('- Field -', 'formality'), value: "" }];
      for( const block of blocks ) {
        if (typeof block.attributes.exclude == 'undefined') {
          options.push({ label: block.attributes.name, value: block.attributes.uid })
          //console.log(block.attributes.uid);
        }
      }
      return options;
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
          <label
            class="components-base-control__label"
          >Options</label>
          <RepeaterControl
            addText={__('Add option', 'formality')}
            removeOnEmpty={true}
            value={options}
            onChange={(val) => { props.setAttributes({options: val}); }}
          >{(value, onChange) => {
            return [
              <TextControl
                placeholder="Value"
                value={value.value}
                onChange={(v) => { value.value = v; onChange(value)}}
              />,
              <TextControl
                placeholder="Label"
                value={value.label}
                onChange={(v) => { value.label = v; onChange(value) }}
              />
            ]
          }}</RepeaterControl>          
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
          <label
            class="components-base-control__label"
          >Conditionals</label>
          <RepeaterControl
            addText={__('Add rule', 'formality')}
            value={rules}
            removeOnEmpty={true}
            addClass='repeater--rules'
            onChange={(val) => { props.setAttributes({rules: val}); }}
          >{(value, onChange) => {
            return [
              <SelectControl
                value={ value.operator }
                options={[
                  { label: 'AND', value: '&&' },
                  { label: 'OR', value: '||' }
                ]}
                onChange={(v) => { value.operator = v; onChange(value) }}
              />,
              <SelectControl
                value={ value.field }
                options={getBlocks()}
                onChange={(v) => { value.field = v; onChange(value) }}
              />,
              <SelectControl
                value={ value.is }
                options={[
                  { label: '=', value: '==' },
                  { label: '≠', value: '!==' },
                  { label: '<', value: '<' },
                  { label: '≤', value: '<=' },
                  { label: '>', value: '>' },
                  { label: '≥', value: '>=' },
                ]}
                onChange={(v) => { value.is = v; onChange(value) }}
              />,
              <TextControl
                placeholder="Value"
                value={value.value}
                onChange={(v) => { value.value = v; onChange(value)}}
              />
            ]
          }}</RepeaterControl>
        </PanelBody>
      </InspectorControls>
      ,
      <div
        class={ "formality__field formality__field--select" + ( focus ? ' formality__field--focus' : '' ) + ( required ? ' formality__field--required' : '' ) + ( value ? ' formality__field--filled' : '' ) }
      >
        <label
          class="formality__label"
          for={ uid }
        >
          { name ? name : __('Field name', 'formality') }
        </label>
        <div
          class="formality__input"
          data-placeholder={ placeholder ? placeholder : __('Select your choice', 'formality') }
        >
          <select
            id={ uid }
            name={ uid }
            required=""
            placeholder={ placeholder ? placeholder : __('Select your choice', 'formality') }
          >
            <option
              disabled
              selected
              value=""
            >{ value ? value : (placeholder ? placeholder : __('Select your choice', 'formality')) }</option>
          </select>
        </div>
      </div>
    ])
  }, 
  save ( props ) {
    return null
  },
});