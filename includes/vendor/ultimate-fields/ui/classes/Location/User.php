<?php
namespace Ultimate_Fields\UI\Location;

use Ultimate_Fields\UI\Location;
use Ultimate_Fields\Field;
use Ultimate_Fields\Location as Core_Location;

/**
 * Handles containers on user edit screens.
 *
 * @since 3.0
 */
class User extends Location {
	/**
	 * Returns the type of the location (e.g. post_type).
	 *
	 * @since 3.0
	 * @return string
	 */
	public static function get_type() {
		return 'user';
	}

	/**
	 * Returns the name of the location.
	 *
	 * @since 3.0
	 * @return string
	 */
	public static function get_name() {
		return __( 'User', 'ultimate-fields' );
	}

	/**
	 * Returns the fields for the location.
	 *
	 * @since 3.0
	 * @return Ultimate_Fields\Field[]
	 */
	public static function get_fields() {
		global $wp_roles;

		$fields = array();

		$fields[] = Field::create( 'tab', 'basic_settings', __( 'Basic Settings' ) )
			->set_icon( 'dashicons-admin-post' );

		# Add fields for user roles.
		$fields[] = Field::create( 'checkbox', 'all_roles', __( 'Show on all user roles', 'ultimate-fields' ) )
			->fancy()
			->set_default_value( true )
			->set_description( __( 'The container would only be assigned to users which have a certain role.', 'ultimate-fields' ) );

		if( get_option( 'users_can_register' ) ) {
			$fields[] = Field::create( 'checkbox', 'registration_form', __( 'Registration Form', 'ultimate-fields' ) )
				->fancy()
				->set_text( __( 'Show in the registration form', 'ultimate-fields' ) );
		}

		$fields[] = Field::create( 'complex', 'user_roles', __( 'User roles', 'ultimate-fields' ) )
			->set_description( __( 'The container will only be accessible if the edited user has a role that is among the ones that are checked above.', 'ultimate-fields' ) )
			->add_dependency( 'all_roles', false )
			->add_fields(array(
				Field::create( 'multiselect', 'visible', __( 'Show on', 'ultimate-fields' ) )
					->add_options( $wp_roles->get_names() )
					->set_input_type( 'checkbox' )
					->set_width( 50 ),
				Field::create( 'multiselect', 'hidden', __( 'Hide on', 'ultimate-fields' ) )
					->add_options( $wp_roles->get_names() )
					->set_input_type( 'checkbox' )
					->set_width( 50 )
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
		$location = Core_Location::create( 'user' );

		if( ! $data[ 'all_roles' ] ) {
			$location->roles = isset( $data[ 'user_roles' ] ) ? $data[ 'user_roles' ] : array();
		}

		if( $data[ 'registration_form' ] ) {
			$location->set_registration_form( true );
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

		if( $roles = $location->roles ) {
			$data[ 'user_roles' ] = $roles;
			$data[ 'all_roles' ]  = false;
		} else {
			$data[ 'all_roles' ]  = true;
			$data[ 'user_roles' ] = array();
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
