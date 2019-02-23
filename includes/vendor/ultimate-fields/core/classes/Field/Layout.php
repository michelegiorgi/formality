<?php
namespace Ultimate_Fields\Field;

use Ultimate_Fields\Field;
use Ultimate_Fields\Field\Repeater;
use Ultimate_Fields\Template;
use Ultimate_Fields\Container\Layout_Group;
use Ultimate_Fields\Datastore\Group as Group_Datastore;
use Ultimate_Fields\Helper\Groups_Iterator;
use Ultimate_Fields\Helper\Layout_Group_Values;
use Ultimate_Fields\Helper\Layout_Rows_Iterator;

/**
 * Extends the repeater field by introducing the concept of columns.
 *
 * @since 3.0
 */
class Layout extends Repeater {
	/**
	 * Holds the amount of columns, which should be used for the field.
	 *
	 * @since 3.0
	 * @var int
	 */
	protected $columns = 12;

	/**
	 * Adds a group to the repeater.
	 *
	 * @since 3.0
	 *
	 * @param string|Layout_Group $group Either the ID of a group or a generated one.
	 * @param array               $args  Arguments for the group.
	 *                                   @see Ultimate_Fields\Container\Layout_Group::__construct().
	 * @return Ultimate_Fields\Field\Layout          The instance of the field.
	 */
	public function add_group( $group, $args = array() ) {
		# If the group has already been created, just use it.
		if( is_a( $group, Layout_Group::class ) ) {
			$this->groups[ $group->get_id() ] = $group;
		} else {
			$group = new Layout_Group( $group, $args );
			$this->groups[ $group->get_id() ] = $group;
		}

		return $this;
	}

	/**
	 * The layout fiel requires proper groups, so the quick syntax is disabled.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Field[] The fields to add to the group.
	 * @return Ultimate_Fields\Field\Repeater.
	 */
	public function add_fields( $fields ) {
		wp_die( 'Ultimate_Fields\\Field\\Layout does not support the short add_fields() syntax!' );
	}

	/**
	 * Enqueues the scripts for the field.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		parent::enqueue_scripts();

		wp_enqueue_script( 'uf-layout' );
		wp_enqueue_script( 'uf-field-layout' );

		# Add the necessary templates
		Template::add( 'layout', 'field/layout/base' );
		Template::add( 'layout-placeholder', 'field/layout/placeholder' );
		Template::add( 'layout-element-prototype', 'field/layout/element-prototype' );
		Template::add( 'layout-row', 'field/layout/row' );
		Template::add( 'layout-group', 'field/layout/group' );
	}

	/**
	 * Exports the settings of the field.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export_field() {
		$settings = parent::export_field();

		$settings[ 'columns' ]      = $this->columns;
		$settings[ 'chooser_type' ] = 'widgets';

		return $settings;
	}

	/**
	 * Exports the data of the field.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export_data() {
		$raw   = $this->get_value( $this->name );
		$value = array();

		# Use the default value if needed
		if( null === $raw && is_array( $this->default_value ) ) {
			$raw = $this->default_value;
		}

		# If there are groups, go through each of them.
		if( $raw ) foreach( $raw as $raw_row ) {
			$row = array();

			foreach( $raw_row as $raw_group ) {
				$datastore = new Group_Datastore( $raw_group );

				# Groups without type are ignored
				if( ! isset( $raw_group[ '__type' ] ) ) {
					continue;
				}

				# If the type of group is no longer available, skip
				if( ! isset( $this->groups[ $raw_group[ '__type' ] ] ) )
					continue;

				# Get the datastore and export data
				$group = $this->groups[ $raw_group[ '__type' ] ];
				$group->set_datastore( $datastore );

				$row[] = $group->export_data();
			}

			if( ! empty( $row ) ) {
				$value[] = $row;
			}
		}

		return array(
			$this->name => $value
		);
	}

	/**
	 * Allows the amount of columns within the field to be changed.
	 *
	 * @since 3.0
	 *
	 * @param int $columns The new amount of columns.
	 * @return Ultimate_Fields\Field\Layout The instance of the field.
	 */
	public function set_columns( $columns ) {
		$this->columns = intval( $columns );

		return $this;
	}

	/**
	 * Returns the amount of columns, used by the field.
	 *
	 * @since 3.0
	 *
	 * @return int
	 */
	public function get_columns() {
		return $this->columns;
	}


	/**
	 * Retrieves the value of the field from a source and saves it in the current datastore.
	 *
	 * This method should not perform any validation - if something is wrong with
	 * the value of the field, simply don't save it. Validation will be performed
	 * later and will return an error anyway, if the internal value is empty.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $source The source which the value of the field should be available in.
	 */
	public function save( $source ) {
		$value = array();

		if( isset( $source[ $this->name ] ) && is_array( $source[ $this->name ] ) ) {
			$raw = $source[ $this->name ];

			foreach( $raw as $raw_row ) {
				$row = array();

				foreach( $raw_row as $column ) {
					if( ! isset( $column[ '__type' ] ) || ! isset( $this->groups[ $column[ '__type' ] ] ) ) {
						continue;
					}

					$group = $this->groups[ $column[ '__type' ] ];
					$group->save( $column );
					$row[] = $group->get_datastore()->get_values();
				}

				$value[] = $row;
			}
		}

		$this->datastore->set( $this->name, $value );
	}

	/**
	 * Imports the field.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $data The data for the field.
	 */
	public function import( $data ) {
		Field::import( $data );

		foreach( $data[ 'layout_groups' ] as $raw ) {
			$id    = isset( $raw[ 'type' ] ) ? $raw[ 'type' ] : $raw[ 'name' ];
			$group = new Layout_Group( $id, $raw[ 'title' ] );

			if( isset( $raw[ 'fields_source' ] ) && 'container' == $raw[ 'fields_source' ] ) {
				// container
				$group->load_from( $raw[ 'container' ] );
			} else {
				// manual
				foreach( $raw[ 'fields' ] as $field ) {
					$group->add_field( $field );
				}
			}

			$group->proxy_data_to_setters( $raw, array(
				'minimum_width'    => 'set_min_width',
				'maximum_width'    => 'set_max_width',
				'maximum'          => 'set_maximum',
				'description'      => 'set_description',
				'title_background' => 'set_title_background',
				'title_color'      => 'set_title_color',
				'border_color'     => 'set_border_color',
				'icon'             => 'set_icon',
				'title_template'   => 'set_title_template'
			));

			$this->add_group( $group );
		}

		# Normal values
		$this->proxy_data_to_setters( $data, array(
			'layout_columns'          => 'set_columns',
			'layout_placeholder_text' => 'set_placeholder_text',
			'layout_background_color' => 'set_background_color'
		));
	}

	/**
	 * Generates the data for file exports.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export() {
		$settings = Field::export();

		$this->export_properties( $settings, array(
			'columns'          => array( 'layout_columns', 12 ),
			'placeholder_text' => array( 'layout_placeholder_text', null ),
			'background_color' => array( 'layout_background_color', '#fff' )
		));

		# Export groups
		$settings[ 'layout_groups' ] = array();
		foreach( $this->groups as $group ) {
			$settings[ 'layout_groups' ][] = $group->export();
		}

		return $settings;
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
		if( ! $value ) {
			return array();
		}

		$rows = array();
		foreach( $value as $row ) {
			$groups = array();

			foreach( $row as $raw_group ) {
				$group = null;

				if( isset( $raw_group[ '__type' ] ) ) {
					$type = $raw_group[ '__type' ];

					foreach( $this->groups as $g ) {
						if( $g->get_id() == $type ) {
							$group = $g;
							break;
						}
					}
				}

				if( is_null( $group ) ) {
					continue;
				}

				$groups[] = new Layout_Group_Values( $raw_group, $group );
			}

			if( ! empty( $groups ) ) {
				$rows[] = new Groups_Iterator( $groups );
			}
		}

		$iterator = new Layout_Rows_Iterator( $rows );
		$iterator->set_source( $source );

		return $iterator;
	}
}
