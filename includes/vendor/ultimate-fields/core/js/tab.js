(function( $ ){

	var tab = UltimateFields.Tab = Backbone.View.extend({
		tagName: 'a',
		className: 'uf-tab',

		events: {
			click: 'clicked'
		},

		initialize: function( args ) {
			var that = this;

			this.render();

			that.$el[ that.model.get( 'invalidTab' ) ? 'addClass' : 'removeClass' ]( 'uf-tab-invalid' );
			this.model.on( 'change:invalidTab', function() {
				that.$el[ that.model.get( 'invalidTab' ) ? 'addClass' : 'removeClass' ]( 'uf-tab-invalid' );
			});
		},

		render: function() {
			var that = this;

			this.$el.attr( 'href', '#' );

			// Add the icon
			if( this.model.get( 'icon' ) ) {
				$( '<span class="uf-tab-icon" />' )
					.appendTo( this.$el )
					.addClass( this.model.get( 'icon' ) );
			}

			// Add the text
			$( '<div class="uf-tab-text" />' )
				.appendTo( this.$el )
				.text( this.model.get( 'label' ) );

			// Activate the tab if possible
			if( this.model.get( 'name' ) == this.model.datastore.get( '__tab' ) ) {
				this.$el.addClass( 'uf-tab-active' );
			}

			// Listen to tab changes
			that.toggleActive();
			this.model.datastore.on( 'change:__tab', function() {
				that.toggleActive();
			});

			// Show/hide the tab when neccessary
			this.toggleVisibility();
			this.model.on( 'change:visible', function() {
				that.toggleVisibility();
			});
		},

		clicked: function() {
			if( ! this.model.get( 'visible' ) ) {
				return false;
			}

			this.model.datastore.set( '__tab', this.model.get( 'name' ) );
			this.$el.blur();

			$( document ).trigger( 'uf-tab-changed' );
			UltimateFields.ContainerLayout.DOMUpdated();

			return false;
		},

		toggleActive: function() {
			var method = this.model.datastore.get( '__tab' ) == this.model.get( 'name' )
				? 'addClass'
				: 'removeClass';

			this.$el[ method ]( 'uf-tab-active' );
		},

		toggleVisibility: function() {
			this.$el[ this.model.get( 'visible' ) ? 'removeClass' : 'addClass' ]( 'disabled' );
		}
	});

})( jQuery );
