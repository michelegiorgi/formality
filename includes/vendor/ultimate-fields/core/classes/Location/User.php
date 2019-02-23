<?php
namespace Ultimate_Fields\Location;

use Ultimate_Fields\Location;
use Ultimate_Fields\Datastore\User_Meta as Datastore;
use Ultimate_Fields\Controller\User as Controller;
use Ultimate_Fields\Form_Object\User as Form_Object;
use Ultimate_Fields\Helper\Data_Source;
use Ultimate_Fields\Location\Supports_Columns;

/**
 * Works as a location definition for users within containers.
 *
 * @since 3.0
 */
class User extends Location {
	use Customizable, Supports_Columns;

	/**
	 * Holds a keyword that would let works_with return true even without an object.
	 *
	 * @since 3.0
	 * @const string
	 */
	const WORKS_WITH_KEYWORD = 'user';

	/**
	 * Holds the roles the container would be shown on.
	 *
	 * @since 3.0
	 * @var string[]
	 */
	protected $roles = array();

	/**
	 * Indicates if the location should be displayed on the registration form.
	 *
	 * @since 3.0
	 * @var bool
	 */
	protected $register = false;

	/**
	 * Creates an instance of the class.
	 * The parameters for this constructor are the same as the parameters of Container->add_location().
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $args Additional arguments for the location.
	 */
	public function __construct( $args = array() ) {
		$this->check_args_for_columns( $args );
		$this->check_args_for_customizer( $args );

		$this->set_and_unset( $args, array(
			'registration_form'	=> 'set_registration_form'
		));

		# Send all arguments to the appropriate setter.
		$this->arguments = $args;
	}

	/**
	 * Returns an instance of the controller, which controls the location (posts).
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\Controller\Comment
	 */
	public function get_controller() {
		return Controller::instance();
	}

	/**
	 * Handles user roles.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $roles The roles to show/hide the container on.
	 * @return Ultimate_Fields\Location\User
	 */
	protected function set_roles( $roles ) {
		$this->roles = $this->handle_value( $this->roles, $roles );

		return $this;
	}

	/**
	 * Indicates if the form works on the registration form.
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
	public function is_displayed_on_register() {
		$this->parse_arguments( $this->arguments );

		return $this->register;
	}

	/**
	 * Lets the form be displayed on the registration page.
	 *
	 * @since 3.0
	 *
	 * @param bool $flag The flag that tells us if it's displayed.
	 * @return Ultimate_Fields\Location\User
	 */
	public function set_registration_form( $show ) {
		$this->register = (bool) $show;

		return $this;
	}

	/**
	 * Returns the settings for the location, sendable to JS.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export_settings() {
		$this->parse_arguments( $this->arguments );

		$data = array();

		if( ! empty( $this->roles ) ) {
			$data[ 'roles' ] = $this->roles;
		}

		return $data;
	}

	/**
	 * Generates a datastore based on an object.
	 *
	 * @since 3.0
	 *
	 * @param mixed $object The post to create a datastore for.
	 * @return mixed
	 */
	public function create_datastore( $object ) {
		if( ! is_a( $object, 'WP_User' ) && ! is_int( $object ) ) {
			return false;
		}

		$datastore = new Datastore;
		$datastore->set_id( is_int( $object ) ? $object : $object->ID );

		return $datastore;
	}

	/**
	 * Determines whether the location works with a certain object(type).
	 *
	 * @since 3.0
	 *
	 * @param mixed $object An object or a string to work with.
	 * @return bool
	 */
	public function works_with( $source ) {
		# Check for the attachment type
		if( $source === self::WORKS_WITH_KEYWORD ) {
			return true;
		}

		# Initialize arguments if needed
		$this->parse_arguments( $this->arguments );

		# If there is no object, then we don't work with it
		if( ! is_object( $source ) ) {
			return false;
		}

		# Convert to a proper user
		if( is_a( $source, 'WP_User' ) ) {
			$user = $source;
		} elseif( is_a( $source, Data_Source::class ) && 'user_meta' == $source->type && $existing = get_user_by( 'id', $source->item ) ) {
			$user = $existing;
		} else {
			return false;
		}

		# Check for roles
		if( ! empty( $this->roles ) ) {
			$roles = $user->roles;

			foreach( $this->roles[ 'hidden' ] as $role ) {
				if( in_array( $role, $roes ) ) {
					return false;
				}
			}

			$visible = 0 === count( $this->roles[ 'visible' ] );
			foreach( $this->roles[ 'visible' ] as $role ) {
				if( in_array( $role, $roles ) ) {
					$visible = true;
					break;
				}
			}

			if( ! $visible ) {
				return false;
			}
		}

		return $user;
	}

	/**
	 * Checks if the location works with a front-end forms object.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Form_Object $object The object to check.
	 * @return bool
	 */
	public function works_with_object( $object ) {
		// ToDo: Check roles & stuff
		return is_a( $object, Form_Object::class );
	}

	/**
	 * Checks if the location should be displayed in the customizer based on the current category.
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
	public function customizer_active_callback() {
		if( is_admin() ) {
			return false;
		}

		if( get_queried_object() && is_a( get_queried_object(), 'WP_User' ) ) {
			$user = get_queried_object();
			$source = Data_Source::parse( 'user_' . $user->ID, 'customizer' );
			return $this->works_with( $source );
		}

		return false;
	}

	/**
	 * Adds the needed filters and actions for admin columns.
	 *
	 * @since 3.0
	 */
	public function init_admin_columns() {
		add_filter( 'manage_users_columns',          array( $this, 'change_columns' ) );
		add_action( 'manage_users_custom_column' ,   array( $this, 'manage_column' ), 10, 3 );
		add_filter( 'manage_users_sortable_columns', array( $this, 'change_sortable_columns' ) );
		add_action( 'pre_get_users',                array( $this, 'sort_query_by_columns' ) );
	}

	/**
	 * Outputs the value of a column.
	 *
	 * @since 3.0
	 *
	 * @param string $column_name The name of the column to display.
	 * @param int    $item_id     The ID of the item that is being displayed.
	 */
	public function manage_column( $output, $column_name, $item_id ) {
		return $this->render_column( $column_name, $item_id );
	}

	/**
	 * Changes the sorting of column.
	 *
	 * @since 3.0
	 *
	 * @param WP_User_Query $query The query to modify.
	 */
	public function sort_query_by_columns( $user_search ) {
		if ( 'users' != get_current_screen()->id ) {
			return;
		}

		if( empty( $this->get_admin_columns() ) || ! isset( $_GET[ 'orderby' ] ) ) {
			return;
		}

		$orderby = $_GET[ 'orderby' ];
		$order   = false;
		foreach( $this->get_admin_columns() as $column ) {
			if( $column->get_name() != $orderby )
				continue;

			$order = 'ASC' == strtoupper( $_GET[ 'order' ] ) ? 'ASC' : 'DESC';
		}

		if( ! $order ) {
			return;
		}

		$user_search->set( 'orderby',  'meta_value' );
		$user_search->set( 'order',    $order );
		$user_search->set( 'meta_key', $orderby );
	}

	/**
	 * Returns the settings for the location, which will be exported.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export() {
		$settings = parent::export();
		$this->export_rule( $settings, 'roles' );

		# Export customizable data
		$this->export_customizable_data( $settings );

		# Export REST data
		$this->export_rest_data( $settings );

		# Export columns
		$this->export_column_data( $settings );

		return $settings;
	}

	/**
	 * Imports the location from PHP/JSON.
	 *
	 * @since 3.0
	 *
	 * @param  [mixed[] $args The arguments to import.
	 */
	public function import( $args ) {
		if( isset( $args[ 'roles' ] ) && ! empty( $args[ 'roles' ] ) ) {
			$this->set_roles( $this->extract_value( $args[ 'roles' ] ) );
		}

		# Check for the customizer
		$this->import_customizable_data( $args );

		# Check for columns
		$this->import_column_data( $args );

		# Check for rest data
		$this->import_rest_data( $args );
	}
}
