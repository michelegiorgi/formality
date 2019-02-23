(function( $ ) {

	var layout = window.ufLayout = {};

	/**
	 * Contains helpers for mouse position.
	 */
	layout.mousePosition = {
	    /**
	     * Retrieves mouse mosition for a specific event.
	     */
	    getMousePosition: function( event ) {
			return {
				x: event.pageX,
				y: event.pageY
			};
	    },

		/**
		 * Disables the selection of the root element.
		 *
		 * Otherwise while dragging, text will be selected.
		 */
		disableSelection: function() {
	        this.$el
	        	.attr( 'unselectable', 'on' )
	        	.css( 'user-select', 'none' )
	        	.on( 'selectstart', false )
				.on( 'dragstart', false );
	    },

		/**
		* Disableds the selection of the whole body.
		*/
		disableBodySelection: function() {
			$( 'body' )
				.attr( 'unselectable', 'on' )
				.css( 'user-select', 'none' )
				.on( 'selectstart.uf-layout', false );
		},

		/**
		* Enables the selection of the whole body.
		*/
		enableBodySelection: function() {
			$( 'body' )
				.attr( 'unselectable', false )
				.css( 'user-select', false )
				.off( 'selectstart.uf-layout' );
		}
	}

	/**
	 * Create a basic events object, which can e added to the prototype of any other class.
	 */
	layout.Eventful = {
		on: function( name, callback ) {
			this.events         = this.events || {};
			this.events[ name ] = this.events[ name ] || [];
			this.events[ name ].push( callback );
		},

		off: function( name ) {
			if( name in this.events ) {
				delete this.events[ name ];
			}
		},

		trigger: function( name ) {
			var args;

			if( ! this.events || ! this.events[ name ] )
				return;

			args = [];
			$.each( arguments, function( index, arg ) {
				args.push( arg );
			});

			args.shift(); // Remove the name

			$.each( this.events[ name ], function( i, callback ){
				callback.apply( null, args );
			});
		},

		bind: function( method, element ) {
			return function() {
				method.apply( element, arguments );
			};
		}
	};

	/**
	 * This is a class, which represents elements/blocks.
	 */
	layout.Element = function( args ) {
		var that = this, $resizer;

		if( 'string' == typeof args.el ) {
			this.$el = $( '<div />' )
				.addClass( args.el )
				.data( 'width', args.width )
				.data( 'type', args.type.id );
		} else {
			this.$el = args.el;
		}
				
		// Save a reverse handle
		this.$el.data( 'element', this );

		// Save the basic properties
		this.type  = args.type;
		this.width = args.width || this.$el.data( 'width' );
		this.free  = 0;

		this.$el.on( 'click', '.uf-layout-element-remove', function( e ) {
			e.preventDefault();
			that.$el.remove();
			that.trigger( 'destroy' );
		});

		// Make sure external plugins know what happened
		this.$el.trigger( 'uf-layout-added', this );
    
		// Add final elements if possible
		this.addFinalElements();
	}

	// Add methods to elements
	$.extend( layout.Element.prototype, layout.mousePosition, layout.Eventful, {
		/**
		 * When the group is finally in place, add all elements.
		 */
		addFinalElements: function() {
			if( this.elementsAdded ) {
				return;
			}

			// Check if the group header is already in place
			if( ! this.$el.find( '.uf-group-header' ).length ) {
				return;
			}

			// Add the resizer
			$resizer = $( '<span class="uf-layout-resize"></span>' )
				.append( '<del class="wp-ui-highlight" />' )
				.append( '<del class="wp-ui-highlight" />' )
				.append( '<del class="wp-ui-highlight" />' );

			this.$el.find( '.uf-group-header' ).append( $resizer );

			// Initialize the resizing script
			this.initializeResizer();

			this.elementsAdded = true;
		},

		/**
		 * Adjust the elements size to a certain number of columns.
		 */
		adjustToColumns: function( columns, animate ) {
			var prop,
				that = this,
				$el  = this.$el,
				pixelWidth,
				percentageWidth;

			// Check the width
			width = Math.min( this.type.max, Math.max( this.type.min, this.width ) );

			// Prepare the CSS
			pixelWidth = {
				width: ( width / columns ) * $el.closest( '.uf-layout-row-groups' ).width() + 'px'
			};
			percentageWidth = {
				width: ( 100 / columns ) * width + '%'
			};

			if( animate ) {
				$el.animate( pixelWidth, 200, function() {
					$el.css( percentageWidth );
					that.trigger( 'resized' );
				});
			} else {
				$el.css( percentageWidth );
			}
		},

		/**
		 * Initializes the resizer of the element.
		 */
		initializeResizer: function() {
			var that = this;

			this.$el.on( 'mousedown', function( e ) {
				that.startResizing( e );
			});

			this.disableSelection();
		},

		/**
		 * Starts the process of resizing.
		 */
		startResizing: function( e ) {
			var that = this,
				$target;

			$target = $( e.target );
			if( ! $target.closest( '.uf-layout-resize' ).length ) {
				// Not resizing, but moving. Bubble the event to up layout
				this.trigger( 'draggingStarted', e );
				return;
			}

			this.disableBodySelection();

			this.start   = this.getMousePosition( e );
			this.down    = false;
			this.started = false;
			this.$el.addClass( 'uf-layout-element-resizing' );

			$( document ).on( 'mousemove.uf-element-resize', this.mouseMoved.bind( this ) );
			$( document ).on( 'mouseup.uf-element-resize', this.stop.bind( this ) );
		},

		/**
		 * Handles mouse movement.
		 */
		mouseMoved: function( e ) {
			var that = this, width;

			// Check if the mouse has been moved
			this.now = this.getMousePosition( e );

			// Check if resizing has started at all
			if( ! this.started ) {
				var h = Math.abs( this.now.x - this.start.x ),
					v = Math.abs( this.now.y - this.start.y );

				if( h > 3 || v > 3 ) {
					var colWidth;

					this.started = true;

					// Get/prepare dimenstions
					this.pixelWidth = this.$el.outerWidth();
					colWidth = this.pixelWidth / this.width;
					this.minWidth   = this.type.min * colWidth;
					this.maxWidth = Math.min( this.type.max, this.free + this.width ) * colWidth;
				} else {
					// Nothing to do yet
					return;
				}
			}

			// Change widths
			width = this.pixelWidth + this.now.x - this.start.x;
			this.$el.css({
				width: Math.floor( Math.min( this.maxWidth, Math.max( this.minWidth, width ) ) )
			});

			// Trigger a temporary width
			this.trigger( 'temporary-size', Math.round( ( this.$el.outerWidth() / this.$el.parent().width() ) * this.columns ) );
		},

		/**
		 * Stops the mouse movement.
		 */
		stop: function( e ) {
			var that = this, width;

			// Unbind events
			$( document ).off( 'mousemove.uf-element-resize' );
			$( document ).off( 'mouseup.uf-element-resize' );
			this.$el.removeClass( 'uf-layout-element-resizing' );

			// Calculate the new width
			width = ( this.$el.outerWidth() / this.$el.parent().width() ) * this.columns;
			width = Math.round( width );

			// Adjust the width
			this.width = width;
			this.adjustToColumns( this.columns, true );

			// Reset
			this.down = false;
			this.started = false;

			// Set a small timeout to let adjustments start
			this.trigger( 'resized' );

			this.enableBodySelection();
		},

		/**
		 * Converts the row to JSON, allowing easier debugging.
		 */
		toJSON: function() {
			return {
				type:  this.type,
				width: this.width
			};
		},

		/**
		 * Propagates the size and location of the element.
		 */
		setAttributes: function( attributes ) {
			this.trigger( 'update-attributes', attributes );
		}
	});

	/**
	 * This is a class, which represents rows.
	 */
	layout.Row = function( args ) {
		var that = this;

		this.$el = 'string' == typeof args.el
			? $( '<div />' ).addClass( args.el )
			: args.el;
		this.$groups = this.$el.find( '.uf-layout-row-groups' );

		this.elements = [];
		this.columns = args.columns;
		this.used = 0;

		this.$el.on( 'click', '.uf-layout-row-remove', function( e ) {
			e.preventDefault();

			that.$el.fadeOut( 200, function() {
				that.$el.remove();
			});

			that.trigger( 'destroy' );
		});

		this.$el.hover(function() {
			that.$el.addClass( 'wp-ui-highlight' );
		}, function() {
			that.$el.removeClass( 'wp-ui-highlight' );
		});

	}

	// Add methods to rows
	$.extend( layout.Row.prototype, layout.Eventful, {
		/**
		 * Adds an element to the row.
		 */
		addElement: function( element ) {
			var that = this;

			// Add locally, set simple properties
			this.elements.push( element );
			element.columns = this.columns;
			this.used += element.width;

			// When resized, recalculate
			element.on( 'resized', function() {
				that.calculateSpace();
				that.trigger( 'element-resized' )
			});

			// When destroyed, remove
			element.on( 'destroy', function() {
				that.elements.splice( that.elements.indexOf( element ), 1 );

				// Remove the row or recalculate
				if( ! that.elements.length ) {
					that.destroy();
				} else {
					that.calculateSpace();
				}

				that.trigger( 'element-resized' )
			});

			// Bubble up the event when moving
			element.on( 'draggingStarted', function( e ) {
				that.trigger( 'elementDraggingStarted', e, element );
			});

			// Propagate the current free space
			this.propagateFreeSpace();
		},

		/**
		 * Detaches an element from the view.
		 */
		detachElement: function( element ) {
			this.elements.splice( this.elements.indexOf( element ), 1 );
			this.calculateSpace();
			element.off( 'resized' );
			element.off( 'destroy' );
			element.off( 'draggingStarted' );
		},

		/**
		 * Check if the row can accept a new element.
		 */
		accepts: function( args ) {
			var $element   = args.$helper,
				mouse      = args.mouse,
				type       = args.type,
				thisTop    = this.$el.offset().top,
				thisHeight = this.$el.height(),
				fits, enoughFree, min;

			// Use either the minimum or current size of the helper
			min = args.$helper.data( 'element' )
				? args.$helper.data( 'element' ).width
				: type.min;

			// Checks if the elements fits vertically
			fits = thisTop <= mouse.y && ( thisTop + thisHeight ) >= mouse.y;

			// Check if there is space
			enoughFree = this.columns - this.used >= min;

			if( fits ) {
				if( enoughFree ) {
					this.$el.addClass( 'uf-layout-row-waiting wp-ui-highlight' );
					return true;
				} else {
					this.$el.addClass( 'uf-layout-row-full' );
					return false;
				}
			}

			this.$el.removeClass( 'uf-layout-row-full' );

			return false;
		},

		/**
		 * Clears the classes of the row and the placeholders inside.
		 */
		cleanup: function() {
			this.$el.removeClass( 'uf-layout-row-waiting uf-layout-row-full' );
			this.removePlaceholder();
			this.calculateSpace();

			// If the row is empty, just remove it
			if( ! this.used ) {
				this.destroy();
			} else {
				// Sort the internal element collection
				this.elements.sort(function( a, b ) {
					return a.$el.index() > b.$el.index() ? 1 : -1;
				});
			}
		},

		/**
		 * Creates a placeholder.
		 */
		addPlaceholder: function( args ) {
			var that  = this,
				mouse = args.mouse,
				type  = args.type,
				max,
				element;

			max = type.max;
			if( args.$helper.data( 'element' ) ) {
				max = args.$helper.data( 'element' ).width;
			}

			this.placeholder = new layout.Element({
				el:   'uf-layout-element uf-layout-element-placeholder',
				width: Math.min( this.columns - this.used, max ),
				type:  type
			});

			if( args.$helper.data( 'element' ) ) {
				this.placeholderElement = args.$helper.data( 'element' );
			} else {
				this.placeholderElement = false;
			}

			this.placeholder.$el.appendTo( this.$groups );
			this.placeholder.adjustToColumns( this.columns );
			this.repositionPlaceholder( args );
		},

		/**
		 * Removes the palceholder, if any.
		 */
		removePlaceholder: function() {
			if( this.placeholder ) {
				this.placeholder.$el.remove();
				this.placeholder = false;
			}

			this.$el.removeClass( 'uf-layout-row-waiting wp-ui-highlight' );
		},

		/**
		 * Repositions the placeholder within the row.
		 */
		repositionPlaceholder: function( args ) {
			var that   = this,
				placed = false;

			this.elements.forEach(function( element ) {
				if( ! placed && args.mouse.x < element.$el.offset().left + element.$el.width() / 2 ) {
					element.$el.before( that.placeholder.$el );
					placed = true;
				}
			});

			if( ! placed ) {
				this.placeholder.$el.appendTo( this.$groups );
				placed = true;
			}
		},

		/**
		 * Populates the placeholder with a real element.
		 */
		populatePlaceholder: function( args ) {
			var that = this,
				element;

			// Create the element
			if( this.placeholderElement ) {
				element = this.placeholderElement;
			} else {
				element = new layout.Element({
					el:     args.className,
					width:  this.placeholder.width,
					type:   this.placeholder.type
				});

				element.adjustToColumns( this.columns );
				args.populateElement( element );
				element.addFinalElements();
			}

			// Replace and remove the placeholder
			this.placeholder.$el.replaceWith( element.$el );
			this.placeholder = false;

			// Adjust the actual row
			this.$el.removeClass( 'uf-layout-empty-row' );

			// Add to the collection
			this.addElement( element );
		},

		/**
		 * Calculates how much space is used and how much is free
		 */
		calculateSpace: function() {
			var that = this;

			this.used = 0;

			this.elements.forEach( function( element ) {
				that.used += element.width;
			});

			this.propagateFreeSpace();

			this.$el.attr( 'data-used', this.used );

			return this.used;
		},

		/**
		 * Propagates the amound of used/free space to elements.
		 */
		propagateFreeSpace: function() {
			var free = this.columns - this.used;

			this.elements.forEach(function( element ) {
				element.free = free;
			});
		},

		/**
		 * Destroys the row.
		 */
		destroy: function() {
			this.$el.remove();
			this.trigger( 'destroy' );
		},

		/**
		 * Converts the row to JSON, allowing easier debugging.
		 */
		toJSON: function() {
			return {
				used:     this.used,
				columns:  this.columns,
				elements: this.elements
			};
		}
	});

	/**
	 * The main class.
	 */
	layout.Core = function( $el, args ) {
		this.$el = $( $el );

		this.args = $.extend( {
			columns: 12,
			types:   {},
			distance: 3, // Amount of pixels before dragging starts

			// CSS classes for the various elements
			body:      'uf-layout-content',
			row:       'uf-layout-row',
			emptyRow:  'uf-layout-empty-row',
			element:   'uf-layout-element',
			prototype: 'uf-layout-element-prototype'
		}, args );

		this.initialize();
	}

	$.extend( layout.Core.prototype, layout.mousePosition, {
		/**
		 * Initializes the layout.
		 */
		initialize: function() {
			var that = this;

			this.$body = this.find( 'body' );

			this.down = false;           // For mousedown
			this.start = { x: 0, y: 0 }; // Starting mouse position, will be changed
			this.now   = { x: 0, y: 0 }; // Current mouse position, will be calculated for each pixel.
			this.where = { x: 0, y: 0 }; // The point wher ethe mouse start dragging the current helper

			this.rows = []; // Holds all added rows

			this.addStyles();
			this.disableSelection();
			this.addEmptyRow();

			this.find( 'prototype' ).each(function() {
				var $element = $( this );
				that.initializePrototype( $element );
			});

			this.$body.sortable({
				items: '> .uf-layout-row:not(.uf-layout-empty-row)',
				handle: '.uf-layout-handle',
				axis: 'y',
				stop: _.bind( this.save, this )
			});

			// Once everything is in place, let external elements know what's where
			this.save();
		},

		/**
		 * Finds all elements within the root element.
		 */
		find: function( selector, $container ) {
			if( selector in this.args ) {
				selector = '.' + this.args[ selector ];
			}

			return ( $container || this.$el ).find( selector );
		},

		/**
		 * Returns the settings for a data type.
		 */
		getTypeSettings: function( id ) {
			var type;

			this.args.types.forEach(function( available ) {
				if( id == available.id ) {
					type = $.extend( {}, available );
					return false;
				}
			});

			if( ! type.min ) {
				type.min = 1;
			}

			if( ! type.max ) {
				type.max = this.args.columns;
			}

			return type;
		},

		/**
		 * Sets a row up.
		 */
		setupRow: function( data, $el ) {
			var that = this, row;

			// Prepare the arguments for the row
			data = data || {};
			data.columns = this.args.columns;

			// This object will represent the row
			row = new layout.Row( data );
			that.rows.push( row );

			// Add listeners
			row.on( 'destroy', function() {
				that.rows.splice( that.rows.indexOf( row ), 1 );
			});

			row.on( 'elementDraggingStarted', function( e, element ) {
				that.elementDown( e, element, row );
			});

			row.on( 'element-resized', function() {
				that.save();
			});

			this.$el.trigger( 'uf-setup-row', row );

			return row;
		},

		/**
		 * Adds the initial styles to elements.
		 */
		addStyles: function() {
			var that = this;

			// Go through each row and work with the elements inside
			this.find( 'row', this.$body ).each(function() {
				var $row        = $( this ),
					rowElements = [],
					row;

				// This object will represent the row
				row = that.setupRow({
					el: $row
				});

				that.find( 'element', $row ).each(function() {
					var $element = $( this ), element;

					// Create an object
					element = new layout.Element({
						el:   $element,
						type: that.getTypeSettings( $element.data( 'type' ) )
					});

					// Adjust the widths
					element.adjustToColumns( that.args.columns );

					// Add to the row
					row.addElement( element );
				});
			});
		},

		/**
		 * Adds an empty row at the end of the body if there isnt one.
		 */
		addEmptyRow: function() {
			var that = this, row;

			// If there is an existing row, don't add a new one
			if( this.find( 'emptyRow' ).length ) {
				return;
			}

			// Create the row
			this.emptyRow = row = this.setupRow({
				el: this.args.row + ' ' + this.args.emptyRow
			});

			// Add at the end of the body
			this.$body.append( row.$el );

			// Allow plugins to setup the appearance of the row
			this.args.placeholderRow( row.$el, row );
		},

		/**
		 * Initializes a prototype.
		 */
		initializePrototype: function( $prototype ) {
			var that = this;

			$prototype.on( 'mousedown', function( e ) {
				that.prototypeDown( e, $prototype );
			});

			$prototype.on( 'click', function( e ) {
				that.prototypeClicked( e, $prototype );
			});
		},

		/**
		 * Start the process of dragging an element.
		 */
		elementDown: function( e, element, row ) {
			var that = this, pos = {};

			// Save properties to the HTML element
			element.$el.data({
				element: element,
				width:   element.width
			});

			// Indicate the current interaction for other methods
			this.sourceRow = row;
			this.sourceElement = element;

			// Mark down the start
			this.start = this.getMousePosition( e );
			this.where = {
				x: this.start.x - ( element.$el.offset().left - this.$el.offset().left ),
				y: this.start.y - ( element.$el.offset().top  - this.$el.offset().top )
			}

			// Let the rest work
			that.somethingDown( element.$el );
		},

		/**
		 * Starts the process of dragging a prototype.
		 */
		prototypeDown: function( e, $prototype ) {
			var that = this;

			// Indicate that there is no existing element
			this.sourceRow = false;

			// Mark down the start
			this.start = this.getMousePosition( e );
			this.where = {
				x: this.start.x - ( $prototype.offset().left - this.$el.offset().left ),
				y: this.start.y - ( $prototype.offset().top  - this.$el.offset().top )
			}

			this.somethingDown( $prototype );
		},

		/**
		 * When a prototype is clicked, it should be added on a new row.
		 */
		prototypeClicked: function( e, $prototype ) {
			var that = this, $row, row, element, type;

			e.preventDefault();

			// Initialize the row
			row = this.emptyRow;

			// Create the element
			type = that.getTypeSettings( $prototype.data( 'type' ) );
			element = new layout.Element({
				el:     'uf-layout-element',
				width:  Math.min( type.max, this.args.columns ),
				type:   type
			});

			// Add the element to the row and adjust things
			element.$el.appendTo( row.$groups );
			element.adjustToColumns( row.columns );
			this.args.populateElement( element );
			element.addFinalElements();

			// Clean up the row
			row.$el.removeClass( 'uf-layout-empty-row' );

			// Add to the collection
			row.addElement( element );

			// Finally, add another empty row
			this.addEmptyRow();

			// Save
			this.save();
		},

		/**
		 * Handles the mousedown on any element.
		 */
		somethingDown: function( $item ) {
			var that      = this,
				$document = $( document );

			// Start listening for mouse movement
			$document.on( 'mousemove.uf-layout', function( e ) {
				that.mouseMoved( e, $item );
			});

			// Create a callback for when moving stops
			$document.on( 'mouseup.uf-layout', function( e ) {
				$document.off( 'mousemove.uf-layout' );
				$document.off( 'mouseup.uf-layout' );

				this.mouseUp( e );
			}.bind( this ) );
		},

		/**
		 * Handles each move of the mouse.
		 */
		mouseMoved: function( e, $item ) {
			var that = this, accepts;

			// Save the position
			this.now = this.getMousePosition( e );

			// Check if the dragging has already started
			if( ! this.down ) {
				var x = Math.abs( this.start.x - this.now.x ),
					y = Math.abs( this.start.y - this.now.y );

				if( x < this.args.distance && y < this.args.distance ) {
					return;
				}

				this.down = true;
				this.startDragging( $item );
			}

			// Start by repositioning the helper in order to follow mouse movement
			this.$helper.css({
				top:  this.now.y - this.where.y,
				left: this.now.x - this.where.x
			});

			// Check rows, from top to the bottom
			accepts = {
				$helper: that.$helper,
				type:    that.getTypeSettings( that.$helper.data( 'type' ) ),
				mouse:   that.now
			};

			this.rows.forEach(function( row ) {
				if( ! row.accepts( accepts ) ) {
					if( ! that.sourceRow ) {
						row.removePlaceholder();

						if( row == that.currentRow ) {
							that.currentRow = false;
						}
					}

					return;
				}

				if( row != that.currentRow ) {
					// Remove the placeholder if the new row can handle the element
					if( that.currentRow ) {
						that.currentRow.removePlaceholder();
					}

					// Save the row and add the palceholder
					that.currentRow = row;
					row.addPlaceholder( accepts );
				} else {
					row.repositionPlaceholder( accepts );
				}

				return false;
			});
		},

		/**
		 * Creates the current helper based on the moved prototype
		 */
		startDragging: function( $item ) {
			var pos;

			if( this.sourceRow ) {
				this.$helper = $item;

				// Start by detaching the element from the row
				this.sourceRow.detachElement( $item.data( 'element' ) );

				// Mark down the actual position of the element
				pos = {
					x: $item.offset().left - this.$el.offset().left,
					y: $item.offset().top  - this.$el.offset().top
				}

				// Move the element to the body
				$item
					.css({
						top: pos.y,
						left: pos.x,
						width: $item.width()
					})
					.addClass( 'uf-layout-helper' )
					.appendTo( this.$el );
			} else {
				this.$helper = $item.clone();
				this.$helper.addClass( 'uf-layout-helper' ).appendTo( this.$el );
				this.$helper.data( 'original', $item );
			}
		},

		/**
		 * Dragging has started.
		 */
		mouseUp: function( e ) {
			var that = this, $helper, $original;

			if( ! this.down ) {
				return;
			}

			if( this.currentRow ) {
				// Adding an element
				this.currentRow.populatePlaceholder({
					className:      this.args.element,
					populateElement: this.args.populateElement
				});

				if( this.sourceRow ) {
					this.$helper.removeClass( 'uf-layout-helper' ).css({
						top: 0,
						left: 0
					});

					this.$helper.data( 'element' ).adjustToColumns( this.args.columns );

					// Recalculate
					this.currentRow.calculateSpace();
					this.currentRow.cleanup();
				} else {
					this.$helper.remove();
				}
			} else {
				if( this.sourceRow ) {
					this.sourceRow.$el.find( '.uf-layout-row-groups' ).append( this.sourceElement.$el.find( '.uf-layout-row-groups' ) );
					this.sourceRow.addElement( this.sourceElement );
				} else {
					// Animate the helper back and destroy it
					$helper   = this.$helper;
					$original = $helper.data( 'original' );
					this.$helper.animate({
						top:  $original.offset().top  - this.$el.offset().top,
						left: $original.offset().left - this.$el.offset().left
					}, function() {
						$helper.remove();
					});
				}
			}

			// Reset
			this.$helper    = null;
			this.down       = false;
			that.currentRow = false;
			that.sourceRow  = false;

			this.rows.forEach(function( row ) {
				row.cleanup();
			});

			// Ensure there is an empty row
			this.addEmptyRow();

			// Save the results
			this.save();
		},

		/**
		 * Saves the current positioning.
		 */
		save: function() {
			var that = this,
				row  = 0;

			// Do a simple crawl and trigger jQuery events
			this.find( 'body' ).find( '.' + this.args.row ).each(function() {
				var $row = $( this ), index = 0;

				$row.find( '.' + that.args.element ).each(function() {
					var $element = $( this );

					$element.data( 'element' ).setAttributes({
						index: index++,
						row:   row,
						width: $element.data( 'element' ).width
					});
				});

				row++;
			});

			this.$el.trigger( 'layout-updated' );
		}
	});

	$.fn.layout = function( args ) {
		return this.each(function() {
			new layout.Core( this, args );
		});
	}

})( jQuery );
