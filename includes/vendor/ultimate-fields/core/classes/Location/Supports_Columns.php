<?php
namespace Ultimate_Fields\Location;

use Ultimate_Fields\Admin_Column;

/**
 * Adds the necessary functionality to let a location have columns.
 *
 * @since 3.0
 */
trait Supports_Columns {
	/**
	 * The columns, which should be associated with the location.
	 *
	 * @since 3.0
	 * @var Ultimate_Fields\Admin_Column[]
	 */
	protected $admin_columns = array();

	/**
	 * Checks the passed arguments for columns.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $arguments The arguments to check.
	 */
	protected function check_args_for_columns( & $args ) {
		if( ! isset( $args[ 'admin_columns' ] ) )
			return;

		$this->add_admin_columns( $args[ 'admin_columns' ] );
		unset( $args[ 'admin_columns' ] );
	}

	/**
	 * Adds the necessary hooks for columns.
	 *
	 * @since 3.0
	 */
	abstract protected function init_admin_columns();

	/**
	 * Adds columns to the admin.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Admin_Column[] $columns An array of the columns to add.
	 * @return Supports_Columns
	 */
	public function add_admin_columns( $columns ) {
		foreach( $columns as $column ) {
			$this->admin_columns[] = $column;
		}

		return $this;
	}

	/**
	 * Returns all columns.
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\Admin_Column[]
	 */
	public function get_admin_columns() {
		return $this->admin_columns;
	}

	/**
	 * Changes the columns, associated with a certain content type.
	 *
	 * @since 3.0
	 *
	 * @param array $post_columns An array of column names.
	 * @return array
	 */
	public function change_columns( $columns ) {
		$customized = array();

		# Move the checkbox
		foreach( $columns as $key => $value ) {
			$customized[ $key ] = $value;
			break;
		}

		foreach( $this->admin_columns as $column ) {
			if( Admin_Column::PREPEND == $column->get_position() ) {
				$customized[ $column->get_name() ] = $column->get_label();
			}
		}

		foreach( $columns as $key => $name ) {
			$customized[ $key ] = $name;

			if( 'title' == $key || 'name' == $key ) {
				foreach( $this->admin_columns as $column ) {
					if( Admin_Column::AFTER_TITLE == $column->get_position() ) {
						$customized[ $column->get_name() ] = $column->get_label();
					}
				}
			}
		}

		foreach( $this->admin_columns as $column ) {
			if( Admin_Column::APPEND == $column->get_position() ) {
				$customized[ $column->get_name() ] = $column->get_label();
			}
		}

		return $customized;
	}

	/**
	 * Outputs the value of a column.
	 *
	 * @since 3.0
	 *
	 * @param string $column_name The name of the column to display.
	 * @param int    $item_id     The ID of the item that is being displayed.
	 * @return string
	 */
	public function render_column( $column_name, $item_id ) {
		$output = '-';

		foreach( $this->admin_columns as $column ) {
			if( $column->get_name() != $column_name ) {
				continue;
			}

			# Get the datastore and field needed
			$datastore = $this->create_datastore( $item_id );
			$field     = $column->get_field();

			# Load the value into the field
			$field->set_datastore( $datastore );
			$value = $field->get_value();

			# If there is a value, process and use it
			if( $value ) {
				$output = $column->output( $field->process( $field->handle( $value ) ), $item_id, $field, $this );
			}
		}

		return $output;
	}

	/**
	 * Changes the columns, which are sortable.
	 *
	 * @since 3.0
	 *
	 * @param string[] $columns The columns, which are already sortable.
	 * @return string[]
	 */
	public function change_sortable_columns( $columns ) {
		foreach( $this->admin_columns as $column ) {
			if( $column->is_sortable() ) {
				$columns[ $column->get_name() ] = $column->get_name();
			}
		}

		return $columns;
	}

	/**
	 * Changes an array of exportable (JSON) information to include column data.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $settings The settings where to include data.
	 */
	public function export_column_data( & $settings ) {
		if( empty( $this->admin_columns ) ) {
			return;
		}

		$columns = array();
		foreach( $this->admin_columns as $column ) {
			$columns[ $column->get_name() ] = $column->export();
		}

		$settings[ 'admin_columns' ] = $columns;
	}

	/**
	 * Imports column data (mainly from JSON).
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $args The data to check for column.
	 */
	protected function import_column_data( $args ) {
		if( ! isset( $args[ 'admin_columns' ] ) ) {
			return;
		}

		$columns = array();
		foreach( $args[ 'admin_columns' ] as $key => $data ) {
			$column = Admin_Column::create( $key );
			$column->import( $data );
			$columns[] = $column;
		}

		$this->add_admin_columns( $columns );
	}
}
