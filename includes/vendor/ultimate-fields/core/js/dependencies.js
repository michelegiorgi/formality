(function( $ ){

	var uf         = window.UltimateFields,
		dependency = {};

	// Export the handle
	uf.Dependency = dependency;

	// Create the simple rule
	dependency.Rule = Backbone.Model.extend({
		defaults: {
			valid: false
		},

		/**
		 * Set the datastore of the rule.
		 */
		setDatastore: function( datastore ) {
			var that = this;

			this.datastore = this.locateDatastore( datastore );

			// Once the datastore is set, save the field within the datastore
			this.field = this.get( 'field' ).replace( /\.\.\//g, '' );

			// Check the dependency
			this.check();

			// Add a listener
			this.datastore.on( 'change:' + this.field, function() {
				that.check();
			});
		},

		/**
		 * Checks the datastore
		 */
		check: function() {
			var value  = this.datastore.get( this.field ),
				needed = this.get( 'value' ),
				valid  = true;

			if( ( 'object' == typeof value ) && -1 != [ '>=', '<=', '>', '<', '=', '==' ].indexOf( this.get( 'compare' ) ) ) {
				value = value.length;
			} else if( 'undefined' == typeof value ) {
				value = false;
			}

			switch( this.get( 'compare' ).toUpperCase() ) {
				case '>=':
					if( ( typeof( needed ) == 'number' ) || ( typeof( needed ) == 'string' ) ) {
						valid = parseFloat( value ) >= parseFloat( needed );
					} else {
						valid = value.length >= parseInt( needed );
					}
					break;

				case '<=':
					if( ( typeof( needed ) == 'number' ) || ( typeof( needed ) == 'string' ) ) {
						valid = parseFloat( value ) <= parseFloat( needed );
					} else {
						valid = value.length <= parseInt( needed );
					}
					break;

				case '<':
					if( ( typeof( needed ) == 'number' ) || ( typeof( needed ) == 'string' ) ) {
						valid = parseFloat( value ) < parseFloat( needed );
					} else {
						valid = value.length < parseInt( needed );
					}
					break;

				case '>':
					if( ( typeof( needed ) == 'number' ) || ( typeof( needed ) == 'string' ) ) {
						valid = parseFloat( value ) > parseFloat( needed );
					} else {
						valid = value.length > parseInt( needed );
					}
					break;

				case '!=':
					valid = value != needed;
					break;

				case 'NOT_NULL':
					valid = ( value == false || ! value || ( ( 'string' == typeof value ) && 'false' === value ) ) ? false : true;
					break;

				case 'NULL':
					if( 'object' == typeof value ) {
						valid = 0 === value.length;
					} else {
						valid = !value;
					}
					break;

				case 'IN':
					if( ! value ) {
						valid = false;
					} else {
						if( value.indexOf( ',' ) != -1 ) {
							var i, parts = value.split( ',' );
							valid = false;
							for( i in parts ) {
								if( needed.indexOf( parts[i] ) != -1 )
									valid = true;
							}
						} else {
							valid = needed.indexOf( value ) != -1;
						}
					}
					break;

				case 'NOT_IN':
					if( value.indexOf( ',' ) != -1 ) {
						var i, parts = value.split( ',' );
						valid = false;
						for( i in parts ) {
							if( needed.indexOf( parts[i] ) != -1 )
								valid = true;
						}
					} else {
						valid = needed.indexOf( value ) == -1;
					}
					break;

				case 'CONTAINS':
					if( 'object' != typeof needed ) {
						valid = value && ( 'object' == typeof value ) && -1 != value.indexOf( needed );
					} else {
						var i;
						valid = false;

						_.each( value, function( subValue ) {
							if( _.contains( needed, subValue ) ) {
								valid = true;
							}
						});
					}
					break;

				case 'DOES_NOT_CONTAIN':
					if( 'object' != typeof needed ) {
						valid = ! value || ( 'object' != typeof value ) || -1 == value.indexOf( needed );
					} else {
						var i;
						valid = true;

						_.each( value, function( subValue ) {
							if( _.contains( needed, subValue ) ) {
								valid = false;
							}
						});
					}

					break;

				case 'CONTAINS_GROUP':
					valid = false;
					if( 'object' == typeof value ) _.each( value, function( group ) {
						if( ( 'object' == typeof group ) && ( '__type' in group ) && needed == group.__type ) {
							valid = true;
						} else if( ( 'object' == typeof group ) && group[ 0 ] && ( '__type' in group[ 0 ] ) ) {
							_.each( group, function( row ) {
								if( needed == row.__type ) {
									valid = true;
								}
							})
						}
					});
					break;

				case 'DOES_NOT_CONTAIN_GROUP':
				valid = true;
					if( 'object' == typeof value ) _.each( value, function( group ) {
						if( ( 'object' == typeof group ) && ( '__type' in group ) && needed == group.__type ) {
							valid = false;
						} else if( ( 'object' == typeof group ) && group[ 0 ] && ( '__type' in group[ 0 ] ) ) {
							_.each( group, function( row ) {
								if( needed == row.__type ) {
									valid = false;
								}
							})
						}
					});
					break;

				default:
				case '=':
				case '==':
					if( ( 'string' == typeof needed ) && ( 'object' == typeof value ) && 'length' in value ) {
						valid = value.indexOf( needed ) != -1;
					} else {
						valid = value == needed;
					}
					break;
			}

			this.set( 'valid', valid );
		},

		/**
		 * Locates the datastore for a the current rule.
		 */
		locateDatastore: function( datastore ) {
			var that  = this,
				up    = 0,
				field = this.get( 'field' );

			// Check how many levels up we need
			while( 0 === field.indexOf( '../' ) ) {
				up++;
				field = field.replace( '../', '' );
			}

			// Go and find the datastore
			while( up-- ) {
				if( datastore.parent ) {
					datastore = datastore.parent;
				} else {
					return datastore;
				}
			}

			return datastore;
		}
	});

	// Create a model for rules
	dependency.Rules = Backbone.Collection.extend({
		model: dependency.Rule
	});

	// Create a group of dependencies
	dependency.Group = Backbone.Model.extend({
		defaults: {
			valid: true
		},

		initialize: function( args ) {
			var that = this;

			this.rules = new dependency.Rules( args.rules );

			// Set the datastore to rules in order to force them to initialize
			this.rules.each(function( rule ) {
				rule.setDatastore( that.get( 'datastore' ) );

				rule.on( 'change:valid', function() {
					that.check();
				});
			});

			// Perform an initial check
			this.check();
		},

		/**
		 * Checks the status.
		 */
		check: function() {
			var valid = true;

			this.rules.each(function( rule ) {
				if( ! rule.get( 'valid' ) ) {
					valid = false;
				}
			});

			this.set( 'valid', valid );
		}
	});


})( jQuery );
