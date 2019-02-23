<?php
namespace Ultimate_Fields\Form_Object;

use Ultimate_Fields\Form_Object;
use Ultimate_Fields\Datastore\Options as Datastore;

/**
 * Handles options/setings in front-end forms.
 *
 * @since 3.0
 */
class Options extends Form_Object {
	/**
	 * Returns the keywords, which the class works with.
	 * Those can be used for the 'create_new' argument of uf_head().
	 *
	 * @since 3.0
	 *
	 * @return string[]
	 */
	public static function get_keywords() {
		return array(
			'options' => get_class()
		);
	}

	/**
	 * Creates a datastore that works with the given object.
	 *
	 * @since 3.0
	 *
	 * @return Datastore The datastore that should be used with the front-end container.
	 */
	public function get_datastore() {
		static $datastore;

		if( ! is_null( $datastore ) ) {
			return $datastore;
		} else {
			return $datastore = new Datastore;
		}
	}

	/**
	 * Saves the object if needed.
	 *
	 * @since 3.0
	 */
	public function save( $args = array() ) {
		# Options are generic, so there is no item to save
	}

	/**
	 * Returns the URL for the site as an internal object.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_url() {
		return home_url( '/' );
	}

	/**
	 * Returns the fields for the container.
	 *
	 * @since 3.0
	 *
	 * @param mixed $include Which fields to include?
	 */
	public function setup_fields( $include = 'all' ) {
		$this->fields = false;
	}
}
