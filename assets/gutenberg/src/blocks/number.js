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
  BaseControl,
} = wp.components;

const { 
  RichText,
  MediaUpload,
  InspectorControls
} = wp.editor;

const blockicon = () => (
<svg width="24px" height="24px" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg">
  <g id="number" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
    <circle id="bg" fill="#5058EF" cx="12" cy="12" r="12"></circle>
    <path d="M11.9999626,11.0093023 L13.137038,9.9433944 C13.3645568,9.72985472 13.62252,9.54769197 13.9038798,9.40196177 C14.4088496,9.13923012 14.9838383,9.000107 15.5656178,9.000107 C17.4592687,9.000107 19,10.4444356 19,12.2198403 C19,13.995245 17.4592687,15.4394666 15.5656178,15.4394666 C14.9835815,15.4394666 14.4083645,15.3002365 13.9024247,15.0369163 C13.6232903,14.8923096 13.3650704,14.7101736 13.137038,14.4961792 L11.9999399,13.4301998 L10.8630762,14.4960722 C10.6350723,14.7098259 10.3768523,14.8922294 10.095521,15.0377456 C9.59126454,15.3002365 9.01630434,15.4394666 8.43438218,15.4394666 C6.54061716,15.4394666 5,13.9951647 5,12.2197333 C5,10.4443018 6.5404745,9 8.43438218,9 C9.01593341,9 9.59089362,9.13923012 10.0970903,9.40255025 C10.3768523,9.54750472 10.6349296,9.72953373 10.8627053,9.94307341 L11.9999626,11.0093023 Z M8.43438218,10.7119554 C7.5475037,10.7119554 6.82608572,11.3883115 6.82608572,12.2197333 C6.82608572,13.0511551 7.5475037,13.7275112 8.43438218,13.7275112 C8.71072031,13.7275112 8.97199323,13.6644096 9.21095366,13.5399986 C9.34414379,13.4710657 9.46489371,13.3856819 9.57177679,13.2856127 L10.7086863,12.21976 L9.57177679,11.1537736 C9.46489371,11.0536777 9.34388699,10.9683207 9.21240882,10.9000565 C8.97199323,10.7750837 8.71060617,10.7119554 8.43438218,10.7119554 Z M13.2912851,12.2197333 L14.4280806,13.2857197 C14.5353346,13.3860296 14.6558562,13.4714134 14.7868493,13.5391961 C15.0277785,13.6645166 15.2890229,13.7275112 15.5655037,13.7275112 C16.4523822,13.7275112 17.1738002,13.0511819 17.1738002,12.2197333 C17.1738002,11.3882847 16.4523822,10.7119554 15.5655037,10.7119554 C15.2890229,10.7119554 15.0276644,10.775057 14.7884472,10.899468 C14.6557421,10.9684009 14.5349922,11.0536509 14.4282232,11.1538538 L13.2912851,12.2197333 Z" id="Combined-Shape" fill="#FFFFFF" fill-rule="nonzero"></path>
  </g>
</svg>
);

registerBlockType( 'formality/number', {
  title: __('Number', 'formality'),
  description: __('Number field, accept integer or float number value', 'formality'), 
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
    value_min: { type: 'string', default: ''},
    value_max: { type: 'string', default: ''},
    value_step: { type: 'string', default: ''},
  },
  supports: {
    html: false,
    customClassName: false,
  },
  transforms: {
    from: [{
      type: 'block',
      blocks: [ 'formality/select', 'formality/email', 'formality/textarea', 'formality/text'  ],
      transform: function ( attributes ) { return createBlock( 'formality/number', attributes); },
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
    let value_min = props.attributes.value_min
    let value_max = props.attributes.value_max
    let value_step = props.attributes.value_step
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
          <PanelRow
            className="formality_panelrow"
          >
            <TextControl
              type="number"
              label={__('Min value', 'formality')}
              value={value_min}
              onChange={(value) => editAttribute("value_min", value)}
            />
            <TextControl
              type="number"
              label={__('Max value', 'formality')}
              value={value_max}
              onChange={(value) => editAttribute("value_max", value)}
            />
            <TextControl
              type="number"
              label={__('Interval', 'formality')}
              value={value_step}
              onChange={(value) => editAttribute("value_step", value)}
            />
          </PanelRow>
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
        class={ "formality__field formality__field--text" + ( focus ? ' formality__field--focus' : '' ) + ( required ? ' formality__field--required' : '' ) + ( value ? ' formality__field--filled' : '' ) }
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