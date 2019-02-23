(function( $ ){

	/**
	 * This file handles the Group container of Ultimate Fields that is used for repeaters.
	 */
	var container   = UltimateFields.Container,
		group       = container.Group,
		layoutGroup = container.Layout_Group = {};

	/**
	 * Extend the model of the group for layout groups.
	 */
	layoutGroup.Model = group.Model.extend({
		defaults: _.extend( {}, group.Model.prototype.defaults, {
			displayed_index: 1
		})
	});

	/**
	 * This is the inline group for the layout field.
	 */
	layoutGroup.View = group.View.extend({
		className: 'uf-group uf-layout-group',
		
		/**
		 * Renders the normal element of the group.
		 */
		render: function() {
			var that = this,
				tmpl = UltimateFields.template( 'layout-group' );

			this.$el.html( tmpl({
				title:      this.model.get( 'title' ),
				type:       this.model.get( 'type' ),
				icon:       this.model.get( 'icon' ),
				edit_mode:  this.model.get( 'edit_mode' ),
				number:     this.model.get( 'displayed_index' )
			}));

			// Add the necessary style-settings
			this.addStyles();

			// Bind control clicks
			this.bindClicks();

			// Destroy the view when the element is deleted
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

			// When the index is changed, use it
			this.model.on( 'change:displayed_index', function() {
				that.$el.find( '.uf-group-number-inside' ).text( that.model.get( 'displayed_index' ) );
			})
		},

		/**
		 * Binds the group to a layout element.
		 */
		bindToElement: function( element ) {
			var that       = this,
				$indicator = this.$el.find( '.uf-layout-group-width' );

			$indicator.text( element.width );

			element.on( 'resized', function() {
				$indicator.text( element.width );

				// Save the width, as this is the real size of the element
				that.model.datastore.set( '__width', element.width );
			});

			// Update the display for temporary sizes
			element.on( 'temporary-size', function( size ) {
				$indicator.text( size );
			});

			// Update positions
			element.on( 'update-attributes', function( attributes ) {
				that.model.datastore.set( '__width', element.width, {
					silent: true
				});

				that.model.set({
					row:   attributes.row,
					index: attributes.index
				}, {
					silent: true
				});
			});
		}
	});


})( jQuery );
