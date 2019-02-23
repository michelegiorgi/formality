(function( $ ){

	// To improve:
	// Add/remove/add in the middle of nowhere

	var uf       = window.UltimateFields,
		field    = window.UltimateFields.Field,
		repeater = {};

	// Export the handle
	field.Repeater = repeater;

	// This model will handle abstract group types
	repeater.GroupType = Backbone.Model.extend({
		defaults: {
			existing: 0,
			minimum: false,
			maximum: false,
			can_be_removed: true,
			can_be_added: true
		},

		/**
		 * Checks if the group type can be removed.
		 */
		canBeRemoved: function() {
			if( this.get( 'minumum' ) && this.get( 'existing' ) <= this.get( 'minumum' ) ) {
				return false;
			}

			if( ! this.get( 'can_be_removed' ) ) {
				return false;
			}

			return true;
		},

		/**
		 * Checks if the group can be cloned/added.
		 */
		canBeAdded: function() {
			if( this.get( 'maximum' ) && this.get( 'maximum' ) <= this.get( 'existing' ) ) {
				return false;
			}

			if( ! this.get( 'can_be_added' ) ) {
				return false;
			}

			return true;
		}
	});

	// Add some extra functionality for repeater models
	repeater.Model = field.Model.extend({
		/**
		 * Overwrite the datastore method in order to avoid working
		 * with values before there is a datastore to save them in.
		 */
		setDatastore: function( datastore ) {
			var that = this;

			// Do the normal initialization
			field.Model.prototype.setDatastore.call( this, datastore );

			// This collection will hold each added group, no models or views
			this.rows = new UltimateFields.Datastore.Collection();

			// Create a collection for the groups (actual models)
			this.groups = [];

			// This will indicate how many of each group we have
			this.groupTypes = new Backbone.Collection;
			_.each( this.get( 'groups' ), function( group ) {
				that.groupTypes.add( new repeater.GroupType({
					id:       group.id,
					existing: 0,
					minimum:  group.minimum || false,
					maximum:  group.maximum || false
				}));
			});

			// Handle changes
			this.rows.on( 'change sort destroy remove', function( e ) {
				if( e && ( 'changed' in e ) && ( '__tab' in e.changed ) )
					return;

				that.setValue( that.rows.toJSON() );
				that.calcGroupCounts();

				if( that.get( 'validation_enabled' ) ) {
					setTimeout(function() {
						// Let the row get removed from the collection before validation
						that.validate();
					}, 5);
				}

				that.trigger( 'value-saved' );
			});
		},

		/**
		 * Forces rows to be cached/saved properly.
		 */
		refresh: function() {
			this.rows.sort();
		},

		/**
		 * Validates the value of the field.
		 */
		validate: function() {
			var hasErrors = false, message;

			if( this.get( 'required' ) && 0 === this.groups.length ) {
				hasErrors = UltimateFields.L10N.localize( 'repeater-required' );
			}

			_.each( this.groups, function( row ) {
				if( row.validate() ) {
					hasErrors = true;
				}
			});

			// Do normal validation
			if( hasErrors ) {
				var message = 'string' == typeof hasErrors
					? hasErrors
					: UltimateFields.L10N.localize( 'repeater-incorrect-value' ).replace( '%s', this.get( 'label' ) );
			}

			// Check for a minimum items
			if( this.get( 'minimum' ) && this.get( 'minimum' ) > this.rows.length ) {
				message = UltimateFields.L10N.localize( 'repeater-min-value' )
					.replace( '%s', this.get( 'label' ) )
					.replace( '%d', this.get( 'minimum' ) );
			}

			// Check if there are too many items
			if( this.get( 'maximum' ) && this.get( 'maximum' ) < this.rows.length ) {
				message = UltimateFields.L10N.localize( 'repeater-max-value' )
					.replace( '%s', this.get( 'label' ) )
					.replace( '%d', this.get( 'maximum' ) );
			}

			if( message ) {
				this.set( 'invalid', message );
				this.set( 'validation_enabled', true );
				return message;
			} else {
				this.set( 'invalid', false );
				this.set( 'validation_enabled', false );
			}
		},

		/**
		 * Calculates group counts.
		 */
		calcGroupCounts: function() {
			var that = this, isFull, hasMoreThanMin;

			isFull = this.get( 'maximum' ) && this.get( 'maximum' ) > 0
				? this.get( 'maximum' ) <= this.rows.length
				: false;

			hasMoreThanMin = this.get( 'minimum' ) > 0
				? this.rows.length > this.get( 'minimum' )
				: true;

			// Go through each type and synchronize it
			this.groupTypes.each(function( type ) {
				var typeID = type.get( 'id' ),
					count  = 0;

				type.set( 'can_be_added', ! isFull );
				type.set( 'can_be_removed', hasMoreThanMin );

				that.rows.each(function( row ) {
					if( row.get( '__type' ) !== typeID ) {
						return;
					}

					count++;
				});

				type.set( 'existing', count );
			});
		},

		/**
		 * Returns the group type of a group.
		 */
		getGroupType: function( group ) {
			var id;

			if( 'object' == typeof group ) {
				if( 'function' == typeof group.get ) {
					id = group.get( 'id' );
				} else {
					id = group.id;
				}
			} else {
				id = group;
			}

			return this.groupTypes.get( id );
		},

		/**
		 * Returns an SEO-analyzable value of the field.
		 */
		getSEOValue: function() {
			var values = [],
				groups = this.get( 'groups' );

			_.each( this.groups, function( group ) {
				group.get( 'fields' ).each(function( field ) {
					var value = field.getSEOValue();

					if( value ) {
						values.push( value );
					}
				});
			});

			return values.join( ' ' );
		}
	});

	/**
	 * Displays and handles the view of the repeater.
	 */
	repeater.View = field.View.extend({
		initialize: function() {
			var that = this;

			// Do the standard initialization
			field.View.prototype.initialize.apply( this, arguments );

			// Listen for replacements
			this.model.datastore.on( 'value-replaced', function( name ) {
				if( name != that.model.get( 'name' ) ) {
					return;
				}

				that.model.rows.reset([]);
				that.render();
			})
		},

		render: function() {
			var that    = this,
				m       = this.model,
				groups  = m.get( 'groups' ),
				table   = 'table' == m.get( 'layout' ) && 1 == groups.length,
				tmpl    = UltimateFields.template( 'field-repeater' + ( table ? '-table' : '' ) );

			// If there ar eno groups, show a message about it
			if( 0 == groups.length ) {
				this.showNoGroupsMessage();
				return;
			}

			// Render the template and add handles to main element(s).
			this.$el.html( tmpl() );
			this.$groups = this.$el.find( '.uf-repeater-groups' );

			// Adjust the placeholder
			if( table || ( 'widgets' == m.get( 'chooser_type' ) && m.get( 'groups' ).length > 1 ) ) {
				this.$groups.addClass( 'uf-repeater-groups-with-placeholder' );
				this.$el.find( '.uf-repeater-placeholder' ).html( m.get( 'placeholder_text' ) );
			} else if( ! table ) {
				this.$el.find( '.uf-repeater-placeholder' ).remove();
			}

			// Add background
			if( m.get( 'background' ) ) {
				var $bgEl = this.$el.closest( '.uf-field-preview-repeater' ).length
					? this.$el.closest( '.uf-field-preview-repeater' )
					: this.$el.parent();

				$bgEl.css({
					background: m.get( 'background' )
				});
			}

			// Add headings
			if( table ) {
				this.addTableHeadings();
			}

			// Add existing groups
			_.each( m.getValue() || m.get( 'default_value' ) || [], function( row ) {
				that.addGroup({
					data:   row,
					type:   row.__type || that.model.get( 'groups' )[ 0 ].id,
					silent: true
				});
			});

			// Start the sortable
			this.sortable();

			// Once groups have been added, calculate their counts for limit checks
			m.calcGroupCounts();

			// Add the proper elements for adding new groups
			if( groups.length == 1 ) {
				this.addButton();
			} else {
				switch( m.get( 'chooser_type' ) ) {
					case 'widgets': this.addPrototypes(); break;
					case 'tags':    this.addTags();       break;
					default:        this.addDropdown();
				}
			}

			// Toggle classes when needed
			m.rows.on( 'all', _.bind( this.toggleStates, this ) );
			this.toggleStates();
		},

		/**
		 * Indicates that there are no groups to work with.
		 */
		showNoGroupsMessage: function() {
			this.$el.text( UltimateFields.L10N.localize( 'repeater-no-groups' ) );
		},

		/**
		 * Toggles various states whenever data has changed.
		 */
		toggleStates: function() {
			this.toggleMinMax();
			this.toggleEmptyClass();
		},

		/**
		 * Changes basic classes based on the amount of added groups.
		 */
		toggleEmptyClass: function() {
			var method = this.model.rows.length > 0 ? 'removeClass' : 'addClass';
			this.$groups[ method ]( 'uf-repeater-groups-empty' );
		},

		/**
		 * Adds a group to the repeater.
		 */
		addGroup: function( options ) {
			var that = this, datastore, model, view, args, forceDefaults;

			// Check if defaults should be used
			if( options && ! ( 'data' in options ) ) {
				forceDefaults = true;
			}

			options = $.extend( {
				index:     this.model.rows.length + 1,
				type:      this.model.get( 'groups' )[ 0 ].id,
				data:      {},
				silent:    false,
				replace:   false,
				datastore: false
			}, options );

			// Prepare a new datastore for the group or use an existing one
			if( options.datastore ) {
				datastore = options.datastore;
			} else {
				datastore = new UltimateFields.Datastore( options.data );
				datastore.parent = this.model.datastore;
			}

			datastore.set( '__index', options.index );
			datastore.set( '__type', options.type );

			// Allow arguments to be modified before creating the model, view and etc.
			args = {
				model:         UltimateFields.Container.Group.Model,
				view:          UltimateFields.Container.Group[ 'table' == this.model.get( 'layout' ) ? 'RowView' : 'View' ],
				settings:      _.findWhere( this.model.get( 'groups' ), { id: options.type }),
				datastore:     datastore,
				repeaterView:  this
			}

			UltimateFields.applyFilters( 'repeater_group_classes', args );

			// Prepare the container model
			model = new args.model( args.settings );
			model.set( '__type', options.type );
			model.setDatastore( datastore );
			if( forceDefaults ) {
				model.forceDefaultValues();
			}

			// Push the datastore to the rows
			this.model.rows.add( datastore, {
				silent: options.silent
			});

			this.model.groups.push( model );
			this.model.trigger( 'groupAdded', model );

			model.on( 'destroy', function() {
				that.model.groups.splice( that.model.groups.indexOf( model ), 1 );
			});

			// Bind the model to a group type, so it can hide and show itself and control its controls
			model.bindToGroupType( this.model.getGroupType( options.type ) );

			// Create the view
			view = new args.view({
				model: model
			});

			if( options.replace ) {
				options.replace.replaceWith( view.$el );
			} else {
				this.$groups.append( view.$el );
			}

			view.render();

			// Force sort
			that.$groups.children().trigger( 'uf-sorted' );
			model.on( 'destroy', function() {
				that.$groups.children().trigger( 'uf-sorted' );
			});

			// Duplicate the group when requested
			view.on( 'uf-duplicate', function( data ) {
				var $div = $( '<div />' );

				// Place the element after the current group
				view.$el.after( $div );

				that.addGroup({
					type:      options.type,
					datastore: data.datastore,
					replace:   $div
				});

				that.model.refresh();
			});

			// Allow external influence over the group
			UltimateFields.applyFilters( 'repeater_group_created', {
				repeaterView: this,
				model:        model,
				view:         view,
				datastore:    datastore
			});

			// For popup-only groups, open the popup immediately
			if( ! options.silent && 'popup' == model.get( 'edit_mode' ) ) {
				view.openPopup();
			}
		},

		/**
	 	 * Enables the jQuery sortable on the repeater.
	 	 */
	 	sortable: function() {
	 		var that = this;

	 		this.$groups.sortable({
	 			axis:                 'y',
	 			handle:               '> .uf-group-header, > .uf-group-number',
	 			items:                '> .uf-group',
				revert:               100,
	 			forcePlaceholderSize: true,
	 			receive: function( e, ui ) {
	 				that.$groups.children( '.uf-group-prototype' ).each(function() {
	 					that.replacePrototype( $( this ) );
	 				});
	 			},
	 			start: function() {
	 				that.$groups.children().addClass( 'no-click' );
	 			},
				stop: function() {
					// Silently update indexes
					that.$groups.children().trigger( 'uf-sorted' );

					// Sort the actual datastores and save
					that.model.refresh();

					// ALlow items to be clicked
					setTimeout(function() {
		 				that.$groups.children().removeClass( 'no-click' );
					}, 30 );
				}
			});
	 	},

	 	/**
	 	 * Replaces a prototype within the repeater with a normal group.
	 	 */
	 	replacePrototype: function( $proto ) {
	 		var that = this;

	 		this.addGroup({
				data:    {},
				type:    $proto.data( 'type' ),
				silent:  true, // The sortable will trigger the sorted event
				replace: $proto
			});
	 	},

	 	/**
	 	 * Adds a dropdown for adding various types of groups.
	 	 */
		addDropdown: function() {
			var that = this,
				tmpl = UltimateFields.template( 'repeater-dropdown' ),
				$dropdown;

			$dropdown = this.$dropdown = $(tmpl({
				groups: this.model.get( 'groups' ),
				text:   this.model.get( 'add_text' ) || UltimateFields.L10N.localize( 'repeater-add' )
			}));

			// When the select changes, use the new group
			$dropdown.on( 'change', 'select', function() {
				that.addGroup({
					silent: false,
					type:   $dropdown.find( 'select' ).val()
				});
			});

			// Also add on click
			$dropdown.on( 'click', '.uf-repeater-add-button', function() {
				// add a new group at the end
				that.addGroup({
					silent: false,
					type:   $dropdown.find( 'select' ).val()
				});

				return false;
			});

			this.$groups.after( $dropdown );
		},

	 	/**
	 	 * Adds a list of tags for adding various types of groups.
	 	 */
		addTags: function() {
			var that = this,
				tmpl = UltimateFields.template( 'repeater-tags' ),
				$tags;

			$tags = this.$tags = $(tmpl({
				groups: this.model.get( 'groups' ),
				text:   this.model.get( 'add_text' ) || UltimateFields.L10N.localize( 'repeater-add' )
			}));

			$tags.on( 'click', '.uf-repeater-tags-tag', function( e ) {
				var $tag = $( e.target );

				e.preventDefault();

				// add a new group at the end
				that.addGroup({
					silent: false,
					type:   $tag.data( 'group' )
				});

				$tag.blur();
			});

			// Toggle each type individually
			this.model.groupTypes.on( 'change', function() {
				that.toggleTags( $tags );
			});
			that.toggleTags( $tags );

			this.$groups.after( $tags );
		},

		/**
		 * Toggles the visiblity of tabs.
		 */
		toggleTags: function( $tags ) {
			var that = this;

			$tags.find( '.uf-repeater-tags-tag' ).each(function() {
				var $tag = $( this ),
					type = that.model.groupTypes.get( $tag.data( 'group' ) );

				$tag[ type.canBeAdded() ? 'removeClass' : 'addClass' ]( 'uf-repeater-tags-tag-hidden' );
			});
		},

	 	/**
	 	 * Adds prototype views to the repeater.
	 	 */
	 	addPrototypes: function() {
	 		var that        = this,
	 			$prototypes = this.$prototypes = $( '<div class="uf-repeater-prototypes" />' );

	 		_.each( this.model.get( 'groups' ), function( group ) {
	 			that.addPrototype( $prototypes, group );
	 		});

	 		this.$el.append( $prototypes );

			// Add custom layout
			var layout = new UltimateFields.ContainerLayout({
				el: $prototypes,
				gridSelector: '.uf-repeater-prototypes-column',
				layout: 'grid'
			});

			layout.startGrid();
	 	},

		/**
		 * Adds a new prototype.
		 */
		addPrototype: function( $element, group ) {
			var that = this, prototype;

			// Create the prototype
			prototype = new repeater.Prototype({
				group:     group,
				groupType: this.model.getGroupType( group )
			});

			// Add elements
			$element.append( prototype.$el );
			prototype.connectToSortable( that.$groups );

			// Whenever the prototype is clicked, add a new group at the end
			prototype.on( 'clicked', function() {
				that.addGroup({
					data:    {},
					type:    group.id
				});
			});
		},

	 	/**
	 	 * Toggles the prototypes based on the amount of rows.
	 	 */
	 	toggleMinMax: function() {
	 		var that    = this,
	 			minimum = this.model.get( 'minimum' ),
	 			maximum = this.model.get( 'maximum' ),
	 			count   = this.model.rows.length;

	 		// Toggle the add button/widgets
	 		if( maximum ) {
	 			var reached = maximum <= count,
	 				method  = reached ? 'hide' : 'show',
	 				added   = {};

	 			// Hide the show prototypes
		 		if( 1 == this.model.get( 'groups' ).length ) {
					this.theAddButton.$el[ method ? 'show' : 'hide' ]();
				} else {
					if( 'widgets' == this.model.get( 'chooser_type' ) ) {
						this.$prototypes[ method ]();
					} else {
						this.$dropdown[ method ]();
					}
				}
	 		}
	 	},

		/**
		 * Displays a simple button for adding groups.
		 */
		addButton: function() {
			var that = this,
				button;

			button = this.theAddButton = new UltimateFields.Button({
				text:     this.model.get( 'add_text' ) || UltimateFields.L10N.localize( 'repeater-add' ),
				icon:     'dashicons-plus',
				type:     'primary',
				callback: function() {
					that.addGroup()
				}
			})

			this.$el.append( button.$el );
			button.render();
		},

		/**
		 * Adds headings to the table layout.
		 */
		addTableHeadings: function() {
			var that  = this,
				group = this.model.get( 'groups' )[ 0 ],
				tmpl  = UltimateFields.template( 'repeater-heading' );

			_.each( group.fields, function( field ) {
				var $el;

				$el = $( tmpl( field ) );
				if( field.field_width ) {
					$el.width( field.field_width + '%' )
				}

				that.$el.find( '.uf-table-headings' ).append( $el );
			});

			// ToDo: Check conditional logic
		}
	});

	/**
	 * Handles the prototype view when widgets are used.
	 */
	repeater.Prototype = Backbone.View.extend({
		className: 'uf-repeater-prototypes-column',

		events: {
			'click': 'add'
		},

		/**
		 * Saves the group and listens to the group type.
		 */
		initialize: function( args ) {
			this.group     = args.group;

			if( args.groupType ) {
				this.groupType = args.groupType;
				this.visible   = this.groupType.canBeAdded();

				// Listen for visiblity changes
				this.groupType.on( 'change', _.bind( this.toggle, this ) );
			}

			// Render
			this.render();
		},

		/**
		 * Renders the prototype.
		 */
		render: function() {
			var that = this
				tmpl = UltimateFields.template( 'repeater-prototype' );

			this.$el.append( tmpl( this.group ) );
		},

		/**
		 * Toggles the prototype based on the groups' visiblity.
		 */
		toggle: function() {
			var initial = this.visible;

			if( this.visible && ! this.groupType.canBeAdded() ) {
				this.$el.hide();
				this.visible = false;
			} else if( ! this.visible && this.groupType.canBeAdded() ) {
				this.$el.show();
				this.visible = true;
			}

			if( initial != this.visible) {
				// Force grid layout
				UltimateFields.ContainerLayout.DOMUpdated();
			}
		},

		/**
		 * Connects the element to a sortable.
		 */
		connectToSortable: function( $sortable ) {
			var $prototype = this.$el.find( '.uf-group-prototype' );

			$prototype.draggable({
				connectToSortable: $sortable,
				helper: 'clone',
				revert: 'invalid',
				containment: $sortable.closest( '.input' ),
				start: function() {
					$prototype.addClass( 'dragging' );
				},
				stop: function() {
					setTimeout(function() {
						$prototype.removeClass( 'dragging' );
					}, 30 );
				}
			});
		},

		/**
		 * Sends a message to the repeater that a new group is needed.
		 */
		add: function( e ) {
			if( ! this.$el.find( '.uf-group-prototype' ).is( '.dragging' ) ) {
				this.trigger( 'clicked' );
			}
		}
	});

})( jQuery );
