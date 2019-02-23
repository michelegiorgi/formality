(function( $ ){

	var overlay = UltimateFields.Overlay = {};

	// Models contain details like the title, parents and etc.
	overlay.Model = Backbone.Model.extend({
		defaults: {
			title: 'Overlay'
		}
	});

	// There will be a single instance of this collection at a time
	overlay.Collection = Backbone.Collection.extend({
		model: overlay.Model
	});

	// This will handle a single overlay
	overlay.View = Backbone.View.extend({
		className: 'uf-overlay-screen',

		initialize: function( args ) {
			var that = this;

			this.view = args.child;

			args.child.on( 'popScreen', function() {
				that.trigger( 'popScreen' );
			});
		},

		render: function() {
			this.$el.append( this.view.$el );
			if( this.model.get('media' ) ) {
				this.$el.addClass( 'uf-overlay-screen-media' );
			}
			this.view.render();
		},

		remove: function() {
			this.view.remove();
			this.$el.remove();
		}
	});

	// This will handle all open overlays
	overlay.Wrapper = Backbone.View.extend({
		className: 'wp-core-ui uf-overlay-wrapper',

		events: {
			'click .uf-overlay-header .parent': 'parentClicked',
			'click .uf-overlay-close': 'closeClicked'
		},

		initialize: function() {
			this.screens = new overlay.Collection();
			this.views = [];
			this.currentAlert = false;
			this.$el.attr( 'tabindex', 300 );
		},

		addScreen: function( args ) {
			var that = this, animate;

			this.views.push( args.view );
			this.screens.add( args.model );

			if( ! this.rendered ) {
				this.render();
				this.rendered = true;
				that.$el.focus();
			}

			args.view.$el.addClass( 'uf-overlay-body-level-' + this.screens.length );

			animate = this.screens.length > 1;
			if( animate ) {
				this.$el.find( '.uf-overlay-screen' ).addClass( 'animated' );
				args.view.$el.addClass( 'animated coming-in' );
			}

			this.$el.find( '.uf-overlay-body' ).append( args.view.$el );
			args.view.render();

			if( animate ) {
				args.view.$el.addClass( 'coming-in' ).siblings().addClass( 'going-out' );
				setTimeout(function() {
					args.view.$el.removeClass( 'coming-in' ).siblings().addClass( 'going-out' );
				}, 20);

				setTimeout(function() {
					that.$el.find( '.uf-overlay-body' ).removeClass( 'animated' );
					args.view.$el.siblings( '.uf-overlay-body' ).hide();
				}, 500 );
			}

			// Allow the screen to close itself
			if( 'function' == typeof args.view.view.attachToOverlay ) {
				args.view.view.attachToOverlay( this );
			}

			UltimateFields.ContainerLayout.DOMUpdated();
		},

		removeScreen: function() {
			var that = this, view, removed;

			if( that.currentAlert ) {
				var alert = that.currentAlert;

				that.currentAlert = false;

				// Hide the alert
				alert.removeClass( 'visible' );
				setTimeout(function() {
					alert.remove();
					alert = false;
				}, 300);

				return;
			}

			view = this.views.pop();
			this.screens.pop();

			if( 0 === this.screens.length ) {
				view.remove();
				this.remove();
				that.$el.off( 'keydown.uf-overlay' );
				$( 'body' ).removeClass( 'uf-overlay-open' );

				if( ! this.screens.length ) {
					delete overlay.current;
				}
			} else {
				view.$el.addClass( 'animated' ).prev().addClass( 'animated' );
				setTimeout(function() {
					view.$el.addClass( 'coming-in' ).prev().removeClass( 'going-out' );
				}, 20 );
				setTimeout(function() {
					view.remove();
					that.$el.focus();
				}, 520);
			}
		},

		render: function() {
			var that = this,
				tmpl = UltimateFields.template( 'overlay-wrapper' );

			this.$el.html( tmpl({

			}));

			this.adjustToScreen();
			this.screens.on( 'add remove', function() {
				that.adjustToScreen();
			});

			that.$el.on( 'keydown.uf-overlay', function( e ) {
				if( 27 == e.keyCode ) {
					e.stopImmediatePropagation();
					e.preventDefault();
					that.removeScreen();
				}
			});
		},

		adjustToScreen: function() {
			if( 0 == this.screens.length )
				return;

			this.setTitle();
			this.addButtons();
		},

		setTitle: function() {
			var that     = this,
				$title   = this.$el.find( '.uf-overlay-header h2' ),
				view     = this.views[ this.views.length - 1 ].view,
				$tabs    = ( 'function' == typeof view.getTabs ) ? view.getTabs() : false,
				$oldTabs = $title.siblings( '.uf-tab-wrapper' ),
				lastScreen, i = 1;

			$title.empty();

			// Add the icon of the current screen, if any
			lastScreen = this.screens.at( this.screens.length - 1 );
			if( lastScreen.get( 'icon' ) ) {
				$title.prepend( '<span class="' + lastScreen.get( 'icon' ) + '" />' );
			}

			this.screens.each(function( screen ) {
				if( i++ == that.screens.length ) {
					$title.append( $( '<span class="current" />' ).text( screen.get( 'title' ) ) );
				} else {
					var $link = $( '<a href="#" class="parent" />' ).text( screen.get( 'title' ) );
					$link.attr( 'data-back', that.screens.length - i + 1 );
					$title.append( $link );
					$title.append( '<span class="uf-overlay-separator"></span>' );
				}
			});

			$oldTabs.fadeOut( 300, function() {
				$( this ).remove();
			});

			if( $tabs.length ) {
				var $wrap;

				if( 'function' == typeof view.getTabsWrapper ) {
					$wrap = view.getTabsWrapper();
				} else {
					$wrap = $( '<div class="uf-tab-wrapper" />' ).append( $tabs );
				}

				that.$el.addClass( 'uf-overlay-has-tabs' );

				// append new tabs
				$title.after( $wrap );
			} else {
				that.$el.removeClass( 'uf-overlay-has-tabs' );
			}
		},

		addButtons: function() {
			var that    = this,
				buttons = this.screens.last().get( 'buttons' );

			if( ! buttons || ! buttons.length )
				return;

			this.$el.find( '.uf-overlay-footer' ).empty();

			_.each( buttons, function( button ) {
				var callback, args;

				if( button instanceof UltimateFields.Button ) {
					btn = button;

					// Overwrite the callback
					callback = button.model.get( 'callback' );

					button.model.set( 'callback', function() {
						if( callback ) {
							if( callback( that ) ) {
								that.removeScreen();
							}
						} else {
							that.removeScreen();
						}
					});
				} else {
					args = _.clone( button );

					if( 'callback' in args ) {
						callback = args.callback;
					}

					args.callback = function() {
						if( callback ) {
							if( callback( that ) ) {
								that.removeScreen();
							}
						} else {
							that.removeScreen();
						}
					};

					var btn = new UltimateFields.Button( args );
				}

				btn.$el.appendTo( that.$el.find( '.uf-overlay-footer' ) );
				btn.render();
			});
		},

		parentClicked: function( e ) {
			var i, steps = parseInt( $( e.target ).data( 'back' ) );

			e.preventDefault();

			for( i=0; i<steps; i++ )
				this.removeScreen();
		},

		closeClicked: function( e ) {
			e.preventDefault();
			e.currentTarget.blur();
			this.removeScreen();
		},

		/**
		 * Displays an alert overlay.
		 */
		alert: function( args ) {
			var that = this,
				tmpl = UltimateFields.template( 'overlay-alert' ),
				$alert, button;

			$alert = $( tmpl( {
				title: args.title
			}));

			// Add the normal body
			$alert.find( '.uf-overlay-alert-body' ).append( args.body );

			// Add buttons
			button = new UltimateFields.Button({
				text: 'OK',
				type: 'primary',
				callback: function() {
					$alert.removeClass( 'visible' );
					that.currentAlert = false;
					setTimeout(function() { $alert.remove() }, 300);
				}
			});

			// Add the alert to the dom
			button.$el.appendTo( $alert.find( '.uf-overlay-alert-footer' ) );
			button.render();

			// Show the alert
			this.$el.find( '.uf-overlay-box' ).append( $alert );
			setTimeout(function() {
				$alert.addClass( 'visible' );
				button.$el.focus();
			}, 30 );

			// Save a handle
			this.currentAlert = $alert;
		}
	});

	// This function will spawn a new overlay
	overlay.show = function( args ) {
		var current = overlay.current, model, view;

		model = new overlay.Model({
			title: args.title,
			icon: args.icon
		});

		if( 'buttons' in args ) {
			model.set( 'buttons', args.buttons );
		}

		if( 'media' in args ) {
			model.set( 'media', true );
		}

		view = new overlay.View({
			model: model,
			child: args.view
		});

		$( 'body' ).addClass( 'uf-overlay-open' );

		if( ! current ) {
			current = overlay.current = new overlay.Wrapper();
			current.$el.appendTo( 'body' );
			current.addScreen({
				view: view,
				model: model
			});
			// current.render();
		} else {
			current.addScreen({
				view: view,
				model: model
			});
		}

		return current;
	}

})( jQuery );
