(function( $ ){

	var field        = UltimateFields.Field,
		sidebarField = field.Sidebar = {},
		lastID       = 0;

	/**
	 * This is the model for a sidebar.
	 *
	 * Sidebars will be listed in multiple fields and it will be important to update
	 * their views properly in each field that has the same key.
	 */
	UltimateFields.Sidebar = Backbone.Model.extend({
		defaults: {
			id:          '',
			name:        '',
			description: '',
			_builtin:    false
		},

		/**
		 * Prevents automatic syncronisations of containers with the backend.
		 */
		sync: function() {
			return false; // No automatic syncing
		}
	});

	/**
	 * A collection for sidebars.
	 *
	 * Will be used and filled globally for each field name.
	 */
	UltimateFields.Sidebars = Backbone.Collection.extend({
		model: UltimateFields.Sidebar
	});

	/**
	 * Works as a model, which contains sidebars which are associated with a field name.
	 */
	UltimateFields.SidebarManager = Backbone.Model.extend({
		/**
		 * Initializes the manager.
		 */
		initialize: function( args ) {
			var existing = UltimateFields.SidebarManager.getExisting( args.field );

			sidebars = new UltimateFields.Sidebars( existing.sidebars );

			this.set( 'sidebars', sidebars );
			this.set( 'nonce', existing.nonce );

			sidebars.on( 'change add remove', _.bind( this.sync, this ) );
		},

		/**
		 * Whenever sidebars change, this calls WP to save them.
		 */
		sync: function() {
			$.ajax({
				url:  ajaxurl,
				type: 'post',
				data: {
					action  : 'uf_manage_sidebars',
					field:    this.get( 'field' ),
					nonce:    this.get( 'nonce' ),
					uf_ajax:  true,
					sidebars: _.map( this.get( 'sidebars' ).where({ _builtin: false }), function( a ) {
						return a.toJSON();
					})
				}
			});
		}
	}, {
		/**
		 * Caches existing managers.
		 */
		managers: {},

		/**
		 * Returns all existing sidebars.
		 */
		getExisting: function( field ) {
			if( ! this.all ) {
				this.all = UltimateFields.L10N.localize( 'uf-sidebars' ) || {};
			}

			return this.all[ field ] || [];
		},

		/**
		 * Creates a new manager based on a field.
		 */
		getManagersForField: function( field ) {
			if( field.get( 'name' ) in this.managers ) {
				return this.managers[ field.get( 'name' ) ].get( 'sidebars' );
			} else {
				var args = {
					field: field.get( 'name' )
				};

				this.managers[ field.get( 'name' ) ] = new UltimateFields.SidebarManager( args );

				return this.managers[ field.get( 'name' ) ].get( 'sidebars' );
			}
		}
	});

	/**
	 * The model for the sidebar field.
	 */
	sidebarField.Model = field.Model.extend({
		/**
		 * Returns the collection of sidebars, which can be used by the field.
		 */
		getSidebars: function() {
			return UltimateFields.SidebarManager.getManagersForField( this );
		}
	});

	/**
	 * The view for the field.
	 */
	sidebarField.View = field.View.extend({
		events: {
			'keydown .uf-sidebars-add-name':        'fieldKeyDown',
			'keydown .uf-sidebars-add-description': 'fieldKeyDown',
			'click .uf-sidebars-add-button':        'add',
			'change input[type=radio]':             'save'
		},

		/**
		 * Determines what to render.
		 */
		render: function() {
			if( this.model.get( 'editable' ) ) {
				this.inputID = ++lastID;
				this.renderEditor();
			} else {
				this.renderSimpleSelect();
			}
		},

		/**
		 * Renders a basic select for choosing a sidebar.
		 */
		renderSimpleSelect: function() {
			var that    = this,
				$select = $( '<select />' ),
				current = this.model.getValue();

			// Add options
			this.model.getSidebars().each(function( sidebar ) {
				var $option = $( '<option />' )
					.attr( 'value', sidebar.get( 'id' ) )
					.text( sidebar.get( 'name' ) )
					.prop( 'selected', sidebar.get( 'id' ) == current )
					.appendTo( $select );
			});

			// Add the select to the dom and listen for changes
			this.$el.append( $select );
			$select.on( 'change', function() {
				that.model.setValue( $select.val() );
			});

			// If there is no current value, use the first one from the select
			if( ! current ) {
				this.model.setValue( $select.val() );
			}
		},

		/**
		 * Renders a full-blown field/editor.
		 */
		renderEditor: function() {
			var that     = this,
				tmpl     = UltimateFields.template( 'sidebar-base' ),
				sidebars = this.model.getSidebars(),
				value;

			// Add the template
			this.$el.html( tmpl({

			}));

			// Add existing sidebars
			sidebars.each(function( sidebar ) {
				that.listSidebar( sidebar );
			})

			// Whenever there is a new sidebar, add it to the list
			sidebars.on( 'add', function( sidebar ) {
				that.listSidebar( sidebar );
			});

			// Select a new item if the old one is not available
			sidebars.on( 'destroy', function() {
				if( 0 == that.$el.find( 'input[type=radio]:checked' ).length ) {
					that.$el.find( 'input[type=radio]' ).eq( 0 ).prop( 'checked', 'checked' ).trigger( 'change' );
				}
			});

			// Select the current sidebar if any
			if( value = this.model.getValue() ) {
				that.$el.find( 'input[type=radio]' ).each(function() {
					if( this.value == value ) {
						this.checked = 'checked';
					}
				});
			}
		},

		/**
		 * Adds a sidebar to the list.
		 */
		listSidebar: function( sidebar ) {
			var that = this,
				tmpl = UltimateFields.template( 'sidebar-row' ),
				$el;

			$el = $( tmpl({
				sidebar: sidebar.toJSON(),
				uniqueID: this.getInputID()
			}));
			this.$el.find( 'tbody' ).append( $el );

			sidebar.on( 'destroy', function() {
				$el.remove();
			});

			$el.on( 'click', '.remove', function( e ) {
				e.preventDefault();

				if( confirm( 'Are you sure you want to delete this sidebar? Deleting it here will delete it from the whole website.' ) ) {
					sidebar.destroy();
				}
			});
		},

		/**
		 * Generates an ID for the sidebar's radio input.
		 */
		getInputID: function() {
			return 'uf-sidebar-' + this.inputID;
		},

		/**
		 * Handles keyups within the fields.
		 */
		fieldKeyDown: function( e ) {
			if( 13 == e.keyCode ) {
				e.preventDefault();

				this.add();
			}
		},

		/**
		 * Tries adding a new sidebar.
		 */
		add: function( e ) {
			var that = this, name, id, description, model;

			if( e ) {
				e.preventDefault();
			}

			// Collect and check
			name        = this.$el.find( '.uf-sidebars-add-name' ).val();
			id          = name.toLowerCase().replace( /\s/, '-' ).trim();
			description = this.$el.find( '.uf-sidebars-add-description' ).val();

			if( ! name.length ) {
				return;
			}

			model = new UltimateFields.Sidebar({
				id:          id,
				name:        name,
				description: description
			});

			// Add to the collection
			this.model.getSidebars().add( model );

			// Select the model for the current field\
			this.$el.find( 'input[type=radio]' ).each(function() {
				this.checked = ( this.value == name ) ? 'checked' : false;

				$( this ).trigger( 'change' );
			});

			// Reset the "form"
			this.$el.find( '.uf-sidebars-add-name' ).val( '' );
			this.$el.find( '.uf-sidebars-add-description' ).val( '' );
		},

		/**
		 * Save the value of the field whenever inputs change.
		 */
		save: function( e ) {
			if( e.target.checked ) {
				this.model.setValue( e.target.value );
			}
		},

		/**
		 * Focuses the input within the field.
		 */
		focus: function() {
			this.$el.find( 'input:eq(0)' ).focus();
		},

		/**
		 * Adjust the view of the input to a certain width.
		 */
		adjustToWidth: function( width ) {
			width = width || this.$el.width();

			if( width < 600 ) {
				this.$el.addClass( 'uf-sidebars-small' );
			} else {
				this.$el.removeClass( 'uf-sidebars-small' );
			}
		}
	});

})( jQuery );
