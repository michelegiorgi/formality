(function( $ ){

	var field      = UltimateFields.Field,
		embedField = field.Embed = {};

	// This will cache embed codes
	embedField.cache = {};

	/**
	 * Basic model for the number field.
	 */
	embedField.Model = field.Model.extend({
		setDatastore: function( datastore ) {
			var url, code;

			field.Model.prototype.setDatastore.call( this, datastore );

			// Look for cache
			if( code = datastore.get( this.get( 'name' )  + '_embed_code' ) ) {
				embedField.cache[ this.getValue() ] = {
					url: this.getValue(),
					code: code
				};
			}
		}
	});

	/**
	 * Handles the input of the number field.
	 */
	embedField.View = field.View.extend({
		/**
		 * Renders the fields' view.
		 */
		render: function() {
			var that = this,
				tmpl = UltimateFields.template( 'embed' ),
				callback, url, code = '';

			// Prepare the URL and cache it if needed
			if( url = this.model.getValue() ) {
				if( url in embedField.cache ) {
					code = embedField.cache[ url ].code;
				} else if( code = this.model.datastore.get( this.model.get( 'name' ) + '_embed_code' ) ) {
					embedField.cache[ url ] = {
						url:  url,
						code: code
					}
				} else {
					code = '';
				}

				this.currentlyPreviewed = url;
			} else {
				this.currentlyPreviewed = false;
			}

			this.$el.html( tmpl({
				url:  url,
				code: code
			}));

			this.clearButton = new UltimateFields.Button({
				text: UltimateFields.L10N.localize( 'clear' ),
				icon: 'dashicons-no',
				cssClass: 'uf-button-right',
				callback: _.bind( this.clear, this )
			});

			// Create a clear button
			this.clearButton.$el.appendTo( this.$el.find( '.uf-embed-footer' ) );
			this.clearButton.render();

			// Hide the clear button if there is no value
			if( ! this.model.getValue() || ! this.model.getValue().length ) {
				this.clearButton.$el.hide();
			}

			// Listen for URL changes (here because of _.throttle)
			callback = _.throttle( _.bind( this.valueChanged, this ), 300 );
			this.$el.on( 'change keyup', '.uf-embed-url', callback );
		},

		/**
		 * Reacts to changes in the value.
		 */
		valueChanged: function() {
			var that  = this,
				value = this.$el.find( '.uf-embed-url' ).val(),
				isURL = true;

			// Determine if the entered value has any chance of being an existing URL
			isURL = value.match( /https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&\/\/=]*)/i );

			if( isURL ) {
				if( value in embedField.cache ) {
					this.showPreview( embedField.cache[ value ] );
				} else {
					this.loadPreview( value );
				}
			} else {
				this.clearPreview();
			}

			// Toggle the clear button
			if( value.length ) {
				this.clearButton.$el.show();
			} else {
				this.clearButton.$el.hide();
			}

			// If the value is a URL or empty (both valid), save it
			if( ! value.length || isURL ) {
				this.model.setValue( value );
			}
		},

		/**
		 * Loads the preview of the field, while controlling the loader.
		 */
		loadPreview: function( url ) {
			var that = this;

			// Trigger a mechanism that prevents double queries
			if( this.currentlyLoadedPreview == url ) {
				return;
			}
			this.currentlyLoadedPreview = url;

			// Indicate that something is loading
			this.$el.find( '.uf-embed-top' ).addClass( 'uf-embed-loading' );

			$.ajax({
				url: window.location.href,
				type: 'post',
				dataType: 'json',
				data: {
					uf_action: 'get_embed_' + this.model.get( 'name' ),
					embed_url: url,
					nonce:     this.model.get( 'nonce' )
				},
				success: function( result ) {
					embedField.cache[ result.url ] = result;
					that.$el.find( '.uf-embed-top' ).removeClass( 'uf-embed-loading' );
					that.showPreview( result );
				},
				error: function() {
					alert( 'An error occurred. Please try again!' );
				}
			})
		},

		showPreview: function( data ) {
			var classMethod;

			// Don't update unless changed
			if( this.currentlyPreviewed == data.url ) {
				return;
			}

			// Update the preview
			classMethod = -1 != data.code.indexOf( 'iframe' ) ? 'addClass' : 'removeClass';
			this.currentlyPreviewed = data.url
			this.$el.find( '.uf-embed-preview' )
				.html( data.code )
				[ classMethod ]( 'uf-embed-preview-iframe' );

			this.$el.find( '.uf-embed-top' ).removeClass( 'uf-embed-empty' );
		},

		/**
		 * Clears the preview when there is nothing appropriate.
		 */
		clearPreview: function() {
			this.$el.find( '.uf-embed-top' )
			 	.addClass( 'uf-embed-empty' )
				.find( '.uf-embed-preview' )
				.empty();

			this.currentlyPreviewed = false;
		},

		/**
		 * Clears the preview and the input.
		 */
		clear: function() {
			this.clearPreview();
			this.$el.find( '.uf-embed-url' ).val( '' );
			this.model.setValue( false );
			this.clearButton.$el.hide();
		},

		/**
		 * Focuses the address input.
		 */
		focus: function() {
			this.$el.find( '.uf-embed-url' ).focus();
		}
	});

})( jQuery );
