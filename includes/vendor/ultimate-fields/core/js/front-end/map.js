/**
 * Performs all actions, which are related to rendering a map through Ultimate Fields.
 */
jQuery(function( $ ){

	/**
	 * Initialize maps
	 */
	$( '.uf-map' ).each(function() {
		var $map = $( this ),
			data = $map.data(),
			center, map, pin;

		center = new google.maps.LatLng( data.lat, data.lng );

		map = new google.maps.Map( $map.get( 0 ), {
			center:      center,
			zoom:        parseInt( data.zoom ),
			scrollwheel: false
		});

		pin = new google.maps.Marker({
			position: center,
			map: map,
		});
	});

});
