(function( $ ){

	var field     = UltimateFields.Field,
		iconField = field.Icon = {};

	// Holds added stylesheets to prevent duplication
	iconField.loadedStylesheets = []

	// Holds the last popup ID
	iconField.lastPopupID = 0;

	// Loads a stylesheet
	iconField.loadStylesheet = function( stylesheet ) {
		if( -1 != iconField.loadedStylesheets.indexOf( stylesheet ) ) {
			return;
		}

		iconField.loadedStylesheets.push( stylesheet );

		$( '<link rel="stylesheet" type="text/css" />' )
			.attr( 'href', stylesheet )
			.appendTo( 'head' );
	}

	/**
	 * Basic model for the icon field.
	 */
	iconField.Model = field.Model.extend({
		/**
		 * Upon initialization, load icon sets.
		 */
		initialize: function() {
			field.Model.prototype.initialize.apply( this, arguments );

			_.each( this.get( 'icon_sets' ), iconField.Model.loadSet );
		},

		/**
		 * Returns the sets of the model.
		 */
		getIconSets: function() {
			var sets = {};

			_.each( this.get( 'icon_sets' ), function( name ) {
				sets[ name ] = iconField.Model.sets[ name ];
			});

			return sets;
		}
	}, {
		sets: {},

		/**
		 * Retrieves a set from the localized values.
		 */
		loadSet: function( name ) {
			if( name in iconField.Model.sets ) {
				return; // Already loaded
			}

			iconField.Model.sets[ name ] = UltimateFields.L10N.localize( 'icon_sets' )[  name ];
		},

		/**
		 * Locates an icon within sets.
		 */
		locate: function( className ) {
			var result;

			// Create the skeleton
			result = {
				setKey:    '',
				set:       false,
				group:     '',
				className: className
			}

			// Go through sets
			_.each( iconField.Model.sets, function( set, key ) {
				_.each( set.groups, function( group ) {
					if( -1 != group.icons.indexOf( className ) ) {
						result.setKey = key;
						result.set    = set;
						result.group  = group.groupName;

						return false;
					}
				});
			});

			return result;
		}
	});

	/**
	 * Handles the input of the icon field.
	 */
	iconField.View = field.View.extend({
		/**
		 * Renders the appropriate main view.
		 */
		render: function() {
			if( this.model.getValue() ) {
				this.renderPreview();
			} else {
				this.renderButton();
			}
		},

		/**
		 * Returns the button for selecting an icon (used in both top views).
		 */
		getSelectButton: function() {
			return new UltimateFields.Button({
				text:     UltimateFields.L10N.localize( this.model.getValue() ? 'change-icon' : 'select-icon' ),
				icon:     'dashicons-search',
				type:     'primary',
				callback: _.bind( this.openPopup, this )
			});
		},

		/**
		 * Renders the button for selecting an icon.
		 */
		renderButton: function() {
			var that   = this,
				button = this.getSelectButton();

			this.$el.empty().append( button.$el );
			button.render();
		},

		/**
		 * Renders the preview when an icon has been chosen
		 */
		renderPreview: function() {
			var that = this,
				tmpl = UltimateFields.template( 'icon-preview' ), icon, button;

			// Locate the icon set
			icon = iconField.Model.locate( this.model.getValue() );

			// Load the stylesheet if needed
			if( icon.set.stylesheet ) {
				iconField.loadStylesheet( icon.set.stylesheet );
			}

			// Load the template
			this.$el.html( tmpl({
				icon: icon.set.prefix + ' ' + icon.className
			}));

			// Add a select button
			button = this.getSelectButton();
			this.$el.find( '.uf-icon-preview' ).append( button.$el );
			button.render();

			// Add a clear button
			button = new UltimateFields.Button({
				text: UltimateFields.L10N.localize( 'remove-icon' ),
				icon: 'dashicons-no',
				callback: function() {
					that.model.setValue( false );
					that.render();
				}
			});

			this.$el.find( '.uf-icon-preview' ).append( button.$el );
			button.render();
		},

		/**
		 * Opens the popup for selecting an icon.
		 */
		openPopup: function() {
			var that = this, overlayLayer;

			// Create the view
			view = new iconField.PopupView({
				model: this.model
			});

			// Show the overlay
			console.time( 'opening' );
			overlayLayer = UltimateFields.Overlay.show({
				view: view,
				title: UltimateFields.L10N.localize( 'select-icon' ),
				buttons: view.getButtons()
			});

			// Listen for saving
			view.on( 'save', function( icon ) {
				that.model.setValue( icon );

				// Remove the overlay
				overlayLayer.removeScreen();

				// Re-render the field
				that.render();
			});
		},

		/**
		 * Focuses the input
		 */
		focus: function() {
			this.$el.find( '.uf-button:eq(0)' ).focus();
		}
	});

	/**
	 * Works as the layer within a popup, which allows an icon to be selected.
	 */
	iconField.PopupView = Backbone.View.extend({
		className: 'uf-icon-popup',

		events: {
			'change input:radio': 'changed',
			'keyup .uf-icon-search-input': 'search'
		},

		/**
		 * Renders the view.
		 */
		render: function() {
			var that = this,
				tmpl = UltimateFields.template( 'icon-popup' ),
				value = this.currentValue = this.model.getValue(), icon;

			// Render the basic structure
			this.$el.html( tmpl({
				sets:      this.model.getIconSets(),
				inputName: 'uf-icon-popup-' + ( ++iconField.lastPopupID )
			}));

			// Populate the sidebar
			this.populateSidebarPreview();

			// Toggle tabs
			setTimeout(function() {
				var $tab = that.$el.find( '.uf-icon-list-' + that.model.get( '__tab' ) ).show();

				// Select the current icon
				if( value ) {
					var $checked = that.$el.find( 'input[type=radio]' ).filter( '[value="' + value + '"]' ).prop({
						checked: 'checked'
					});

					// Scroll if needed
					$tab.scrollTop( $checked.offset().top - $tab.offset().top );
				}
			}, 10 );

			this.model.on( 'change:__tab', function() {
				that.$el.find( '.uf-icon-list-' + that.model.get( '__tab' ) ).show().siblings( '.uf-icon-list' ).hide();
			});
		},

		/**
		 * Returns the buttons for the popup.
		 */
		getButtons: function() {
			var that    = this,
				buttons = [];

			buttons.push( this.selectButton = new UltimateFields.Button({
				text:     UltimateFields.L10N.localize( 'select-icon' ),
				icon:     'dashicons-yes',
				disabled: this.model.getValue() ? false : true,
				type:     'primary',
				callback: _.bind( this.save, this )

			}));

			buttons.push({
				text:     UltimateFields.L10N.localize( 'cancel' ),
				icon:     'dashicons-no',
				callback: function() { return true; }
			});

			return buttons;
		},

		/**
		 * Returns the tabs for the popup.
		 */
		getTabs: function() {
			var that      = this,
				tabs      = [],
				datastore;

			// This datastore is meant to work only for the tabs
			datastore = new UltimateFields.Datastore;

			// Pre-select tab if needed
			this.icon = this.model.getValue()
				? iconField.Model.locate( this.model.getValue() )
				: false;

			// Since there is a value, see which tab is active
			if( this.icon ) {
				datastore.set( '__tab', this.icon.setKey );
				this.model.set( '__tab', this.icon.setKey );
			}

			// Go through each set and create a tab
			_.each( this.model.getIconSets(), function( set, key ) {
				var model, view;

				// While here, load styles
				if( set.stylesheet ) {
					iconField.loadStylesheet( set.stylesheet );
				}

				model = new Backbone.Model({
					invalidTab: false,
					icon:       false,
					label:      set.name,
					name:       key,
					visible:    true
				});

				if( ! datastore.get( '__tab' ) ) {
					datastore.set( '__tab', key );
					that.model.set( '__tab', key );
				}

				model.datastore = datastore;

				view = new UltimateFields.Tab({
					model: model
				});

				tabs.push( view.$el );
			});

			// Listen to tab changes
			datastore.on( 'change:__tab', function() {
				that.model.set( '__tab', datastore.get( '__tab' ) );
			});

			// Add tabs, only if there is a single tab
			return 1 == tabs.length ? [] : tabs;
		},

		/**
		 * Saves the value
		 */
		save: function() {
			// Save the value
			this.trigger( 'save', this.currentValue );
		},

		/**
		 * Handles changes of the input.
		 */
		changed: function( e ) {
			if( ! e.target.checked ) {
				return;
			}

			// Save the value
			this.currentValue = e.target.value;

			// Toggle the button
			this.selectButton.model.set( 'disabled', false );

			// Update the preview
			this.populateSidebarPreview();
		},

		/**
		 * Changes the icon in the sidebar.
		 */
		populateSidebarPreview: function() {
			var value = this.currentValue || this.model.getValue(), icon, className;

			if( ! value ) {
				this.$el.find( '.uf-icon-current' ).hide();
				return;
			}

			icon = iconField.Model.locate( value );
			className = 'uf-icon-current-span ' + icon.set.prefix + ' ' + icon.className;

			this.$el.find( '.uf-icon-current' ).show();
			this.$el.find( '.uf-icon-current-span' ).attr( 'class', className );
			this.$el.find( '.uf-icon-current-name' ).text( icon.className );
		},

		/**
		 * Filters the results in the viewport
		 */
		search: function() {
			var that = this,
				q    = this.$el.find( '.uf-icon-search-input' ).val().toLowerCase();

			console.time( 'search' );

			if( q.length ) {
				this.$el.find( '.uf-icon-group' ).each(function() {
					var $group     = $( this ),
					$selectors = $group.find( '.uf-icon-group-selector' );

					$selectors.each(function() {
						var $selector = $( this );

						$selector[ -1 == $selector.data( 'icon' ).toLowerCase().indexOf( q ) ? 'addClass' : 'removeClass' ]( 'uf-icon-group-selector-hidden' );
					});

					$group[ $selectors.not( '.uf-icon-group-selector-hidden' ).length ? 'removeClass' : 'addClass' ]( 'uf-icon-group-hidden' );
				});
			} else {
				this.$el.find( '.uf-icon-group, .uf-icon-group-selector' ).removeClass( 'uf-icon-group-hidden uf-icon-group-selector-hidden' );
			}

			console.timeEnd( 'search' );
		}
	});

})( jQuery );
