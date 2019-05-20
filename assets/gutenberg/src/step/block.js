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


registerBlockType( 'formality/step', {
  title: __('Step', 'formality'),
  description: __('Standard text field, good for short answers and 1 line information', 'formality'), 
  icon: 'universal-access-alt',
  category: 'formality',
  attributes: {
    uid: { type: 'string', default: '' },
    name: { type: 'string', default: ''},
    description: { type: 'string', default: ''},
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