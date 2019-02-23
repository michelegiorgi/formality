<?php
namespace Ultimate_Fields\Controller;

use Ultimate_Fields\Controller;
use Ultimate_Fields\Template;
use Ultimate_Fields\Data_API;
use Ultimate_Fields\Datastore\Post_Meta as Datastore;
use Ultimate_Fields\Location\Attachment as Attachment_Location;
use Ultimate_Fields\Controller\REST_API;

/**
 * Handles the media locations.
 *
 * @since 3.0
 */
class Attachment extends Controller {
	use REST_API;

	/**
	 * Adds the neccessary hooks for the controller.
	 *
	 * @since 3.0
	 */
	protected function __construct() {
		add_action( 'print_media_templates', array( $this, 'display' ) );
		add_action( 'wp_prepare_attachment_for_js', array( $this, 'prepare_attachment' ), 10, 3 );
		add_action( 'attachment_updated', array( $this, 'save' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_enqueue_media', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_enqueue_media', array( $this, 'do_ajax' ) );
		$this->rest();
	}

	/**
	 * Loads all templates, scripts and settings for containers.
	 *
	 * @since 3.0
	 */
	public function display() {
		foreach( $this->combinations as $combination ) {
			$this->render_combination( $combination );
		}

		# Register the neccessary templates
		Template::add( 'attachment', 'container/attachment' );
		Template::add( 'field-attachment', 'field/wrap/attachment' );
		Template::add( 'controller-attachment', 'attachment-controller' );
		Template::add( 'attachment-expand', 'attachment-expand' );
		Template::add( 'attachment-warning', 'attachment-warning' );

		# Media is enqueued late in the footer, so we need to force template output
		Template::instance()->output_templates();
	}

	/**
	 * Loads all templates, scripts and settings for a container.
	 * This function only outputs the default container settings, no data here.
	 *
	 * @since 3.0
	 *
	 * @param Container $container The container to display.
	 */
	public function render_combination( $combination ) {
		$container = $combination[ 'container' ];
		$settings = $container->export_settings();

		$locations = array();
		foreach( $combination[ 'locations' ] as $location ) {
			$locations[] = $location->export_settings();
		}
		$settings[ 'locations' ] = $locations;
		$settings[ 'layout' ]    = 'rows';

		echo sprintf(
            '<script class="uf-attachment-container-settings" type="text/json">%s</script>',
            json_encode( $settings )
        );
	}

	/**
	 * Prepares the needed data for an attachment.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $response   The response that is being prepared.
	 * @param WP_Post $attachment An attachment whose response is prepared.
	 * @param mixed   $meta       The metadata about the attachment.
	 * @return mixed[]
	 */
	public function prepare_attachment( $response, $attachment, $meta ) {
		# Create a datastore for the containers
		$datastore = new Datastore();
		$datastore->set_id( $attachment->ID );

		# Go through each combination and add data
		foreach( $this->combinations as $combination ) {
			$container = $combination[ 'container' ];
			$container->set_datastore( $datastore );
			$data = array();

			foreach( $container->get_fields() as $field ) {
				$data = array_merge( $data, $field->export_data() );
			}

			$response[ 'uf_data_' . $container->get_id() ] = $data;
		}

		return $response;
	}

	/**
	 * Enqueues scripts when needed.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		# No scripts are needed if there are no media models
		if( ! wp_script_is( 'media-models' ) ) {
			return;
		}

		# Ensure unique field names
		$this->ensure_unique_field_names();

		wp_enqueue_script( 'uf-container-attachment' );

		# Enqueue individual scripts
		foreach( $this->combinations as $combination ) {
			$combination[ 'container' ]->enqueue_scripts();
		}

		# Attach translations
		// ultimate_fields()->l10n()->enqueue();

		return false;
	}

	/**
	 * Handles media saving.
	 *
	 * @since 3.0
	 *
	 * @param int $post_id The ID of the attachment that is being saved.
	 */
	public function save( $post_id ) {
		# Maybe there are no changes
		if( ! isset( $_POST[ 'changes' ] ) )
			return;

		$mime_type = get_post_mime_type( $post_id );

		# Ensure unique field names
		$this->ensure_unique_field_names();

		# All values will be saved in the same datastore
		$datastore = new Datastore;
		$datastore->set_id( $post_id );

		# This will hold all messages
		$errors = array();

		# Try saving each of the containers in the queue
		foreach( $this->combinations as $combination ) {
			$container = $combination[ 'container' ];
			$supported = false;

			foreach( $combination[ 'locations' ] as $location ) {
				if( $location->supports_mime_type( $mime_type ) ) {
					$supported = true;
					break;
				}
			}

			if( ! $supported ) {
				continue;
			}

			# Prepare the data for the container
			$data_key = 'uf_data_' . $container->get_id();

			if( ! isset( $_POST[ 'changes' ][ $data_key ] ) ) {
				continue;
			}

			$data = json_decode( stripslashes( $_POST[ 'changes' ][ $data_key ] ), true );

			# Set the datastore to the container and fields
			$container->set_datastore( $datastore );

			# Save the container into the datastore and keep messages
			$errors = array_merge(
				$errors,
				$container->save( $data )
			);
		}

		# Save or die
		if( empty( $errors ) ) {
			$datastore->commit();
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
		$this->add_fields_to_endpoint( 'attachment', $fields, $container );
	}

	/**
	 * Reads out a value from the data API.
	 *
	 * @since 3.0
	 *
	 * @param  Ultimate_Fields\Field $field The field whose value is needed.
	 * @param  mixed     $item  The API item (post).
	 * @return mixed
	 */
	protected function get_api_value( $field, $item ) {
		return Data_API::instance()->get_value( $field->get_name(), 'attachment_' . $item[ 'id' ] );
	}

	/**
	 * Updates a REST value through the data API.
	 *
	 * @since 3.0
	 *
	 * @param  Ultimate_Fields\Field $field The field whose value is being saved.
	 * @param  mixed     $value The value to save.
	 * @param  WP_Post   $item  The item that the value should be associated with.
	 * @return bool
	 */
	protected function save_api_value( $field, $value, $item ) {
		return Data_API::instance()->update_value( $field->get_name(), $value, 'attachment_' . $item->ID );
	}

	/**
	 * Performs AJAX for media items.
	 *
	 * @since 3.0
	 */
	public function do_ajax() {
		ultimate_fields()->ajax( Attachment_Location::WORKS_WITH_KEYWORD );
	}
}
