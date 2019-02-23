<?php
namespace Ultimate_Fields\Field;

/**
 * Handles the file field.
 *
 * @since 3.0
 */
class Image extends File {
	/**
	 * Holds the allowed file type.
	 *
	 * @since 3.0
	 * @var boolean
	 */
	protected $file_type = 'image';

	/**
	 * Holds the format required when using the_value().
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $output_type = 'image';

	/**
	 * Contains the image size which will be used for output.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $output_size = 'full';

	/**
	 * Enqueues the needed scripts for the field.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		parent::enqueue_scripts();

		wp_enqueue_script( 'uf-field-image' );
	}

	/**
	 * Changes the output type for the field.
	 *
	 * @since 3.0
	 *
	 * @param string $type The output type needed ('image', 'link', 'url', 'id').
	 * @return Ultimate_Fields\Field\Image
	 */
	public function set_output_type( $type ) {
		$this->output_type = $type;

		return $this;
	}

	/**
	 * Changes the image size, which will be used if output_type equals 'image'.
	 *
	 * @since 3.0
	 *
	 * @param string $size The name of th eimage sized.
	 * @return Ultimate_Fields\Field\Image
	 */
	public function set_output_size( $size ) {
		$this->output_size = $size;

		return $this;
	}

	/**
	 * Returns the output size of the field.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_output_size() {
		return $this->output_size;
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
			'image_output_type' => 'set_output_type',
			'image_size'        => 'set_output_size'
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
			'output_type' => array( 'image_output_type', 'image' ),
			'output_size' => array( 'image_size', 'full' ),
		));

		return $settings;
	}

	/**
	 * Proceses a single value based on the fields' settings.
	 *
	 * @since 3.0
	 *
	 * @param int $id The ID of the file to process.
	 * @return string
	 */
	protected function process_single_value( $id ) {
		switch( $this->output_type ) {
			case 'link':
				$url   = wp_get_attachment_url( $id );
				$image = wp_get_attachment_image( $id, $this->output_size );

				return sprintf(
					'<a href="%s">%s</a>',
					$url,
					$image
				);

			case 'image':
				return wp_get_attachment_image( $id, $this->output_size );

			default:
				return parent::process_single_value( $id );
		}
	}
}
