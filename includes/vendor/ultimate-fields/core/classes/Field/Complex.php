<?php
namespace Ultimate_Fields\Field;

use Ultimate_Fields\Field;
use Ultimate_Fields\Container;
use Ultimate_Fields\Container\Complex_Group;
use Ultimate_Fields\Datastore;
use Ultimate_Fields\Datastore\Group as Group_Datastore;
use Ultimate_Fields\Datastore\Complex as Complex_Datastore;
use Ultimate_Fields\Template;
use Ultimate_Fields\Helper\Complex_Values;

/**
 * Allows multiple fields to be displayed within a single input, allowing the reusal of fields.
 *
 * The complex field is similar to the repeater field, but displays the fields just once. This allows the
 * usage of a group of fields in multiple instances, even in the same container. Additionally, in a container
 * with rows layout, the complex field allows grid fields to be used in order to allow more flexible and
 * intuitive layouts.
 *
 * @since 3.0
 */
class Complex extends Field {
	/**
	 * This is a group with fields that functions within the field.
	 *
	 * @since 3.0
	 * @var Ultimate_Fields\Container\Complex_Group
	 */
	protected $group;

	/**
	 * Holds the technique that is used when saving and reading data. See the next constants for details.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $save_mode = 'array';

	/**
	 * Indicates that the values of the sub-fields of this field should be saved as an array.
	 *
	 * @since 3.0
	 * @var string
	 */
	const ARRAY_MODE = 'array';

	/**
	 * Indicates that the values of the sub-fields of this field should be saved as if the fields lived
	 * on the same level as the current field.
 	 *
 	 * @since 3.0
 	 * @var string
 	 */
 	const MERGE_MODE = 'merge';

	/**
	 * When in MERGE_MODE, this prefix will allow multiple complex field to be used with in the same
	 * container and to contain the same sub-fields.
	 *
	 * @since 3.
	 * @var string
	 */
	protected $prefix = '';

	/**
	 * Holds the layout, which will be used for the internal group.
	 *
	 * @see Ultimate_Fields\Container->layout
	 * @var string
	 */
	protected $layout = 'grid';

	/**
	 * Indicates if the sub-fields should be loaded from another container.
	 *
	 * @since 3.0
	 * @var bool
	 */
	protected $load_from_container = false;

	/**
	 * Holds the hash/ID of the container that fields will be loaded from.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $load_from;

	/**
	 * Holds the internal datastore, once generated.
	 *
	 * @since 3.0
	 * @var Ultimate_Fields\Datastore
	 */
	protected $internal_datastore;

	/**
	 * Merges the fields from the complex field into the same level as the field.
	 *
	 * @since 3.0
	 *
	 * @param string $prefix An optional prefix to use.
	 * @return Ultimate_Fields\Field\Complex
	 */
	public function merge( $prefix = '' ) {
		$this->save_mode = self::MERGE_MODE;
		$this->prefix    = $prefix;

		return $this;
	}

	/**
	 * Allows the prefix to be changed when in merge mode.
	 *
	 * @since 3.0
	 *
	 * @param string $prefix The prefix.
	 * @return Ultimate_Fields\Field\Complex
	 */
	public function set_prefix( $prefix ) {
		$this->prefix = $prefix;

		return $this;
	}

	/**
	 * Adds fields to the complex group.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $fields The sub-fields to add.
	 * @return Ultimate_Fields\Field\Complex
	 */
	public function add_fields( $fields ) {
		$group = $this->get_group();

		foreach( $fields as $field ) {
			$group->add_field( $field );
		}

		return $this;
	}

	/**
	 * Instruct the field to load an existing container.
	 *
	 * @since 3.0
	 *
	 * @param string $id The ID of the container to use.
	 * @return Ultimate_Fields\Field\Complex
	 */
	public function load_from_container( $id ) {
		$this->load_from_container = true;
		$this->load_from = $id;

		return $this;
	}

	/**
	 * Returns the group that works with the sub-fields.
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\Container\Group
	 */
	public function get_group() {
		if( ! is_null( $this->group ) ) {
			return $this->group;
		}

		if( $this->load_from_container ) {
			$group = false;

			foreach( Container::get_registered() as $container ) {
				if( $container->get_id() == $this->load_from ) {
					$group = new Complex_Group( $container->get_id() );
					$group->add_fields( $container->get_fields() );
				}
			}
		} else {
			$group = new Complex_Group( 'complex_group' );
		}

		if( ! $group ) {
			return $this->group = false;
		}

		$group->set_layout( $this->layout );
		return $this->group = $group;
	}

	/**
	 * Exports the field for usage by JS.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export_field() {
		$settings = parent::export_field();

		$group = $this->get_group();
		if( $group ) {
			$settings[ 'group' ] = $group->export_settings();
		} else {
			$settings[ 'group' ] = false;
		}

		return $settings;
	}

	/**
	 * Sets the datastore that is used for sub-values.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Datastore $datastore The datastore to use.
	 */
	public function set_datastore( Datastore $datastore ) {
		parent::set_datastore( $datastore );
		$this->internal_datastore = null;
	}

	/**
	 * Based on the current mode, sets up a proper datastore.
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\Datastore
	 */
	protected function get_internal_datastore() {
		if( ! is_null( $this->internal_datastore ) ) {
			return $this->internal_datastore;
		}

		# Start a datastore up
		if( self::MERGE_MODE == $this->save_mode ) {
			$datastore = new Complex_Datastore;
			$datastore->set_datastore( $this->datastore );
			$datastore->set_prefix( $this->prefix );
		} else {
			$datastore = new Group_Datastore( $this->get_value( $this->name ) );
		}

		# Save a handle and return
		return $this->internal_datastore = $datastore;
	}

	/**
	 * Proxies the values of the internal datastore to the parent one.
	 *
	 * @since 3.0
	 */
	protected function save_internal_datastore() {
		$datastore = $this->get_internal_datastore();

		if( self::MERGE_MODE == $this->save_mode ) {
			$datastore->save();
		} else {
			$this->datastore->set( $this->name, $datastore->get_values() );
		}
	}

	/**
	 * Saves the data of the field.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $source The source to check for values.
	 */
	public function save( $source ) {
		# Isolate the source to the current field
		$source = isset( $source[ $this->name ] ) ? $source[ $this->name ] : array();

		# Get the right datastore
		$datastore = $this->get_internal_datastore();

		# Put the values into a datastore
		if( $this->get_group() ) {
			foreach( $this->get_group()->get_fields() as $field ) {
				$field->set_datastore( $datastore );
				$field->save( $source );
			}
		}

		# Save
		$this->save_internal_datastore();
	}

	/**
	 * Exports the values of sub-fields to send to JavaScript.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export_data() {
		$datastore = $this->get_internal_datastore();

		# Go through fields and gather values
		$data = array();

		if( $this->group ) {
			foreach( $this->group->get_fields() as $field ) {
				$field->set_datastore( $datastore );
				$data = array_merge( $data, $field->export_data() );
			}
		}

		return array(
			$this->name => $data
		);
	}

	/**
	 * Enqueues the templates for the field.
	 *
	 * @since 3.0
	 */
	public function templates() {
		Template::add( 'complex-group', 'field/complex-group' );
	}

	/**
	 * Instruct the field to use grid layout.
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\Field\Complex
	 */
	public function grid_layout() {
		$this->layout = 'grid';

		if( ! is_null( $this->group ) ) {
			$this->group->grid_layout();
		}

		return $this;
	}

	/**
	 * Instruct the field to use rows layout.
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\Field\Complex
	 */
	public function rows_layout() {
		$this->layout = 'rows';

		if( ! is_null( $this->group ) ) {
			$this->group->rows_layout();
		}

		return $this;
	}

	/**
	 * Changes the layout of the group within the field.
	 *
	 * @since 3.0
	 *
	 * @param string $layout The layout to use.
	 * @return Ultimate_Fields\Field\Complex
	 */
	public function set_layout( $layout ) {
		$this->layout = $layout;

		if( ! is_null( $this->group ) ) {
			$this->group->set_layout( $layout );
		}

		return $this;
	}

	/**
	 * Enqueues the scripts for the field.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		if( $this->get_group() ) {
			$this->get_group()->enqueue_scripts();
		} else {
			ultimate_fields()->localize( 'complex-no-group', __( 'The group with the sub-fields of this field is missing.', 'ultimate-fields' ) );
		}

		wp_enqueue_script( 'uf-field-complex' );
	}

	/**
	 * Imports the field.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $data The data for the field.
	 */
	public function import( $data ) {
		parent::import( $data );

		# Load the fields/source
		if( isset( $data[ 'complex_fields_source' ] ) && 'container' == $data[ 'complex_fields_source' ] ) {
			# Load another container
			$this->load_from_container = true;
			$this->load_from = $data[ 'complex_container' ];
		} else {
			# Add manual fields
			$this->add_fields( $data[ 'complex_fields' ] );
		}

		# Merge if needed, add the prefix
		if( isset( $data[ 'complex_save_mode' ] ) && self::MERGE_MODE == $data[ 'complex_save_mode' ] ) {
			$prefix = '';

			if( isset( $data[ 'complex_prefix' ] ) && $data[ 'complex_prefix' ] ) {
				$prefix = $data[ 'complex_prefix' ];
			}

			$this->merge( $prefix );
		}

		# Change the layout
		if( isset( $data[ 'complex_layout' ] ) ) {
			$this->set_layout( $data[ 'complex_layout' ] );
		}
	}

	/**
	 * Generates the data for file exports.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export() {
		$settings = parent::export();

		# Save the source for the fields
		if( $this->load_from_container ) {
			$settings[ 'complex_fields_source' ] = 'container';
			$settings[ 'complex_container' ] = $this->load_from;
		} else {
			$settings[ 'complex_fields_source' ] = 'manual';
			$settings[ 'complex_fields' ]        = array();

			foreach( $this->group->get_fields() as $field ) {
				$settings[ 'complex_fields' ][] = $field->export();
			}
		}

		# Add merge mode if needed
		if( self::MERGE_MODE == $this->save_mode ) {
			$settings[ 'complex_save_mode' ] = self::MERGE_MODE;

			if( $this->prefix ) {
				$settings[ 'complex_prefix' ] = $this->prefix;
			}
		}

		# Change the layout
		if( 'rows' == $this->layout ) {
			$settings[ 'complex_layout' ] = 'rows';
		}

		return $settings;
	}

	/**
	 * Indicates if the field can handle a certain key.
	 *
	 * @since 3.0
	 *
	 * @param mixed                  $value  The value that is to be handled.
	 * @param Ultimate_Fields\Helper\Data_Source $source The source for retrieving the value.
	 * @return bool
	 */
	public function can_handle( $source ) {
		if( self::MERGE_MODE == $this->save_mode ) {
			return (bool) $this->get_group()->get_fields()[ $source->name ];
		} else {
			return $this->name == $source->name;
		}
	}

	/**
	 * Handles a value for the front-end.
	 *
	 * @since 3.0
	 *
	 * @param mixed $value The value to handle.
	 * @param mixed $source The context of the value.
	 * @return mixed
	 */
	public function handle( $value, $source = null ) {
		if( self::MERGE_MODE == $this->save_mode ) {
			return $value;
		} else {
			if( is_array( $value ) && ! empty( $value ) ) {
				return new Complex_Values( $value, $this->get_group() );
			} else {
				return false;
			}
		}
	}

	/**
	 * Performs AJAX.
	 *
	 * @since 3.0
	 *
	 * @param string $action The action that is being performed.
	 * @param mixed  $item   The item that is being edited.
	 */
	public function perform_ajax( $action, $item ) {
		if( $this->get_group() ) {
			$this->get_group()->perform_ajax( $item, $action );			
		}
	}
}
