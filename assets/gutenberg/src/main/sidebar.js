/**
 * Internal block libraries
 */
 

const { __ } = wp.i18n;

const {
  PluginSidebar,
  PluginSidebarMoreMenuItem,
} = wp.editPost;

const { registerPlugin } = wp.plugins;

const { 
  source
} = wp.blocks;

const { 
  ColorPalette,
  ColorPicker,
  ColorIndicator,
  PanelBody,
  PanelRow,
  Button,
  TextControl,
  ToggleControl,
  ButtonGroup,
  BaseControl,
  Dropdown,
  Tooltip,
  FontSizePicker,
  RangeControl
} = wp.components;

const { 
  RichText,
  MediaUpload,
  InspectorControls
} = wp.editor;

const {
	Component,
	Fragment,
	createElement
} = wp.element;

const { withSelect } = wp.data;
const { compose } = wp.compose;

class Formality_Sidebar extends Component {
  
	constructor() {
		super( ...arguments );
    
    //check if formality keys are already defined
    let formality_keys = wp.data.select('core/editor').formality;
    
    //set initial state
    let initarray = {}
    initarray['keys'] = (formality_keys ? formality_keys['keys'] : []);
    
    let default_keys = {
      '_formality_type': "standard",
      '_formality_color1': "#000000",
      '_formality_color2': "#ffffff",
      '_formality_fontsize': 20,
    }
    for (var default_key in default_keys) {
      initarray[default_key] = (formality_keys ? formality_keys[default_key] : '')
    }
		this.state = initarray
				
    
    //if formality keys are not defined, read post metas
    if(!formality_keys) {
  		wp.apiFetch({
    		path: `/wp/v2/formality_form/${this.props.postId}`,
    		method: 'GET'
      }).then(
  			(data) => {
    			initarray = {};
    			initarray['keys'] = [];
    			for (var default_key in default_keys) {
      			if(data.meta[default_key]) {
        			initarray[default_key] = data.meta[default_key];
      			} else {
        			initarray[default_key] = default_keys[default_key];
        			initarray["keys"] = initarray["keys"].concat(default_key);
      			}
          }
    			this.setState(initarray, () => {
            wp.data.select('core/editor').formality = this.state;
            this.applyFormalityStyles()
          })
    		  return data;
    		},
  			(err) => {
    			return err;
    		}
  		);
		}
    
    this.updateFormalityOptions = function(name, value) {
    	const keys = this.state.keys.concat(name);
    	let option_array = { keys: keys }
    	option_array[name] = value;
  		this.setState(option_array, () => {
        wp.data.select('core/editor').formality = this.state;
        console.log(wp.data.select('core/editor').formality)
        //force save button
        wp.data.dispatch('core/editor').editPost({meta: {_non_existing_meta: true}});
        this.applyFormalityStyles()
      });
  	}
  	
  	this.applyFormalityStyles = function() {
    	let root = document.documentElement;
      let element = document.getElementsByClassName("edit-post-visual-editor");
    	root.style.setProperty('--formality_col1', this.state['_formality_color1']);
      root.style.setProperty('--formality_col2', this.state['_formality_color2']);
      root.style.setProperty('--formality_fontsize', (this.state['_formality_fontsize'] + "px"));      
      if(this.state['_formality_type']=="conversational") {
        element[0].classList.add("conversational");
      } else {
        element[0].classList.remove("conversational");
      }
  	}
  	  	
	}

/*
  static getDerivedStateFromProps( nextProps, state ) {
		if ( ( nextProps.isPublishing || nextProps.isSaving ) && !nextProps.isAutoSaving ) {
			wp.apiRequest({
  			path: `/formality/v1/options?id=${nextProps.postId}`,
  			method: 'POST',
  			data: state
  		}).then(
				( data ) => { return data; },
				( err ) => { return err; }
			);
		}
	}
*/

	render() {
  	  	
		return (
			<Fragment>
  			<BaseControl
  			  label={__("Form type")}
          help={ this.state['_formality_type']=="standard" ? 'Classic layout form' : 'Distraction free form' }
        >
          <ButtonGroup>
            <Button
              isPrimary={ this.state['_formality_type']=="standard" ? true : false }
              isDefault={ this.state['_formality_type']=="standard" ? false : true }
              onClick={() => this.updateFormalityOptions('_formality_type', 'standard')}
            >Standard</Button>
            <Button
              isPrimary={ this.state['_formality_type']=="conversational" ? true : false }
              isDefault={ this.state['_formality_type']=="conversational" ? false : true }
              onClick={() => this.updateFormalityOptions('_formality_type', 'conversational')}
            >Conversational</Button>
          </ButtonGroup>
        </BaseControl>
        <PanelRow
            className="formality_colorpicker"
          >
          <BaseControl
            label={ __("Primary color") }
            help="Texts, Labels, Borders, etc."
            >
            <Dropdown
              className="components-color-palette__item-wrapper components-color-palette__custom-color"
              contentClassName="components-color-palette__picker"
              renderToggle={ ( { isOpen, onToggle } ) => (
                <button
                  type="button"
                  style={{ background: this.state['_formality_color1'] }}
                  aria-expanded={ isOpen }
                  className="components-color-palette__item"
                  onClick={ onToggle }
                ></button>
              ) }
              renderContent={ () => (
                <ColorPicker
                  color={ this.state['_formality_color1'] }
                  onChangeComplete={(value) => this.updateFormalityOptions('_formality_color1', value.hex)}
                  disableAlpha
                />
              ) }
            />
          </BaseControl>
          <BaseControl
            label={ __( 'Secondary color' ) }
            help="Backgrounds, Input suggestions, etc."
          >
            <Dropdown
              className="components-color-palette__item-wrapper components-color-palette__custom-color"
              contentClassName="components-color-palette__picker"
              renderToggle={ ( { isOpen, onToggle } ) => (
                <button
                  type="button"
                  style={{ background: this.state['_formality_color2'] }}
                  aria-expanded={ isOpen }
                  className="components-color-palette__item"
                  onClick={ onToggle }
                ></button>
              ) }
              renderContent={ () => (
                <ColorPicker
                  color={ this.state['_formality_color2'] }
                  onChangeComplete={(value) => this.updateFormalityOptions('_formality_color2', value.hex)}
                  disableAlpha
                />
              ) }
            />
          </BaseControl>
        </PanelRow>
        <PanelRow
          className="formality_fontsize"
        >
          <BaseControl
            label={ __( 'Font size', 'formality' ) }
            help={ __( "Align this value to your theme's fontsize", 'formality' ) }
          >
            <RangeControl
              value={ this.state['_formality_fontsize'] }
              onChange={ ( newFontSize ) => this.updateFormalityOptions('_formality_fontsize', newFontSize) }
              min={ 16 }
              max={ 24 }
              beforeIcon="editor-textcolor"
              afterIcon="editor-textcolor"
            />
          </BaseControl>
        </PanelRow>
			</Fragment>
		)
	}
}

const FS = withSelect( ( select, { forceIsSaving } ) => {
	const {
		getCurrentPostId,
		isSavingPost,
		isPublishingPost,
		isAutosavingPost,
	} = select( 'core/editor' );
	return {
		postId: getCurrentPostId(),
		isSaving: forceIsSaving || isSavingPost(),
		isAutoSaving: isAutosavingPost(),
		isPublishing: isPublishingPost(),
	};
} )( Formality_Sidebar );

/*
registerPlugin( 'hello-gutenberg', {
	icon: 'admin-site',
	render: FS,
} );
*/



//var el = wp.element.createElement;

function customizeProductTypeSelector( OriginalComponent ) {
	return function( props ) {
		if ( props.slug === 'formality_meta' ) {
			return createElement(FS, props);
		} else {
			return createElement(
				OriginalComponent,
				props
			);
		}
	}
};

wp.hooks.addFilter(
	'editor.PostTaxonomyType',
	'formality',
	customizeProductTypeSelector
);