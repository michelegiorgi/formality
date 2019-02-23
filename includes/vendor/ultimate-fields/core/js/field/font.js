(function( $ ){

	var field     = UltimateFields.Field,
		fontField = field.Font = {},
		index     = 0;

	/**
	 * Add some very basic static functions.
	 */
	_.extend( fontField, {
		/**
		 * Parses a font variant to weight and style.
		 */
		parseFontVariant: function( variant ) {
			var weight = 'normal',
				style  = 'normal';

			if( variant.match( /^\d+$/ ) ) {
				weight = variant;
			} else if( variant.match( /^\d+\w+$/ ) ) {
				weight = parseInt( variant );
				style  = variant.replace( /\d/g, '' );
			} else if( variant == 'italic' ) {
				style = 'italic';
			}

			return {
				weight: weight,
				style: style
			};
		},

		/**
		 * Retrieves the style attribute needed for a font variant.
		 */
		getVariantStyle: function( variant ) {
			var data = fontField.parseFontVariant( variant );
			return "font-weight: " + data.weight + "; font-style: " + data.style;
		},

		/**
		 * Retrieves the human-readable title of a font.
		 */
		getVariantDescription: function( variant ) {
			var data = fontField.parseFontVariant( variant );
			return UltimateFields.L10N.localize( 'font-description' ).replace( '%s', data.weight ).replace( '%s', data.style );
		}
	});

	/**
	 * Handles the datepicker input of a field.
	 */
	fontField.View = field.View.extend({
		/**
		 * Chooses which view to render.
		 */
		render: function() {
			if( this.model.getValue() ) {
				this.renderPreview();
			} else {
				this.renderButton();
			}
		},

		/**
		 * Renders a button, which will open the popup for selecting a font.
		 */
		renderButton: function() {
			var that = this, button;

			// This button will open the popup
			button = new UltimateFields.Button({
				text:     UltimateFields.L10N.localize( 'select-font' ),
				type:     'primary',
				icon:     'dashicons-editor-textcolor',
				callback: _.bind( this.openPopup, this )
			});

			// Ensure there is a blank canvas and add the button
			this.$el.empty().append( button.$el );

			button.render();
		},

		/**
		 * Opens the popup for choosing fonts.
		 */
		openPopup: function() {
			var that = this, overlayLayer;

			// Create the view
			view = new fontField.PopupView({
				model: this.model
			});

			// Show the overlay
			overlayLayer = UltimateFields.Overlay.show({
				view: view,
				title: UltimateFields.L10N.localize( 'select-font' ),
				buttons: view.getButtons()
			});

			view.$el.parent().addClass( 'uf-fonts-overlay-screen' );

			// Listen for saving
			view.on( 'save', function( fontModel, variants ) {
				that.model.setValue({
					family:     fontModel.get( 'family' ),
					variants: variants
				});

				// Remove the overlay
				overlayLayer.removeScreen();

				// Re-render the field
				that.render();
			});
		},

		/**
		 * Renders the preview of the font.
		 */
		renderPreview: function() {
			var that  = this,
				tmpl  = UltimateFields.template( 'font' ),
				value = this.model.getValue(),
				font, button;

			// Compatibility stuff
			if( 'variations' in value ) {
				value.variants = value.variations;
			}

			// Get the font
			setTimeout(function() {
				var url = value.family.replace( /\s/g, '+' ) + ':' + value.variants.join( ',' );
				$( "<link href='http://fonts.googleapis.com/css?family=" + url + "' rel='stylesheet' type='text/css' />" ).appendTo( 'head' );
			}, 30);

			// Add the preview
			this.$el.html( tmpl( {
				font:                  value,
				getVariantStyle:       fontField.getVariantStyle,
				getVariantDescription: fontField.getVariantDescription,
			}));

			// Add buttons
			$footer = this.$el.find( '.uf-font-footer' );

			button = new UltimateFields.Button({
				text:     UltimateFields.L10N.localize( 'change-font' ),
				icon:     'dashicons-search',
				type:     'primary',
				callback: _.bind( that.openPopup, that )
			});

			$footer.append( button.$el );
			button.render();

			button = new UltimateFields.Button({
				text:     UltimateFields.L10N.localize( 'font-clear' ),
				icon:     'dashicons-no',
				callback: _.bind( that.clear, that )
			});

			$footer.append( button.$el );
			button.render();
		},

		/**
		 * Clears the value of the field.
		 */
		clear: function() {
			this.model.setValue( false );
			this.render();
		},

		/**
		 * Focuses the input
		 */
		focus: function() {
			this.$el.find( '.uf-button:eq(0)' ).focus();
		}
	});

	/**
	 * Works as the first popup, which appears inth e overlay and lists fonts.
	 */
	fontField.PopupView = Backbone.View.extend({
		/**
		 * Renders the popup.
		 */
		render: function() {
			var that = this,
				tmpl = UltimateFields.template( 'font-popup' );

			this.$el.html( tmpl() );

			// Add elements
			this.addFilters();
			this.loadFonts();
		},

		/**
		 * Returns the buttons for the popup.
		 */
		getButtons: function() {
			return [
				{
					text:     UltimateFields.L10N.localize( 'cancel' ),
					icon:     'dashicons-no',
					callback: function() { return true; }
				}
			];
		},

		/**
		 * Renders the filters in the sidebar.
		 */
		addFilters: function() {
			var that = this, fields, $sidebar;

			fields = [
				{
					type:  'Textarea',
					name:  'text',
					label: UltimateFields.L10N.localize( 'font-preview-text' ),
					rows:  5
				}, {
					type:        'Text',
					label:       UltimateFields.L10N.localize( 'font-search' ),
					name:        'search',
				}, {
					type:        'Multiselect',
					label:       UltimateFields.L10N.localize( 'font-categories' ),
					name:        'categories',
					input_type:  'checkbox',
					orientation: 'vertical',
					options:     this.model.get( 'categories' ),
					field_width: 50
				}, {
					type:        'Multiselect',
					label:       UltimateFields.L10N.localize( 'font-subsets' ),
					name:        'subsets',
					input_type:  'checkbox',
					orientation: 'vertical',
					options:     this.model.get( 'subsets' ),
					field_width: 50
				}
			];

			$sidebar = this.$el.find( '.media-sidebar' ).container({
				fields: fields,
				id:     'font-filter',
				layout: 'grid'
			}, {
				text:       'Grumpy wizards make toxic brew for the evil Queen and Jack.',
				search:     '',
				categories: [],
				subsets:    []
			});

			$sidebar.on( 'values-changed', function( e, values ) {
				that.applyFilters( values );
			});
		},

		/**
		 * Loads all available fonts and lists them afterwards.
		 */
		loadFonts: function() {
			var that = this;

			$.ajax({
				type: 'post',
				url: window.location.href,
				data: {
					uf_action: 'get_fonts_list_' + this.model.get( 'name' ),
					nonce:     this.model.get( 'nonce' )
				},
				success: function( data ) {
					that.model.set( 'fonts', new fontField.Fonts( $.parseJSON( data ) ) );
					that.fontsLoaded();
				}
			});
		},

		/**
		 * Filters fonts based on the values in the sidebar.
		 */
		applyFilters: function( filters ) {
			var that = this, s = filters.search || false, available = 0;

			this.model.get( 'fonts' ).each(function( font ) {
				var visible = true;

				font.set( 'previewText', filters.text );

				if( filters.subsets.length ) {
					_.each( filters.subsets, function( subset ) {
						if( -1 == font.get( 'subsets' ).indexOf( subset ) ) {
							visible = false;
						}
					});
				}

				if( visible && filters.categories.length ) {
					if( -1 == filters.categories.indexOf( font.get( 'category' ) ) ) {
						visible = false;
					}
				}

				if( visible && s ) {
					visible = font.get( 'family' ).toLowerCase().indexOf( s ) != -1;
				}

				font.set( 'filtered', ! visible );
				if( visible ) {
					available++;
				}
			});

			this.pagination.set({
				page:  1,
				total: available,
				max:   Math.ceil( available / 20 )
			});

			that.listFonts( 1 );
		},

		/**
		 * Lists fonts once they're loaded.
		 */
		fontsLoaded: function() {
			var that   = this;

			// List the first page
			this.listFonts( 1 );

			// Add the pagination
			this.addPagination();
			this.pagination.on( 'change:page', function() {
				that.listFonts( that.pagination.get( 'page' ) );
			});
		},

		/**
		 * Lists a specific set of fonts.
		 */
		listFonts: function( page ) {
			var that      = this,
				$list     = this.$el.find( '.uf-fonts-list' ),
				available = this.model.get( 'fonts' ).where({ filtered: false }),
				i;

			// Clear old ones
			$list.empty();

			// Add fonts
			for( i = ( page - 1 ) * 20; i < Math.min( page * 20 - 1, available.length ); i++ ) {
				this.renderFont( $list, available[ i ] );
			}

			// Go through the visible fonts and add classes
			i = 0;
			$list.children( ':visible' ).each(function() {
				$( this )[ ++i % 2 ? 'addClass' : 'removeClass' ]( 'even' );
			});

		},

		/**
		 * Generates the pagination that is shown in the bottom of the popup.
		 */
		addPagination: function() {
			var that = this,
				model, view, max = this.model.get( 'fonts' ).length;

			this.pagination = model = new UltimateFields.Pagination.Model({
				page:  1,
				max:   Math.ceil( parseInt( max ) / 20 ),
				total: max,
				labels: [ UltimateFields.L10N.localize( 'font' ), UltimateFields.L10N.localize( 'fonts' ) ]
			});

			view = new UltimateFields.Pagination.View({
				model: model
			});

			view.$el.appendTo( this.$el.find( '.uf-fonts-pagination' ) );
			view.render();
		},

		/**
		 * Renders a font in the list.
		 */
		renderFont: function( $list, font ) {
			var that = this, view;

			// Load that first family
			this.loadFont( font.get( 'family' ), font.get( 'variants' )[ 0 ] );

			// Generate the view
			view = new fontField.FontListView({
				model: font
			});

			$list.append( view.$el );
			view.render();

			// Handle clicks
			view.on( 'clicked', function() {
				that.openFont( font );
			});
		},

		/**
		 * Loads a single font, totally async.
		 */
		loadFont: function( family, variants ) {
			var url = family.replace( /\s/g, '+' ) + ':', html;

			// Prepare the URL either for a single font or an array of variants.
			if( typeof variants == 'string' ) {
				url += variants;
			} else {
				url += variants.join( ',' );
			}

			// Prepare the HTML
			html = "<link href='http://fonts.googleapis.com/css?family=" + url + "' rel='stylesheet' type='text/css' />";

			// Load the font, but with a little delay so nothing is blocked
			setTimeout(function() {
				$( html ).appendTo( 'head' );
			}, 30);
		},

		/**
		 * Adds an overlay level for the font selection.
		 */
		openFont: function( font ) {
			var that = this, view;

			// Create the view
			view = new fontField.FontVariantsView({
				model: font
			});

			// Show the overlay
			UltimateFields.Overlay.show({
				view: view,
				title: font.get( 'family' ),
				buttons: view.getButtons()
			});

			// Handle saving
			view.on( 'save', function( variants ) {
				// Bubble the event up to the field
				that.trigger( 'save', font, variants );
			});
		}
	});

	/**
	 * Handles fonts.
	 */
	fontField.FontModel = Backbone.Model.extend({
		defaults: {
			filtered:    false,
			previewText: 'Grumpy wizards make toxic brew for the evil Queen and Jack.'
		}
	});

	/**
	 * Handles a collection of fonts.
	 */
	fontField.Fonts = Backbone.Collection.extend({
		model: fontField.FontModel
	});

	/**
	 * Displays a preview of the font within the list in the popup.
	 */
	fontField.FontListView = Backbone.View.extend({
		className: 'uf-fonts-font',

		events: {
			click: 'clicked'
		},

		/**
		 * Renders the preview.
		 */
		render: function() {
			var that    = this,
				tmpl = UltimateFields.template( 'font-preview' );

			// Add attributes
			this.$el.css( 'font-family', this.model.get( 'family' ) );

			// Add the DOM element for the font.
			$font = this.$el.html( tmpl( this.model.toJSON() ) );
		},

		/**
		 * Handles clicks on the font.
		 */
		clicked: function() {
			this.trigger( 'clicked' );
		}
	});

	/**
	 * This is the view for picking font variants.
	 */
	fontField.FontVariantsView = Backbone.View.extend({
		className: 'uf-variants',

		events: {
			'change :checkbox': 'checkboxChanged',
		},

		/**
		 * Initializes the view.
		 */
		initialize: function( args ) {
			args = args || {};

			this.text = this.model.get( 'previewText' );
			this.selected = [];
		},

		/**
		 * Renders the view for the selector.
		 */
		render: function() {
			var that = this,
				tmpl = UltimateFields.template( 'font-variants' );

			this.$el.html( tmpl( {
				family:                this.model.get( 'family' ),
				variants:              this.model.get( 'variants' ),
				selected:              [],
				text:                  this.text,
				getVariantStyle:       fontField.getVariantStyle,
				getVariantDescription: fontField.getVariantDescription
			}));
		},

		getButtons: function() {
			var that = this;

			return [
				this.selectButton = new UltimateFields.Button({
					text:     UltimateFields.L10N.localize( 'select' ),
					disabled: true,
					type:     'primary',
					icon:     'dashicons-edit',
					callback: _.bind( that.save, that )
				}),
				{
					text:     UltimateFields.L10N.localize( 'cancel' ),
					callback: function() { return true; },
					icon:     'dashicons-no'
				}
			];
		},

		/**
		 * Handles the changes of a selectobx.
		 */
		checkboxChanged: function() {
			var that = this, selected = [];

			this.$el.find( ':checked' ).each(function(){
				selected.push( this.value );
			});

			// Cache the value, it will be saved with the close button in the popup.
			this.selected = selected;

			// Change the state of the select button if needed
			this.selectButton.model.set( 'disabled', selected.length == 0 );
		},

		/**
		 * Indicates that the font has been chosen.
		 */
		save: function() {
			this.trigger( 'save', this.selected );

			return true;
		}
	})

})( jQuery );
