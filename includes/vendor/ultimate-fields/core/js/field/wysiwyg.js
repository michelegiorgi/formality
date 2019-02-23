(function( $ ){

	var uf     = window.UltimateFields,
		field  = uf.Field,
		editor = UltimateFields.Field.WYSIWYG = { id: 0 };

	editor.Model = field.Textarea.Model.extend({});

	editor.View = field.Textarea.View.extend({
		/**
		 * Because of the way TinyMCE works, it needs to be regenerated
		 * when the field gets moved.
		 */
		initialize: function( args ) {
			var that = this;

			// Call the parent constructed
			field.View.prototype.initialize.call( this, args );

			// When the field is moved, regenerate the editor
			this.$el.closest( '.uf-field' ).on( 'uf-sorted', function(){
				that.render();
			});
		},

		/**
		 * Adds a jQuery callback for really rendering the field.
		 */
		render: function() {
			var that = this;

			if( $.isReady ) {
				this.reallyRender();
			} else {
				$( _.bind( that.reallyRender, that ) );
			}

			return this.$el;
		},

		/**
		 * Does the rendering.
		 */
		reallyRender: function() {
			var that = this, html, tmpl, id, mceInit;

			// Generate an ID for the editor
			this.id = id = 'uf_wysiwyg_' + editor.id++;

			// Load the template
			html = $( '#ultimate-fields-field-wysiwyg' ).html();
			tmpl = _.template( html );

			this.$el.html( tmpl({
				mceID: id,
				rows: this.model.get( 'rows' )
			}));

			// Add the content to the textarea.
			this.$el.find( 'textarea' ).val( this.model.getValue() || '' );

			// Differ the rendering of the editor to another thread
			setTimeout(function(){
				that.initMCE();
			}, 1);

			// Listen to any change in the textarea
			that.$el.find( 'textarea' ).on( 'change keyup', function(){
				that.model.setValue( $( this ).val() );
			});
		},

		/**
		 * Initializes TinyMCE & QT for the current editor.
		 */
		initMCE: function() {
			var that = this,
				id   = this.id + '_id';

			// Initialize the editor
			mceInit = $.extend( {}, tinyMCEPreInit.mceInit[ 'uf_dummy_editor_id' ], {
				body_class: id,
				elements: id,
				rows: this.model.get( 'rows' ) || 10,
				selector: '#' + id
			});

			// Setup TinyMCE if available
			if( 'undefined' != typeof tinymce ) {
				tinyMCEPreInit.mceInit[ id ] = $.extend( {}, mceInit );
				tinyMCEPreInit.mceInit[ id ].setup = function( editor ) {
					editor.on( 'change', function( e ) {
						var value = editor.getContent();

						// Fix empty paragraphs before un-wpautop
						value = value.replace( /<p>(?:<br ?\/?>|\u00a0|\uFEFF| )*<\/p>/g, '<p>&nbsp;</p>' );

						// Remove paragraphs
						value = switchEditors._wp_Nop( value );

						that.model.setValue( value );
					});
				}

				tinymce.init( tinyMCEPreInit.mceInit[ id ] );
			}

			// Setup quicktags
			var qtInit = $.extend( {}, tinyMCEPreInit.qtInit[ 'uf_dummy_editor_id' ], {
				id: id
			});
			tinyMCEPreInit.qtInit[ id ] = $.extend( {}, qtInit );
			quicktags( tinyMCEPreInit.qtInit[ id ] );

			// Init QuickTags
			QTags._buttonsInit();

			// Indicate tha there is no active editor
			if ( ! window.wpActiveEditor ) {
				window.wpActiveEditor = this.id;
			}
		},

		/**
		 * Focuses the input within the field.
		 */
		focus: function() {
			var $textarea = this.$el.find( 'textarea' );

			if( $textarea.is( ':visible' ) ) {
				$textarea.focus();
			} else {
				tinymce.execCommand( 'mceFocus', false, $textarea.attr( 'id' ) );
			}
		}
	});

})( jQuery );
