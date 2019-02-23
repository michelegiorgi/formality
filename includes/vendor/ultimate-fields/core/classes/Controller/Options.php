<?php
namespace Ultimate_Fields\Controller;

use Ultimate_Fields\Controller;
use Ultimate_Fields\Container;
use Ultimate_Fields\Location;
use Ultimate_Fields\Helper\Callback;
use Ultimate_Fields\Template;
use Ultimate_Fields\Data_API;
use WP_REST_Server;
use WP_REST_Response;
use WP_Error;

/**
 * Handles the options page location.
 *
 * @since 3.0
 */
class Options extends Controller {
	/**
	 * Adds the neccessary hooks for the controller.
	 *
	 * @since 3.0
	 */
	protected function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_containers' ) );
		add_action( 'uf.options_page.save', array( $this, 'save' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
	}

	/**
	 * Adds a combination of a container and a location to the controller.
	 *
	 * @since 3.0
	 *
	 * @param Container $container The container that will be controlled.
	 * @param Location  $Location  The location where the container should be displayed.
	 */
	public function attach( Container $container, Location $location ) {
		parent::attach( $container, $location );

		# Check if the location should generate a page
		if( ! $location->get_page() ) {
			$location->generate_page( $container );
		}
	}

	/**
	 * Attaches metaboxes when neccessary.
	 *
	 * @since 3.0
	 */
	public function add_containers() {
		foreach( $this->combinations as $combination ) {
			$this->handle_container( $combination );
		}
	}

	/**
	 * Handles a specific container-locations combination.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $combination The processable combination.
	 */
	protected function handle_container( $combination ) {
		$container  = $combination[ 'container' ];
		$pages      = array();

		$context    = $default_context  = 'normal';
		$priority   = $default_priority = 'high';

		$datastore  = false;

		# Go through each combination and extract post types + context and priority.
		foreach( $combination[ 'locations' ] as $location ) {
			if( ! $page = $location->get_page() ) {
				continue;
			}

			if( 'network' == $page->get_type() && ! is_network_admin() ) {
				continue;
			}

			$pages[] = $page->get_screen_id();

			if( ( $location_context = $location->get_context() ) != $default_context ) {
				$context = $location_context;
			}

			if( ( $location_priority = $location->get_priority() ) != $default_context ) {
				$priority = $location_priority;
			}

			if( false == $datastore ) {
				$datastore = $location->get_datastore();
				$container->set_datastore( $datastore );
			}
		}

		if( empty( $pages ) ) {
			return;
		}

		# Create a custom callback that will include the conainer.
		$callback = new Callback( array( $this, 'display' ) );
		$callback[ 'container' ] = $container;

		# Add the meta box
		add_meta_box(
			$container->get_id(),
			$container->get_title(),
			$callback->get_callback(),
			$pages,
			$context,
			$priority
		);
	}

	/**
	 * Displays a container.
	 *
	 * @since 3.0
	 *
	 * @param Callback $callback The callback that is being called.
	 */
	public function display( $callback ) {
		$container = $callback[ 'container' ];

		$json = array(
			'type'      => 'Options',
			'settings'  => $container->export_settings(),
			'data'      => $container->export_data()
		);

        echo sprintf(
            '<div class="uf-container" data-type="%s">
				<script type="text/json">%s</script>
				' . $this->get_no_js_message() . '
				<span class="spinner hide-if-no-js"></span>
			</div>',
            'Options',
            json_encode( $json )
        );

		if( 'seamless' == $json[ 'settings' ][ 'style' ] ) {
			$this->unbox();
		}

		# Register the neccessary templates
		Template::add( 'options', 'container/options' );
		Template::add( 'container-error', 'container/error' );
		Template::instance()->output_templates();

		# Force-initialize
		?>
		<script type="text/javascript">
		UltimateFields.initializeContainers()
		</script>
		<?php
	}

	/**
	 * Enqueues scripts when needed.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		# Post meta containers don't work in the front-end
		if( ! is_admin() || ! function_exists( 'get_current_screen' ) ) {
			return false;
		}

		# Check if the options page is being edited.
		$screen_id    = get_current_screen()->id;
		$combinations = array();

		foreach( $this->combinations as $combination ) {
			$enqueue = false;

			# Gather all post types
			foreach( $combination[ 'locations' ] as $location ) {
				if( ! $page = $location->get_page() ) {
					continue;
				}

				if( is_network_admin() && 'network' != $location->get_page()->get_type() )
					continue;

				$page_screen_id = $page->get_screen_id();
				if( 'network' == $location->get_page()->get_type() ) {
					$page_screen_id .= '-network';
				}

				if( $screen_id == $page_screen_id ) {
					$enqueue = true;
					break;
				}
			}

			# Check if the container is being displayed
			if( ! $enqueue ) {
				continue;
			}

			$combinations[] = $combination;
		}

		if( empty( $combinations ) ) {
			return;
		}

		# Ensure unique field names for the needed combinations
		$this->ensure_unique_field_names( $combinations );

		# Enqueue options scripts and then get container fields
		wp_enqueue_script( 'uf-container-options' );
		wp_enqueue_script( 'ultimate-fields-min' );
		foreach( $combinations as $combination ) {
			$combination[ 'container' ]->enqueue_scripts();
		}

		# Attach translations
		ultimate_fields()->l10n()->enqueue();

		return false;
	}

	/**
	 * Handles options saving.
	 *
	 * @since 3.0
	 */
	public function save( $page ) {
		$queue      = array();
		$datastores = array();

		# Add containers to the saving queue
		foreach( $this->combinations as $combination ) {
			$save_container = false;

			# Gather all post types
			foreach( $combination[ 'locations' ] as $location ) {
				if( $page == $location->get_page() ) {
					$save_container = true;

					$datastore = $location->get_datastore();
					$combination[ 'container' ]->set_datastore( $datastore );
					$datastores[] = $datastore;

					break;
				}
			}

			# Check if the container is being saved
			if( $save_container ) {
				$queue[] = $combination;
			}
		}

		# If there are no matching containers, don't proceed
		if( empty( $queue ) ) {
			return;
		}

		$this->ensure_unique_field_names( $queue );

		# This will hold all messages
		$errors = array();

		# Try saving each of the containers in the queue
		foreach( $queue as $combination ) {
			$container = $combination[ 'container' ];

			# Prepare the data for the container
			$data_key = 'uf_options_' . $container->get_id();
			$data = isset( $_POST[ $data_key ] )
				? json_decode( stripslashes( $_POST[ $data_key ] ), true )
				: array();

			# Save the container into the datastore and keep messages
			$errors = array_merge(
				$errors,
				$container->save( $data )
			);
		}

		# Save or die
		if( empty( $errors ) ) {
			foreach( $datastores as $datastore ) {
				$datastore->commit();
			}
		} else {
			$this->error( $errors );
		}
	}

	/**
	 * Returns the namespace for the API.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_rest_namespace() {
		static $namespace;

		if( is_null( $namespace ) ) {
			/**
			 * Allows the namespace for options in the API to be different in case
			 * it conflicts with any other plugin or library.
			 *
			 * @since 3.0
			 *
			 * @param string                 $namespace  The API namespace, used for options endpoints.
			 * @param Ultimate_Fields\Controller\Options $controller The controller that's adding the route.
			 * @return string
			 */
			$namespace = apply_filters( 'uf.rest_namespace', 'ultimate-fields/v2', $this );
		}

		return $namespace;
	}

	/**
	 * Creates additional API endpoints for all needed fields.
	 *
	 * @since 3.0
	 */
	public function rest_api_init() {
		$namespace = $this->get_rest_namespace();

		register_rest_route( $namespace, 'options', array(
			array(
				'methods'  => array( 'GET', 'POST' ),
				'callback' => array( $this, 'handle_options_route' ),
			)
		));
	}

	/**
	 * Handler for the options route.
	 *
	 * @since 3.0
	 *
	 * @param WP_REST_Request $request The request that is being handled.
	 * @return WP_REST_Response
	 */
	public function handle_options_route( $request ) {
		return 'POST' == $request->get_method()
			? $this->update_rest_options( $request )
			: $this->get_rest_options( $request );
	}

	/**
	 * Returns the fields, which are API-ready.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	protected function get_rest_fields() {
		# Gether all options
		$combos = array();

		foreach( $this->combinations as $combination ) {
			$fields = array();

			foreach( $combination[ 'locations' ] as $location ) {
				foreach( $location->get_api_fields() as $key => $value ) {
					if( is_int( $key ) ) {
						$key   = $value;
						$value = WP_REST_Server::READABLE;
					}

					$fields[ $key ] = $value;
				}
			}

			$combos[] = array(
				'container' => $combination[ 'container' ],
				'fields'    => $fields
			);
		}

		return $combos;
	}

	/**
	 * Reads out options for the API.
	 *
	 * @since 3.0
	 *
	 * @param WP_REST_Request $request The request that is being handled.
	 * @param WP_REST_Response|WP_Error
	 */
	public function get_rest_options( $request ) {
		$options = array();
		$api     = Data_API::instance();

		# Gether all options
		foreach( $this->get_rest_fields() as $combo ) {
			foreach( $combo[ 'fields' ] as $field => $access ) {
				$value = $api->get_value( $field, 'option' );

				if( is_object( $value ) && method_exists( $value, 'export' ) ) {
					$value = $value->export();
				}

				$options[ $field ] = $value;
			}
		}

		# Generate the response
		$namespace = $this->get_rest_namespace();
		$response = new WP_REST_Response( $options );
		$response->add_link( 'collection', rest_url( $namespace . '/options' ) );

		return $response;
	}

	/**
	 * Mass-updates REST options.
	 *
	 * @since 3.0
	 *
	 * @param WP_REST_Request $request The request that is being handled.
	 * @param WP_REST_Response
	 */
	public function update_rest_options( $request ) {
		$parameters = $request->get_params();
		$queue      = array();

		# Collect all editable options
		foreach( $this->get_rest_fields() as $combo ) {
			$container     = $combo[ 'container' ];

			foreach( $combo[ 'fields' ] as $field => $access ) {
				if( ! isset( $parameters[ $field ] ) ) {
					continue;
				}

				# Check for permissions
				if( ! $combo_checked && ! $container->check_user() ) {
					$message = __( 'You don\'t have sufficient access to do this.', 'ultimate-fields' );
					return new WP_Error( 'rest_mass_update_failed', $message, array(
						'status' => 500
					));
				}
				$combo_checked = true;

				if( $access === WP_REST_Server::EDITABLE ) {
					$queue[ $field ] = $parameters[ $field ];
					continue;
				}

				# Report issues
				$error   = 'rest_' . $field . '_update_failed';
				$message = __( 'Failed to update %s: The field is read-only.', 'ultimate-fields' );
				$message = sprintf( $message, $field );

				return new WP_Error( $error, $message, array(
					'status' => 500
				));
			}
		}

		# Check if there is anything to save
		if( empty( $queue ) ) {
			$message = __( 'Failed to update options: There are no writeable fields submitted.', 'ultimate-fields' );
			return new WP_Error( 'rest_mass_update_failed', $message, array(
				'status' => 500
			));
		}

		# Finally, saving time
		$api = Data_API::instance();
		foreach( $queue as $name => $value ) {
			$api->update_value( $name, $value, 'option' );
		}

		return new WP_REST_Response( array(
			'success' => true,
			'message' => __( 'The options were successfully updated.', 'ultimate-fields' )
		), 200 );
	}
}
