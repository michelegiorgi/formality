(function( $ ){

	var uf    = window.UltimateFields,
		field = uf.Field = {};

	// Create a basic field model
	field.Model = Backbone.Model.extend({
		defaults: {
			label:                '',
			visible:              true,
			invalid:              false, // No validation state
			required:             false,
			hide_label:           false,
			tab:                  false,
			field_width:          100,
			description:          '',
			default_value:        '',
			validation_message:   false,
			description_position: 'input',
			validation_enabled:   false,
			html_attributes:      {}
		},

		/**
		 * Perform additional steps on initialization.
		 */
		initialize: function() {
		},

		/**
		 * Prevent the default synchronization.
		 */
		sync: function() {
			return false;
		},

		/**
		 * Sets a new datastore.
		 */
		setDatastore: function( datastore ) {
			var that = this;

			this.datastore = datastore;

			// Use the default value if any.
			this.useDefaultValueIfNeeded();

			// Start dependencies
			this.initializeDependencies();

			// Listen for validation states
			datastore.on( 'change:' + this.get( 'name' ), function() {
				if( that.get( 'validation_enabled' ) ) {
					that.validate();
				}
			});
		},

		/**
		 * If there is no value for the field, but a default one is available,
		 * this method will set the default value as the fields' value.
		 */
		useDefaultValueIfNeeded: function() {
			// ToDo: Removed && this.get( 'default_value' ), did it break something?
			if( ! this.datastore.get( this.get( 'name' ) ) ) {
				this.setValue( this.get( 'default_value' ) );
			}
		},

		/**
		 * Returns the current value of the field.
		 */
		getValue: function() {
			var value = this.datastore.get( this.get( 'name' ) );

			if( 'undefined' == typeof value ) {
				value = this.get( 'default_value' );
			}

			return value;
		},

		/**
		 * Sets a new value of the field.
		 */
		setValue: function( value, args ) {
			this.datastore.set( this.get( 'name' ), value, args );
		},

		/**
		 * Adds all neccessary listeners for dependencies.
		 */
		initializeDependencies: function() {
			var that = this;

			// Not much to do without dependencies
			if( ! this.get( 'dependencies' ) ) {
				return;
			}

			// Save all groups in one place
			this.dependencyGroups = [];

			_.each( this.get( 'dependencies' ), function( raw ) {
				var rules = [];

				_.each( raw, function( rule ) {
					rules.push( new UltimateFields.Dependency.Rule( rule ) );
				});

				var group = new UltimateFields.Dependency.Group({
					datastore: that.datastore,
					rules:    rules
				});

				// Save the group
				that.dependencyGroups.push( group );

				// Add a listener
				group.on( 'change:valid', function() {
					that.checkDependencies();
				});
			});

			// Do an initial check
			this.checkDependencies();
		},

		/**
		 * Checks what's up with dependencies.
		 */
		checkDependencies: function() {
			var visible = false;

			_.each( this.dependencyGroups, function( group ) {
				if( group.get( 'valid' ) ) {
					visible = true;
				}
			});

			this.set( 'visible', visible );
		},

		/**
		 * Validates a value.
		 */
		validateValue: function( value ) {
			var rule = this.get( 'validation_rule' ), valid;

			// Do the appropriate check
			if( rule ) {
				// Regex time
				var terminator, regex, flags, expr;

				// Parse the regex
				terminator = rule.charAt( 0 );
				rule       = rule.split( terminator );
				regex      = rule[ 1 ];
				flags      = rule[ 2 ];

				// Create an expression
				expr = new RegExp( regex, flags );

				// Validate finally
				valid = value.match( expr );
			} else {
				if( ( 'object' == typeof value ) && '[object Array]' == Object.prototype.toString.call( value ) ) {
					// Normal arrays
					valid = value.length > 0;
				} else {
					// Simple validation
					valid = value ? true : false;
				}
			}

			return valid;
		},

		/**
		 * Validates the field.
		 */
		validate: function( silent ) {
			var required = this.get( 'required' ),
				value    = this.getValue(),
				rule     = this.get( 'validation_rule' ),
				valid;

			silent = silent || false;

			// Even if the field is not required, but has a value and a validation rule, check
			if( value && rule ) {
				required = true;
			}

			// Invisible fields (because of validation logic) are not required
			if( ! this.get( 'visible' ) ) {
				required = false;
			}

			// If not required, go on
			if( ! required ) {
				this.set( 'invalid', false );
				return;
			}

			// Do the appropriate check
			valid = this.validateValue( value );

			// Final check
			if( ! valid ) {
				var message = this.get( 'validation_message' );

				if( ! message ) {
					message = UltimateFields.L10N.localize( 'invalid-field-message' ).replace( '%s', this.get( 'label' ) );
				}

				if( ! silent ) {
					this.set( 'invalid', message );
				}

				// Indicate that validation should be performed on every change
				this.set( 'validation_enabled', true );

				return message;
			} else {
				this.set( 'invalid', false );
				this.set( 'validation_enabled', false );
			}
		},

		/**
		 * Returns additional data for the customizer.
		 */
		getCustomizerContext: function() {
			return false;
		},

		/**
		 * Generates a value for Yoast SEO
		 */
		getSEOValue: function() {
			// By default we don't want to send anything to the SEO plugin.
			return false;
		}
	});

	/**
	 * This model will indicate that the field type is not valid.
	 */
	field.InvalidTypeModel = field.Model.extend({

	});

	// Create a collection class for the fields
	field.Collection = Backbone.Collection.extend({
		model: function( fieldData ) {
			var model;

			// Some fields cannot exist
			if( ! field[ fieldData.type ] ) {
				return new field.InvalidTypeModel( fieldData );
			}

			if( 'Model' in field[ fieldData.type ] ) {
				return new field[ fieldData.type ].Model( fieldData );
			} else {
				return new field.Model( fieldData );
			}
		}
	});

	// Create the big view for fields, field.View is for inputs only
	field.Wrap = Backbone.View.extend({
		className: 'uf-field uf-field-layout-row',

		/**
		 * Renders the normal part of the field.
		 * Normally this method should be called only once the field is in the DOM.
		 */
		render: function() {
			var that = this,
				tmpl = this.getTemplate(),
				input, description_position;

			// Add additional classes
			this.$el.addClass( 'uf-field-type-' + this.model.get( 'type' ).toLowerCase() );
			this.$el.addClass( 'uf-field-name-' + this.model.get( 'name' ) );

			// Wrap width
			this.$el.css({
				width: this.model.get( 'field_width' ) + '%'
			});

			if( this.model.get( 'hide_label' ) ) {
				this.$el.addClass( 'uf-field-no-label' );
			}

			// Add attributes
			_.each( this.model.get( 'html_attributes' ), function( value, key ){
				if( 'class' == key ) {
					that.$el.addClass( value );
				} else {
					that.$el.attr( key, value );
				}
			});

			// for the message field, always show the description in the input.
			if( 'Message' == this.model.get( 'type' ) ) {
				description_position = 'input';
			} else {
				description_position = this.model.get( 'description_position' );
			}

			// Add the skeleton
			this.$el.append( tmpl({
				label:                this.model.get( 'label' ),
				required:             this.model.get( 'required' ),
				hide_label:           this.model.get( 'hide_label' ),
				description:          this.model.get( 'description' ),
				description_position: description_position
			}));

			// Locate the validation message
			this.$validationMessage = this.$el.find( '.uf-field-validation-message' );

			// Check visibility
			this.toggle();
			this.toggleValidation();

			this.model.on( 'change:visible', function() { that.toggle(); });
			this.model.on( 'change:invalid', function() { that.toggleValidation(); })
			this.model.datastore.on( 'change:__tab', function() {
				that.toggle();
			});
		},

		/**
		 * Returns the template for the field.
		 */
		getTemplate: function() {
			return UltimateFields.template( 'field-wrap' );
		},

		renderInput: function() {
			if( this.model instanceof field.InvalidTypeModel ) {
				this.$el.find( '.uf-field-input' ).text( 'Unsupported field type: ' + this.model.get( 'type' ) );
				this.input = false;
			} else {
				// Add the actual field
				this.input = input = new field[ this.model.get( 'type' ) ].View({
					model: this.model,
					el: this.$el.find( '.uf-field-input' )
				});

				// Once the base is in place, add listeners to the label
				this.addLabelClickListeners();

				input.render();
			}

			this.inputRendered = true;
		},

		/**
		 * Toggles the visibility of the element.
		 */
		toggle: function() {
			var that    = this,
				visible = this.model.get( 'visible' ),
				tab     = this.model.get( 'tab' ),
				method;

			if( tab && ( tab != this.model.datastore.get( '__tab' ) ) ) {
				visible = false;
			}

			// Save a local handle
			this.visible = visible;

			method = visible ? 'removeClass' : 'addClass';
			this.$el[ method ]( 'uf-field-hidden' );

			if( visible && ! this.inputRendered ) {
				this.renderInput();
				UltimateFields.ContainerLayout.DOMUpdated();
			}
		},

		toggleValidation: function() {
			var that  = this,
				state = this.model.get( 'invalid' );

			if( ! ( '$validationMessage' in this ) ) {
				return;
			}

			clearTimeout( this.validationMessageTimeout );

			if( false === state ) {
				this.$el.removeClass( 'uf-field-invalid' );
				this.$validationMessage.removeClass( 'uf-field-validation-message-visible' );
				this.validationMessageTimeout = setTimeout(function() {
					that.$validationMessage.removeClass( 'uf-field-validation-message-shown' );
				}, 400);
			} else {
				this.$el.addClass( 'uf-field-invalid' );
				this.$validationMessage.addClass( 'uf-field-validation-message-shown' );
				this.validationMessageTimeout = setTimeout(function() {
					that.$validationMessage.addClass( 'uf-field-validation-message-visible' ).text( state );
				}, 10);
			}
		},

		/**
		 * Adds a click listener to the label.
		 */
		addLabelClickListeners: function() {
			var that   = this,
				$label = this.$el.find( 'label ');

			if( ! $label.length ) {
				return;
			}

			if( 'function' == typeof this.input.focus ) {
				$label.on( 'click', _.bind( this.input.focus, this.input ) );
			} else {
				$label.addClass( 'uf-field-label-unclickable' );
			}
		},

		/**
		 * Adjust the classes of the field based on size.
		 */
		useLayout: function( layout ) {
			if( 'rows' === layout ) {
				layout = 'row';
			}

			this.$el
				.addClass( 'uf-field-layout-' + layout )
				.removeClass( 'uf-field-layout-' + ( 'grid' == layout ? 'row' : 'grid' ) );

			if( this.input && 'function' === typeof this.input.adjustToWidth ) {
				this.input.adjustToWidth();
			}
		}
	});

	// Grid wrap for grid fields
	field.GridWrap = field.Wrap.extend({
		className: 'uf-field uf-field-layout-grid'
	});

	// Table cell wrap
	field.TableWrap = field.Wrap.extend({
		className: 'uf-field uf-table-cell',

		render: function() {
			// Normal render
			field.Wrap.prototype.render.call( this );

			// Wrap width
			this.$el.css({
				width: this.model.get( 'field_width' ) + '%'
			});
		},

		/**
		 * Returns the template for the field.
		 */
		getTemplate: function() {
			return UltimateFields.template( 'cell-wrap' );
		}
	});

	/**
	 * A special wrap for media fields.
	 */
	field.AttachmentWrap = field.Wrap.extend({
		className: 'uf-field uf-media-setting',

		/**
		 * Returns the template for the field.
		 */
		getTemplate: function() {
			return UltimateFields.template( 'field-attachment' );
		}
	});

	/**
	 * A special wrap for menu item fields.
	 */
	field.MenuWrap = field.Wrap.extend({
		className: 'uf-field uf-menu-field',

		/**
		 * Returns the template for the field.
		 */
		getTemplate: function() {
			return UltimateFields.template( 'field-menu' );
		}
	});

	// A special wrap for menu fields
	field.TermAddWrap = field.Wrap.extend({
		className: field.Wrap.prototype.className + ' uf-field-term-add'
	});

	// This will represent the normal input/view
	field.View = Backbone.View.extend({
		className: 'input',

		render: function() {
			this.$el.append( this.input() );
		}
	});

	field.Tab = {
		Model: field.Model.extend({
			/**
			 * Even when requested, prevents the tab from having a value.
			 */
			useDefaultValueIfNeeded: function() {},
		}),

		View: field.View.extend({
			render: function() {
				var that = this, datastore, model, view;

				datastore = new UltimateFields.Datastore({
					__tab: this.model.get( 'name' )
				});

				model = new Backbone.Model({
					visible:     true,
					name:        this.model.get( 'name' ),
					label:       this.model.get( 'label' ),
					description: this.model.get( 'description' ),
					icon:        this.model.get( 'icon' ),
				});

				model.datastore = datastore;

				view = new UltimateFields.Tab({
					model: model
				});

				view.$el.appendTo( this.$el );
			}
		})
	}

	field.Section = {
		Model: field.Model.extend({
			defaults: _.extend( {
				icon: false,
				color: false
			}, field.Model.prototype.defaults ),

			/**
			 * Even when requested, prevents the section from having a value.
			 */
			useDefaultValueIfNeeded: function() {}
		}),

		Wrap: field.Wrap.extend({
			className: 'uf-section',

			render: function() {
				var that = this,
					tmpl = UltimateFields.template( 'section' );

				this.$el.html( tmpl( this.model.toJSON() ) );

				if( this.model.get( 'color' ) ) {
					this.$el.addClass( 'uf-section-' + this.model.get( 'color' ) );
				}

				this.toggle();

				this.model.on( 'change:visible', function() { that.toggle(); });
				this.model.on( 'change:invalid', function() { that.toggleValidation(); })
				this.model.datastore.on( 'change:__tab', function() {
					that.toggle();
				});
			},

			/**
			 * Toggles the visibility of the element.
			 */
			toggle: function() {
				var that    = this,
					visible = this.model.get( 'visible' ),
					tab     = this.model.get( 'tab' ),
					method;

				if( tab && ( tab != this.model.datastore.get( '__tab' ) ) ) {
					visible = false;
				}

				// Save a local handle
				this.visible = visible;

				method = visible ? 'removeClass' : 'addClass';
				this.$el[ method ]( 'uf-field-hidden' );
			},

			useLayout: function( layout ) {
				//
			}
		}),

		View: field.View.extend({
			render: function() {
				var that = this, wrap;

				wrap = new field.Section.Wrap({
					model: this.model
				});

				wrap.$el.appendTo( this.$el );
				wrap.render();
			}
		})
	}

})( jQuery );
