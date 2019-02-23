/**
 * Displays pointers on the edit screen
 */
(function( $, UI, window, document ) {

	UI.Pointers = function( pointers ) {
		var that = this;

		// This will hold added pointers
		this.pointers  = [];
		this.displayed = [];
		this.unblocked = [ pointers[ 0 ].selector ];
		this.disabled  = false;
		this.autoClose = false;

		_.each( pointers, function( pointer ){
			that.pointers.push( pointer );
			that.preparePointer( pointer );
		});

		// Show the first pointer
		that.nextPointer();
	}

	_.extend( UI.Pointers.prototype, {
		/**
		 * Connects to the right event for a pointer.
		 */
		preparePointer: function( pointer ) {
			var that   = this,
				unhook = pointer.unhook.split( ' ' );

			if( ! pointer.hook ) {
				that.unblocked.push( pointer.selector );
			} else {
				$( document ).on( pointer.hook, function(){
					// Mark the pointer as displayable
					that.unblocked.push( pointer.selector );
					that.nextPointer();
				});
			}

			// This will hide the pointer
			if( 2 > unhook.length ) {
				unhook[ 1 ] = pointer.selector;
			}

			var dismissed = false;
			$( document ).on( unhook[ 0 ], unhook[ 1 ], function( e ){
				if( dismissed )
					return;

					console.log(unhook[0], unhook[1], pointer);
				// Make sure that only actual strokes are registered for keyup
				if( 'keyup' == unhook[ 0 ] && 9 == e.which ) {
					return;
				}

				dismissed = 1;

				that.autoClose = true;

				try {
					pointer.$pointer.pointer( 'close' );
				} catch( e ) {
					that.displayed.push( pointer.selector );
					that.nextPointer();
					return;
				}

				setTimeout(function(){
					that.autoClose = false;
				}, 50);
				that.displayed.push( pointer.selector );

				// Load the next pointer
				that.nextPointer();
			});
		},

		/**
		 * Displays the next available pointer.
		 */
		nextPointer: function() {
			var that  = this,
				found = false,
				theOne;

			// Don't process pointers if disabled
			if( this.disabled ) {
				return;
			}

			_.each( that.pointers, function( pointer ) {
				if( found )
					return;

				if( that.displayed.indexOf( pointer.selector ) != -1 )
					return;

				theOne = pointer;
				found = true;
			});

			if( theOne && that.unblocked.indexOf( theOne.selector ) != -1 ) {
				that.displayPointer( theOne );
			}
		},

		/**
		 * Displays a certain pointer when the moment is right.
		 */
		displayPointer: function( pointer ) {
			var that = this;

			// Create the pointer and open it
			pointer.$pointer = $( pointer.selector );
			pointer.$pointer.pointer( $.extend( pointer.options, {
				close: function( e ) {
					if( ! that.autoClose ) {
						that.disabled = true;
					}
				}
			}));

			// Scroll to the right point
			if( pointer.scroll != -1 ) setTimeout(function(){
				// Scroll to the pointer first
				$( 'html,body' ).animate({
					scrollTop: pointer.scroll
				});
			}, 850);

			// Wait a little if needed
			if( pointer.timeout ) {
				setTimeout(function(){
					// Display the pointer
					pointer.$pointer.pointer( 'open' );
				} , pointer.timeout);
			} else {
				pointer.$pointer.pointer( 'open' );
			}
		}
	});

	/**
	 * Initialize the object.
	 */
	var pointers = new UI.Pointers( uf_pointers );

})( jQuery, UltimateFields.UI, window, document );
