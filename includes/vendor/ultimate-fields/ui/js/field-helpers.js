(function( $ ){

	var ui        = window.UltimateFields.UI,
		field     = ui.Field;

	/**
	 * Add functionality for the text field.
	 */
	field.Helper.Text = field.Helper.extend({
		/**
		 * Sets the model for the preview up.
		 */
		setupPreview: function( args ) {
			var attributes = args.data.get( 'text_attributes' ) || {};

			args.model.set({
				default_value: args.data.get( 'default_value_text' ),
				placeholder:   attributes.text_placeholder,
				prefix:        attributes.prefix,
				suffix:        attributes.suffix
			});
		}
	});

	/**
	 * Add functionality for the password field.
	 */
	field.Helper.Password = field.Helper.extend({
		/**
		 * Sets the model for the preview up.
		 */
		setupPreview: function( args ) {
			var attributes = args.data.get( 'password_attributes' ) || {};

			args.model.set({
				default_value: args.data.get( 'default_value_password' ),
				placeholder:   attributes.password_placeholder,
				prefix:        attributes.password_prefix,
				suffix:        attributes.password_suffix
			});
		}
	});

	/**
	 * Preview and editing functionality for the WYSIWYG field.
	 */
	field.Helper.WYSIWYG = field.Helper.extend({
		/**
		 * Sets the model for the preview up.
		 */
		setupPreview: function( args ) {
			args.model.set( 'default_value', args.data.get( 'default_value_wysiwyg' ) );
		}
	});

	/**
	 * Add functionality for the checkbox field.
	 */
	field.Helper.Checkbox = field.Helper.extend({
		/**
		 * Sets the model for the preview up.
		 */
		setupPreview: function( args ) {
			args.model.set( 'text', args.data.get( 'checkbox_text' ) || '' );
			args.model.set( 'fancy', args.data.get( 'fancy_checkbox' ) || false );
		},

		/**
		 * Returns the comparators that can be used for conditional logic.
		 *
		 * For the checkbox field, it can only be true or false.
		 */
		getComparators: function() {
			return [
				{
					compare: 'NOT_NULL',
					label:   'is checked',
					operand: false
				},
				{
					compare: 'NULL',
					label:   'is not checked',
					operand: false
				}
			];
		}
	});

	/**
	 * Adds functionality for the previews of select fields.
	 */
	field.Helper.Select = field.Helper.extend({
		/**
		 * Sets up the model for the preview.
		 */
		setupPreview: function( args ) {
			this.loadOptions( args );

			// Proxy the basic settings
			args.model.set({
				input_type:  args.data.get( 'select_input_type' ),
				orientation: args.data.get( 'select_orientation' ),
				use_select2: ( 'dropdown' == args.data.get( 'select_type' ) ) && args.data.get( 'use_select2' )
			});
		},

		/**
		 * Extracts manual options from a fields' data.
		 */
		extractOptions: function( raw ) {
			var options = {};

			// Parse options
			_.each( ( raw || '' ).split( "\n" ), function( option ) {
				var parts;

				if( '' === option ) {
					return;
				}

				option = option.trim();

				if( -1 == option.indexOf( '::' ) ) {
					options[ option ] = option;
				} else {
					parts = option.split( '::' );
					options[ parts[ 0 ].trim() ] = parts[ 1 ].trim();
				}
			});

			return options;
		},

		/**
		 * Adds options to a field based on it's settings.
		 */
		loadOptions: function( field ) {
			if( 'posts' != field.data.get( 'select_options_type' ) ) {
				field.model.set( 'options', this.extractOptions( field.data.get( 'select_options' ) ) )	;
				return;
			}

			// Load dynamic posts
			$.ajax({
				type: 'post',
				data: {
					uf_ajax:   true,
					uf_action: 'select_ui_options',
					post_type: field.data.get( 'select_post_type' )
				},
				success: function( options ) {
					field.model.set( 'options', $.parseJSON( options ) );
					field.model.trigger( 'options-changed' );
				}
			});
		},

		/**
		 * Returns the available comparators for conditional logic.
		 */
		getComparators: function() {
			return [
				{
					compare: '=',
					label:   'equals',
					operand: true
				},
				{
					compare: '!=',
					label:   'is not equal to',
					operand: true
				}
			];
		},

		/**
		 * Creates a view for the operand.
		 */
		operand: function( currentValue ) {
			var that = this, model, datastore, view;

			// Create a blank datastore
			datastore = new UltimateFields.Datastore({
				value: currentValue
			});

			// Create a model for the field
			model = UltimateFields.Field.Collection.prototype.model({
				type:    'Select',
				name:    'value',
				label:   ''
			});

			this.loadOptions({
				model: model,
				data: this.model.datastore
			});

			model.datastore = datastore;

			// Create the view
			view = new UltimateFields.Field.Select.View({
				model: model
			});

			return view;
		}
	});

	/**
	 * Extend the functionality of the select field for the multiselect.
	 */
	field.Helper.Multiselect = field.Helper.Select.extend({
		/**
		 * Sets up the model for the preview.
		 */
		setupPreview: function( args ) {
			this.loadOptions( args );

			// Proxy basic options
			args.model.set( 'input_type', args.data.get( 'multiselect_input_type' ) );
			args.model.set( 'orientation', args.data.get( 'select_orientation' ) );
		},

		/**
		 * Returns comparators for conditional logic.
		 */
		getComparators: function() {
			return [
				{
					compare: 'NOT_NULL',
					label:   'has a checked value',
					operand: false
				},
				{
					compare: 'NULL',
					label:   'does not have a checked value',
					operand: false
				},
				{
					compare: 'CONTAINS',
					label:   'contains',
					operand: true
				},
				{
					compare: 'DOES_NOT_CONTAIN',
					label:   'does not contain',
					operand: true
				}
			];
		}
	});

	/**
	 * Handles the image select field within the fields editor/preview.
	 */
	field.Helper.Image_Select = field.Helper.Select.extend({
		/**
		 * Prepares a model for the preview.
		 */
		setupPreview: function( args ) {
			var that    = this,
				options = {};

			_.each( args.data.get( 'image_select_options' ) || [], function( option ) {
				var image = that.getImageForOption( option );

				if( ! image )
					return;

				options[ option.key ] = {
					image: image,
					label: option.label
				}
			});

			args.model.set( 'options', options );
		},

		/**
		 * Returns the image for an option.
		 */
		getImageForOption: function( option ) {
			var imageID = option.image, image, cached;

			// If there is no image, skip the option
			if( ! imageID )
				return false;

			// Cache files if any
			_.each( option.image_prepared, UltimateFields.Field.File.Cache.add );

			// Load the files
			if ( cached = UltimateFields.Field.File.Cache.get( imageID ) ) {
				image = cached.get( 'url' );
			}

			return image;
		},

		/**
		 * Loads options for the conditional logic operand.
		 */
		loadOptions: function( args ) {
			var options = {};

			_.each( args.data.get( 'image_select_options' ) || [], function( o ) {
				options[ o.key ] = o.label;
			})

			args.model.set( 'options', options );
			args.model.trigger( 'options-changed' );
		}
	});

	/**
	 * Prepares a file field.
	 */
	field.Helper.File = field.Helper.extend({
		/**
		 * Sets a preview up.
		 */
		setupPreview: function( args ) {
			var prefix = args.data.get( 'type' ).toLowerCase();

			_.each(
				args.data.get( 'default_value_' + prefix + '_prepared' ) || {},
				UltimateFields.Field.File.Cache.add
			);
		}
	});

	/**
	 * Extend the file helper for the Image field.
	 */
	field.Helper.Image = field.Helper.File.extend({});

	/**
	 * Extend the file helper for the Audio field.
	 */
	field.Helper.Audio = field.Helper.File.extend({});

	/**
	 * Extend the file helper for the Video field.
	 */
	field.Helper.Video = field.Helper.File.extend({});

	/**
	 * Extend the file helper for the Gallery field.
	 */
	field.Helper.Gallery = field.Helper.File.extend({});

	/**
	 * Adds functionality for the previews of embeds.
	 */
	field.Helper.Embed = field.Helper.extend({
		/**
		 * Proxies the default value to the preview.
		 */
		setupPreview: function( args ) {
			var code;

			if( code = args.data.get( 'default_value_embed_embed_code' ) ) {
				var url = args.data.get( 'default_value_embed' );

				UltimateFields.Field.Embed.cache[ url ] = {
					url:  url,
					code: code
				}

				args.datastore.set( 'embed_field_embed_code', code );
			}
		}
	});

	/**
	 * Adds functionality for the previews of object fields.
	 */
	field.Helper.Object = field.Helper.extend({
		setupPreview: function( args ) {
			args.model.set( 'button_text', args.data.get( 'object_text' ) );
			this.cacheValue( args );
		},

		/**
		 * Caches the value of an object field.
		 */
		cacheValue: function( args ) {
			var type = args.model.get( 'type' ).toLowerCase(),
				defaultKey;

			defaultKey = 'default_value_' + type + '_prepared';
			if( ! args.data.get( defaultKey ) ) {
				return;
			}

			var prepared = args.data.get( defaultKey );
			_.each( prepared, function( object ) {
				UltimateFields.Field.Object.cache[ object.id ] = object;
			});
		},

		/**
		 * Generates a view that will be used for the operand.
		 *
		 * This function should return a normal UltimateFields.Field.View, which
		 * already has a "proper" model associated with it.
		 */
		operand: function( currentValue, ruleView ) {
			var that = this, model, datastore, view, nonce;

			// Read out the cache, if any
			UltimateFields.UI.Context.getCurrent().get( 'fields' ).each(function( field ) {
				if( field.datastore.get( 'name' ) == ruleView.field.datastore.get( 'name' ) ) {
					var prepared = field.datastore.get( 'default_value_object_prepared' );

					_.each( prepared || [], function( item ) {
						UltimateFields.Field.Object.cache[ item.id ] = item;
					});
				}
			});

			// Create a blank datastore along with a model and a view
			datastore = new UltimateFields.Datastore({ default_value_object: currentValue });
			nonce     = _.findWhere( this.model.get( 'rawFields' ), { name: 'default_value_object' } ).nonce;

			model = new UltimateFields.Field.Object.Model({
				name: 'default_value_object',
				nonce: nonce
			});
			model.setDatastore( datastore );
			view = new UltimateFields.Field.Object.View({ model: model });

			return view;
		}
	});

	/**
	 * Extends the functionality of the object field for the objects field.
	 */
	field.Helper.Objects = field.Helper.Object.extend({
		setupPreview: function( args ) {
			args.model.set( 'button_text', args.data.get( 'objects_text' ) );
			this.cacheValue( args );
		}
	});

	/**
	 * Extends the functionality of the object field for the file field.
	 */
	field.Helper.Link = field.Helper.Object.extend({
		setupPreview: function( args ) {
			args.model.set( 'button_text', args.data.get( 'object_text' ) );

			var tc = args.data.get( 'link_target_control' );
			if( ( 'undefined' != typeof tc ) && ! tc ) {
				args.model.set( 'target_control', false );
			}

			this.cacheValue( args );
		},

		/**
		 * Disable conditional logic, it's too complicated
		 */
		getComparators: function(){
			return false;
		}
	});

	/**
	 * Adds UI functionality for the icon field.
	 */
	field.Helper.Icon = field.Helper.extend({
		/**
		 * Sets up the preview of the field.
		 */
		setupPreview: function( args ) {
			var sets = [];

			_.each( UltimateFields.L10N.localize( 'icon_sets' ), function( set, name ) {
				sets.push( name );
				UltimateFields.Field.Icon.Model.loadSet( name );
			});

			// Load the stylesheet if needed
			if( args.model.get( 'default_value' ) ) {
				var icon = UltimateFields.Field.Icon.Model.locate( args.model.get( 'default_value' ) );
				if( icon.set.stylesheet ) {
					UltimateFields.Field.Icon.loadStylesheet( icon.set.stylesheet );
				}
			}

			args.model.set( 'icon_sets', sets );
		}
	});

	/**
	 * Adds UI functionality for the number field.
	 */
	field.Helper.Number = field.Helper.extend({
		/**
		 * Sets up the preview of the field.
		 */
		setupPreview: function( args ) {
			args.model.set({
				slider_enabled: args.data.get( 'number_slider' ),
				minumum:        args.data.get( 'number_minumum' ) || 1,
				maximum:        args.data.get( 'number_maximum' ) || 100,
				step:           args.data.get( 'number_step' )    || 1
			});
		},

		/**
		 * Returns the available comparators for conditional logic.
		 */
		getComparators: function() {
			return [
				{
					compare: '=',
					label:   'equals',
					operand: true
				},
				{
					compare: '>',
					label:   'is greater than',
					operand: true
				},
				{
					compare: '<',
					label:   'is lesser than',
					operand: true
				},
				{
					compare: '>=',
					label:   'is greater than or equal to',
					operand: true
				},
				{
					compare: '<=',
					label:   'is lesser than or equal to',
					operand: true
				},
				{
					compare: '!=',
					label:   'is not equal to',
					operand: true
				}
			];
		},

		/**
		 * Creates a view for the operand.
		 */
		operand: function( currentValue ) {
			var that = this, model, datastore, view;

			// Create a blank datastore
			datastore = new UltimateFields.Datastore({
				value: currentValue
			});

			// Create a model for the field
			model = UltimateFields.Field.Collection.prototype.model({
				type:    'Number',
				name:    'value',
				label:   ''
			});
			model.setDatastore( datastore );

			// Create the view
			view = new UltimateFields.Field.Number.View({
				model: model
			});

			return view;
		}
	});

	/**
	 * Adds UI functionality for the sidebar field.
	 */
	field.Helper.Sidebar = field.Helper.extend({
		/**
		 * Sets up the preview of the field.
		 */
		setupPreview: function( args ) {
			args.model.set({
				editable: args.data.get( 'sidebar_editable' ),
				name:     'default_value_sidebar'
			});
		}
	});

	/**
	 * Adds UI functionality for the tab field.
	 */
	field.Helper.Tab = field.Helper.extend({
		/**
		 * Sets up the preview of the field.
		 */
		setupPreview: function( args ) {
			var icon = args.data.get( 'tab_icon' );

			args.model.set({
				hide_label: true,
				icon: 	    icon ? 'dashicons ' + icon : false
			});
		},

		/**
		 * Disables conditional logic for the field.
		 */
		getComparators: function() {
			return false;
		}
	});

	/**
	 * Adds UI functionality for the section field.
	 */
	field.Helper.Section = field.Helper.extend({
		/**
		 * Sets up the preview of the field.
		 */
		setupPreview: function( args ) {
			var icon = args.data.get( 'section_icon' );

			args.model.set({
				hide_label: true,
				icon:       icon ? 'dashicons ' + icon : false
			});
		},

		/**
		 * Disables conditional logic for the field.
		 */
		getComparators: function() {
			return false;
		}
	});

	/**
	 * Adds UI functionality for the complex field.
	 */
	field.Helper.Complex = field.Helper.extend({
		/**
		 * Sets up the preview of the field.
		 */
		setupPreview: function( args ) {
			var fields = [];

			if( 'container' == args.data.get( 'complex_fields_source' ) ) {
				// Load dynamically
				this.isDynamicallyLoaded = args.data.get( 'complex_container' );
				this.args                = args;
				args.datastore           = false;
			} else {
				// Generate manually
				_.each( args.data.get( 'complex_fields' ), function( sub ) {
					var datastore = new UltimateFields.Datastore( sub ),
					model     = new UltimateFields.UI.Field.Model();

					model.datastore = datastore;
					fields.push( model.generateFieldModel() );
				});

				args.model.set( 'group', {
					id:     'complex_group',
					title:  '',
					fields: fields,
					layout: args.data.get( 'complex_layout' )
				});

				this.isDynamicallyLoaded = false;
			}
		},

		/**
		 * Disables conditional logic for the field.
		 */
		getComparators: function() {
			return false;
		},

		/**
		 * Indicates if an AJAX action is needed in order to load the fields.
		 */
		shouldWait: function( callback ) {
			var that = this;

			if( ! this.isDynamicallyLoaded ) {
				return false;
			}

			ui.loadContainerData( [ this.isDynamicallyLoaded ], function( containers ) {
				var fields = [], data;

				_.each( containers, function( container ) {
					data = container;
				});

				that.args.model.set( 'group', {
					id:     'complex_group',
					title:  '',
					fields: data.fields,
					layout: that.args.data.get( 'complex_layout' )
				});

				var datastoreData = {};
				datastoreData[ that.args.model.get( 'name' ) ] = data.data;

				// Set a datastore in order to let the group work
				that.args.model.setDatastore( new UltimateFields.Datastore( datastoreData ) );

				callback();
			});

			return true;
		},

		/**
		 * Modifies the field after it's rendered.
		 */
		afterRender: function( view ) {
			view.$el.find( '.uf-fields' ).addClass( 'uf-boxed-fields' );
		}
	});

	/**
	 * Adds UI functionality for the repeater field.
	 */
	field.Helper.Repeater = field.Helper.extend({
		/**
		 * Sets up the preview of the field.
		 */
		setupPreview: function( args ) {
			var that   = this,
				groups = [];

			// Saves the groups that should be dynamically loaded
			this.args    = args;
			this.pending = [];
			this.groups  = groups;

			// Go through each group and prepare it
			_.each( args.data.get( 'repeater_groups' ), function( raw ) {
				var group = {
					description: '',
					icon:        '',
					id:          raw.name,
					type:        raw.name
				};

				_.extend( group, raw );

				if( 'container' == raw.fields_source ) {
					that.pending.push( group );
				}

				groups.push( group );
			});

			// Basics
			var labels = args.data.get( 'repeater_labels' ) || {};
			var background = args.data.get( 'repeater_background_color' );
			if( '#fff' == background || '#ffffff' == background ) {
				background = false;
			}

			args.model.set({
				background:       background,
				add_text:         labels.repeater_add_text || 'Add',
				chooser_type:     args.data.get( 'repeater_chooser_type' ),
				placeholder_text: labels.repeater_placeholder_text || (
					1 === groups.length && 'dropdown' == args.data.get( 'repeater_chooser_type' )
						? UltimateFields.L10N.localize( 'repeater-basic-placeholder-single' )
						: UltimateFields.L10N.localize( 'repeater-basic-placeholder-multiple' )
				)
			});
		},

		/**
		 * Normalizes the widths of columns for tables.
		 */
		setupTable: function() {
			var args   = this.args,
				groups = this.groups;

			if( 'table' != args.data.get( 'repeater_layout' ) || 1 !== groups.length ) {
				return;
			}

			var total = 0, ratio;

			// Let the field know about the layout
			args.model.set( 'layout', 'table' );

			// Collect and set widths
			_.each( groups[ 0 ].fields, function( field ) {
				total += parseInt( field.field_width );
			});

			ratio = 100 / total;

			_.each( groups[ 0 ].fields, function( field ) {
				field.field_width = parseInt( field.field_width ) * ratio;
			});

			args.model.set( 'default_value', [{ __type: groups[ 0 ].id }] );
		},

		/**
		 * Indicates if the preview should wait or not.
		 */
		shouldWait: function( callback ) {
			var that = this, queue = [], finishUp;

			finishUp = function()
			{
				that.setupTable();
				that.args.model.set( 'groups', that.groups );
			}

			if( ! this.pending.length ) {
				finishUp();
				return false;
			}

			// Collect IDs
			queue = _.pluck( this.pending, 'container' );

			ui.loadContainerData( queue, function( data ) {
				_.each( data, function( raw, id ) {
					_.each( that.pending, function( group ) {
						if( group.container == id ) {
							that.importDataToGroup( group, raw.fields );
						}
					});
				});

				finishUp();
				callback();
			});

			return true;
		},

		/**
		 * Imports data into a group.
		 */
		importDataToGroup: function( group, data ) {
			var fields = [];

			_.each( data, function( sub ) {
				var datastore = new UltimateFields.Datastore( sub ),
					model     = new UltimateFields.UI.Field.Model();

				model.datastore = datastore;
				fields.push( model.generateFieldModel() );
			});

			group.fields = data;
		},

		/**
		 * Returns comparators for conditional logic.
		 */
		getComparators: function() {
			return [
				{
					compare: 'NOT_NULL',
					label:   'is not empty',
					operand: false
				},
				{
					compare: 'NULL',
					label:   'is empty',
					operand: false
				},
				{
					compare: 'CONTAINS_GROUP',
					label:   'contains',
					operand: true
				},
				{
					compare: 'DOES_NOT_CONTAIN_GROUP',
					label:   'does not contain',
					operand: true
				}
			];
		},

		/**
		 * Creates a view for the operand.
		 */
		operand: function( currentValue ) {
			var that = this, model, datastore, view, groups = {};

			// Load all groups
			_.each( this.model.datastore.get( 'repeater_groups' ), function( group ) {
				groups[ group.name ] = group.title;
			});

			datastore = new UltimateFields.Datastore({
				value: currentValue
			});

			// Create a model for the field
			model = UltimateFields.Field.Collection.prototype.model({
				type:    'Select',
				name:    'value',
				label:   '',
				options: groups
			});

			model.datastore = datastore;

			// Create the view
			view = new UltimateFields.Field.Select.View({
				model: model
			});

			return view;
		}
	}, {
		/**
		 * Sets up the group fields within the editor to auto-populate group types based on names.
		 */
		bindToEditor: function( editor ) {
			var that = this,
				groupsField;

			groupsField = editor.get( 'fields' ).findWhere({ name: 'repeater_groups' });
			groupsField.on( 'groupAdded', function( group ) {
				var titleField = group.get( 'fields' ).findWhere({ name: 'title' }),
					nameField  = group.get( 'fields' ).findWhere({ name: 'name' });

				titleField.on( 'text-changed', function() {
					var title = group.datastore.get( 'title' ),
						name  = group.datastore.get( 'name' );

					if( name.trim().length > 0 )  {
						return;
					}

					name = title
						.trim()
						.toLowerCase()
						.replace( /[\s\-]/g, '_' );

					group.datastore.set( 'name', name );
					nameField.trigger( 'external-value' );
				});
			});
		}
	});

	/**
	 * Adds UI functionality for the layout field.
	 */
	field.Helper.Layout = field.Helper.extend({
		/**
		 * Sets up the preview of the field.
		 */
		setupPreview: function( args ) {
			var groups = [];

			_.each( args.data.get( 'layout_groups' ), function( raw ) {
				var group = $.extend({
					description: '',
					icon: ''
				}, _.clone( raw ) );

				group.type = group.id = group.name;
				groups.push( group );
			});

			args.model.set( 'groups', groups );
		},

		/**
		 * Returns comparators for conditional logic.
		 */
		getComparators: function() {
			return [
				{
					compare: 'NOT_NULL',
					label:   'is not empty',
					operand: false
				},
				{
					compare: 'NULL',
					label:   'is empty',
					operand: false
				},
				{
					compare: 'CONTAINS_GROUP',
					label:   'contains',
					operand: true
				},
				{
					compare: 'DOES_NOT_CONTAIN_GROUP',
					label:   'does not contain',
					operand: true
				}
			];
		},

		/**
		 * Creates a view for the operand.
		 */
		operand: function( currentValue ) {
			var that = this, model, datastore, view, groups = {};

			// Load all groups
			_.each( this.model.datastore.get( 'layout_groups' ), function( group ) {
				groups[ group.name ] = group.title;
			});

			datastore = new UltimateFields.Datastore({
				value: currentValue
			});

			// Create a model for the field
			model = UltimateFields.Field.Collection.prototype.model({
				type:    'Select',
				name:    'value',
				label:   '',
				options: groups
			});

			model.datastore = datastore;

			// Create the view
			view = new UltimateFields.Field.Select.View({
				model: model
			});

			return view;
		}
	}, {
		/**
		 * Sets up the group fields within the editor to auto-populate group types based on names.
		 */
		bindToEditor: function( editor ) {
			var that = this,
				groupsField;

			groupsField = editor.get( 'fields' ).findWhere({ name: 'layout_groups' });
			groupsField.on( 'groupAdded', function( group ) {
				var titleField = group.get( 'fields' ).findWhere({ name: 'title' }),
					nameField  = group.get( 'fields' ).findWhere({ name: 'name' });

				titleField.on( 'text-changed', function() {
					var title = group.datastore.get( 'title' ),
						name  = group.datastore.get( 'name' );

					if( name.trim().length > 0 )  {
						return;
					}

					name = title
						.trim()
						.toLowerCase()
						.replace( /[\s\-]/g, '_' );

					group.datastore.set( 'name', name );
					nameField.trigger( 'external-value' );
				});
			});
		}
	});

	/**
	 * A simple helper, which disables conditional logic for the field.
	 */
	field.Helper.NotAvailableForLogic = field.Helper.extend({
		getComparators: function() {
			return false;
		}
	});

	field.Helper.Message = field.Helper.NotAvailableForLogic.extend();

})( jQuery );
