<?php
namespace Ultimate_Fields\Location;

use Ultimate_Fields\Location;
use Ultimate_Fields\Controller\Shortcode as Controller;
use Ultimate_Fields\Datastore\Shortcode as Datastore;
use Ultimate_Fields\Helper\Data_Source;

/**
 * Works as a location definition for containers as shortcodes.
 *
 * @since 3.0
 */
class Shortcode extends Location {
	/**
	 * Holds a keyword that would let works_with return true even without an object.
	 *
	 * @since 3.0
	 * @const string
	 */
	const WORKS_WITH_KEYWORD = 'shortcode';

	/**
	 * Holds the tag for the sortcode if one is needed.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $tag;

	/**
	 * Holds the template, which will be used within the editor to preview the shortcode.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $template;

	/**
	 * Holds the default template, which will be used within the editor to preview the shortcode.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $default_template = '<div class="uf-shortcode-placeholder">
		<div class="uf-shortcode-placeholder-text">[ <%= __title %> ]</div>
	</div>';

	/**
	 * Holds the ID of an image, that would be used as a preview.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $preview_image;

	/**
	 * Creates an instance of the class.
	 * The parameters for this constructor are the same as the parameters of Container->add_location().
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $args Additional arguments for the location.
	 */
	public function __construct( $args = array() ) {
		# Send all arguments to the appropriate setter.
		$this->arguments = $args;

		if( isset( $args[ 'template' ] ) ) {
			$this->set_template( $args[ 'template' ] );
		}

		if( isset( $args[ 'preview_image' ] ) ) {
			$this->use_image_as_preview( $args[ 'preview_image' ] );
		}
	}

	/**
	 * Returns an instance of the controller, which controls the location (menu items).
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\Controller\Shortcode
	 */
	public function get_controller() {
		return Controller::instance();
	}

	/**
	 * Changes the tag of the shortcode when needed.
	 *
	 * @since 3.0
	 *
	 * @param string $tag The tag to use.
	 * @return Ultimate_Fields\Location\Shortcode
	 */
	public function set_tag( $tag ) {
		$this->tag = $tag;
	}

	/**
	 * Returns the tag for the shortcode.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_tag() {
		return $this->tag ? $this->tag : $this->id;
	}

	/**
	 * Allows a new template to be used for the controller.
	 *
	 * @since 3.0
	 *
	 * @param string $template The template to use. Should be compatible with Underscore's templates.
	 * @return Shortcode
	 */
	public function set_template( $template ) {
		$this->template = $template;

		return $this;
	}

	/**
	 * Returns the raw template (if any) for the shortcode.
	 *
	 * @since 3.0
	 *
	 * @return mixed
	 */
	public function get_raw_template() {
		return $this->template;
	}

	/**
	 * Returns the template for the shortcode's preview.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_template() {
		if( $this->preview_image && 'attachment' == get_post_type( $this->preview_image ) ) {
			return wp_get_attachment_image( $this->preview_image, 'full', false, array(
				'style' => 'display: block; width: 100%; height: auto;'
			));
		}

		if( $this->template ) {
			return $this->template;
		}

		return $this->default_template;
	}

	/**
	 * Uses an image attachment for the preview of the shortcode.
	 *
	 * @since 3.0
	 *
	 * @param mixed $image_id The ID of the image to use as a preview.
	 * @return Shortcode
	 */
	public function use_image_as_preview( $image_id ) {
		$this->preview_image = $image_id;

		return $this;
	}

	/**
	 * Returns the image that is used for preview, if any.
	 *
	 * @since 3.0
	 *
	 * @return mixed
	 */
	public function get_preview_image() {
		return $this->preview_image;
	}

	/**
	 * Generates a datastore based on an object.
	 *
	 * @since 3.0
	 *
	 * @param mixed $object The post to create a datastore for.
	 * @return mixed
	 */
	public function create_datastore( $object ) {
		# This method will only be called in the front-end and
		# will apply to the current shortcode, so we can directly
		# use the static datastore method for the current widget.
		return Datastore::get_current_datastore();
	}

	/**
	 * Determines whether the location works with a certain object(type).
	 *
	 * @since 3.0
	 *
	 * @param mixed $object An object or a string to work with.
	 * @return bool
	 */
	public function works_with( $source ) {
		# Check for the attachment type
		if( $source === self::WORKS_WITH_KEYWORD ) {
			return true;
		}

		return is_a( $source, Data_Source::class ) && 'shortcode' == $source->type;
	}

	/**
	 * Returns the settings for the location, which will be exported.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export() {
		$settings = parent::export();

		$settings[ 'tag' ] = $this->tag;

		# Add the needed data
		if( $this->preview_image ) {
			$settings[ 'preview_image' ] = $this->preview_image;
		} elseif( $this->template ) {
			$settings[ 'template' ] = $this->template;
		}

		return $settings;
	}

	/**
	 * Imports the location from PHP/JSON.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $args The arguments to import.
	 */
	public function import( $args ) {
		$this->set_tag( $args[ 'tag' ] );

		if( isset( $args[ 'preview_image' ] ) && $args[ 'preview_image' ] ) {
			$this->use_image_as_preview( $args[ 'preview_image' ] );
		} elseif( isset( $args[ 'template' ] ) && $args[ 'template' ] ) {
			$this->set_template( $args[ 'template' ] );
		}
	}
}
