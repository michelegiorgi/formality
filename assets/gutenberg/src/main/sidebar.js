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
  RangeControl,
  DropZoneProvider,
  DropZone,
  Spinner,
  ResponsiveWrapper
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
      '_formality_logo': '',
      '_formality_logo_id': '',
      '_formality_bg': '',
      '_formality_bg_id': '',
      '_formality_overlay_opacity': 80
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
            this.applyFormalityStyles();
            this.hideFormalityLoading();
          })
    		  return data;
    		},
  			(err) => {
    			return err;
    		}
  		);
		}
    
    this.hideFormalityLoading = function() {
      let element = document.getElementsByClassName("edit-post-visual-editor");
      element[0].classList.add("is-loaded");
    }
    
    this.updateFormalityOptions = function(name, value) {
    	let keys = this.state.keys.concat(name);
    	let value_id = "";
    	let key_id = "";
      if(name=="_formality_logo"||name=="_formality_bg") {
        if(value) {
          value_id = value.id
          value = value.sizes.full.url
        }
        key_id = name+"_id";
        keys = keys.concat(key_id);
      }
    	let option_array = { keys: keys }
    	option_array[name] = value;
    	if(key_id) { option_array[key_id] = value_id; }
  		this.setState(option_array, () => {
        wp.data.select('core/editor').formality = this.state;
        //console.log(wp.data.select('core/editor').formality)
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
      root.style.setProperty('--formality_bg', this.state['_formality_bg'] ? ( "url(" + this.state['_formality_bg'] + ")" ) : "none");
      root.style.setProperty('--formality_overlay', this.state['_formality_overlay_opacity'] ? ( "0." + this.state['_formality_overlay_opacity'] ) : "0");      
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
        <PanelBody
          title={__('Advanced', 'formality')}
          initialOpen={ false }
        >
            <BaseControl
              label={ __( 'Logo', 'formality' ) }
              help={ __( "Replace Formality logo", 'formality' ) }
            >
              <MediaUpload
                onSelect={(file) => this.updateFormalityOptions('_formality_logo', file)}
                type="image"
                value={ this.state['_formality_logo_id'] }
                render={({ open }) => (
                  <Fragment>
                    <Button
      								className={ this.state['_formality_logo'] ? 'editor-post-featured-image__preview' : 'editor-post-featured-image__toggle' }
      								onClick={ open }
      								aria-label={ ! this.state['_formality_logo'] ? null : __( 'Edit or update the image', 'formality' ) }>
      								{ this.state['_formality_logo'] ? <img src={ this.state['_formality_logo'] } alt="" /> : ''}
      								{ this.state['_formality_logo'] ? '' : __('Set a custom logo', 'formality' ) }
      							</Button>
      							{ this.state['_formality_logo'] ? <Button onClick={() => this.updateFormalityOptions('_formality_logo', '')} isLink isDestructive>{ __('Remove custom logo', 'formality' )}</Button> : ''}
    							</Fragment>
                )}
              />
            </BaseControl>
            <BaseControl
              label={ __( 'Background image', 'formality' ) }
              help={ __( "Add background image", 'formality' ) }
            >
              <MediaUpload
                onSelect={(file) => this.updateFormalityOptions('_formality_bg', file)}
                type="image"
                value={ this.state['_formality_bg_id'] }
                render={({ open }) => (
                  <Fragment>
                    <Button
      								className={ this.state['_formality_bg'] ? 'editor-post-featured-image__preview' : 'editor-post-featured-image__toggle' }
      								onClick={ open }
      								aria-label={ ! this.state['_formality_bg'] ? null : __( 'Edit or update the image', 'formality' ) }>
      								{ this.state['_formality_bg'] ? <img src={ this.state['_formality_bg'] } alt="" /> : ''}
      								{ this.state['_formality_bg'] ? '' : __('Set a background image', 'formality' ) }
      							</Button>
      							{ this.state['_formality_bg'] ? <Button onClick={() => this.updateFormalityOptions('_formality_bg', '')} isLink isDestructive>{ __('Remove background image', 'formality' )}</Button> : ''}
    							</Fragment>
                )}
              />
            </BaseControl>
            <BaseControl
              label={ __( 'Background overlay opacity', 'formality' ) }
              help={ __( "Set background overlay opacity (%)", 'formality' ) }
            >
              <RangeControl
                value={ this.state['_formality_overlay_opacity'] }
                onChange={ ( newOpacity ) => this.updateFormalityOptions('_formality_overlay_opacity', newOpacity) }
                min={ 0 }
                max={ 100 }
                //beforeIcon="editor-textcolor"
                //afterIcon="editor-textcolor"
              />
            </BaseControl>
        </PanelBody>
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


//registerPlugin( 'formality-sidebar', { icon: 'admin-site', render: FS });

function customizeFormalityMeta( OriginalComponent ) {
	return function( props ) {
		if ( props.slug === 'formality_meta' ) {
			return createElement(FS, props);
		} else {
			return createElement(OriginalComponent, props );
		}
	}
};

wp.hooks.addFilter(
	'editor.PostTaxonomyType',
	'formality',
	customizeFormalityMeta
);

