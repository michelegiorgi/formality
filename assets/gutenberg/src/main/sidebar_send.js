/**
 * Internal block libraries
 */

const { __ } = wp.i18n;

const {
	PluginSidebar,
	PluginSidebarMoreMenuItem
} = wp.editPost;

const {
	PanelBody,
	TextControl,
	TextareaControl,
	TabPanel,
	ClipboardButton,
	PanelRow
} = wp.components;

const { select } = wp.data;

const {
	Component,
	Fragment
} = wp.element;

const { withSelect } = wp.data;

const { compose } = wp.compose;

const { registerPlugin } = wp.plugins;

const { PluginDocumentSettingPanel } = wp.editPost;

import { iconMark as sidebaricon } from '../main/icons.js'

class Formality_Sidebar_Advanced extends Component {
	constructor() {
		super( ...arguments );

    //get post metas
    let formality_keys = wp.data.select('core/editor').getEditedPostAttribute('meta')

    //define default values    
    let default_keys = {
      '_formality_thankyou': '',
      '_formality_thankyou_message': '',
      '_formality_error': '',
      '_formality_error_message': '',
      '_formality_email': '',
    }
        
    //check if formality keys are already defined
    if(typeof formality_keys['_formality_thankyou'] == 'undefined') {
      formality_keys = default_keys
      wp.data.dispatch('core/editor').editPost({meta: formality_keys})
    }

    this.state = formality_keys

    //update general form options function
    this.updateFormalityOptions = function(name, value) {
    	let option_array = {}
    	option_array[name] = value;
  		this.setState(option_array, () => {
        wp.data.dispatch('core/editor').editPost({meta: option_array})
      });
  	}

	}

	render() {
  	const postType = select("core/editor").getCurrentPostType();
  	const postId = select("core/editor").getCurrentPostId();
  	const postPermalink = select('core/editor').getPermalink();
    if ( postType !== "formality_form" ) { return null; }
  
    
		return (
			<Fragment>
				<PluginSidebarMoreMenuItem
					target="formality-sidebar-advanced"
				>
					{ __( 'Formality options', 'formality' ) }
				</PluginSidebarMoreMenuItem>
				<PluginSidebar
					name="formality-sidebar-advanced"
					title={ __( 'Advanced', 'formality' ) }
				>
				  <PanelBody
            title={__('Information', 'formality')}
          >
				    <strong>Standalone version</strong>
				    <p>This is an independent form, that are not tied to your posts or pages, and you can visit at this web address: <a class="formality-admin-info-permalink" target="_blank" href=""></a></p>
				    <PanelRow
				      className='components-panel__row--copyurl'
				    >
  				    <TextControl
                value={ postPermalink }
                disabled
              />
              <ClipboardButton
                icon="admin-page"
            		text={ postPermalink }
            	>
            	</ClipboardButton>
          	</PanelRow>
				    <strong>Embedded version</strong>
				    <p>But you can also embed it, into your post or pages with Formality block or with this specific shortcode:</p>
				    <PanelRow
				      className='components-panel__row--copyurl'
				    >
  				    <TextControl
                value={ '[formality id="' + postId + '"]' }
                disabled
              />
              <ClipboardButton
                icon="admin-page"
            		text={ '[formality id="' + postId + '"]' }
            	>
            	</ClipboardButton>
          	</PanelRow>
				  </PanelBody>
					<PanelBody
            title={__('Notifications', 'formality')}
            initialOpen={ false }
          >
            <p>Formality automatically saves all the results in the Wordpress database, but if you want you can also activate e-mail notifications, by entering your address.</p>
            <TextControl
              //label={__('Error message', 'formality')}
              placeholder={__('E-mail address', 'formality')}
              value={ this.state['_formality_email'] }
              onChange={(value) => this.updateFormalityOptions('_formality_email', value)}
            />
					</PanelBody>
					<PanelBody
            title={__('Status messages', 'formality')}
            initialOpen={ false }
          >
						<TextControl
						  className={'components-base-control--nomargin'}
              label={__('Thank you message', 'formality')}
              placeholder={__('Thank you', 'formality')}
              value={ this.state['_formality_thankyou'] }
              onChange={(value) => this.updateFormalityOptions('_formality_thankyou', value)}
            />
						<TextareaControl
						  placeholder={__('Your data has been successfully submitted. You are very important to us, all information received will always remain confidential. We will contact you as soon as possible.', 'formality')}
              value={ this.state['_formality_thankyou_message'] }
              onChange={(value) => this.updateFormalityOptions('_formality_thankyou_message', value)}
            />
						<TextControl
						  className={'components-base-control--nomargin'}
              label={__('Error message', 'formality')}
              placeholder={__('Error', 'formality')}
              value={ this.state['_formality_error'] }
              onChange={(value) => this.updateFormalityOptions('_formality_error', value)}
            />
						<TextareaControl
						  placeholder={__("Something went wrong and we couldn't save your data. Please retry later or contact us by e-mail or phone.", 'formality')}
              value={ this.state['_formality_error_message'] }
              onChange={(value) => this.updateFormalityOptions('_formality_error_message', value)}
            />
					</PanelBody>
				</PluginSidebar>
			</Fragment>
		)
	}
}

const FSS = withSelect( ( select, { forceIsSaving } ) => {
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
} )( Formality_Sidebar_Advanced );

registerPlugin( 'formality-sidebar-advanced', {
	icon: sidebaricon,
	render: Formality_Sidebar_Advanced,
} );