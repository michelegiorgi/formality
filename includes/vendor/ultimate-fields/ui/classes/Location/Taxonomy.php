<?php
namespace Ultimate_Fields\UI\Location;

use Ultimate_Fields\Field;
use Ultimate_Fields\Field\WP_Object as Object_Field;
use Ultimate_Fields\Location as Core_Location;
use Ultimate_Fields\Helper\Util;
use Ultimate_Fields\UI\Location;

/**
 * Handles containers on taxonomy terms.
 *
 * @since 3.0
 */
class Taxonomy extends Location {
	/**
	 * Returns the type of the location (e.g. post_type).
	 *
	 * @since 3.0
	 * @return string
	 */
	public static function get_type() {
		return 'taxonomy';
	}

	/**
	 * Returns the name of the location.
	 *
	 * @since 3.0
	 * @return string
	 */
	public static function get_name() {
		return __( 'Taxonomy', 'ultimate-fields' );
	}

	/**
	 * Returns the fields for the location.
	 *
	 * @since 3.0
	 * @return Ultimate_Fields\Field[]
	 */
	public static function get_fields() {
		$fields       = array();
		$t_options    = ultimate_fields()->get_available_taxonomies( false );
		$hierarchical = array_keys( ultimate_fields()->get_available_taxonomies( true ) );

		$fields[] = Field::create( 'tab', 'basic_settings', __( 'Basic Settings' ) )
			->set_icon( 'dashicons-admin-post' );

		$fields[] = Field::create( 'radio', 'location_type', __( 'Location type', 'ultimate-fields' ) )
			->add_options(array(
				'location' => __( 'Show the container based on a taxonomy and rules', 'ultimate-fields' ),
				'term'     => __( 'Show the container based on a particular term(s)', 'ultimate-fields' )
			));

		# Start with the taxonomy itself
		$fields[] = Field::create( 'multiselect', 'taxonomy', __( 'Taxonomy', 'ultimate-fields' ) )
			->add_options( $t_options )
			->set_input_type( 'checkbox' )
			->add_dependency( 'location_type', 'location' )
			->set_description( __( 'The container will be displayed only for the selected taxonomies.', 'ultimate-fields' ) );

		# Add specific levels
		$fields[] = Field::create( 'complex' , 'levels', __( 'Levels', 'ultimate-fields' ) )
			->add_dependency( 'taxonomy', $hierarchical, 'contains' )
			->add_dependency( 'location_type', 'location' )
			->set_description( __( 'Enter as numbers, separated by commas.', 'ultimate-fields' ) )
			->add_fields(array(
				Field::create( 'text', 'visible', __( 'Show on', 'ultimate-fields' ) )
					->set_width( 50 ),
				Field::create( 'text', 'hidden', __( 'Hide on', 'ultimate-fields' ) )
					->set_width( 50 ),
			));

		$fields[] = Field::create( 'complex', 'terms', __( 'Term', 'ultimate-fields' ) )
			->add_dependency( 'location_type', 'term' )
			->set_description( __( 'Select a term to use as a base for the rules of the location.', 'ultimate-fields' ) )
			->add_fields(array(
				Field::create( 'wp_object', 'term', __( 'Item', 'ultimate-fields' ) )
					->add( 'terms' )
					->set_width( 50 )
					->hide_label(),
				Field::create( 'select', 'operator', __( 'Operator', 'ultimate-fields' ) )
					->set_input_type( 'radio' )
					->add_dependency( 'term', '', 'NOT_NULL' )
					->add_options(array(
						'is'     => __( 'is', 'ultimate-fields' ),
						'is_not' => __( 'is not', 'ultimate-fields' )
					))
					->set_width( 20 ),
				Field::create( 'select', 'type', __( 'Item type', 'ultimate-fields' ) )
					->set_input_type( 'radio' )
					->add_dependency( 'term', '', 'NOT_NULL' )
					->add_options(array(
						'term'   => __( 'the current term', 'ultimate-fields' ),
						'parent' => __( 'the parent of the current term', 'ultimate-fields' )
					))
					->set_width( 30 )
			));

		$fields = array_merge( $fields, self::get_customizer_fields() );
		$fields = array_merge( $fields, self::get_rest_fields() );
		$fields = array_merge( $fields, self::get_column_fields() );

		return $fields;
	}

	/**
	 * Exports the location as a real, core location.
	 *
	 * @since 3.0
	 *
	 * @return Core_Location
	 */
	public static function export( $data, $helper ) {
		if( $data[ 'location_type' ] == 'term' ) {
			$selector = $data[ 'terms' ];
			$terms    = wp_list_pluck( Object_Field::extract( $selector[ 'term' ] ), 'item' );
			$visible  = array();
			$hidden   = array();

			$location = Core_Location::create( 'taxonomy' );

			if( 'is' == $selector[ 'operator' ] ) {
				$visible = $terms;
			} else {
				$hidden = $terms;
			}

			$combined = compact( 'visible', 'hidden' );
			if( 'term' == $selector[ 'type' ] ) {
				$location->terms = $combined;
			} else {
				$location->parents = $combined;
			}
		} else {
			$location = Core_Location::create( 'taxonomy', $data[ 'taxonomy' ] );

			# Prepare and set the levels if necessary
			$levels = array(
				'visible' => array(),
				'hidden'  => array()
			);

			if( isset( $data[ 'levels' ] ) && is_array( $data[ 'levels' ] ) ) {
				if( isset( $data[ 'levels' ][ 'visible' ] ) ) {
					$levels[ 'visible' ] = Util::string_to_numbers( $data[ 'levels' ][ 'visible' ] );
				}

				if( isset( $data[ 'levels' ][ 'hidden' ] ) ) {
					$levels[ 'hidden' ] = Util::string_to_numbers( $data[ 'levels' ][ 'hidden' ] );
				}
			}

			$location->levels = $levels;
		}

		# Setup customizer data
		self::setup_location_customizer( $location, $data );

		# Setup the rest API
		self::setup_location_rest( $location, $data );

		# Setup admin columns
		self::setup_location_columns( $location, $data );

		return $location;
	}

	/**
	 * Returns the data of a core location if it can work with it or false if not.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Location $location The location to export data from.
	 * @return mixed
	 */
	public static function get_settings_for_import( $location ) {
		if( ! is_a( $location, Core_Location::class ) ) {
			return false;
		}

		$data = array(
			'__type' => self::get_type()
		);

		if( ( $parents = $location->parents ) || ( $terms = $location->terms ) ) {
			# ID-based selectors
			$terms = $parents ? $parents : $terms;

			# Check if we are hiding or showing
			if( isset( $terms[ 'hidden' ] ) ) {
				$raw     = $terms[ 'hidden' ];
				$exclude = true;
			} else {
				$raw     = $terms[ 'visible' ];
				$exclude = false;
			}

			# Convert the raw values to object data
			$the_term = false;
			foreach( $raw as $term ) {
				if( 0 === strpos( $term . '', '-' ) ) {
					$exclude = true;
					$term = is_int( $term )
						? absint( $term )
						: preg_replace( '~^-~', '', $term );
				}

				$the_term = 'term_' . $term;
			}

			# Parse the actual values
			$data[ 'location_type' ] = 'term';
			$data[ 'terms' ]         = array(
				'term'     => $the_term,
				'operator' => $exclude ? 'is_not' : 'is',
				'type'     => $parents ? 'parent' : 'term'
			);
		} else {
			# Rule-based selectors
			$data[ 'location_type' ] = 'location';
			$data[ 'taxonomy' ]      = $location->get_taxonomies();

			if( $levels = $location->levels ) {
				$data[ 'levels' ] = array(
					'visible' => implode( ',', $levels[ 'visible' ] ),
					'hidden'  => implode( ',', $levels[ 'hidden' ] )
				);
			}
		}

		# Check for customizer data
		self::import_customizer( $location, $data );

		# Check for REST data
		self::import_rest( $location, $data );

		# Check for admin columns
		self::import_admin_columns( $location, $data );

		return $data;
	}
}
