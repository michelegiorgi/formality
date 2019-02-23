(function( $ ){

	var uf            = window.UltimateFields,
		field         = uf.Field,
		textareaField = field.Textarea = {};

	textareaField.Model = field.Model.extend({
		/**
		 * Returns a value for Yoast SEO.
		 */
		getSEOValue: function() {
			return this.getValue();
		}
	});

	textareaField.View = field.View.extend({
		events: {
			'change textarea': 'change'
		},

		/**
		 * Renders the input of the field.
		 */
		render: function() {
			var $input;
			
			$input = $( '<textarea />' )
				.attr( 'rows', this.model.get( 'rows' ) )
				.val( this.model.getValue() )
				.appendTo( this.$el );
		},

		/**
		 * Saves the value of the field when it gets changed.
		 */
		change: function() {
			var value  = this.model.getValue(),
				$input = this.$el.find( 'textarea' );

			if( value != $input.val() ) {
				this.model.setValue( $input.val() );
			}
		},

		/**
		 * Focuses the input within the field.
		 */
		focus: function() {
			this.$el.find( 'textarea' ).focus();
		}
	});

})( jQuery );
