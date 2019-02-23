<?php
namespace Ultimate_Fields\Field;

use Ultimate_Fields\Template;

/**
 * Handles links based on objects or manually entered ones.
 *
 * @since 3.0
 */
class Link extends WP_Object {
	/**
	 * Indicates whether the new tab control should be displayed or not.
	 *
	 * @since 3.0.2
	 */
	protected $target_control = true;

	/**
	 * Enqueues the scripts for the field.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		parent::enqueue_scripts();

		wp_enqueue_script( 'uf-field-link' );

		Template::add( 'link', 'field/link');
	}

	/**
	 * Adds additional data for JavaScript.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export_field() {
		$data = parent::export_field();
		$data['target_control'] = $this->target_control;
		return $data;
	}

	/**
	 * Formats the data, which will be exported for the field.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export_data() {
		$value    = $this->datastore->get( $this->name );
		$prepared = array();
		if( ! $value && $this->default_value ) $value = $this->default_value;

		if( is_array( $value ) ) {
			$value = array(
				'link'    => isset( $value[ 'link' ] ) && $value[ 'link' ] ? $value[ 'link' ] : false,
				'new_tab' => isset( $value[ 'new_tab' ] ) && $value[ 'new_tab' ]
			);

			if( preg_match( '~^\w+_\d+$~', $value[ 'link' ] ) ) {
				$prepared = $this->export_objects( array( $value[ 'link' ] ) );
			}
		} else {
			$value = array(
				'link'    => false,
				'new_tab' => false
			);
		}

		return array(
			$this->name => $value,
			$this->name . '_prepared' => $prepared
		);
	}

	/**
	 * Locates the link of a singular object.
	 *
	 * @since 3.0
	 *
	 * @param string $object The object to locate.
	 * @return mixed
	 */
	public function locate_object_link( $item ) {
		if( ! @preg_match( '~^\w+_\d+$~', $item ) ) {
			return $item;
		}

		# Extract the type and id
		list( $item_type, $item_id ) = explode( '_', $item );

		$types = $this->get_types();
		if( ! isset( $types[ $item_type ] ) ) {
			return false;
		}

		$type = $types[ $item_type ];
		return $type->get_item_link( $item_id );
	}

	/**
	 * Indicates if the field can handle a certain key.
	 *
	 * Lets the field wotk both with the normal key and with {$key}_target.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Helper\Data_Source $source The source for retrieving the value.
	 * @return bool
	 */
	public function can_handle( $source ) {
		if( $source->name == $this->name || $source->name == $this->name . '_target' ) {
			return $this->name;
		}

		return false;
	}

	/**
	 * Handles a value for the front-end (without displaying it).
	 *
	 * @since 3.0
	 *
	 * @param mixed                  $value  The value to parse.
	 * @param Ultimate_Fields\Helper\Data_Source $source The context of the value.
	 * @return mixed
	 */
	public function handle( $value, $source = null ) {
		if( ! $value || ! is_array( $value ) ) {
			return false;
		}

		return array(
			'link'    => $this->locate_object_link( $value[ 'link' ] ),
			'new_tab' => $value[ 'new_tab' ]
		);
	}

	/**
	 * Processes a value for the_value() and get_the_value().
	 *
	 * @since 3.0
	 *
	 * @param mixed                  $value  The value to process.
	 * @param Ultimate_Fields\Helper\Data_Source $source The context of the value.
	 * @return mixed
	 */
	public function process( $value, $source = null ) {
		# Get the value from the local datastore if possible
		if( ! $value || ! is_array( $value ) ) {
			return '';
		}

		if( $this->name . '_target' == $source->name ) {
			return $value[ 'new_tab' ] ? '_blank' : '_self';
		} else {
			# Just get the link
			return $value[ 'link' ];
		}
	}

	/**
	 * Enables the target control.
	 *
	 * @since 3.0.2
	 *
	 * @return Link
	 */
	public function show_target_control() {
		$this->target_control = true;
		return $this;
	}

	/**
	 * Disables the target control.
	 *
	 * @since 3.0.2
	 *
	 * @return Link
	 */
	public function hide_target_control() {
		$this->target_control = false;
		return $this;
	}

	/**
	 * Imports the field.
	 *
	 * @since 3.0.2
	 *
	 * @param mixed[] $data The data for the field.
	 */
	public function import( $data ) {
		parent::import( $data );

		if( isset( $data[ 'link_target_control' ] ) && ! $data[ 'link_target_control' ] ) {
			$this->hide_target_control();
		}
	}

	/**
	 * Generates the data for file exports.
	 *
	 * @since 3.0.2
	 *
	 * @return mixed[]
	 */
	public function export() {
		$settings = parent::export();

		if( ! $this->target_control ) {
			$settings['link_target_control'] = false;
		}

		return $settings;
	}
}
