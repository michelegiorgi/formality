<?php
namespace Ultimate_Fields\Field;

use Ultimate_Fields\Field\File;

/**
 * Adds the necessary functionality for the audio field.
 *
 * @since 3.0
 */
class Audio extends File {
	/**
	 * Holds the allowed file type.
	 *
	 * @since 3.0
	 * @var boolean
	 */
	protected $file_type = 'audio';

	/**
	 * Holds the format required when using the_value().
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $output_type = 'link';

	/**
	 * Indicates if the field works with multiple files or not.
	 *
	 * @since 3.0
	 * @var bool
	 */
	protected $multiple = true;

	/**
	 * Filters out the IDs from a field's value.
	 *
	 * @since 3.0
	 *
	 * @param mixed $value The value that is being prepared.
	 * @return int[]
	 */
	public function extract_ids_from_value( $value ) {
		return $value;
	}

	/**
	 * Enqueues the needed scripts for the field.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		parent::enqueue_scripts();

		wp_enqueue_script( 'uf-field-audio' );
		wp_enqueue_style( 'wp-mediaelement' );
	}

	/**
	 * Changes the output type for the field.
	 *
	 * @since 3.0
	 *
	 * @param string $type The output type needed ('link', 'url', 'id', 'tag').
	 * @return Ultimate_Fields\Field\Audio
	 */
	public function set_output_type( $type ) {
		$this->output_type = $type;

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

		$this->proxy_data_to_setters( $data, array(
			'audio_output_type' => 'set_output_type'
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

		# Cleanup parent properties
		if( isset( $settings[ 'file_output_type' ] ) ) {
			unset( $settings[ 'file_output_type' ] );
		}

		$this->export_properties( $settings, array(
			'output_type' => array( 'audio_output_type', 'link' )
		));

		return $settings;
	}

	/**
	 * Processes a value for output in the front-end.
	 *
	 * @since 3.0
	 *
	 * @param mixed $value The value to use.
	 * @return string
	 */
	public function process( $value ) {
		if( ! $value || ! is_array( $value ) || empty( $value ) ) {
			return '';
		}

		$existing = get_posts( array(
			'post_type'      => 'attachment',
			'posts_per_page' => -1,
			'post__in'       => $value,
			'orderby'        => 'post__in',
			'order'          => 'ASC'
		));

		if( empty( $existing ) ) {
			return '';
		}

		$sources = array();
		foreach( $existing as $post ) {
			$sources[] = sprintf(
				'<source src="%s" type="%s">',
				wp_get_attachment_url( $post->ID ),
				get_post_mime_type( $post->ID )
			);
		}

		return sprintf(
			'<audio controls>%s</audio>',
			implode( "\n", $sources )
		);
	}
}
