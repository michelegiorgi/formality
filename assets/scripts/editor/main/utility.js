
import React from 'react'

const { 
  PanelBody,
  PanelRow,
  Button,
  TextControl,
  SelectControl,
  ToggleControl,
  ButtonGroup,
  BaseControl,
  RepeaterControl,
} = wp.components;

const { __ } = wp.i18n;


//field block init function
  let checkUID = (props, exclude = 0 ) => {
    //get constants
    const blockid = props.clientId
    const currentuid = props.attributes.uid
    const currentexclude = props.attributes.exclude
    const trueuid = blockid.substr(blockid.length - 12)
    //set exclude attr
    if(currentexclude&&(currentexclude!==exclude)) {
      props.setAttributes({ exclude: exclude })
    }
    if(!currentuid) {
      //set field uid
      props.setAttributes({uid: trueuid })
    } else if(props.isSelected) {
      //check if uid already exist (duplicate block)
      const blocks = wp.data.select('core/block-editor').getBlocks();
      let clones = -1;
      for( const block of blocks ) {
        if(block.attributes.uid == currentuid) {
          clones++
          if(clones>0) {
            //if clone uid exist, reset this uid 
            props.setAttributes({uid: trueuid })
          }
        }
      }
    }
  };


//setAttibutes shortcut
  let editAttribute = (props, key, value, toggle = false) => {
    let tempArray = {}
    if(toggle){ value = props.attributes[key] ? false : true }
    tempArray[key] = value
    props.setAttributes(tempArray)
  };


//get fields list
  let getBlocks = (current) => {
    const blocks = wp.data.select('core/block-editor').getBlocks();
    let options = [{ label: __('- Field -', 'formality'), value: "" }];
    for( const block of blocks ) {
      if (typeof block.attributes.exclude == 'undefined') {
        const name = block.attributes.name ? block.attributes.name : ("Field " + block.attributes.uid)
        if(block.attributes.uid !== current) {
          options.push({ label: name, value: block.attributes.uid })
        }
      }
    }
    return options;
  };

//option list or free text?
  let selectOrText = (props) => {
    const options = props.attributes.options
    const value = props.attributes.value
    if(options) {
      let options_array = [{ label: __('None', 'formality'), value: "" }];
      options.map(option => { 
        options_array.push({ label: ( option["label"] ? option["label"] : option["value"] ), value: option["value"] })
      })
      return <SelectControl
        label={__('Initial value', 'formality')}
        value={value}
        options={options_array}
        onChange={(value) => editAttribute(props, "value", value)}
      />      
    } else {
      return <TextControl
        label={__('Initial value', 'formality')}
        value={value}
        placeholder={ __('None', 'formality') }
        onChange={(value) => editAttribute(props, "value", value)}
      />            
    }
  };

//get input block types
  let getBlockTypes = (exclude = "") => {
    let types = [
      'formality/text',
      'formality/email',
      'formality/textarea',
      'formality/select',
      'formality/number',
      'formality/multiple',
      'formality/switch',
    ]
    if(exclude) {
      for( var i = 0; i < types.length; i++){ 
        if ( types[i] === exclude) {
          types.splice(i, 1); 
        }
      }
    }
    return types;
  }


//return standard sidebar
  let mainOptions = (props, width = true, message = false) => {
    
    let name = props.attributes.name
    let placeholder = props.attributes.placeholder
    let required = props.attributes.required
    let halfwidth = props.attributes.halfwidth
    
    return ([
      <ToggleControl
        label={ required ? __('This is a required field', 'formality') : __('This is a not required field', 'formality') }
        checked={ required }
        onChange={() => editAttribute(props, "required", true, true )}
      />,
      <BaseControl
        label={__('Width', 'formality')}
        className={ width ? "" : "components-base-control--hidden" }
      >
        <ButtonGroup
          className={ "components-button-group--wide" }
        >
          <Button
            isPrimary={ halfwidth ? true : false }
            isDefault={ halfwidth ? false : true }
            onClick={() => editAttribute(props, "halfwidth", true)}
          >{__('Half width', 'formality')}</Button>
          <Button
            isPrimary={ halfwidth ? false : true }
            isDefault={ halfwidth ? true : false }
            onClick={() => editAttribute(props, "halfwidth", false)}
          >{__('Full width', 'formality')}</Button>
        </ButtonGroup>
      </BaseControl>,
      <TextControl
        label={__('Label / Question', 'formality')}
        placeholder={__('Field name', 'formality')}
        value={name}
        onChange={(value) => editAttribute(props, "name", value)}
      />,
      <TextControl
        label={ message ? __('Message', 'formality') : __('Placeholder', 'formality')}
        placeholder={ message ? '' : __('Type your answer here', 'formality')}
        help={ message ? __('Add an information message', 'formality') : ''}
        value={placeholder}
        onChange={(value) => editAttribute(props, "placeholder", value)}
      />,
    ])
  }


//check if field has active conditional rules
  let hasRules = (rules) => {
    let initopen = false
    if(typeof rules[0] !== 'undefined') {
      if("field" in rules[0]) {
        initopen = true
      }
    }
    return initopen
  }

  
//return advanced sidebar
  let advancedPanel = (props, showname = true) => {
    
    //const name = props.attributes.name
    const label = props.attributes.label
    const rules = props.attributes.rules
    const uid = props.attributes.uid
    //const value = props.attributes.value
    //const options = props.attributes.options
    //const focus = props.isSelected
    // eslint-disable-next-line no-unused-vars
    let activepanel = function(rules) {
      let initopen = false
      if(typeof rules[0] !== 'undefined') {
        if("field" in rules[0]) {
          initopen = true
        }
      }
      return initopen
    }
      
    return ([
      <PanelBody
        title={__('Advanced', 'formality')}
        initialOpen={ false }
        icon={ hasRules(rules) ? "hidden" : "" }
      >
        <PanelRow
            className={ "formality_panelrow formality_panelrow--half " + ( showname ? "" : "formality_panelrow--hidden") }
          >
          <TextControl
            label={__('Field ID/Name', 'formality')}
            value={uid}
            disabled
          />
          { selectOrText(props) }
        </PanelRow>
        <p
          className={ "components-base-control__help" }>
          {__('You can set an initial variable value by using field ID as a query var. Example: http://wp.com/form/?', 'formality') + uid + '=xy'}
        </p>
        <label
          className="components-base-control__label"
        >Conditionals</label>
        <p
          className="components-base-control__help">
          {__('Show this field only if:', 'formality')}
        </p>
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
                { label: 'OR', value: '||' },
              ]}
              onChange={(v) => { value.operator = v; onChange(value) }}
            />,
            <SelectControl
              value={ value.field }
              options={getBlocks(uid)}
              onChange={(v) => {
                value.field = v;
                if(!value.operator) { value.operator = '&&' }
                if(!value.is) { value.is = '==' }
                onChange(value)
              }}
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
              placeholder="Empty"
              value={value.value}
              onChange={(v) => { value.value = v; onChange(value)}}
            />,
          ]
        }}</RepeaterControl>
      </PanelBody>,
    ])
  }


//export all
  export {
    checkUID,
    editAttribute,
    getBlocks,
    getBlockTypes,
    mainOptions,
    advancedPanel,
    hasRules,
  }