(function( $ ){

	var field        = UltimateFields.Field,
		objectField  = field.WP_Object,
		objectsField = field.WP_Objects = {};

	/**
	 * Add an extended model.
	 */
	objectsField.Model = objectField.Model.extend({
		/**
		 * Extracts data from the selected items in order to generate a correct value
		 */
		save: function( silent ) {
			var value = [];

			this.get( 'chosen' ).sort();

			this.get( 'chosen' ).each(function( item ) {
				value.push( item.get( 'id' ) );
			});

			// Save the value
			this.setValue( value );

			// Let the views know what happened
			if( ! silent ) {
				this.trigger( 'save' );
			}
		},

		/**
		 * Converts everything selected to a proper element.
		 */
		loadChosenCollection: function() {
			var that  = this,
				items = [];

			_.each( this.getValue(), function( item ) {
				items.push( that.getItemData( item ) );
			});

			this.set( 'chosen', new objectField.Items( items ) );
		},

		/**
		 * Resets the value of the field.
		 */
		reset: function() {
			// Clear the normal value
			this.setValue( [] );

			// CLean collections
			this.get( 'chosen' ).reset();
			this.get( 'selected' ).reset();

			// Let views do their job
			this.trigger( 'save' );
		}
	});

	/**
	 * Extend the singular object view.
	 */
	objectsField.View = objectField.View.extend({
		/**
		 * Renders the field.
		 */
		render: function() {
			var that  = this,
				value = this.model.getValue();

			if( value.length ) {
				this.renderUI();
			} else {
				this.renderButton();
			}

			// Listen for changes
			this.model.on( 'save', function() {
				that.render();
			});

			this.model.get( 'chosen' ).on( 'remove', function() {
				if( 0 == that.model.get( 'chosen' ).length ) {
					that.renderButton();
				}
			});
		},

		/**
		 * Renders the button that opens an object chooser.
		 */
		renderButton: function() {
			var that = this, button, $spinner;

			// Cleanup
			this.$el.empty();

			// Add a select button
			button = new UltimateFields.Button({
				text:      this.model.get( 'button_text' ) || UltimateFields.L10N.localize( 'select-items' ),
				type:      'primary',
				icon:      'dashicons-search',
				cssClass:  'uf-object-select-button',
				callback:  function() {
					if( that.chooser ) {
						that.chooser.remove();
					} else {
						$spinner.addClass( 'is-active' );

						that.choose(function() {
							$spinner.removeClass( 'is-active' );
						});
					}
				}
			});

			button.$el.appendTo( this.$el );
			button.render();

			// Add a spinner
			$spinner = $( '<span class="spinner uf-object-spinner" />' );
			$spinner.appendTo( this.$el );
		},

		/**
		 * Renders the normal UI when items are selected
		 */
		renderUI: function() {
			var that = this,
				tmpl = UltimateFields.template( 'objects-preview' ),
				$list, toggleButton, removeButton, $buttons, $spinner;

			// Add the basic element
			this.$el.html( tmpl() );

			// Add individual items
			$list = this.$el.find( '.uf-objects-list' );
			this.model.loadChosenCollection();
			this.model.get( 'chosen' ).each(function( item ) {
				// Handle item deletions
				item.on( 'unchosen', function() {
					that.model.get( 'chosen' ).remove( item );
					that.model.save( true );
				});

				var view = new objectsField.ItemView({
					model: item
				});

				$list.append( view.$el );
				view.render();
			});

			// Make elements sortable
			$list.sortable({
				handle: '.uf-objects-item-handle',
				axis: 'y',
				stop: function() {
					$list.children().trigger( 'sorted' );
					that.model.save( true );
				}
			});

			// Add buttons
			$buttons = this.$el.find( '.uf-objects-buttons' );

			toggleButton = new UltimateFields.Button({
				text:    UltimateFields.L10N.localize( 'add-items' ),
				icon:    'dashicons-plus',
				callback: function() {
					if( that.chooser ) {
						that.chooser.remove();
					} else {
						$spinner.addClass( 'is-active' );

						that.choose(function() {
							$spinner.removeClass( 'is-active' );
						});
					}
				}
			});

			toggleButton.$el.appendTo( $buttons );
			toggleButton.render();

			renderButton = new UltimateFields.Button({
				text: UltimateFields.L10N.localize( 'remove-all' ),
				icon: 'dashicons-no',
				type: 'primary',
				cssClass: 'uf-button-right',
				callback: function() {
					that.model.reset();
				}
			});

			renderButton.$el.appendTo( $buttons );
			renderButton.render();

			// Add a spinner
			$spinner = $( '<span class="spinner uf-object-spinner" />' );
			toggleButton.$el.after( $spinner );
		}
	});

	/**
	 * Handles item views within the list.
	 */
	objectsField.ItemView = Backbone.View.extend({
		className: 'uf-objects-item',

		events: {
			'click .uf-objects-item-remove': 'remove',
			'sorted': 'saveSort'
		},

		render: function() {
			var that = this,
				tmpl = UltimateFields.template( 'objects-item-preview' );

			// Add the basic structure
			this.$el.html( tmpl() );

			// Add the preview
			this.$el.find( '.uf-objects-item-wrapper' ).html( this.model.get( 'html' ) );
		},

		/**
		 * Removes an item from the list.
		 */
		remove: function() {
			this.$el.remove();
			this.model.trigger( 'unchosen' );
			this.model.save();
		},

		/**
		 * Adds the items' index to its model.
		 */
		saveSort: function() {
			this.model.set( 'index', this.$el.index(), {
				silent: true
			});
		}
	});

})( jQuery );
