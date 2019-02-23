(function( $ ){

	/**
	 * This file handles the Group container of Ultimate Fields that is used for repeaters.
	 */
	var container = UltimateFields.Container,
		group     = container.Group = {};

	group.Model = container.Base.Model.extend({
		/**
		 * Add more defaults for repeater groups.
		 */
		defaults: _.extend({
			duplicateable:    true,
			deleteable:       true,
			maximum:          0,
			title_background: false,
			title_color:      false,
			icon:             false
		}, container.Base.Model.prototype.defaults ),

		backupState: function() {
			var temp = this.datastore.clone();
			temp.parent = this.datastore.parent;
			this.realDatastore = this.datastore;
			this.setDatastore( temp );
		},

		saveState: function() {
			var newData = this.datastore.toJSON();

			if( '__tab' in newData )
				delete newData.__tab;

			this.realDatastore.set( newData );
			delete this.datastore;
			this.setDatastore( this.realDatastore );
			this.trigger( 'stateSaved' );
		},

		restoreState: function() {
			this.setDatastore( this.realDatastore );
		},

		/**
		 * Binds a group to it's type, so the group can toggle its cotnrols.
		 */
		bindToGroupType: function( type ) {
			var that = this;

			if( ! type ) {
				return;
			}

			// Collect initial values
			this.set({
				duplicateable: type.canBeAdded(),
				deleteable:    type.canBeRemoved()
			});

			type.on( 'change', function() {
				that.set({
					duplicateable: type.canBeAdded(),
					deleteable:    type.canBeRemoved()
				});
			});
		}
	});

	group.View = container.Base.View.extend({
		className: 'uf-group',

		events: {
			'uf-sorted': 'saveSort'
		},

		render: function() {
			var that = this,
				tmpl = UltimateFields.template( 'repeater-group' ),
				clicks, background, color;

			this.$el.html( tmpl({
				title:      this.model.get( 'title' ),
				type:       this.model.get( 'type' ),
				icon:       this.model.get( 'icon' ),
				edit_mode:  this.model.get( 'edit_mode' ),
				number:     this.$el.index() + 1 - this.$el.prevAll( '.uf-repeater-placeholder' ).length
			}));

			// Add the necessary style-settings
			this.addStyles();

			// Bind control clicks
			this.bindClicks();

			// Bind the destroyer
			this.model.on( 'destroy', this.remove.bind( this ) );

			// Add inline fields
			if( 'popup' != this.model.get( 'edit_mode' ) ) {
				this.addInlineElements();
			} else {
				this.$el.addClass( 'uf-group-hidden' );
			}

			// When values change, change the title
			this.addTitleListener();

			// Toggle button
			this.toggleElements();
			this.model.on( 'change:duplicateable', _.bind( this.toggleElements, this ) );
			this.model.on( 'change:deleteable', _.bind( this.toggleElements, this ) );

			// Whenever there are errors, add some styles
			this.addValidationStateListener();
		},

		/**
		 * Adds color styles to the group.
		 */
		addStyles: function() {
			var background, color, border;

			// Style the title if needed
			if( background = this.model.get( 'title_background' ) ) {
				this.$el.find( '.uf-group-title' ).css( 'background-color', background );
			}

			if( color = this.model.get( 'title_color' ) ) {
				this.$el.find( '.uf-group-title' ).css( 'color', color );
			}

			if( border = this.model.get( 'border_color' ) ) {
				this.$el.css( 'border-color', border );
			}
		},

		/**
		 * Binds the clicks for basic elements.
		 */
		bindClicks: function() {
			var that = this, clicks;

			// Assign first-level events before rendering the content/fields
			clicks = {
				'h3':                          'toggle',
				'.uf-group-control-close':     'close',
				'.uf-group-control-open':      'open',
				'.uf-group-control-remove':    'delete',
				'.uf-group-control-popup':     'openPopup',
				'.uf-group-control-duplicate': 'duplicate'
			}

			_.each( clicks, function( handler, className ) {
				that.$el.find( className ).on( 'click', function( e ) {
					e.preventDefault();

					// When dragging and etc, don't let the buttons work
					if( that.$el.is( '.no-click' ) )
						return;

					that[ handler ]();
				});
			});
		},

		/**
		 * Adds inline elements like fields and etc..
		 *
		 * This is only used when the group can be edited without a popup.
		 */
		addInlineElements: function() {
			var that = this;

			if( ! this.fieldsRendered && ! this.model.datastore.get( '__hidden' ) ) {
				this.addFields();
				this.fieldsRendered = true;
				UltimateFields.ContainerLayout.DOMUpdated();
			}

			// When values change, re-render the fields
			this.model.on( 'stateSaved', function() {
				if( ! that.fieldsRendered ) {
					return;
				}

				that.$fields.empty();
				that.addFields( that.$fields );
			});

			// Hide/show content
			if( this.model.datastore.get( '__hidden' ) ) {
				this.$el.addClass( 'uf-group-hidden' );
			}
		},

		/**
		 * Listens for changes the title.
		 */
		addTitleListener: function() {
			var that = this;

			this.model.datastore.on( 'change', function() {
				that.updateTitlePreview();
			});

			that.updateTitlePreview();
		},

		/**
		 * Adds a listener, which monitors the validation state of the group.
		 */
		addValidationStateListener: function() {
			var that = this;

			this.model.on( 'change:validationErrors', function() {
				var method = that.model.get( 'validationErrors' ).length
					? 'addClass'
					: 'removeClass';

				that.$el[ method ]( 'uf-group-invalid' );
			});
		},

		delete: function() {
			this.model.datastore.destroy();
			this.model.destroy();
			this.remove();
		},

		duplicate: function() {
			this.trigger( 'uf-duplicate', {
				datastore: this.model.datastore.clone()
			});
		},

		saveSort: function() {
			var displayed;

			this.model.datastore.set( '__index', this.$el.index(), {
				silent: true
			});

			displayed = this.$el.index() + 1 - this.$el.prevAll( '.uf-repeater-placeholder' ).length;
			this.$el.find( '.uf-group-number-inside' ).eq( 0 ).text( displayed );
		},

		openPopup: function() {
			var that = this,
				view;

			// Save the state of the datastore
			this.model.backupState();

			view = new group.fullScreenView({
				model: this.model
			});

			UltimateFields.Overlay.show({
				view: view,
				title: this.model.datastore.get( 'title' ) || this.model.get( 'title' ),
				buttons: view.getbuttons()
			});
		},

		/**
		 * Closes the group.
		 */
		close: function() {
			var that    = this,
				$inside = this.$el.find( '.uf-group-inside' ).eq( 0 );

			this.$el.addClass( 'uf-group-hidden' );
			this.model.datastore.set( '__hidden', true );
		},

		/**
		 * Opens the group.
		 */
		open: function() {
			var that    = this,
				$inside;

			if( 'popup' == this.model.get( 'edit_mode' ) ) {
				this.openPopup();
				return;
			}

			$inside = this.$el.find( '.uf-group-inside' ).eq( 0 );
			this.$el.removeClass( 'uf-group-hidden' );
			this.model.datastore.set( '__hidden', false );

			// Render fields if required
			if( ! this.fieldsRendered && ! this.model.datastore.get( '__hidden' ) ) {
				this.addFields();
				this.fieldsRendered = true;
			}

			UltimateFields.ContainerLayout.DOMUpdated( true );
			this.focusFirstField();
		},

		/**
		 * Toggles the visiblity of the group.
		 */
		toggle: function() {
			if( this.model.datastore.get( '__hidden' ) ) {
				this.open();
			} else {
				this.close();
			}
		},

		updateTitlePreview: function() {
			var tmpl  = _.template( this.model.get( 'title_template' ) ),
				$em   = this.$el.find( '.uf-group-title-preview' ).eq( 0 ),
				title, prefix;

			try {
				var data = this.model.datastore.toJSON();
				data.fields = this.model.get( 'fields' );
				title = tmpl( data );
			} catch( e ){
				title = '';
			}

			prefix = this.model.get( 'title' ) ? ': ' : '';

			if( title.length ) {
				$em.show().html( prefix + title );
			} else {
				$em.hide().empty();
			}
		},

		/**
		 * Toggles elements based on settings.
		 */
		toggleElements: function() {
			this.$el.find( '.uf-group-control-duplicate' )[ this.model.get( 'duplicateable' ) ? 'show' : 'hide' ]();
			this.$el.find( '.uf-group-control-remove' )[ this.model.get( 'deleteable' ) ? 'show' : 'hide' ]();
		}
	});

	/**
	 * Handles the view of the group within a table layout
	 */
	group.RowView = group.View.extend({
		className: 'uf-group uf-table-row',

		/**
		 * Renders the view.
		 */
		render: function() {
			var that = this,
				tmpl = UltimateFields.template( 'table-row' ),
				clicks, background, color;

			this.$el.html( tmpl({
				title:      this.model.get( 'title' ),
				type:       this.model.get( 'type' ),
				icon:       this.model.get( 'icon' ),
				edit_mode:  this.model.get( 'edit_mode' ),
				number:     this.$el.index() + 1 - this.$el.prevAll( '.uf-repeater-placeholder' ).length
			}));

			// Add the necessary style-settings
			this.addStyles();

			// Bind control clicks
			this.bindClicks();

			this.model.on( 'destroy', this.remove.bind( this ) );

			// Add inline fields
			this.addInlineElements();

			// Toggle button
			this.toggleElements();
			this.model.on( 'change:duplicateable', _.bind( this.toggleElements, this ) );
			this.model.on( 'change:deleteable', _.bind( this.toggleElements, this ) );

			// Whenever there are errors, add some styles
			this.addValidationStateListener();
		},

		/**
		 * Adds inline elements like fields and etc..
		 *
		 * This is only used when the group can be edited without a popup.
		 */
		addInlineElements: function() {
			var that = this;

			this.addFields( null, {
				wrap: UltimateFields.Field.TableWrap
			});
		}
	});

	/**
	 * Handles the display of groups within an overlay.
	 */
	group.fullScreenView = UltimateFields.Container.Group.View.extend({
		tagName:   'form',
		className: 'uf-popup uf-popup-group',

		render: function() {
			var that = this,
				tmpl = UltimateFields.template( 'popup-group' )

			this.$el.attr({
				action: window.location.href,
				method: 'post'
			});

			this.$el.html(tmpl({
				title: this.model.get( 'title' )
			}));

			this.$fields = this.$el.find( '.uf-fields' );
			this.addFields( this.$fields, {
				tabs: false
			});

			this.focusFirstField();
		},

		getbuttons: function() {
			var that = this, buttons = [];

			buttons.push({
				type: 'primary',
				cssClass: 'uf-button-save-popup',
				text: UltimateFields.L10N.localize( 'repeater-save' ).replace( '%s', this.model.get( 'title' ) ),
				icon: 'dashicons-category',
				callback: _.bind( this.save, this )
			});

			if( this.model.get( 'deleteable' ) ) {
				buttons.push({
					type: 'secondary',
					cssClass: 'uf-button-delete-popup',
					text: UltimateFields.L10N.localize( 'repeater-delete' ).replace( '%s', this.model.get( 'title' ) ),
					icon: 'dashicons-no-alt',
					callback: function() { that.delete(); return true; }
				});
			}

			return buttons;
		},

		save: function( overlay ) {
			var that   = this,
				errors = this.model.validate();

			if( ! errors ) {
				this.model.saveState();
				return true;
			} else {
				var $body = $( '<div />' ), $ul = $( '<ul />' );

				_.each( errors, function( error ) {
					$( '<li />' )
						.appendTo( $ul )
						.html( error );
				});

				$ul.appendTo( $body );

				$body.append( $( '<p />' ).text( UltimateFields.L10N.localize( 'error-corrections' ) ) );

				overlay.alert({
					title: UltimateFields.L10N.localize( 'container-issues-title' ),
					body:  $body.children()
				});
			}
		},

		close: function( e ) {
			this.model.restoreState();
		},

		delete: function() {
			this.model.restoreState();
			this.model.datastore.destroy();
			this.model.destroy();
		},

		remove: function() {
			this.$el.remove();
			this.model.restoreState();
		},

		/**
		 * Attaches the view to an overlay
		 */
		 attachToOverlay: function( overlay ) {
 			var that = this;

 			this.$el.on( 'submit', function( e ) {
 				e.preventDefault();

 				if( that.save( overlay ) ) {
 					overlay.removeScreen();
 				}
 			});
 		}
	});

	// Complex groups
	group.ComplexView = container.Base.View.extend({
		className: 'uf-complex-fields',

		render: function() {
			var that = this,
				tmpl = UltimateFields.template( 'complex-group' );

			this.$el.html( tmpl() );

			this.model.on( 'destroy', function() {
				that.remove();
			});

 			// Add normal fields
 			this.addFields();
		},

		/**
		 * Indicates whether the container supports inline tabs.
		 */
		allowsInlineTabs() {
			return false;
		}
	});


})( jQuery );
