(function( $ ){

	var field    = UltimateFields.Field,
		mapField = field.Map = {};

	/**
	 * Basic model for the map field.
	 */
	mapField.Model = field.Model.extend({

	});

	/**
	 * Handles the input of the map field.
	 */
	mapField.View = field.View.extend({
		/**
		 * Based on the availability of the API, renders either a map or an error.
		 */
		render: function() {
			var that = this;

			if( that.apiLoaded() ) {
				setTimeout( that.renderInput.bind( this ), 10 );
			} else {
				that.showErrorStatus();
			}
		},

		/**
		 * Checks if the API is already loaded.
		 *
		 * @return <boolean>
		 */
		apiLoaded: function() {
			if( typeof google == 'undefined' || typeof google.maps == 'undefined' ) {
				return false;
			} else {
				return true;
			}
		},

		/**
		 * When the Google Maps API is present, this will generate the whole input.
		 */
		renderInput: function() {
			var that  = this,
				tmpl = UltimateFields.template( 'map-base' ),
				value = this.model.getValue(),
				$map, $input, center, zoom;

			// Add the structure.
			that.$el.html( tmpl() );

			// Initialize the map
			$map = this.$el.find( '.uf-map-ui div' );

			$map.css({
				height: this.model.get( 'height' ) || 400
			});

			if( value && typeof value == 'object' ) {
				center = new google.maps.LatLng( value.latLng.lat, value.latLng.lng );
				zoom   = parseInt( value.zoom );
			} else {
				center = new google.maps.LatLng( 38.8865, -77.0969 );
				zoom   = 8;
			}

			this.map = new google.maps.Map( $map.get( 0 ), {
				center: center,
				zoom: zoom
			});

			// Add autocomplete
			$input = this.$el.find( '.uf-map-input' ).on( 'keydown', function( e ) {
				if( e.which == 13 ) {
					e.preventDefault();
				}
			});
			this.autocomplete = new google.maps.places.Autocomplete( $input.get( 0 ) );

			if( value && typeof value == 'object' ) {
				that.addElements();
			}

			// Handle changes
			google.maps.event.addListener( this.autocomplete, 'place_changed', function() {
				var place = that.autocomplete.getPlace();

				if( ! place.geometry ) {
					return;
				}

				// Center the map
				that.map.setCenter( place.geometry.location );

				// Change the zoom
				if( place.geometry.viewport ) {
					that.map.fitBounds( place.geometry.viewport );
				} else {
					that.map.setZoom( 17 );
				}

				// Save the value
				that.model.setValue({
					latLng:       { lat: place.geometry.location.lat(), lng: place.geometry.location.lng() },
					address:      place.formatted_address,
					zoom:         that.map.getZoom(),
					addressParts: $.map( place.address_components, function( line ) {
						return line.long_name
					})
				});

				that.addElements();
			});

			// When the field is toggled, force a map resize event
			this.model.on( 'change:visible', function() {
				setTimeout(function(){
					google.maps.event.trigger( that.map, 'resize' );
				}, 50 );
			});
		},

		/**
		 * Handles location changes.
		 */
		addElements: function() {
			var that = this;

			// Clear old elements
			if( typeof that.infoWindow != 'undefined' ) {
				that.infoWindow.close();
			}
			if( typeof that.marker != 'undefined' ) {
				that.marker.setMap( null );
			}

			// Add a marker
			that.marker = new google.maps.Marker({
				map:       that.map,
				position:  that.map.getCenter(),
				draggable: true
			});

			// Show a popup
			that.infoWindow = new google.maps.InfoWindow();
			that.infoWindow.setContent( that.model.getValue().address );
			that.infoWindow.open( that.map, that.marker );

			// Handle clearing
			google.maps.event.addListener( that.infoWindow, 'closeclick', function() {
				that.clearLocation();
			});

			google.maps.event.addListener( that.marker, 'dragend', function( event ) {
				var latLng = event.latLng,
					lat    = latLng.lat(),
					lng    = latLng.lng();

				that.model.setValue({
					latLng:       { lat: lat, lng: lng },
					address:      lat + ', ' + lng,
					zoom:         that.map.getZoom(),
					addressParts: [ lat + ', ' + lng ]
				});

				that.infoWindow.setContent( lat + ', ' + lng );
			});
		},

		/**
		 * Clears the location.
		 */
		clearLocation: function() {
			this.marker.setMap( null );
			this.infoWindow.close();
			this.model.setValue( false );
		},

		/**
		 * Shows a message that the API couldn't be loaded.
		 */
		showErrorStatus: function() {
			this.$el.append( UltimateFields.template( 'map-error' )() );
		},

		/**
		 * Focuses the address input.
		 */
		focus: function() {
			this.$el.find( '.uf-map-input' ).focus();
		}
	});

})( jQuery );
