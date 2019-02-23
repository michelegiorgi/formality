(function( $ ){

	var pagination = UltimateFields.Pagination = {};

	/**
	 * Model, which will be used to interact with the pagination.
	 */
	pagination.Model = Backbone.Model.extend({
		defaults: {
			page:  3,
			max:   30,
			total: 123,
			labels: [ 'Font', 'Fonts' ]
		}
	});

	/**
	 * The view of the pagination.
	 */
	pagination.View = Backbone.View.extend({
		className: 'tablenav uf-pagination',

		events: {
			'click .first-page':     'goToFirst',
			'click .prev-page':      'goToPrev',
			'click .next-page':      'goToNext',
			'click .last-page':      'goToLast',
			'keydown .current-page': 'keyDown'
		},

		/**
		 * Renders the view.
		 */
		render: function() {
			var that = this,
				tmpl = UltimateFields.template( 'pagination' );

			this.$el.html( tmpl( this.model.toJSON() ) );
			this.update();

			this.model.on( 'change', _.bind( this.update, this ) );
		},

		/**
		 * Updates all numbers on model changes.
		 */
		update: function() {
			var that = this,
				m    = this.model,
				totalText;

			// Update texts
			totalText = m.get( 'total' ) + ' ' + m.get( 'labels' )[ m.get( 'count' ) === 1 ? 0 : 1 ];
			this.$el.find( '.displaying-num' ).text( totalText );
			this.$el.find( '.current-page' ).val( m.get( 'page' ) );
			this.$el.find( '.total-pages' ).text( m.get( 'max' ) );

			// Toggle enabled/disabled classes
			this.$el.find( '.first-page' )[ 1 == m.get( 'page' ) ? 'addClass' : 'removeClass' ]( 'disabled' );
			this.$el.find( '.prev-page' )[ 1 == m.get( 'page' ) ? 'addClass' : 'removeClass' ]( 'disabled' );
			this.$el.find( '.next-page' )[ m.get( 'page' ) < m.get( 'max' ) ? 'addClass' : 'removeClass' ]( 'disabled' );
			this.$el.find( '.last-page' )[ m.get( 'page' ) < m.get( 'max' ) ? 'addClass' : 'removeClass' ]( 'disabled' );
		},

		goToFirst: function( e ) {
			e.preventDefault();
			this.model.set( 'page', 1 );
		},

		goToPrev: function( e ) {
			e.preventDefault();
			this.model.set( 'page', Math.max( 1, this.model.get( 'page' ) - 1 ) );
		},

		goToNext: function( e ) {
			e.preventDefault();
			this.model.set( 'page', Math.min( this.model.get( 'max' ), this.model.get( 'page' ) + 1 ) );
		},

		goToLast: function( e ) {
			e.preventDefault();
			this.model.set( 'page', this.model.get( 'max' ) );
		},

		keyDown: function( e ) {
			var page;

			if( 13 != e.keyCode )
				return;

			e.preventDefault();

			page = parseInt( this.$el.find( '.current-page' ).val() );
			page = Math.max( 1, page );
			page = Math.min( page, this.model.get( 'max' ) );
			this.model.set( 'page', page );
		}
	});

})( jQuery );