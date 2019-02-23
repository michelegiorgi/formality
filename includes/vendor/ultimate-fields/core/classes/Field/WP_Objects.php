<?php
namespace Ultimate_Fields\Field;

use Ultimate_Fields\Template;

/**
 * Extends the object field by allowing the selection of multiple objects within the same field.
 *
 * @since 3.0
 */
class WP_Objects extends WP_Object {
	/**
	 * Indicates that only a single item can be selected.
	 *
	 * @since 3.0
	 * @var bool
	 */
	protected $multiple = true;

	/**
	 * Holds the maximum amount of selectable items.
	 *
	 * @since 3.0
	 * @var int
	 */
	protected $max = 0;

	/**
	 * Holds the output format for the field.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $output_format = 'comma';

	/**
	 * Sets the maximum amount of items that can be added to the field.
	 *
	 * This only prevents additional items from being added, but does not
	 * limit the amount of already added items for existing fields.
	 *
	 * @since 3.0
	 *
	 * @param int $max Either a number as a limit or 0 as no limit.
	 * @return Ultimate_Fields\Field\Objects The instance of the field.
	 */
	public function set_max( $max ) {
		$this->max = max( 0, $max );

		return $this;
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

		$settings[ 'max' ] = $this->max;

		return $settings;
	}

	/**
	 * Prepares data for the field.
	 *
	 * Except the normal value of the field, this function will also retrieve the data about the
	 * options, which are already selected in order to prevent AJAX calls later.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export_data() {
		$value    = $this->datastore->get( $this->name );
		if( is_null( $value ) && $this->default_value ) $value = $this->default_value;
		$value = $value ? $value : array();
		$prepared = $this->export_objects( $value );

		return array(
			$this->name               => $value,
			$this->name . '_prepared' => $prepared
		);
	}

	/**
	 * Enqueues the scripts for the field.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		parent::enqueue_scripts();

		# Add objectS-specific JS
		wp_enqueue_script( 'uf-field-wp-objects' );

		# Add templates
		Template::add( 'objects-preview', 'field/objects-preview' );
		Template::add( 'objects-item-preview', 'field/objects-item-preview' );

		# Add translations
		ultimate_fields()->localize( 'objects-max', __( 'Maximum amount of %d items has already been reached!', 'ultimate-fields'  ) );
	}

	/**
	 * Sanitizes a value before saving it.
	 *
	 * @since 3.0
	 *
	 * @param mixed $value The value to sanitize.
	 * @return mixed
	 */
	public function sanitize( $value ) {
		return is_array( $value )
			? array_filter( $value )
			: array();
	}

	/**
	 * Changes the output format of the field when using the_value().
	 *
	 * @since 3.0
	 *
	 * @param string $format The format ('comma', 'ordered', 'unordered', 'paragraphs').
	 * @return Ultimate_Fields\Field\Objects
	 */
	public function set_output_format( $format ) {
		$this->output_format = $format;

		return $this;
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

		# Proxy the normal data
		$this->proxy_data_to_setters( $data, array(
			'wp_objects_output_format' => 'set_output_format',
			'wp_maximum_objects'       => 'set_max'
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
		$settings = parent::export();

		$this->export_properties( $settings, array(
			'output_format' => array( 'wp_objects_output_format', 'comma' ),
			'max'           => array( 'wp_maximum_objects', 0 )
		));

		return $settings;
	}

	/**
	 * Handles a value for the front-end.
	 *
	 * @since 3.0
	 *
	 * @param mixed                  $value  The value to parse.
	 * @param Ultimate_Fields\Helper\Data_Source $source The context of the value.
	 * @return mixed
	 */
	public function handle( $value, $source = null ) {
		// Without a value at all, there is nothing to process
		if( ! $value || ! is_array( $value ) ) {
			return false;
		}

		// Put in a queue
		$queue = array();
		$keys  = array();

		foreach( $value as $row ) {
			if( ! $row ) {
				continue;
			}

			// Extract the ID/type from the value
			if( $pair = $this->extract_pairs_from_value( $row ) ) {
				list( $item_type, $item_id ) = $pair;

				if( ! isset( $queue[ $item_type ] ) )
					$queue[ $item_type ] = array();

				$queue[ $item_type ][] = intval( $item_id );
				$keys[] = $row;
			} else {
				continue;
			}
		}

		// Load
		$unsorted = array();
		foreach( $this->get_types() as $type_name => $type ) {
			if( ! isset( $queue[ $type_name ] ) ) {
				continue;
			}

			$unsorted = array_merge( $unsorted, $type->export_items( $queue[ $type_name ] ) );
		}

		// Get back to the original sorting
		$sorted = array();
		foreach( $keys as $key ) {
			if( isset( $unsorted[ $key ] ) ) {
				$sorted[ $key ] = $unsorted[ $key ];
			}
		}

		// Cache
		foreach( $sorted as $key => $item ) {
			$this->item_cache[ $key ] = $item;
		}

		// Extract the basics
		$data = array();
		foreach( $sorted as $key => $item ) {
			$raw = $item->get_original();
			$raw->uf_object_key = $key;
			$data[] = $raw;
		}

		return $data;
	}

	/**
	 * Processes an already handled value to the format the field has for it's output.
	 *
	 * @since 3.0
	 *
	 * @param mixed $value The value to process.
	 * @return string
	 */
	public function process( $value ) {
		if( ! $value || ! is_array( $value ) ) {
			return '';
		}

		// Get the items to display
		$data = array();
		foreach( $value as $item ) {
			if( ! is_object( $item ) || ! property_exists( $item, 'uf_object_key' ) ) {
				continue;
			}

			$data[] = parent::process( $item );
		}

		// Merge
		switch( $this->output_format ) {
			case 'ordered':
				$output = '<ol><li>' . implode( '</li><li>', $data ) . '</li></ol>';
				break;
			case 'unordered':
				$output = '<ul><li>' . implode( '</li><li>', $data ) . '</li></ul>';
				break;
			case 'paragraphs':
				$output = implode( "\n", array_map( 'wpautop', $data ) );
				break;
			case 'comma':
			default:
				$output = implode( ', ', $data );
				break;
		}

		// All done
		return $output;
	}
}
