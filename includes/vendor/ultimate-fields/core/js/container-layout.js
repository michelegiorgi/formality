(function( $ ){

	var resizers = [];

	var triggerResizers = _.throttle( function() {
		_.each( resizers, function( resizer ) {
			resizer();
		});

		$( document ).trigger( 'uf-grid-resize' );
	}, 1 );

	$( window ).on( 'resize', triggerResizers );

	UltimateFields.ContainerLayout = Backbone.View.extend({
		initialize: function( args ) {
			// Flags
			this.gridAdded = false;

			// Save the args (not a model)
			this.args = $.extend( {
				mainPoint: 500,
				gridSelector: '.uf-field, .uf-section, .uf-tab-wrapper, .uf-inline-tab'
			}, args || {} );

			// Add a proper callback
			resizers.push( _.bind( this.update, this ) );
		},

		update: function() {
			var w = this.$el.width();
			var layout = this.args.layout;

			// Choose the correct layout and apply it
			if( 'rows' === this.args.layout ) {
				if( w > this.args.mainPoint ) {
					// Standard mode
					this.$el
						.addClass( 'uf-fields-layout-rows' )
						.removeClass( 'uf-fields-layout-grid' );

					layout = 'rows';
				} else {
					// When the width is not enough, switch to grid
					this.$el
						.addClass( 'uf-fields-layout-grid' )
						.removeClass( 'uf-fields-layout-rows' );

					layout = 'grid';
				}
			} else {
				this.$el
					.addClass( 'uf-fields-layout-grid' );
			}

			// Proxy the layout to the field wrappers
			_.each( this.args.fields, function( wrap ) {
				wrap.useLayout( layout );
			});

			// Update the grid lines when necessary
			if( 'grid' == layout ) {
				if( this.gridAdded ) {
					this.resizeGrid();
				} else {
					this.startGrid();
				}
			}

			// Change tabs
			if( this.args.tabs && this.args.container.allowsInlineTabs() ) {
				this.args.tabs
					.removeClass( 'uf-tabs-layout-' + ( 'rows' == layout ? 'grid' : 'rows' ) )
					.addClass( 'uf-tabs-layout-' + layout );

				if( w > this.args.mainPoint ) {
					this.$el.removeClass( 'uf-fields-inline-tabs' );
				} else {
					this.$el.addClass( 'uf-fields-inline-tabs' );
				}
			}
		},

		startGrid: function() {
			// Locate elements
			this.$gridElements = this.$el.children( this.args.gridSelector );

			this.resizeGrid();
			resizers.push( _.bind( this.resizeGrid, this ) );

			// Fix the grid when there is conditional logic or the tab changes
			if( this.args.container ) {
				this.args.container.model.get( 'fields' ).on( 'change:visible', triggerResizers );
				this.args.container.model.datastore.on( 'change:__tab', triggerResizers );
			}

			// Save the flag
			this.gridAdded = true;
		},

		resizeGrid: function() {
			var that = this, top, left;

			// Let fields update their sizes
			$( document ).trigger( 'uf-before-resize' );

			// Crawl
			this.$gridElements.filter( ':visible' ).each(function() {
				var $el = $( this ), elTop, elLeft;

				if( $el.hasClass( 'uf-tab-wrapper' ) || $el.hasClass( 'uf-inline-section' ) ) {
					top = left = undefined;
					return;
				}

				elTop  = $el.offset().top - parseInt( $el.css( 'margin-top' ) );
				elLeft = $el.offset().left - parseInt( $el.css( 'margin-left' ) );

				// For the first element, get the offsets
				if( 'undefined' == typeof left ) {
					left = elLeft;
					top  = elTop;
				}

				// Add the top index
				if( elTop == top ) {
					$el.addClass( 'top-row' );
				} else if( $el.hasClass( 'top-row' ) ) {
					$el.removeClass( 'top-row' );
				}

				// Add the left index
				if( elLeft == left ) {
					$el.addClass( 'first-col' );
				} else if( $el.hasClass( 'first-col' ) ) {
					$el.removeClass( 'first-col' );
				}
			});
		},

		adjustTabs: function( $tabs ) {
			this.$args.tabs = $tabs;
			this.update();
		}
	}, {
		DOMUpdated: function( withTimeout ) {
			if( 'undefined' == typeof withTimeout ) {
				withTimeout = false;
			}

			withTimeout
				? setTimeout( triggerResizers, 1 )
				: triggerResizers();
		}
	});

})( jQuery );
