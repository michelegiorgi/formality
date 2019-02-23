<?php
namespace Ultimate_Fields\UI;

use Ultimate_Fields\Container\Repeater_Group;
use Ultimate_Fields\Admin_Column;
use Ultimate_Fields\Field;
use WP_REST_Server;

/**
 * Handles location definitions.
 *
 * @since 3.0
 */
abstract class Location {
	/**
	 * Indicate that the location can only be used once within a container.
	 *
	 * @since 3.0
	 * @var bool
	 */
	const LIMIT = false;

	/**
	 * Returns the type of the location (e.g. post_meta).
	 *
	 * @since 3.0
	 * @return string
	 */
	/* abstract */ public static function get_type() {
		return 'none';
	}

	/**
	 * Returns the name of the location.
	 *
	 * @since 3.0
	 * @return string
	 */
	/* abstract */ public static function get_name() {
		return '';
	}

	/**
	 * Returns the fields for the location.
	 *
	 * @since 3.0
	 * @return Ultimate_Fields\Field[]
	 */
	/* abstract */ public static function get_fields() {
		return array();
	}

	/**
	 * Exports the settings for the current location.
	 *
	 * @since 3.0
	 * @return Ultimate_Fields\Container\Repeater_Group
	 */
	public static function settings() {
		$type   = call_user_func( array( get_called_class(), 'get_type' ) );
		$name   = call_user_func( array( get_called_class(), 'get_name' ) );
		$fields = call_user_func( array( get_called_class(), 'get_fields' ) );

		$group = new Repeater_Group( $type );
		$group
			->set_title( $name )
			->add_fields( $fields )
			->set_layout( 'rows')
			->set_description_position( 'label' )
			->set_title_template( '' );

		return $group;
	}

	/**
	 * Returns the array data for a location.
	 * This method is used to extract the data from a container for the UI.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Location  $location The location of the core container.
	 * @param Ultimate_Fields\Container $container The container the location belogns to.
	 * @return mixed[]
	 */
	public static function get_location_data( $location, $container = null ) {
		foreach( Container_Settings::get_location_classes() as $class_name ) {
			if( ! method_exists( $class_name, 'get_settings_for_import' ) ) {
				continue;
			}

			$data = call_user_func( array( $class_name, 'get_settings_for_import' ), $location, $container );

			if( $data ) {
				return $data;
			}
		}

		return false;
	}

	/**
	 * Sets up REST API data to a certain core location.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Location $location The location that should be set up.
	 * @param mixed[]      $data     The data that will setup the location.
	 */
	protected static function setup_location_rest( $location, &$data ) {
		if( ! isset( $data[ 'expose_in_rest' ] ) || ! $data[ 'expose_in_rest' ] || empty( $data[ 'api_fields' ] ) )
			return;

		$fields = array();

		foreach( $data[ 'api_fields' ] as $field ) {
			$access = isset( $field[ 'editable' ] ) && $field[ 'editable' ]
				? WP_REST_Server::EDITABLE
				: WP_REST_Server::READABLE;

			$fields[ $field[ 'field_name' ] ] = $access;
		}

		$location->expose_api_fields( $fields );
	}

	/**
	 * Sets up columns for a location.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Location $location The location that should be set up.
	 * @param mixed[]      $data     The data that will setup the location.
	 */
	protected static function setup_location_columns( $location, &$data ) {
		if( ! isset( $data[ 'columns' ] ) || ! $data[ 'columns' ] )
			return;

		$columns = array();

		foreach( $data[ 'columns' ] as $raw ) {
			if( ! $raw[ 'field_name' ] ) {
				continue;
			}

			$column = Admin_Column::create( $raw[ 'field_name' ] );

			if( isset( $raw[ 'sortable' ] ) && $raw[ 'sortable' ] ) {
				$column->sortable();
			}

			if( isset( $raw[ 'position' ] ) ) {
				if( Admin_Column::PREPEND == $raw[ 'position' ] ) {
					$column->preprend();
				} elseif( Admin_Column::AFTER_TITLE == $raw[ 'position' ] ) {
					$column->append_after_title();
				}
			}

			$columns[] = $column;
		}

		$location->add_admin_columns( $columns );
	}

	/**
	 * Sets up customizer data for a location.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Location $location The location that should be set up.
	 * @param mixed[]      $data     The data that will setup the location.
	 */
	protected static function setup_location_customizer( $location, &$data ) {
		if( ! isset( $data[ 'show_in_customizer' ] ) || ! $data[ 'show_in_customizer' ] )
			return;

		$location->show_in_customizer();

		if( ! empty( $data[ 'customizer_fields' ] ) ) {
			$location->set_dynamic_fields( $data[ 'customizer_fields' ] );
		}
	}

	/**
	 * Returns customizer fields.
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\Field[]
	 */
	protected static function get_customizer_fields() {
		$fields = array();

		$fields[] = Field::create( 'tab', 'customizer', __( 'Customizer', 'ultimate-fields' ) )
			->set_icon( 'dashicons-admin-customizer' );

		$fields[] = Field::create( 'checkbox', 'show_in_customizer', __( 'Show in customizer', 'ultimate-fields' ) )
			->set_text( __( 'Show this container in the customizer when the customizer was opened from a post that this location applies to', 'ultimate-fields' ) )
			->fancy();

		$fields[] = Field::create( 'fields_selector', 'customizer_fields', __( 'postMessage fields', 'ultimate-fields' ) )
			->set_description( __( 'The selected fields will be sent to the page without refreshing.' ) )
			->add_dependency( 'show_in_customizer' );

		return $fields;
	}

	/**
	 * Returns rest API fields.
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\Field[]
	 */
	protected static function get_rest_fields() {
		$fields = array();

		$fields[] = Field::create( 'tab', 'rest_api_section', __( 'REST API', 'ultimate-fields' ) )
			->set_icon( 'dashicons-hammer' );

		$fields[] = Field::create( 'checkbox', 'expose_in_rest', __( 'Expose', 'ultimate-fields' ) )
			->set_text( __( 'Expose the values of this container to the REST API', 'ultimate-fields' ) )
			->fancy();

		$fields[] = Field::create( 'repeater', 'api_fields', __( 'API fields', 'ultimate-fields' ) )
			->set_layout( 'table' )
			->set_description( __( 'The selected fields will be exposed to the REST API.', 'ultimate-fields' ) )
			->add_dependency( 'expose_in_rest' )
			->add_group( 'field', array(
				'title'  => __( 'Field', 'ultimate-fields' ),
				'fields' => array(
					Field::create( 'field_selector', 'field_name', __( 'Field', 'ultimate-fields' ) )
						->set_width( 70 ),
					Field::create( 'checkbox', 'editable', __( 'Editable', 'ultimate-fields' ) )
						->set_default_value( false )
						->fancy()
						->set_text( __( 'Yes', 'ultimate-fields' ) )
						->set_width( 30 )
				)
			))
			->set_add_text( __( 'Add field', 'ultimate-fields' ) );

		return $fields;
	}

	/**
	 * Returns admin column fields.
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\Field[]
	 */
	protected static function get_column_fields() {
		$fields = array();

		$fields[] = Field::create( 'tab', 'columns_tab', __( 'Columns', 'ultimate-fields' ) )
			->set_icon( 'dashicons-exerpt-view' );

		$fields[] = Field::create( 'repeater', 'columns', __( 'Admin Columns', 'ultimate-fields' ) )
			->add_group( 'column', array(
				'title'  => __( 'Column', 'ultimate-fields' ),
				'fields' => array(
					Field::create( 'field_selector', 'field_name', __( 'Field', 'ultimate-fields' ) )
						->set_width( 50 ),
					Field::create( 'select', 'position', __( 'Position', 'ultimate-fields' ) )
						->set_default_value( '' . Admin_Column::APPEND )
						->add_options(array(
							'' . Admin_Column::PREPEND     => __( 'prepend', 'ultimate-fields' ),
							'' . Admin_Column::AFTER_TITLE => __( 'after title', 'ultimate-fields' ),
							'' . Admin_Column::APPEND      => __( 'append', 'ultimate-fields' ),
						))
						->set_width( 20 ),
					Field::create( 'checkbox', 'sortable', __( 'Sortable', 'ultimate-fields' ) )
						->set_default_value( false )
						->set_width( 30 )
						->set_text( __( 'Yes', 'ultimate-fields' ) )
						->fancy()
				)
			))
			->set_layout( 'table' )
			->set_placeholder_text( __( 'Please click the "Add column" button to add a new column.', 'ultimate-fields' ) )
			->set_add_text( __( 'Add column', 'ultimate-fields' ) )
			->set_description( __( "If you need to display additional columns on the post type listing, add them here.", 'ultimate-fields' ) );

		return $fields;
	}

	/**
	 * Exports data for the customizer from a location.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Location $Location The location to extract data from.
	 * @param mixed[]      $data     The data where values should be stored.
	 */
	protected static function import_customizer( $location, & $data ) {
		if( ! method_exists( $location, 'is_shown_in_customizer' ) || ! $location->is_shown_in_customizer() )
			return;

		$data[ 'show_in_customizer' ] = true;
		$data[ 'customizer_fields' ]  = $location->get_dynamic_fields();
	}

	/**
	 * Exports data for the REST API from a location.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Location $Location The location to extract data from.
	 * @param mixed[]      $data     The data where values should be stored.
	 */
	protected static function import_rest( $location, & $data ) {
		if( ! $fields = $location->get_api_fields() )
			return;

		$data[ 'expose_in_rest' ] = true;
		$data[ 'api_fields' ]     = array();

		foreach( $fields as $name => $editable ) {
			$data[ 'api_fields' ][] = array(
				'field_name' => $name,
				'editable'   => (bool) $editable,
				'__type'     => 'field'
			);
		}
	}

	/**
	 * Exports data for admin columns from a location.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Location $Location The location to extract data from.
	 * @param mixed[]      $data     The data where values should be stored.
	 */
	protected static function import_admin_columns( $location, & $data ) {
		if( !$location_columns = $location->get_admin_columns() )
			return;

		$columns = array();
		foreach( $location_columns as $column ) {
			$columns[] = array(
				'__type' => 'column',
				'field_name' => $column->get_name(),
				'sortable'   => $column->is_sortable(),
				'position'   => $column->get_position() . ''
			);
		}

		$data[ 'columns' ] = $columns;
	}
}
