(function( $ ){

	/**
	 * This file handles the term meta container of Ultimate Fields.
	 */
	var container = UltimateFields.Container,
		taxonomy  = container.Taxonomy = {},
		controller;

	/**
	 * A basic model for taxonomy containers.
	 */
 	taxonomy.Model = container.Base.Model.extend({});

	/**
	 * Handles the views for editing terms.
	 */
 	taxonomy.View = UltimateFields.Container.Base.View.extend({
		className: 'uf-taxonomy-elements',
		
		/**
 		 * Initializes the view.
 		 */
 		initialize: function() {
 			var that = this;

			// Start locations
 			this.initializeLocations( UltimateFields.Location.Taxonomy );
 		},

 		/**
 		 * Renders the visible part of the container.
 		 */
 		render: function() {
 			var that  = this,
				templateID, tmpl, fieldsArgs = {};

			// Determine the correct template to use
			templateID = 'taxonomy-' + this.model.get( 'view' );
			if( 'edit' == this.model.get( 'view' ) && 'boxed' == this.model.get( 'style' ) ) {
				templateID += '-boxed';
			}

			tmpl  = UltimateFields.template( templateID );

 			this.$el.html( tmpl( this.model.toJSON() ) );

			// Add fields
			if( 'add' == this.model.get( 'view' ) ) {
				fieldsArgs.wrap = UltimateFields.Field.TermAddWrap;
			}
 			this.addFields( null, fieldsArgs );

 			// Save values when changed and initially
 			this.initializeHiddenField();

 			// Connect to the controller
 			controller.addContainer( this );

			// Adjust element classes
			if( 'seamless' == this.model.get( 'style' ) ) {
				this.$el.find( '.uf-fields' ).eq( 0 ).addClass( 'uf-fields-seamless' );
			} else {
				this.$el.find( '.uf-fields' ).eq( 0 ).addClass( 'uf-boxed-fields' );
			}
 		},

 		/**
 		 * Resets the form.
 		 */
 		reset: function() {
 			this.model.reset();
 			this.render();
 		}
 	});

	/**
 	 * This handles the visibility of the group based on rules and locations.
 	 */
 	UltimateFields.Location.Taxonomy = UltimateFields.Location.extend({
		defaults: {
			visible: true,
			levels: { visible: [], hidden: [] },
			parents: { visible: [], hidden: [] }
		},

		/**
		 * Starts listening for all needed items/rules.
		 */
		listen: function() {
			var that = this;

			this.checked = new Backbone.Model();

			if( this.get( 'levels' ) && ! this.empty( this.get( 'levels' ) ) ) {
				this.listenForLevels();
			}

			if( this.get( 'parents' ) && ! this.empty( this.get( 'parents' ) ) ) {
				this.listenForParents();
			}
		},

		/**
		 * Listens for level changes on hierarchical taxonomies.
		 */
		listenForLevels: function() {
			var that    = this,
				levels  = this.get( 'levels' ),
				$select = $( 'select#parent' ),
				check;

			// Check if there is a selector at all
			if( ! $select.length ) {
				return;
			}

			// Format levels
			levels.visible = levels.visible.map(function( level ) { return parseInt( level ) });
			levels.hidden  = levels.hidden.map(function( level ) { return parseInt( level ) });

			// Does the checks
			check = function() {
				var level;

				$select.find( 'option' ).each(function() {
					var $option = $( this );

					if( ( 'undefined' == typeof level ) && $option.attr( 'value' ) == $select.val() ) {
						level = $option.attr( 'class' );
					}
				});

				level = level || 'level--1';
				level = parseInt( level.replace( 'level-', '' ) ) + 2;

				that.checked.set( 'levels', that.checkSingleValue( level, levels ) );
			}

			// Listen for changes
			$select.change( _.bind( check, this ) );
			check();
		},

		/**
		 * Listens for parent changes on hierarchical taxonomies.
		 */
		listenForParents: function() {
			var that    = this,
				parents = this.get( 'parents' ),
				$select = $( 'select#parent' ),
				check;

			// Check if there is a selector at all
			if( ! $select.length ) {
				return;
			}

			// Format parents
			parents.visible = parents.visible.map(function( parent ) { return parseInt( parent ) });
			parents.hidden  = parents.hidden.map(function( parent ) { return parseInt( parent ) });

			// Does the checks
			check = function() {
				that.checked.set( 'parents', that.checkSingleValue( parseInt( $select.val() ), parents ) );
			}

			// Listen for changes
			$select.change( _.bind( check, this ) );
			check();
		}
	});

 	/**
 	 * This controller will handle, validate and reset all groups.
 	 */
 	taxonomy.Controller = container.Controller.extend({
 		/**
 		 * Binds the controller to the actual form.
 		 */
 		bindToForm: function() {
 			var that = this;

 			if( $( '#edittag' ).length ) {
	 			$( '#edittag' ).on( 'submit', function( e ){
					if( ! that.validate() ) {
						e.preventDefault();
					}
				});
 			} else if ( $( '#addtag' ).length ) {
 				var originalHandler, originalParser;

 				// Replace the form validation handler
 				originalHandler = validateForm;
 				validateForm = function( form ) {
 					var $form  = $( form );

 					return that.validate() && originalHandler.call( wpAjax, $form );
 				}

 				// When the form is submitted properly, reset containers
 				originalParser = wpAjax.parseAjaxResponse;
 				wpAjax.parseAjaxResponse = function() {
 					var result = originalParser.apply( wpAjax, arguments );

 					if( ! result.errors ) {
 						that.reset();
 					}

 					return result;
 				}
 			}
 		},

 		/**
 		 * Resets all added forms.
 		 */
 		reset: function() {
 			_.each( this.containers, function( container ) {
 				container.reset();
 			});

			// Remove error messages
			$( 'h1:eq(0)' ).siblings( '.uf-error' ).remove();
 		},

 		/**
		 * Lists problems with validation.
		 *
		 * @param <Array.String> problems String representation of the problems.
		 */
		showErrorMessage: function( problems ) {
			var $div = this.generateErrorMessage( problems );

			// Remove old messages
			$( 'h1:eq(0)' ).siblings( '.uf-error' ).remove();

			// Add the message
			$( 'h1:eq(0)' ).after( $div );

			// Scroll the body to the top
			$( 'html,body' ).animate({
				scrollTop: 0
			});
		}
 	});

 	// Create a single controller that would be used with all forms
 	controller = new taxonomy.Controller();

 	// Connect the controller to the form
 	$( document ).on( 'uf-init', function() {
 		controller.bindToForm();
 	});

})( jQuery );
