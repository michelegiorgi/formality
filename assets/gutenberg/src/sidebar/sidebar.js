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

const {
	Component,
	Fragment
} = wp.element;

const { withSelect } = wp.data;

const { compose } = wp.compose;

class Formality_Sidebar extends Component {
  
	constructor() {
		super( ...arguments );
    
    let formality_keys = wp.data.select('core/editor').formality;
    
		this.state = {
  		keys: (formality_keys ? formality_keys['keys'] : []),
  		'_formality_type': (formality_keys ? formality_keys['_formality_type'] : ''),
  		'_formality_color1': (formality_keys ? formality_keys['_formality_color1'] : ''),
  		'_formality_color2': (formality_keys ? formality_keys['_formality_color2'] : '')
    }
  
    this.colors = [ 
      { name: 'Black', color: '#000000' }, 
      { name: 'Gray', color: '#666666' },
      { name: 'White', color: '#ffffff' } 
    ];
    
    this.updateFormalityOptions = function(name, value) {
    	const keys = this.state.keys.concat(name);
    	let option_array = { keys: keys }
    	option_array[name] = value; 
  		this.setState(option_array, () => {
        wp.data.select('core/editor').formality = this.state;
        console.log(wp.data.select('core/editor').formality)
      });
  	}

    if(!formality_keys) {
  		wp.apiFetch({
    		path: `/wp/v2/formality_form/${this.props.postId}`,
    		method: 'GET'
      }).then(
  			(data) => { 
    			this.setState({
            '_formality_type': data.meta['_formality_type'],
            '_formality_color1': data.meta['_formality_color1'],
            '_formality_color2': data.meta['_formality_color2']
    		  });
    		  wp.data.select('core/editor').formality = this.state;
    		  return data;
    		},
  			(err) => {
    			return err;
    		}
  		);
		}
	}

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
            <BaseControl label={__("Primary color")}>
              <ColorPalette 
                colors={ this.colors } 
                value={ this.state['_formality_color1'] }
                onChange={(value) => this.updateFormalityOptions('_formality_color1', value)}
              />
            </BaseControl>
            <BaseControl label={ __( 'Secondary color' ) }>
              <ColorPalette 
                colors={ this.colors } 
                value={ this.state['_formality_color2'] }
                onChange={(value) => this.updateFormalityOptions('_formality_color2', value)}
              />
            </BaseControl>
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



var el = wp.element.createElement;

function customizeProductTypeSelector( OriginalComponent ) {
	return function( props ) {
		if ( props.slug === 'formality_meta' ) {
			return el(FS);
		} else {
			return el(
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