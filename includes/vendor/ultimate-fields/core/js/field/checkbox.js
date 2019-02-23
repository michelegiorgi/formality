(function( $ ){

	var uf            = window.UltimateFields,
		field         = uf.Field,
		checkboxField = field.Checkbox = {};

	checkboxField.Model = field.Model.extend({
		/**
		 * If there is no value for the field, but a default one is available,
		 * this method will set the default value as the fields' value.
		 */
		useDefaultValueIfNeeded: function( force ) {
			if( force ) {
				this.setValue( this.get( 'default_value' ) );
			} else {
				// Nothing to do here, checkboxes only work with boolean values,
				// so nothing can be determined at this point.
			}
		}
	});

	/**
	 * Displays the input of the checkbox.
	 */
	checkboxField.View = field.View.extend({
		events: {
			'change input': 'changed'
		},

		/**
		 * Renders the field.
		 */
		render: function() {
			var that = this,
				$input, $label;

			this.$el.addClass( 'uf-checkbox' );

			$input = $( '<input type="checkbox" />' );
			if( this.model.getValue() ) $input.prop( 'checked', 'checked' );
			$label = $( '<label />' ).html( this.model.get( 'text' ) ).prepend( $input );

			if( this.model.get( 'fancy' ) ) {
				this.$el.addClass( 'uf-fancy-checkbox' );
				$input.after( '<span class="uf-fancy-checkbox-wrap wp-ui-highlight">\
					<span class="uf-fancy-checkbox-button" />\
				</span>' );

				$input
					.on( 'focus', function() {
						that.$el.addClass( 'uf-fancy-checkbox-focused' );
					})
					.on( 'blur',  function() {
						that.$el.removeClass( 'uf-fancy-checkbox-focused' );
					});
			}

			this.$el.append( $label );
		},

		/**
		 * Handles changes of the select field.
		 */
		changed: function( e ) {
			this.model.setValue( e.target.checked );
		},

		/**
		 * Brings the focus to the checkbox.
		 */
		focus: function() {
			this.$el.find( 'input' ).focus();
		}
	});

})( jQuery );
