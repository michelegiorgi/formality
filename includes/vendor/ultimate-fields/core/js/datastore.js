(function( $ ){

	var uf = window.UltimateFields;

	/**
	 */
	uf.Datastore = Backbone.Model.extend({
		clone: function() {
			var cloned = Backbone.Model.prototype.clone.apply( this );
			if( this.parent )
				cloned.parent = this.parent;
			return cloned;
		},

		export: function() {
			var data = $.extend( true, {}, this.toJSON() );
			return this.processExportedObject( data );
		},

		processExportedObject: function( object ) {
			var that = this;

			if( Object.prototype.toString.call( object ) === '[object Object]' ) {
				var data = {};

				$.each( object, function( key, value ) {
					if( 0 === key.indexOf( '__' ) ) {
						return;
					}
					
					data[ key ] = that.processExportedObject( value );
				});

				return data;
			} else {
				return object;
			}
		}
	});

	uf.Datastore.Collection = Backbone.Collection.extend({
		model: uf.Datastore,

		comparator: function( datastore ) {
			return datastore.get( '__index' );
		}
	});

})( jQuery );
