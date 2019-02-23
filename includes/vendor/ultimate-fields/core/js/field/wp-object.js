(function( $ ){

	var field       = UltimateFields.Field,
		objectField = field.WP_Object = { cache: {} };

	/**
	 * Basic model for object fields.
	 */
	objectField.Model = field.Model.extend({
		initialize: function() {
			var that = this;

			field.Model.prototype.initialize.call( this );

			// This will hold all items, which are visually selected.
			this.set( 'selected', new objectField.Items() );

			// This will hold all items, which are saved.
			this.set( 'chosen', new objectField.Items() );
		},

		/**
		 * When a datastore is set, load items from it.
		 */
		setDatastore: function( datastore ) {
			var selected;

			field.Model.prototype.setDatastore.apply( this, arguments );

			if( selected = this.getValue() ) {
				selected = this.getItemData( selected );
				this.get( 'chosen' ).add( selected );
			}
		},

		/**
		 * Loads items from the database.
		 */
		loadItems: function( mode, filters, callback, page ) {
			var that = this, data, success;

			// Data includes the full request
			data = {
				uf_action: 'get_objects_' + this.get( 'name' ),
				field_id:  this.get( 'id' ),
				nonce:     this.get( 'nonce' ),
				filters:   $.extend( { filter: true }, filters || {} ),
				selected:  this.get( 'multiple' ) ? this.getValue() : [ this.getValue() ],
				mode:      'search' == mode ? 'search' : 'initial',
				page:      page || 1,
				uf_ajax:   true
			};

			// Success callback
			success = function( result ) {
				var items = new objectField.Items( result.items );

				// Cache the result
				this.lastQuery = {
					mode: mode,
					filters: filters,
					result: result,
					page: data.page
				};

				items.each(function( item ) {
					objectField.cache[ item.get( 'id' ) ] = item.toJSON();
				});

				if( callback ) callback({
					items:   items,
					filters: result.filters,
					more:    result.more
				});
			}.bind( this );

			// Fire the call
			$.ajax({
				url:      window.location.href,
				type:     'post',
				dataType: 'json',
				data:     data,
				success:  success
			});
		},

		/**
		 * Loads the next page of results.
		 */
		loadMoreItems: function( callback ) {
			var mode    = this.lastQuery.mode,
				filters = this.lastQuery.filters,
				page    = this.lastQuery.page + 1;

			this.loadItems( mode, filters, callback, page );
		},

		/**
		 * Extracts data from the selected items in order to generate a correct value
		 */
		save: function() {
			var value = false;

			this.get( 'chosen' ).each(function( item ) {
				value = item.get( 'id' );
			});

			// Save the value
			this.setValue( value );

			// Let the views know what happened
			this.trigger( 'save' );
		},

		/**
		 * Resets the value of the field.
		 */
		reset: function() {
			// Clear the normal value
			this.setValue( false );

			// CLean collections
			this.get( 'chosen' ).reset();
			this.get( 'selected' ).reset();

			// Let views do their job
			this.trigger( 'save' );
		},

		/**
		 * Retrieves data about an item, either through AJAX or from cache.
		 */
		getItemData: function( item ) {
			var that = this, prepared, found;

			// Check for preloaded values
			prepared = this.datastore.get( this.get( 'name' ) + '_prepared' ) || {};

			if( found = _.findWhere( prepared, { id: item } ) ) {
				objectField.cache[ item ] = found;
				return found;
			}

			if( item in objectField.cache ) {
				return objectField.cache[ item ];
			}
		},

		/**
		 * Returns data, usable for SEO purposes.
		 */
		getSEOValue: function() {
			var output = _.map( this.getValue() || [], this.generateSEOItemOutput );

			return output.length ? '<ul><li>' + output.join( '</li><li>' ) + '</li></ul>' : false;
		},

		/**
		 * Generates a singular object-based link.
		 */
		generateSEOItemOutput: function( id ) {
			if( ! ( id in objectField.cache ) ) {
				return false;
			}

			var item = objectField.cache[ id ];

			return '<a href="%s">%s</a>'
				.replace( '%s', item.url )
				.replace(  '%s', item.title );
		}
	});

	/**
	 * Handles the input of the object field.
	 */
	objectField.View = field.View.extend({
		/**
		 * Renders the field.
		 */
		render: function() {
			var that  = this;

			if( this.model.get( 'chosen' ).length ) {
				this.renderUI();
			} else {
				this.renderButton();
			}

			// Listen for changes
			this.model.on( 'save', function() {
				that.render();
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
				text:      this.model.get( 'button_text' ) || UltimateFields.L10N.localize( 'select-item' ),
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
		 * Opens the item chooser.
		 */
		choose: function( callback ) {
			var that = this, chooser, $chooser;

			if( ( $chooser = this.$el.find( '.uf-chooser' ) ).length ) {
				return;
			}

			// Create a new chooser
			chooser = this.chooser = new objectField.ChooserView({
				model: this.model
			});

			chooser.prepare(function() {
				chooser.$el.appendTo( that.$el );
				chooser.render();
				chooser.show();

				if( callback ) {
					callback();
				}
			});

			chooser.on( 'removed', function() {
				if( that.toggleButton ) {
					that.toggleButton.model.set( 'icon', 'dashicons-arrow-down' );
				}

				that.chooser = false;
			});
		},

		/**
		 * Renders the normal UI when an item is selected.
		 */
		renderUI: function() {
			var that = this,
				tmpl = UltimateFields.template( 'object-preview' ),
				item, toggleButton, removeButton, $buttons, $spinner;

			// Get the info about the current item
			item = this.model.getItemData( this.model.getValue() );

			// If a post does not exist, reset the value
			if( ! item ) {
				this.model.setValue( false );
				this.renderButton();
				return;
			}

			// Add the basic element
			this.$el.html( tmpl({
				item: item
			}));

			// Add buttons
			$buttons = this.$el.find( '.uf-object-buttons' );

			toggleButton = this.toggleButton = new UltimateFields.Button({
				text:    '',
				icon:    'dashicons-arrow-down',
				callback: function() {
					if( that.chooser ) {
						that.chooser.remove();
					} else {
						$spinner.addClass( 'is-active' );

						that.choose(function() {
							$spinner.removeClass( 'is-active' );
							toggleButton.model.set( 'icon', 'dashicons-arrow-up' );
						});
					}
				}
			});

			toggleButton.$el.appendTo( $buttons );
			toggleButton.render();

			removeButton = new UltimateFields.Button({
				text: UltimateFields.L10N.localize( 'remove' ),
				icon: 'dashicons-no',
				type: 'primary',
				cssClass: 'uf-button-right',
				callback: function() {
					if( that.chooser ) that.chooser.remove();
					that.model.setValue( false );
					that.model.get( 'selected' ).reset();
					that.model.get( 'chosen' ).reset();
					that.model.trigger( 'save' );
				}
			});

			removeButton.$el.appendTo( $buttons );
			removeButton.render();

			// Add a spinner
			$spinner = $( '<span class="spinner uf-object-spinner" />' );
			toggleButton.$el.after( $spinner );
		},

		/**
		 * Focuses the first button within the input.
		 */
		focus: function() {
			this.$el.find( '.uf-button:eq(0)' ).focus();
		}
	});

	/**
	 * Handles the item, which allows users to choose items.
	 */
	objectField.ChooserView = Backbone.View.extend({
		className: 'uf-chooser-wrapper',

		/**
		 * Initializes the view.
		 */
		initialize: function() {
			this.filters = {
				search:  '',
				filters: [],
				page:    1
			}

			// Listen for changes, throttle keyups
			this.$el.on(
				'keyup',
				 '.uf-chooser-filter-input',
				 _.debounce( _.bind( this.filter, this ), 200 )
			 );

			this.$el.on(
				'change',
				'.uf-chooser-filter-type select',
				_.bind( this.filter, this )
			);
		},

		/**
		 * Pre-loads elements and calls the callback when ready to display.
		 */
		prepare: function( prepared ) {
			var that = this;

			this.model.loadItems( 'initial', this.filters, function( result ) {
				that.preloadedItems = result;
				prepared( result );
			});
		},

		/**
		 * Renders the chooser.
		 */
		render: function() {
			var that = this,
				tmpl = UltimateFields.template( 'object-chooser' ),
				$footer,
				selectButton, cancelButton;

			this.$el.html( tmpl({
				show_filters: ! this.model.get( 'hide_filters' ),
				filters: this.preloadedItems.filters
			}));

			$footer = this.$el.find( '.uf-chooser-footer' );

			// Add buttons to the chooser
			selectButton = new UltimateFields.Button({
				disabled: 0 == this.model.get( 'selected' ).length,
				text: UltimateFields.L10N.localize( 'select' ),
				type: 'primary',
				icon: 'dashicons-yes',
				callback: function() {
					that.save();
				}
			});

			selectButton.$el.appendTo( $footer );
			selectButton.render();

			// When the selection changes, update the select button
			this.model.get( 'selected' ).on( 'all', function() {
				selectButton.model.set( 'disabled', 0 == that.model.get( 'selected' ).length );
			});

			// Add a loader
			this.$spinner = $( '<span class="spinner uf-object-spinner" />' );
			selectButton.$el.after( this.$spinner );

			this.model.get( 'selected' ).on( 'add remove', function() {
				selectButton.model.set( 'disabled', 0 == that.model.get( 'selected' ).length )
			});

			// Add a button to close the chooser
			cancelButton = new UltimateFields.Button({
				text: UltimateFields.L10N.localize( 'cancel' ),
				icon: 'dashicons-no',
				cssClass: 'uf-chooser-button-right',
				callback: function(){
					that.remove();
					that.trigger( 'removed' );
				}
			});

			cancelButton.$el.appendTo( $footer );
			cancelButton.render();

			// Load the list
			that.renderItems( this.preloadedItems );

			// Add select2 to the type dropdown
			this.$el.find( '.uf-chooser-filter-type select' ).select2({
				placeholder: UltimateFields.L10N.localize( 'object-filter' )
			});
		},

		/**
		 * Hides the element.
		 */
		hide: function() {
			this.$el.hide();
		},

		/**
		 * Shows the chooser and focuses the search field.
		 */
		show: function() {
			this.$el.show()
				.find( '.uf-chooser-filter-input' ).focus();
		},

		/**
		 * Saves the current selection.
		 */
		save: function() {
			var chosen = this.model.get( 'chosen' );

			// Clear stuff
			if( ! this.model.get( 'multiple' ) ) {
				chosen.reset();
			}

			this.model.get( 'selected' ).each(function( item ) {
				chosen.add( item );
			});

			// Save the actual items
			this.model.save();
			this.remove();
		},

		/**
		 * Renders items into the list.
		 */
		renderItems: function( data ) {
			var that  = this,
				$list = this.$el.find( '.uf-chooser-list' ),
				loading = false;

			// Cleanup the list and events
			$list.empty();
			$list.off( 'scroll.uf-chooser' );

			// If there are no item, render a message only
			if( 0 === data.items.length ) {
				$list.addClass( 'uf-chooser-list-empty' );
				$list.html( UltimateFields.template( 'object-chooser-empty' )() );
				return;
			}

			$list.removeClass( 'uf-chooser-list-empty' );

			// Add normal items
			data.items.each(function( item ) {
				var view = that.renderItem( item );

				$list.append( view.$el );
				view.render();
			});

			// If there are more items to be loaded, add a loader
			if( data.more ) {
				$list.append( '<div class="uf-chooser-preloader"><span class="spinner is-active"></div>' );

				$list.on( 'scroll.uf-chooser', function( e ) {
					var list, height, full, scrolled;

					if( loading ) {
						return;
					}

					list     = $list.get( 0 );
					height   = list.offsetHeight;
					full     = list.scrollHeight;
					scrolled = list.scrollTop;

					if( 120 < full - height - scrolled ) {
						return;
					}

					// Start loading
					loading = true;

					that.model.loadMoreItems(function( newData ) {
						newData.items.each(function( item ) {
							var view = that.renderItem( item );

							$list.append( view.$el );
							view.render();

							if( ! newData.more ) {
								// Remove the placeholder
								$list.find( '.uf-chooser-preloader' ).remove();
								$list.off( 'scroll.uf-chooser' );
							} else {
								// Move the preloader to the end
								$list.append( $list.find( '.uf-chooser-preloader' ) );
							}

							// Unblock
							loading = false;
						});
					});
				});
			}
		},

		/**
		 * Renders a singular item and returns it's DOM element.
		 */
		renderItem: function( item ) {
			var that = this, view;

			view = new objectField.ItemView({
				model: item
			});

			// If the item is selected, save it in the collection and set the model flag to true.
			if( that.model.get( 'chosen' ).get( item.get( 'id' ) ) ) {
				item.set( 'selected', true );
				that.model.get( 'selected' ).add( item );
			}

			// Listen for changes
			item.on( 'change:selected', function() {
				that.itemSelected( item );
			});

			// If there is a maximum amount of items, make sure not to exeed it
			if( this.model.get( 'max' ) ) {
				this.controlItemLimits( item );

				this.model.get( 'selected' ).on( 'add remove', function() {
					that.controlItemLimits( item );
				});

				item.on( 'impossible_click', function() {
					var message = UltimateFields.L10N.localize( 'objects-max' ).replace( '%d', that.model.get( 'max' ) );
					alert( message );
				});
			}

			return view;
		},

		/**
		 * Adjusts an item to limits when there is a maximum set to the field.
		 */
		controlItemLimits: function( item ) {
			var max        = this.model.get( 'max' ) - this.model.get( 'chosen' ).length,
				selectable = max > this.model.get( 'selected' ).length;

			item.set( 'selectable', selectable );
		},

		/**
		 * Handles the selection of items.
		 */
		itemSelected: function( item ) {
			var that = this, selectedItems;

			// If the item is not selected, we're not doing anything
			if( ! item.get( 'selected' ) ) {
				this.model.get( 'selected' ).remove( item );
				return;
			}

			selectedItems = this.model.get( 'selected' );

			// Deselect other items if needed
			if( ! this.model.get( 'multiple' ) ) {
				selectedItems.each(function( selected ) {
					selected.set( 'selected', false );
				});

				selectedItems.reset();
			}

			// Add the item to the selected list, if selected
			selectedItems.add( item );
		},

		/**
		 * Uses the filters to get new results.
		 */
		filter: function() {
			var that = this, filters = {};

			// Extract and save the filters
			this.filters = filters = {
				search:  this.$el.find( '.uf-chooser-filter-input' ).val()       || '',
				filters: this.$el.find( '.uf-chooser-filter-type select' ).val() || []
			}

			// Empty the selected list
			this.model.get( 'selected' ).reset();

			// Start the loading process
			this.$spinner.addClass( 'is-active' );
			this.model.loadItems( 'search', this.filters, function( result ) {
				that.renderItems( result );
				that.$spinner.removeClass( 'is-active' );
			});
		},

		/**
		 * Removes the chooser
		 */
		remove: function() {
			this.model.get( 'selected' ).reset();
			this.$el.remove();
			this.trigger( 'removed' );
		}
	});

	/**
	 * Works as a model for items within the chooser.
	 */
	objectField.Item = Backbone.Model.extend({
		defaults: {
			selected: false,
			selectable: true
		}
	});

	/**
	 * A collection for items.
	 */
	objectField.Items = Backbone.Collection.extend({
		model: objectField.Item,

		comparator: function( item ) {
			return item.get( 'index' );
		}
	});

	/**
	 * Item views.
	 */
	objectField.ItemView = Backbone.View.extend({
		className: 'uf-chooser-item',

		events: {
			click: 'clicked'
		},

		render: function(){
			var that = this,
				tmpl = UltimateFields.template( 'object-item' );

			this.$el.html( tmpl( this.model.toJSON() ) );

			if( this.model.get( 'selected' ) ) this.toggle();
			this.model.on( 'change:selected', _.bind( this.toggle, this ) );
		},

		/**
		 * Toggles selection classes.
		 */
		toggle: function() {
			this.$el[ this.model.get( 'selected' ) ? 'addClass' : 'removeClass' ]( 'uf-chooser-item-selected' );
		},

		/**
		 * Selects the item.
		 */
		clicked: function( e ) {
			e.preventDefault();

			if( this.model.get( 'selected' ) ) {
				this.model.set( 'selected', false );
			} else {
				if( this.model.get( 'selectable' ) ) {
					this.model.set( 'selected', true );
				} else {
					this.model.trigger( 'impossible_click' );
				}
			}
		}
	});

})( jQuery );
