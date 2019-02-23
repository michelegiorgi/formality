(function( $ ){

	var field        = UltimateFields.Field,
		objectField  = field.WP_Object,
		linkField    = field.Link = {};

	/**
	 * Add an extended model.
	 */
	linkField.Model = objectField.Model.extend({
		defaults: _.extend( {}, objectField.Model.prototype.defaults, {
			target_control: true
		}),
		
		/**
		 * Extracts data from the selected items in order to generate a correct value,
		 * mainly when something is selected in the chooser.
		 */
		save: function() {
			var link = false, value = _.clone( this.getValue() );

			this.get( 'chosen' ).each(function( item ) {
				link = item.get( 'id' );
			});

			// Save the value
			value.link = link;
			this.setValue( value );

			// Let the views know what happened
			this.trigger( 'save' );
		},

		/**
		 * Performs additional validation.
		 */
		validate: function() {
			// Check if there is a value to validate
			var value = this.getValue(), link, valid = true;

			if( value && ( 'object' == typeof value ) && ( 'link' in value ) ) {
				link = value.link;

				if( link && ! link.match( /^\w+_\d+$/ ) && link.length ) {
					// If there is a valid value, check if it's an URL
					if( ! link.match( /https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/ ) ) {
						valid = false;
					}
				}
			}

			// Final check
			if( ! valid ) {
				var message = 'Please enter a valid URL!';
				this.set( 'invalid', message );
				return message;
			} else {
				this.set( 'invalid', false );
			}

			// Fall back to the default validation
			return objectField.Model.prototype.validate.call( this );
		},

		/**
		 * Returns data, usable for SEO purposes.
		 */
		getSEOValue: function() {
			var value = this.getValue();

			if( ! value || false === value.link ) {
				return false;
			}

			if( value.link.match( /^\w+_\d+$/ ) ) {
				return this.generateSEOItemOutput( value.link );
			} else {
				return '<a href="%s">%s</a>'.replace( /%s/g, value.link );
			}
		}
	});

	/**
	 * Extend the singular object view.
	 */
	linkField.View = objectField.View.extend({
		events: {
			'change .uf-link-url-input':     'saveURL',
			'change .uf-link-new-tab-input': 'saveTab'
		},

		/**
		 * Adds listeners on initialisation.
		 */
		initialize: function() {
			var that = this;

			// When the model is saved, re-render the input
			this.model.on( 'save', function() {
				that.render();
			});
		},

		/**
		 * Renders the field.
		 */
		render: function() {
			var that   = this,
				tmpl   = UltimateFields.template( 'link' ),
				value  = this.model.getValue(),
				$tab, button;

			this.$el.html( tmpl({
				value: value,
				target_control: this.model.get( 'target_control' )
			}));

			// Locate elements
			$url = this.$el.find( '.uf-link-url-input' );
			$tab = this.$el.find( '.uf-link-new-tab' );

			// Add the "Select" button
			button = new UltimateFields.Button({
				text: this.model.get( 'button_text' ) || UltimateFields.L10N.localize( 'select' ),
				icon: 'dashicons-search',
				type: 'primary',
				callback: function() {
					that.choose(function() {
						$tab.hide();
					});
				}
			})

			this.$el.find( '.uf-link-chooser' ).append( button.$el );
			button.render();

			// Populate values
			if( value.link && 'function' != typeof value.link ) {
				if( value.link.match( /^\w+_\d+$/ ) ) {
					this.showObjectPreview( value );
				} else {
					$url.val( value.link );
				}
			}

			if( value.new_tab ) {
				$tab.find( 'input' ).prop({ checked: 'checked' });
			}
		},

		/**
		 * Saves the URL if manually typed.
		 */
		saveURL: function( e ) {
			var value = _.clone( this.model.getValue() );
			value.link = e.target.value;
			this.model.setValue( value );
		},

		/**
		 * Saves the tab setting.
		 */
		saveTab: function( e ) {
			var value = _.clone( this.model.getValue() );
			value.new_tab = e.target.checked;
			this.model.setValue( value );
		},

		/**
		 * Show the preview of an object instead of URL.
		 */
		showObjectPreview: function( value ) {
			var that     = this,
				$chooser = this.$el.find( '.uf-link-chooser' ),
				$new_tab = this.$el.find( '.uf-link-new-tab' ),
				tmpl     = UltimateFields.template( 'object-preview' ),
				item, $preview, toggleButton, removeButton, $buttons, $spinner;

			// Get the info about the current item
			item = this.model.getItemData( value.link );

			// If a post does not exist, reset the value
			if( ! item ) {
				$chooser.show();
				return;
			}

			// Remove old objects
			$chooser.hide().siblings( '.uf-object' ).remove();

			// Add the new view
			$preview = $( '<div class="uf-object" />' );
			$chooser.after( $preview );
			$preview.html( tmpl({
				item: item
			}));

			// Add buttons
			$buttons = this.$el.find( '.uf-object-buttons' );

			toggleButton = new UltimateFields.Button({
				text:    '',
				icon:    'dashicons-arrow-down',
				callback: function() {
					if( that.chooser ) {
						that.chooser.remove();
					} else {
						$spinner.addClass( 'is-active' );
						$new_tab.hide();

						// Open the chooser
						that.choose(function() {
							$spinner.removeClass( 'is-active' );
							toggleButton.model.set( 'icon', 'dashicons-arrow-up' );
						});

						// When the chooser is removed, show the tab control
						that.chooser.on( 'removed', function() { $new_tab.show(); });
						that.model.on( 'save',      function() { $new_tab.show(); });
					}
				}
			});

			toggleButton.$el.appendTo( $buttons );
			toggleButton.render();

			removeButton = new UltimateFields.Button({
				text: 'Remove',
				icon: 'dashicons-no',
				type: 'primary',
				cssClass: 'uf-button-right',
				callback: function() {
					var value = _.clone( that.model.getValue() );

					if( that.chooser ) that.chooser.remove();

					value.link = false;
					that.model.setValue( value );
					that.model.get( 'selected' ).reset();
					that.model.get( 'chosen' ).reset();
					that.render();
				}
			});

			removeButton.$el.appendTo( $buttons );
			removeButton.render();

			// Add a spinner
			$spinner = $( '<span class="spinner uf-object-spinner" />' );
			toggleButton.$el.after( $spinner );
		},

		/**
		 * Focuses the first meaningful item in the field.
		 */
		focus: function() {
			var $input = this.$el.find( '.uf-link-url-input' );

			if( $input.is( ':visible' ) && $input.length ) {
				$input.focus();
			} else {
				this.$el.find( '.uf-button:visible' ).eq( 0 ).focus();
			}
		}
	});


})( jQuery );
