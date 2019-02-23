(function( $ ){

	var uf           = window.UltimateFields,
		field        = uf.Field,
		messageField = field.Message = {};

	/**
	 * A dummy view.
	 */
	messageField.View = field.View.extend({
		render: function() {}
	});

})( jQuery );
