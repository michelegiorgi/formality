(function( $ ){

	var uf       = window.UltimateFields,
		field    = window.UltimateFields.Field,
		repeater = field.Repeater,
		layout   = field.Layout = {};

	// Add some extra functionality for layout models
	layout.Model  = repeater.Model.extend({
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

			// Check counts
			this.calcGroupCounts();
		},

		/**
		 * Starts listening for data changes.
		 */
		listenToGroupChanges: function() {
			var that = this;

			// Handle changes
			this.rows.on( 'change sort destroy', function( e ) {
				if( ( 'changed' in e ) && ( '__tab' in e.changed ) )
					return;

				that.exportToDatastore();
				that.calcGroupCounts();
			});
		},

		/**
		 * When there is a reason to, exports the added rows in a proper format.
		 */
		exportToDatastore: function() {
			var exported = [], max = 0, i, totalIndex = 1;

			// Get the maximum amount of rows
			this.groups.forEach(function( group ) {
				max = Math.max( max, group.get( 'row' ) + 1 );
			});

			// Create empty rows
			for( i=0; i<max; i++ ) {
				exported.push([]);
			}

			// Go through rows and export them
			this.groups.forEach(function( group ) {
				var rowIndex = group.get( 'row' );

				exported[ rowIndex ].push({
					index: group.get( 'index' ),
					data:  group.datastore,
					group: group
				});
			});

			// Go through each row, sort results and then export them
			for( i=0; i<max; i++ ) {
				// Sort
				exported[ i ].sort(function compare(a, b) {
					return a.index > b.index  ? 1 : -1;
				});

				// Export
				exported[ i ] = exported[ i ].map(function( combo ) {
					combo.group.set( 'displayed_index', totalIndex++ );
					return combo.data.toJSON();
				});
			}

			this.setValue( exported );
			this.trigger( 'value-saved' );
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

	// Define the view for the layout
	layout.View = repeater.View.extend({
		initialize: function() {
			var that = this;

			// Do the standard initialization
			field.View.prototype.initialize.apply( this, arguments );

			// Listen for replacements
			this.model.datastore.on( 'value-replaced', function( name ) {
				if( name != that.model.get( 'name' ) ) {
					return;
				}

				that.model.groups = [];
				that.model.rows.reset([]);
				that.render();
			})
		},

		/**
		 * Renders the view.
		 */
		render: function() {
			var that  = this,
				tmpl  = UltimateFields.template( 'layout' ),
				proto = UltimateFields.template( 'layout-element-prototype' ),
				types = [];

			// Add the CSS class and the basic layout
			this.$el.addClass( 'uf-layout' );
			this.$el.html( tmpl() );

			// Add all existing elements
			this.addExistingData();

			// Add all types as draggables
			_.each( this.model.get( 'groups' ), function( group ) {
				var type = that.model.getGroupType( group.id ), $proto;

				types.push({
					id:    group.id	,
					title: group.title,
					min:   group.min_width,
					max:   group.max_width
				});

				$proto = $( proto({
					type:        group.id,
					title:       group.title,
					description: group.description
				}));

				type.on( 'change', function() {
					$proto[ type.canBeAdded() ? 'show' : 'hide' ]();
					UltimateFields.ContainerLayout.DOMUpdated();
				});

				if( ! type.canBeAdded() ) {
					$proto.hide();
				}

				that.$el.find( '.uf-layout-draggables' ).append( $proto );
			});

			// Start the external layout script
			this.$el.layout({
				columns: this.model.get( 'columns' ),
				types:   types,
				placeholderRow: function( $row, row ) {
					$row.append( UltimateFields.template( 'layout-placeholder' )() );
					row.$groups = $row.find( '.uf-layout-row-groups' );
				},
				populateElement: this.populateElement.bind( this )
			});

			// Listen for updates in the layout, in order to dump values
			this.$el.on( 'layout-updated', function() {
				that.model.exportToDatastore();
			});

			// Start a grid within the draggables to adjust spacings based on position
			var layout = new UltimateFields.ContainerLayout({
				el: this.$el.find( '.uf-layout-draggables' ),
				gridSelector: '.uf-repeater-prototypes-column'
			});

			layout.startGrid();

			// When everything is set up, start listening for changes
			this.model.listenToGroupChanges();
		},

		/**
		 * Creates and prepares the view for a group.
		 */
		prepareGroupView: function( type, data, currentIndex ) {
			var that = this, settings, datastore, model, view;

			settings = _.findWhere( this.model.get( 'groups' ), {
				id: type
			});

			datastore = new UltimateFields.Datastore( data || {} );
			datastore.parent = this.model.datastore;

			// datastore.set( '__index', options.index );
			datastore.set( '__type', type );

			// Prepare the container model
			model = new UltimateFields.Container.Layout_Group.Model( settings );
			model.set( '__type', settings.id );
			model.setDatastore( datastore );
			if( currentIndex ) {
				model.set( 'displayed_index', currentIndex )
			}

			// Push the datastore to the rows
			this.model.rows.add( datastore, {
				silent: false
			});

			this.model.groups.push( model );
			model.on( 'destroy', function() {
				that.model.groups.splice( that.model.groups.indexOf( model ), 1 );
			});

			// Create the view
			view = new UltimateFields.Container.Layout_Group.View({
				model: model
			});

			return view;
		},

		/**
		 * Populates an element once its within the layout.
		 */
		populateElement: function( element ) {
			var that = this, view;

			view = this.prepareGroupView( element.type.id );
			element.$el.append( view.$el );
			view.render();

			// Bind the view to the element it's contained within.
			view.bindToElement( element );

			// If the model is destroyed, remove the element
			view.model.on( 'destroy', function() {
				element.$el.remove();
				element.trigger( 'destroy' );
			});
		},

		/**
		 * Adds the existing data to the view.
		 */
		addExistingData: function() {
			var that = this, $content = this.$el.find( '.uf-layout-content' ), rowIndex = 0, totalIndex = 1;

			_.each( this.model.getValue() || [], function( row ) {
				var $row       = $( UltimateFields.template( 'layout-row' )() )
					$rowGroups = $row.find( '.uf-layout-row-groups' );

				// Add the row to the content
				$content.append( $row );

				_.each( row, function( element ) {
					var $element, view;

					$element = $( '<div class="uf-layout-element" />' )
						.data({
							type:  element.__type,
							width: element.__width
						})
						.appendTo( $rowGroups )
				
					view = that.prepareGroupView( element.__type, element, totalIndex++ );
					view.model.set( 'row', rowIndex );
					$element.append( view.$el );
					view.render();

					// Once the layout plugin associates the div with an element, bind the view to it
					$element.on( 'uf-layout-added', function( e, layoutElement ) {
						view.bindToElement( layoutElement );

						// If the model is destroyed, remove the element
						view.model.on( 'destroy', function() {
							layoutElement.$el.remove();
							layoutElement.trigger( 'destroy' );
						});
					});

					rowIndex++;
				});
			});

			// Calculate the count of existing groups after they are in place
			this.model.calcGroupCounts();
		},

		/**
		 * Adjust the view of the input to a certain width.
		 */
		adjustToWidth: function( width ) {
			width = width || this.$el.width();

			if( 400 > width ) {
				this.$el.addClass( 'uf-layout-small');
			} else {
				this.$el.removeClass( 'uf-layout-small');
			}
			// console.log(width);
		}
	});

})( jQuery );
