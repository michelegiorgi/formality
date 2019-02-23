<?php
namespace Ultimate_Fields\Controller;

use Ultimate_Fields\Admin_Column;

/**
 * Contains the basic functionality for managing admin columns.
 *
 * @since 3.0
 */
trait Admin_Column_Manager {
	/**
	 * Goes through every combination of the container and adds column actions.
	 *
	 * @since 3.0
	 */
	public function initialize_admin_columns() {
		foreach( $this->combinations as $combination ) {
			$container = $combination[ 'container' ];

			foreach( $combination[ 'locations' ] as $location ) {
				$this->setup_admin_columns( $location, $container );
			}
		}
	}

	/**
	 * Sets up the columns of a location.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Location  $location  The location whose columns are needed.
	 * @param Ultimate_Fields\Container $container The container that the location belongs to.
	 */
	protected function setup_admin_columns( $location, $container ) {
		$columns = $location->get_admin_columns();

		if( empty( $columns ) ) {
			return;
		}

		$fields  = $container->get_fields();
		$location->init_admin_columns();

		foreach( $location->get_admin_columns() as $column ) {
			$field = $fields[ $column->get_name() ];

			if( ! $field ) {
				continue;
			}

			$column->set_field( $field );
		}
	}
}
