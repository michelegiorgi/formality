<?php
namespace Ultimate_Fields\Controller;

use Ultimate_Fields\Controller;
use Ultimate_Fields\Template;
use Ultimate_Fields\Datastore\User_Meta as Datastore;
use Ultimate_Fields\Data_API;
use WP_User;
use Ultimate_Fields\Location\User as User_Location;
use Ultimate_Fields\Controller\REST_API;
use Ultimate_Fields\Controller\Admin_Column_Manager;

/**
 * Handles the user location.
 *
 * @since 3.0
 */
class User extends Controller {
	use REST_API, Admin_Column_Manager;

	/**
	 * Indicates if the registration form is being currently displayed.
	 *
	 * @since 3.0
	 * @var bool
	 */
	protected $in_registration_form = false;

	/**
	 * Adds the neccessary hooks for the controller.
	 *
	 * @since 3.0
	 */
	protected function __construct() {
		add_action( 'show_user_profile',      array( $this, 'display' ) );
		add_action( 'edit_user_profile',      array( $this, 'display' ) );
		add_action( 'user_new_form',          array( $this, 'display_new_user' ) );
		add_action( 'register_form',          array( $this, 'display_registration_form' ) );
		add_action( 'profile_update',         array( $this, 'save' ) );
		add_action( 'user_register',          array( $this, 'save' ) );
		add_action( 'login_enqueue_scripts',  array( $this, 'enqueue_login_scripts' ) );
		add_action( 'admin_enqueue_scripts',  array( $this, 'enqueue_scripts' ) );
		add_action( 'current_screen',         array( $this, 'initialize_admin_columns' ) );
		add_action( 'current_screen',         array( $this, 'do_ajax' ) );
		$this->rest();
	}

	/**
	 * Displays all containers on normal forms.
	 *
	 * @since 3.0
	 *
	 * @param WP_User $user The user that is being edited.
	 */
	public function display( WP_User $user ) {
		foreach( $this->combinations as $combination ) {
			$this->display_container( $combination, $user );
		}
	}

	/**
	 * Displays all needed containers on new user forms.
	 */
	public function display_new_user() {
		foreach( $this->combinations as $combination ) {
			$this->display_container( $combination );
		}
	}

	/**
	 * Display on the registrationf orm.
	 *
	 * @since 3.0
	 */
	public function display_registration_form() {
		$this->in_registration_form = true;

		foreach( $this->combinations as $combination ) {
			$display = false;

			foreach( $combination[ 'locations' ] as $location ) {
				if( $location->is_displayed_on_register() ) {
					$display = true;
					break;
				}
			}

			if( ! $display ) {
				continue;
			}

			$this->display_container( $combination );
		}
	}

	/**
	 * Displays a container.
	 *
	 * @since 3.0
	 *
	 * @param Container  $container The container to display.
	 * @param WP_User    $user      The user that is being edited (optional).
	 */
	public function display_container( $combination, $user = null ) {
		$container = $combination[ 'container' ];

		$datastore = new Datastore;
		if( ! is_null( $user ) ) {
			$datastore->set_id( $user->ID );
		}
		$container->set_datastore( $datastore );

		# Gather location data
		$locations = array();
		foreach( $combination[ 'locations' ] as $location ) {
			$locations[] = $location->export_settings();
		}

		# Combine locations and settings
		$settings = $container->export_settings();
		$settings[ 'locations' ] = $locations;
		$settings[ 'registration_form' ] = false;

		# Adjust settings for the registration form
		if( $this->in_registration_form ) {
			$settings[ 'registration_form' ] = true;
			$settings[ 'layout' ]            = 'grid';
		} elseif( 'boxed' == $container->get_style() ) {
			$settings[ 'style' ] = 'boxed';
		}

		$json = array(
			'type'      => 'User',
			'settings'  => $settings,
			'data'      => $container->export_data(),
		);

        $tag = sprintf(
            '<div class="uf-container uf-container-label-200" data-type="%s"><script type="text/json">%s</script></div>',
            'User',
            json_encode( $json )
        );

		echo $this->get_no_js_message();

		# Based on the style of the container, either include a seamless or boxed version
		if( $this->in_registration_form ) {
			echo $tag;
		} elseif( 'boxed' == $container->get_style() ) {
			$templates = Template::instance();
			$templates->include_template( 'container/user-boxed', array(
				'tag'   => $tag,
				'title' => $container->get_title()
			));
		} else {
			echo '<div class="uf-container-seamless">';
				echo $tag;
			echo '</div>';
		}

		# Register the neccessary templates
		Template::add( 'user', 'container/user' );
		Template::add( 'container-error', 'container/error' );
	}

	/**
	 * Enqueues scripts when needed.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts( $force = false ) {
		if( true !== $force ) {
			# Post meta containers don't work in the front-end
			if( ! is_admin() || ! function_exists( 'get_current_screen' ) ) {
				return;
			}

			# Check the forms
			if( ! in_array( get_current_screen()->id, array( 'profile', 'user', 'user-edit', 'user-edit-network' ) ) ) {
				return;
			}
		}

		# Ensure unique field names
		$this->ensure_unique_field_names();

		wp_enqueue_script( 'uf-container-user' );

		# Enqueue individual scripts
		foreach( $this->combinations as $combination ) {
			$combination[ 'container' ]->enqueue_scripts();
		}

		# Attach translations
		ultimate_fields()->l10n()->enqueue();

		return false;
	}

	/**
	 * Enqueues scripts for the login page.
	 *
	 * @since 3.0
	 */
	public function enqueue_login_scripts() {
		$this->enqueue_scripts( true );
	}

	/**
	 * Handles user saving.
	 *
	 * @since 3.0
	 *
	 * @param int $user_id The ID of the user that is being currently created/saved.
	 */
	public function save( $user_id ) {
		global $pagenow;

		# Bail on multisites
		if( is_multisite() && ms_is_switched() ) {
			return;
		}

		$current_page = isset( $pagenow ) ? $pagenow : false;
		if( 'wp-login.php' != $current_page && ( ! is_admin() || defined( 'UF_UI_IMPORTING' ) ) ) {
			return;
		}

		# Ensure unique field names
		$this->ensure_unique_field_names();

		# All values will be saved in the same datastore
		$user      = get_user_by( 'id', $user_id );
		$datastore = new Datastore;
		$datastore->set_id( $user_id );

		# This will hold all messages
		$errors = array();

		# Try saving each of the containers in the queue
		foreach( $this->combinations as $combination ) {
			$container = $combination[ 'container' ];

			# Set the datastore to the container and fields
			$container->set_datastore( $datastore );

			# Prepare the data for the container
			$data_key = 'uf_user_meta_' . $container->get_id();
			$data = isset( $_POST[ $data_key ] )
				? json_decode( stripslashes( $_POST[ $data_key ] ), true )
				: array();

			# Soft-save the container
			$messages = $container->save( $data );

			# Check if the container is visible
			$visible = false;
			foreach( $combination[ 'locations' ] as $location ) {
				if( $location->works_with( $user ) ) {
					$visible = true;
					break;
				}
			}

			if( $visible ) {
				$errors = array_merge( $errors, $messages );
			}
		}

		# Save or die
		if( empty( $errors ) ) {
			$datastore->commit();
		} else {
			$this->error( $errors );
		}
	}

	/**
	 * Registers a location with the REST API.
	 *
	 * @since 3.0
	 *
	 * @param  Ultimate_Fields\Location  $location  The location that is being linked.
	 * @param  Ultimate_Fields\Container $container The container the location belongs to.
	 * @param  mixed[]       $fields    The fields that should be exposed.
	 */
	protected function register_rest_location( $location, $container, $fields ) {
		$this->add_fields_to_endpoint( 'user', $fields, $container );
	}

	/**
	 * Reads out a value from the data API.
	 *
	 * @since 3.0
	 *
	 * @param  Ultimate_Fields\Field $field The field whose value is needed.
	 * @param  mixed     $item  The API item (user).
	 * @return mixed
	 */
	protected function get_api_value( $field, $item ) {
		return Data_API::instance()->get_value( $field->get_name(), 'user_' . $item[ 'id' ] );
	}

	/**
	 * Updates a REST value through the data API.
	 *
	 * @since 3.0
	 *
	 * @param  Ultimate_Fields\Field $field The field whose value is being saved.
	 * @param  mixed     $value The value to save.
	 * @param  WP_User   $item  The item that the value should be associated with.
	 * @return bool
	 */
	protected function save_api_value( $field, $value, $item ) {
		return Data_API::instance()->update_value( $field->get_name(), $value, 'user_' . $item->ID );
	}

	/**
	 * Performs AJAX when needed.
	 *
	 * @since 3.0
	 */
	public function do_ajax( $screen ) {
		if( in_array( $screen->id, array( 'profile', 'user-edit', 'user-new' ) ) ) {
			ultimate_fields()->ajax( User_Location::WORKS_WITH_KEYWORD );
		}
	}
}
