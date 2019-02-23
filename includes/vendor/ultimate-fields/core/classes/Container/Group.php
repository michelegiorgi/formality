<?php
namespace Ultimate_Fields\Container;

use Ultimate_Fields\Container;
use Ultimate_Fields\Datastore\Group as Datastore;

/**
 * Handles groups within repeaters.
 *
 * @since 3.0
 */
class Group extends Container {
    protected $layout = 'grid';

    /**
     * Adds the appropriate actions.
     *
     * @since 3.0
     *
     * @param string $id   The ID for the group.
     * @param array  $args Arguments for creating the group.
     */
    public function __construct( $id, $args = array() ) {
        parent::__construct( $id, $args );
        $this->datastore = new Datastore();
    }

    /**
     * Exports the data of the group, including type and visiblity.
     *
     * @since 3.0
     *
     * @return array
     */
    public function export_data() {
        $data = parent::export_data();

        $data[ '__type' ]   = $this->id;
        $data[ '__hidden' ] = (bool) $this->datastore->get( '__hidden' );

        return $data;
    }

	/**
	 * Exports the group for JSON/PHP.
	 *
	 * @since 3.0
	 * 
	 * @return array
	 */
    public function export() {
        $settings = parent::export();

        $settings[ 'type' ] = $this->id;
        unset( $settings[ 'id' ] );

        return $settings;
    }

	/**
	 * Performs an AJAX call based on a specific item.
	 *
	 * @since 3.0
	 *
	 * @param mixed $item The item that is being changed.
	 */
	public function perform_ajax( $item, $action ) {
		foreach( $this->get_fields() as $field ) {
			$field->perform_ajax( $action, $item );
		}
	}
}
