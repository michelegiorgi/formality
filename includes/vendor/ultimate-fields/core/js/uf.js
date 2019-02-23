(function( $ ){

	var root = window.UltimateFields = window.UltimateFields || {};

	/**
	 * Boots the plugin up, initializing all needed elements.
	 */
	root.boot = _.once( function() {
		UltimateFields.L10N.init();
	});

	// This function returns a template when needed and a cache
	root.templates = {};
	root.template = function( name ) {
		var html, tmpl;

		if( name in root.templates ) {
			return root.templates[ name ];
		}

		// Find the HTML
		html = $( '#ultimate-fields-' + name ).html();

		// Create and cache
		tmpl = _.template( html );
		root.templates[ name ] = tmpl;

		return tmpl;
	}

	// Initializes all containers, which are not initialized
	root.initializeContainers = function() {
		UltimateFields.boot();

		$( '.uf-container:not(.initialized)' ).each(function() {
			var $root = $( this ),
				data  = JSON.parse( $root.find( 'script' ).html() );

			$root.addClass( 'initialized' );

			// Separate the process to prevent erros from breaking everything
			UltimateFields.initializeContainer( $root, data );
		});

		UltimateFields.ContainerLayout.DOMUpdated();
	}

	// Initializes a container
	root.initializeContainer = function( $root, container, data ) {
		var datastore, model, view;

		// Create the model and the datastore
		data = data || container.data;
		datastore = new UltimateFields.Datastore( data );
		model = new UltimateFields.Container[ container.type ].Model( container.settings );
		model.set( 'originalData', _.clone( data ) );
		model.setDatastore( datastore );

		// Add the container type to the wrap
		$root.addClass( 'uf-container-' + container.type.toLowerCase().replace( /_/g, '-' ) );

		// Render the view
		view = new UltimateFields.Container[ container.type ].View({
			// el: $root,
			model: model
		});

		view.render();
		view.$el.appendTo( $root );
		view.trigger( 'addedToDOM' );

		return {
			view:      view,
			model:     model,
			datastore: datastore
		}
	}

	// A simpele button class
	UltimateFields.ButtonModel = Backbone.Model.extend({
		defaults: {
			type:      'secondary',
			cssClass:  '',
			text:      'Button',
			callback:  function() { alert( 'This button needs a callback!' ) },
			icon:      '',
			disabled:  false
		}
	});

	UltimateFields.Button = Backbone.View.extend({
		tagName: 'a',
		className: 'uf-button ',

		events: {
			'click': 'click'
		},

		initialize: function( args ) {
			this.model = new UltimateFields.ButtonModel( args );
			this.$el.attr( 'href', '#' );
		},

		render: function() {
			var that = this;

			this.$el.addClass( 'button-' + this.model.get( 'type' ) );

			if( this.model.get( 'text' ).length ) {
				this.$el.text( this.model.get( 'text' ) );
			} else {
				this.$el.addClass( 'uf-button-no-text' );
			}

			if( this.model.get( 'cssClass' ) ) {
				this.$el.addClass( this.model.get( 'cssClass' ) );
			}

			this.setIcon();
			this.model.on( 'change:icon', _.bind( this.setIcon, this ) );

			if( this.model.get( 'disabled' ) ) {
				this.$el.attr( 'disabled', 'disabled' );
			}

			this.model.on( 'change:disabled', function() {
				that.$el.attr( 'disabled', that.model.get( 'disabled' ) ? 'disabled' : false );
			});
		},

		/**
		 * Changes the icon of the button.
		 */
		setIcon: function() {
			var icon = this.model.get( 'icon' ), $icon;

			if( icon ) {
				if( icon.match( /^dashicons-[^\s]+$/ ) ) {
					icon = 'dashicons ' + icon;
				}

				$icon = this.$el.find( '.uf-button-icon' );
				if( $icon.length ) {
					$icon.replaceWith( '<span class="uf-button-icon ' + icon + '"></span>' );
				} else {
					this.$el.prepend(  '<span class="uf-button-icon ' + icon + '"></span>');
				}
			} else {
				this.$el.find( '.uf-button-icon' ).remove();
			}
		},

		click: function( e ) {
			e.preventDefault();

			if( ! this.model.get( 'disabled' ) ) {
				this.model.get( 'callback' )();
			}
		}
	});

	/**
	 * Create a controller.
	 */
	UltimateFields.Controller = function( args ) {
		this.initialize( args );
	}

	UltimateFields.Controller.extend = Backbone.Model.extend;

	/**
	 * Works with translatable strings.
	 */
	var l10n = root.L10N = {
		/**
		 * Contains all translateable strings.
		 */
		strings: {},

		/**
		 * Loads the strings if not already loaded.
		 */
		init: function() {
			$.extend( l10n.strings, ( 'undefined' != typeof uf_l10n ) ? uf_l10n : {} );
		},

		/**
		 * Localizes a string by it's key.
		 */
		localize: function( key ) {
			return key in l10n.strings
				? l10n.strings[ key ]
				: key;
		}
	}

	/**
	 * Dispatches events similarly to WordPress
	 */
	UltimateFields.EventsHub = new Backbone.Model;

	// Like apply_filters in WP, this allows data to be modified.
	UltimateFields.applyFilters = function( name, data ) {
		UltimateFields.EventsHub.trigger( name, data );
	}

	// Add a listener for a specific type of data
	UltimateFields.addFilter = function( name, callback ) {
		UltimateFields.EventsHub.on( name, function( data ) {
			callback( data );
		});
	}

	/**
	 * Location stuff
	 */
	UltimateFields.Location = Backbone.Model.extend({
		defaults: {
			visible: true
		},

		/**
		 * Initialize some properties.
		 */
		initialize: function() {
			var that = this;

			// This will contain the values for
			this.checked = new Backbone.Model();

			// Start listening
			this.listen();

			// Check
			this.check();
			this.checked.on( 'change', function() {
				that.check();
			});
		},

		/**
		 * Starts all listeners.
		 */
		/* abstract */ listen: function() {},

		/**
		 * Checks the state of the location.
		 */
		check: function() {
			var visible = true;

			_.each( this.checked.toJSON(), function( value ) {
				if( ! value ) {
					visible = false;
					return false;
				}
			});

			this.set( 'visible', visible );
		},

		/**
		 * Checks if a rule is empty.
		 */
		empty: function( arr ) {
			return 0 === arr.visible.length && 0 === arr.hidden.length;
		},

		/**
		 * Checks a single value based on rules.
		 */
		checkSingleValue: function( value, rules ) {
			if( rules.hidden.length && rules.hidden.indexOf( value ) != -1 )
				return false;

			if( rules.visible.length ) {
				return rules.visible.indexOf( value ) != -1;
			} else {
				return true;
			}
		},

		/**
		 * Checks multiple values based on rukes.
		 */
		checkMultipleValues: function( values, rules ) {
			var hidden  = false,
				visible = 0 === rules.visible.length;

			_.each( values, function( value ) {
				if( rules.hidden.length && -1 != rules.hidden.indexOf( value ) ) {
					hidden = true;
				}

				if( -1 != rules.visible.indexOf( value ) ) {
					visible = true;
				}
			});

			return ! hidden && visible;
		}
	});

	UltimateFields.Locations = Backbone.Collection.extend({
		model: UltimateFields.Location,

		isVisible: function() {
			var visible = false;

			// No rules to disable the visibility
			if( 0 === this.length ) {
				return true;
			}

			this.each( function( location ) {
				if( location.get( 'visible' ) ) {
					visible = true;
				}
			});

			return visible;
		}
	});

	/**
	 * Allow creation of simple containers through jQuery.
	 */
	$.fn.container = function( settings, data ) {
		var $el = $( this ),
			datastore, model, view;

		// Create a datastore
		datastore = new UltimateFields.Datastore( data );

		// Create a model
		model = new UltimateFields.Container.Base.Model( settings );

		// Connect the datastore and the model
		model.setDatastore( datastore );

		// Create the view
		view = new UltimateFields.Container.Base.View({
			model: model,
			el:    $el
		});

		view.render();

		// Listen for changes of the datastore
		datastore.on( 'change', function( e ) {
			$el.trigger( 'values-changed', [ datastore.toJSON(), e.changed ] );
		});

		return this;
	}

})( jQuery );
