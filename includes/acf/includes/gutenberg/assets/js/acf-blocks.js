(function($, undefined){
	
	// vars
	var blocks = {};
	
	/**
	*  acf.getBlocks
	*
	*  Returns an array of all block instances.
	*
	*  @date	23/10/18
	*  @since	5.7.8
	*
	*  @param	type $var Description. Default.
	*  @return	type Description.
	*/
	acf.getBlocks = function(){
		return Object.keys( blocks ).map(function( key ){
			return blocks[key];
		});
	};
	
	/**
	*  acf.getBlock
	*
	*  Returns a block instance for the given cid.
	*
	*  @date	10/10/18
	*  @since	5.7.8
	*
	*  @param	string The block clientId.
	*  @return	object
	*/
	acf.getBlock = function( cid ){
		return blocks[ cid ] || null;
	};
	
	/**
	*  acf.newBlock
	*
	*  Returns a new block instance for the given props.
	*
	*  @date	10/10/18
	*  @since	5.7.8
	*
	*  @param	object props The block properties.
	*  @return	object
	*/
	acf.newBlock = function( props ){
		
		// creat new
		var block = new acf.models.Block( props );
		
		// append
		blocks[ block.cid ] = block;
		
		// return
		return block;
	};
	
	/**
	*  registerBlockTypes
	*
	*  Registers an array of block types.
	*
	*  @date	11/4/18
	*  @since	5.6.9
	*
	*  @param	array blocks The array of block types.
	*  @return	void
	*/
	acf.registerBlockTypes = function( blocks ){
		blocks.map(acf.registerBlockType);
	};
	
	/**
	*  acf.registerBlockType
	*
	*  Registers a single block type.
	*
	*  @date	7/8/18
	*  @since	5.7.3
	*
	*  @param	object blockType The block type settings localized from PHP.
	*  @return	object The result from wp.blocks.registerBlockType().
	*/
	acf.registerBlockType = function( blockType ){
		
		// bail early if wp.blocks does not exist
		if( !wp || !wp.blocks || !wp.blocks.registerBlockType ) {
			return false;
		}
		
		// bail ealry if is excluded post_type
		if( blockType.post_types && blockType.post_types.length ) {
			var postType = $('#post_type').val();
			if( blockType.post_types.indexOf(postType) === -1 ) {
				return false;
			}
		}
		
		// extend block type with default functionality
		$.extend(blockType, {
			
			// each block requires data attributes to be defined
			attributes: {
				id: { 
					type: 'string',
				},
				data: { 
					type: 'object',
				},
				name: { 
					type: 'string',
				},
		        mode: {
		            type: 'string',
		        },
			},
			
			// callback used to render block HTML each time it is selected / deselected
			edit: function( props ) {
				
				// get block
				var block = acf.getBlock( props.clientId );
				
				// create new block if does not yet exist
				if( !block ) {
					block = acf.newBlock( props );
				}
				
				// render
				return block.render( props );
			},
	
			// callback used when saving the block
			save: function( props ) {
				return null;
			}
		});
		
		// register
		var result = wp.blocks.registerBlockType( blockType.name, blockType );
		
		// return
		return result;
	};
	
	/**
	*  blocksManager
	*
	*  Global functionality for managing blocks.
	*
	*  @date	7/8/18
	*  @since	5.7.3
	*
	*  @param	void
	*  @return	void
	*/
	var blocksManager = new acf.Model({
		wait: 'ready',
		initialize: function(){
			acf.registerBlockTypes( acf.get('blocks') );
		}
	});
	
	/**
	*  acf.models.Block
	*
	*  The block type model.
	*
	*  @date	10/10/18
	*  @since	5.7.8
	*
	*  @param	void
	*  @return	void
	*/
	acf.models.Block = acf.Model.extend({
		
		/** @var object Default data for each block. */
		data: {
			id:		'',
			data:	{},
			name: 	'',
			align: 	'',
			mode:	''
		},
		
		/**
		*  setup
		*
		*  Called during initialization to setup the instance data.
		*
		*  @date	23/10/18
		*  @since	5.7.8
		*
		*  @param	object block The block props provided by blockType.edit().
		*  @return	void
		*/
		setup: function( block ){
			
			// store reference to blockType
			this.blockType = wp.blocks.getBlockType( block.name );
			
			// set attributes for newly added blocks
			if( !block.attributes.id ) {
				block.attributes.id = acf.uniqid('block_');
				block.attributes.name = block.name;
				block.attributes.mode = this.blockType.mode;
			}
			
			// check if this block is a duplciate
			acf.getBlocks().map(function( b ){
				if( b.get('id') === block.attributes.id ) {
					block.attributes.id = acf.uniqid('block_');
				}
			});
			
			// sync block (copy data)
			this.sync( block );
		},
		
		/**
		*  sync
		*
		*  Updates the local reference to "block" and syncs attributes for use with this.get().
		*
		*  @date	23/10/18
		*  @since	5.7.8
		*
		*  @param	object block The block props provided by blockType.edit().
		*  @return	void
		*/
		sync: function( block ){
			
			// store reference of block
			this.block = block;
			
			// copy cid
			this.cid = block.clientId;
			
			// copy data
			$.extend(this.data, block.attributes);
		},
		
		/**
		*  $selectors
		*
		*  jQuery selector functions to quickly find block elements.
		*
		*  @date	23/10/18
		*  @since	5.7.8
		*
		*  @param	void
		*  @return	jQuery
		*/
		$block: function(){
			return $('#block-' + this.cid);
		},
		
		$inspector: function(){
			return $('#acf-block-inspector-' + this.get('id'));
		},
		
		$previewButton: function(){
			return $('#acf-block-preview-button-' + this.get('id'));
		},
		
		$panel: function(){
			return $('#acf-block-panel-' + this.get('id'));
		},
		
		$body: function(){
			return $('#acf-block-body-' + this.get('id'));
		},
		
		$fields: function(){
			return $('#acf-block-fields-' + this.get('id'));
		},
		
		$preview: function(){
			return $('#acf-block-preview-' + this.get('id'));
		},
		
		$toolbar: function(){
			return $('.editor-block-toolbar');
		},
		
		/**
		*  supports
		*
		*  Checks if the blockType supports a specific option similar to this.get()
		*
		*  @date	23/10/18
		*  @since	5.7.8
		*
		*  @param	string name The option name.
		*  @return	mixed|false
		*/
		supports: function( name ){
			return this.blockType.supports[name] || null;
		},
		
		/**
		*  initialize
		*
		*  Called during initialization after instance is setup.
		*
		*  @date	23/10/18
		*  @since	5.7.8
		*
		*  @param	void
		*  @return	void
		*/
		initialize: function(){
			// do nothing
			//console.log('initialize', this);
		},
		
		/**
		*  render
		*
		*  Renders the block HTML using Gutenberg friendly el().
		*  Called from blockType.edit() each time the block is selected / unselected / changed.
		*
		*  @date	23/10/18
		*  @since	5.7.8
		*
		*  @param	object block The block props provided by blockType.edit().
		*  @return	void
		*/
		render: function( block ){
			
			// WP elements
			var el = wp.element.createElement;
			var Fragment = wp.element.Fragment;
			var BlockControls = wp.editor.BlockControls;
			var Toolbar = wp.components.Toolbar;
			var InspectorControls = wp.editor.InspectorControls;
			var IconButton = wp.components.IconButton;
			
			// sync block
			this.sync( block );
			
			// block DOM does not yet exist. Use timeout.
			this.setTimeout(function(){
				
				// fetch fields
				if( this.$fields().length ) {
					this.fetchFields();
				}
				
				// fetch preview
				if( this.$preview().length ) {
					this.fetchPreview();
				}
			});
			
			// return elements in a fragment
			return el(
				Fragment,
				null,
				
				// block controls
				el(
                    BlockControls,
                    null,
                    el(
	                    Toolbar,
	                    null,
	                    (this.supports('mode')) && el(
		                    IconButton,
		                    {
			                    className: "components-icon-button components-toolbar__control",
			                    label: (this.get('mode') == 'preview') ? acf.__('Switch to Edit') : acf.__('Switch to Preview'),
			                    icon: (this.get('mode') == 'preview') ? 'edit' : 'welcome-view-site',
			                    onClick: this.proxy(this.toggleMode)
							}
	                    )
                    )
                ),
                
                // inspector controls
                el(
                    InspectorControls,
                    null,
                    el(
						'div',
						{
							id: 'acf-block-panel-' + this.get('id'),
							className: 'acf-block-panel'
						},
						el(
							'div',
							{
								className: 'acf-block-panel-actions'
							},
							(this.supports('mode')) && el(
								'button',
								{
									type: 'button',
									id: 'acf-block-preview-button-' + this.get('id'),
									className: 'button acf-block-preview-button',
									onClick: this.proxy(this.toggleMode)
								},
								(this.get('mode') == 'preview') ? acf.__('Switch to Edit') : acf.__('Switch to Preview')
							),
						),
						(this.get('mode') == 'preview') && el(
							'div',
							{
								id: 'acf-block-fields-' + this.get('id'),
								className: 'acf-block-fields acf-fields',
							}
						)
					)
                ),
                
                // block content
				el(
					'div',
					{
						id: 'acf-block-body-' + this.get('id'),
						className: 'acf-block-body is-' + this.get('mode')
					},
					(this.get('mode') == 'edit') && el(
						'div',
						{
							id: 'acf-block-fields-' + this.get('id'),
							className: 'acf-block-fields acf-fields',
						}
					),
					(this.get('mode') == 'preview') && el(
						'div',
						{
							id: 'acf-block-preview-' + this.get('id'),
							className: 'acf-block-preview'
						}
					)
				)
			);
			
		},
		
		/**
		*  fetchFields
		*
		*  Loads the fields HTML via AJAX.
		*
		*  @date	23/10/18
		*  @since	5.7.8
		*
		*  @param	void
		*  @return	void
		*/
		fetchFields: function(){
			
			// vars
			var $fields = this.$fields();
			
			// bail ealry if already loaded
			if( $fields.hasClass('is-loaded') ) {
				return;
			}
			
			// add class
			$fields.addClass('is-loading');
			
			// ajax
			$.ajax({
		    	url: acf.get('ajaxurl'),
				dataType: 'html',
				type: 'post',
				cache: false,
				data: acf.prepare_for_ajax({
					action:	'acf/ajax/render_block_edit',
					block: this.data
				}),
				context: this,
				success: function( html ){
					
					// update classes
					$fields.removeClass('is-loading').addClass('is-loaded');
					
					// append html
					$fields.html( html );
					
					// append html and do action
					acf.doAction('append', $fields);
					
					// add event
					this.on( $fields, 'change keyup', 'onChange' );
				}
			});
		},
		
		/**
		*  fetchPreview
		*
		*  Loads the preview HTML via AJAX.
		*  Uses a timeout to avoid wasted ajax requests during "change" events.
		*
		*  @date	23/10/18
		*  @since	5.7.8
		*
		*  @param	void
		*  @return	void
		*/
		fetchPreview: function(){
			
			// vars
			var $preview = this.$preview();
			
			// abort previous xhr
			if( this.xhrPreview ) {
				this.xhrPreview.abort();
			}
			
			// bail ealry if already loaded
			if( $preview.hasClass('is-loaded') ) {
				return;
			}
			
			// add class
			$preview.addClass('is-loading');
			
			// ajax
			this.xhrPreview = $.ajax({
		    	url: acf.get('ajaxurl'),
				dataType: 'html',
				type: 'post',
				cache: false,
				data: acf.prepare_for_ajax({
					action:	'acf/ajax/render_block_preview',
					block: this.data
				}),
				context: this,
				success: function( html ){
					
					// update classes
					$preview.removeClass('is-loading').addClass('is-loaded');
					
					// append html
					$preview.html( html );
				}
			});
		},
		
		/**
		*  toggleMode
		*
		*  Toggles between edit and preview mode.
		*
		*  @date	23/10/18
		*  @since	5.7.8
		*
		*  @param	void
		*  @return	void
		*/
		toggleMode: function(){
			
			// toggle
			if( this.get('mode') == 'preview' ) {
				this.setAttributes({ mode: 'edit' });
			} else {
				this.setAttributes({ mode: 'preview' });
			}
		},
		
		/**
		*  onChange
		*
		*  Triggered during "change" event.
		*
		*  @date	23/10/18
		*  @since	5.7.8
		*
		*  @param	object e The event object.
		*  @param	jQuery $el The jQuery element from e.
		*  @return	void
		*/
		onChange: function( e, $el ){
			
			// remove 'is-loaded' class from preview allowing preview to be updated
			this.$preview().removeClass('is-loaded');
			
			// update attibutes (updates block and triggers render)
			this.setAttributes({
				data: acf.serialize( this.$fields(), 'acf-' + this.get('id') ),
			});
		},
		
		/**
		*  setAttributes
		*
		*  Wrapper for this.block.setAttributes()
		*
		*  @date	23/10/18
		*  @since	5.7.8
		*
		*  @param	object attributes An object of attributes to update.
		*  @return	void
		*/
		setAttributes: function( attributes ){
			return this.block.setAttributes( attributes );
		}
	});
	
	/**
	*  gutenbergHelper
	*
	*  Gutenberg hides all UI during initialization.
	*  This helper triggers the 'refresh' action after UI is visble to fix field widths.
	*
	*  @date	31/10/18
	*  @since	5.7.8
	*
	*  @param	void
	*  @return	void
	*/
	var refreshHelper = new acf.Model({
		wait: 'ready',
		initialize: function(){
			setTimeout(function(){
				acf.doAction('refresh');
			}, 0);
		}	
	});
	
})(jQuery);

(function($, undefined){
	
	// wp.data.select( 'core/editor' )
	// wp.data.dispatch( 'core/editor' )
	// wp.data.dispatch( 'core/editor' ).savePost()
	//wp.data.dispatch( 'core/editor' ).editPost({ foo: 'bar' });
	
	
	
/*
	} else {
		dispatch( removeNotice( SAVE_POST_NOTICE_ID ) );
		dispatch( removeNotice( AUTOSAVE_POST_NOTICE_ID ) );

		request = apiFetch( {
			path: `/wp/v2/${ postType.rest_base }/${ post.id }`,
			method: 'PUT',
			data: toSend,
		} );
	}
*/
	



/*


const { __ } = wp.i18n;
	const { registerBlockType } = wp.blocks;
	const { InspectorControls } = wp.editor;
	const { TextControl, PanelBody } = wp.components;

registerBlockType('jsforwpblocks/meta-box', {
	title: 'Example - Meta Box', 
	description: 'An example of how to build a block with a meta box field.', 
	category: 'common',       
	keywords: [
		'Meta',
		'Custom field',
		'Box'
	],    
	attributes: {        
		text: {            
			type: 'string',            
			source: 'meta',            
			meta: 'jsforwpblocks_gb_metabox'        
		}    
	},    
	edit: function edit(props) {        
		var text = props.attributes.text,            
		className = props.className,            
		setAttributes = props.setAttributes;        
		
		return wp.element.createElement(TextControl, {
						label: 'Meta box',                    
						value: text,  
						name: 'acftest',                  
						onChange: function onChange(text) {                        
							return setAttributes({ text: text });                    
						}                
					});
					
		return [
			wp.element.createElement(
				InspectorControls, 
				null, 
				wp.element.createElement(
					PanelBody,                
					null,                
					wp.element.createElement(TextControl, {
						label: 'Meta box',                    
						value: text,                    
						onChange: function onChange(text) {                        
							return setAttributes({ text: text });                    
						}                
					})            
				)        
			), 
			wp.element.createElement(
				'div',            
				{ className: className },            
				wp.element.createElement(                
					'p',                
					null,                
					'Check the meta'           
				)        
			)
		];    
	},    
	save: function save(props) {        
		return wp.element.createElement(            
			'p',            
			null,            
			'Check the meta'      
		);    
	}
});
	
*/
	
})(jQuery);

// @codekit-prepend "_blocks.js";
// @codekit-prepend "_test1.js";