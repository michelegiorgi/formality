<?php
namespace Ultimate_Fields\Field;

use Ultimate_Fields\Template;
use Ultimate_Fields\Field;
use Ultimate_Fields\Field\File;

/**
 * Handles video fields.
 *
 * @since 3.0
 */
class Video extends File {
	/**
	 * Holds the allowed file type.
	 *
	 * @since 3.0
	 * @var boolean
	 */
	protected $file_type = 'video';

	/**
	 * Holds the output width for the video tag on the front-end.
	 *
	 * @since 3.0
	 * @var int
	 */
	protected $output_width = 1280;

	/**
	 * Holds the output height for the video tag on the front-end.
	 *
	 * @since 3.0
	 * @var int
	 */
	protected $output_height = 720;

	/**
	 * Filters out the IDs from a field's value.
	 *
	 * @since 3.0
	 *
	 * @param mixed $value The value that is being prepared.
	 * @return int[]
	 */
	public function extract_ids_from_value( $value ) {
		$ids = array();

		if( ! isset( $value[ 'videos' ] ) ) {
			$value = array(
				'videos' => (array) $value,
				'poster' => false
			);
		}

		if( $value[ 'videos' ] ) {
			$ids = $value[ 'videos' ];
		}

		if( $value[ 'poster' ] ) {
			$ids[] = $value[ 'poster' ];
		}

		return $ids;
	}

	/**
	 * Based on the current value of the field this will convert an array of
	 * JS-prepared attachments into a value that should be sent to JavaScript.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $items The items that are preloaded for the field.
	 * @return mixed
	 */
	protected function generate_value_from_attachments( $items ) {
		$result = array(
			'videos' => array(),
			'poster' => false
		);

		$value = $this->get_value();

		if( is_array( $value ) ) {
			if( isset( $value[ 'videos' ] ) && is_array( $value[ 'videos' ] ) ) {
				foreach( $value[ 'videos' ] as $id ) {
					foreach( $items as $item ) {
						if( $id == $item[ 'id' ] ) {
							$result[ 'videos' ][] = $id;
						}
					}
				}
			}

			if( isset( $value[ 'poster' ] ) && intval( $value[ 'poster' ] ) ) {
				foreach( $items as $item ) {
					if( $item[ 'id' ] == $value[ 'poster' ] ) {
						$result[ 'poster' ] = $value[ 'poster' ];
						break;
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Enqueues the needed scripts for the field.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		parent::enqueue_scripts();

		wp_enqueue_script( 'uf-field-video' );
		wp_enqueue_style( 'wp-mediaelement' );

		# Add a template for the video preview
		Template::add( 'video', 'field/video' );

		# Add translations
		ultimate_fields()
			->localize( 'video-add-poster',    __( 'Add poster', 'ultimate-fields' ) )
			->localize( 'video-change-poster', __( 'Change poster', 'ultimate-fields' ) )
			->localize( 'video-remove-poster', __( 'Remove poster', 'ultimate-fields' ) )
			->localize( 'video-select-files',  __( 'Select videos', 'ultimate-fields' ) )
			->localize( 'clear',               __( 'Clear', 'ultimate-fields' ) )
			->localize( 'video-select-poster', __( 'Select poster', 'ultimate-fields' ) );
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
			'video_output_width'  => 'set_output_width',
			'video_output_height' => 'set_output_height'
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
			'output_width' => array( 'video_output_width', 1280 ),
			'output_height' => array( 'video_output_height', 720 )
		));

		return $settings;
	}

	/**
	 * Changes the output width for the video tag on the front-end.
	 *
	 * @since 3.0
	 *
	 * @param int
	 * @return Ultimate_Fields\Field|Video
	 */
	public function set_output_width( $width ) {
		$this->output_width = $width;
		return $this;
	}

	/**
	 * Changes the output height for the video tag on the front-end.
	 *
	 * @param int
	 * @return Ultimate_Fields\Field|Video
	 */
	public function set_output_height( $height ) {
		$this->output_height = $height;
		return $this;
	}

	/**
	 * When the data API is being used, this method will "handle" a value.
	 *
	 * @param  mixed       $value  The raw value.
	 * @param  Data_Source $source The data source that the value is associated with.
	 * @return mixed
	 */
	public function handle( $value, $source = null ) {
		$value   = Field::handle( $value, $source );
		$checked = array(
			'poster' => false,
			'videos' => array()
		);

		if( is_array( $value ) ) {
			if( isset( $value['poster'] ) && $value['poster'] ) {
				$checked['poster'] = intval( $value['poster'] );
			}

			foreach( $value['videos'] as $id ) {
				if( $id ) {
					$checked['videos'][] = intval( $id );
				}
			}
		}

		return $checked;
	}

	/**
	 * Prepares teh value of the field for display.
	 *
	 * @since 3.0
	 *
	 * @param mixed $value The video data.
	 * @return string
	 */
	public function process( $value ) {
		if( empty( $value['videos'] ) ) {
			return '';
		}

		$output = '<video controls';

		if( $value['poster'] ) {
			$output .= ' poster="' . esc_attr( wp_get_attachment_url( $value['poster'] ) ) . '"';
		}

		foreach( $value['videos'] as $id ) {
			$output .= sprintf(
				'<source src="%s" type="%s">',
				wp_get_attachment_url( $id ),
				get_post_mime_type( $id )
			);
		}

		$output .= '>';

		$output .= '</video>';

		return $output;
	}
}
