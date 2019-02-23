<?php
namespace Ultimate_Fields\Controller;

use Ultimate_Fields\Helper\Callback;
use WP_REST_Server;

/**
 * Contains helpers for the API.
 *
 * @since 3.0
 */
trait REST_API {
	/**
	 * Hooks the needed hooks for the rest API.
	 *
	 * @since 3.0
	 */
	protected function rest() {
		add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
	}

	/**
	 * Exposes all needed fields to the WordPress API.
	 *
	 * @since 3.0
	 */
	public function rest_api_init() {
		foreach( $this->combinations as $combination ) {
			foreach( $combination[ 'locations' ] as $location ) {
				$fields = $location->get_api_fields();

				if( empty( $fields ) ) {
					continue;
				}

				$container = $combination[ 'container' ];
				$this->register_rest_location( $location, $container, $fields );
			}
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
	abstract protected function register_rest_location( $location, $container, $fields );

	/**
	 * Adds the fields from a container to the API.
	 *
	 * @since 3.0
	 *
	 * @param  string        $endpoit   The name of the endpoint.
	 * @param  mixed[]       $fields    The fields that should be exposed.
	 * @param  Ultimate_Fields\Container $container The container the location belongs to.
	 */
	protected function add_fields_to_endpoint( $endpoint, $fields, $container ) {
		foreach( $fields as $key => $field ) {
			if( is_int( $key ) ) {
				$key   = $field;
				$value = WP_REST_Server::READABLE;
			} else {
				$value = $field;
			}

			$field = $container->get_field( $key );

			# Create a callback for retrieving the value
			$get_callback = new Callback( array( $this, 'read_field_value' ), array(
				'container' => $container,
				'field'     => $field
			));

			# Create a callback for retrieving the updated value
			$update_callback = new Callback( array( $this, 'save_field_value' ), array(
				'container'  => $container,
				'field'      => $field
			));

			# Prepare the final arguments
			$description = $field->get_description();
			$args = array(
				'get_callback'    => $get_callback->get_callback(),
				'schema'          => array(
					'description' => $description ? $description : $field->get_label(),
					'type'        => 'string'
				)
			);

			if( $value == WP_REST_Server::EDITABLE ) {
				$args[ 'update_callback' ] = $update_callback->get_callback();
			}

			# Change the value type if needed
			if( method_exists( $field, 'get_api_data_type' ) ) {
				$args[ 'schema' ][ 'type' ] = $field->get_api_data_type();
			}

			# Register the field
			register_rest_field( $endpoint, $key, $args );
		}
	}

	/**
	 * Reads the value of a field based on a location.
	 *
	 * @since 3.0
	 *
	 * @param  Ultimate_Fields\Callback $callback The callback that contains the field and etc.
	 * @param  mixed[]      $item     The item whose value is needed.
	 * @return mixed
	 */
	public function read_field_value( $callback, $post ) {
		$value = $this->get_api_value( $callback[ 'field' ], $post );

		if( is_object( $value ) && method_exists( $value, 'export' ) ) {
			$value = $value->export();
		}

		return $value;
	}

	/**
	 * Saves the value of a field when called through the REST API.
	 *
	 * @since 3.0
	 *
	 * @param  Ultimate_Fields\Callback $callback The callback that contains the field and etc.
	 * @param  mixed        $value    The value that should be saved.
	 * @param  object       $item     The native item that should be saved.
	 * @return mixed
	 */
	public function save_field_value( $callback, $value, $item ) {
		$ret = $this->save_api_value( $callback[ 'field' ], $value, $item );

		if( false !== $ret ) {
			return true;
		}

		$error   = 'rest_' . $callback[ 'field' ]->get_name() . '_failed';
		$message = __( 'Failed to update %s.', 'ultimate-fields' );
		$message = sprintf( $message, $callback[ 'field' ]->get_label() );
		return new WP_Error( $error, $message, array(
			'status' => 500
		));
	}

	/**
	 * Reads out a value from the data API.
	 *
	 * @since 3.0
	 *
	 * @param  Ultimate_Fields\Field $field The field whose value is needed.
	 * @param  mixed     $item  The API item.
	 * @return mixed
	 */
	abstract protected function get_api_value( $field, $item );

	/**
	 * Updates a REST value through the data API.
	 *
	 * @since 3.0
	 *
	 * @param  Ultimate_Fields\Field $field The field whose value is being saved.
	 * @param  mixed     $value The value to save.
	 * @param  mixed     $item  The item that the value should be associated with.
	 * @return bool
	 */
	abstract protected function save_api_value( $field, $value, $item );
}
