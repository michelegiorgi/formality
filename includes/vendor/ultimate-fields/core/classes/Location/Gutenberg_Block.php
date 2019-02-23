<?php
namespace Ultimate_Fields\Location;

use Ultimate_Fields\Location;
use Ultimate_Fields\Controller\Gutenberg_Block as Controller;
use Ultimate_Fields\Datastore\Gutenberg_Block as Datastore;
use Ultimate_Fields\Helper\Data_Source;

/**
 * This location displays fields as a block in Gutenberg.
 *
 * @since 3.0
 */
class Gutenberg_Block extends Location {
	/**
	 * Holds a keyword that would let works_with return true even without an object.
	 *
	 * @since 3.0
	 * @const string
	 */
	const WORKS_WITH_KEYWORD = 'block';

	/**
	 * A callback, which renders the block.
	 *
	 * @since 3.0
	 * @var callable
	 */
	protected $callback;

	/**
	 * The icon for the block.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $icon = 'dashicons-awards';

	/**
	 * A category for the icon. Options unknown yet.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $category = 'layout';

    /**
	 * Creates an instance of the class.
	 * The parameters for this constructor are the same as the parameters of Container->add_location().
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $args Additional arguments for the location.
	 */
	public function __construct( $args = array() ) {
		$this->set_and_unset( $args, array(
			'callback' => 'set_callback',
			'icon'     => 'set_icon',
			'category' => 'set_category'
		));

		# Send all arguments to the appropriate setter.
		$this->arguments = $args;
	}

	/**
	 * Returns an instance of the controller, which controls the location (menu items).
	 *
	 * @since 3.0
	 *
	 * @return Controller
	 */
	public function get_controller() {
		return Controller::instance();
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
		//
	}

	/**
	 * Updates the callback of the location.
	 *
	 * @since 3.0
	 *
	 * @param callable $callback A function, which computes the output of the block.
	 * @return Gutenberg_Block
	 */
	public function set_callback( $callback ) {
		$this->callback = $callback;

		return $this;
	}

	/**
	 * Returns the callback for the location.
	 *
	 * @since 3.0
	 *
	 * @return callable
	 */
	public function get_callback() {
		return $this->callback;
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

		return is_a( $source, Data_Source::class ) && 'block' == $source->type;
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
		# will apply to the current block, so we can directly
		# use the static datastore method for the current widget.
		return Datastore::get_current_datastore();
	}

	/**
	 * Allows the icon of the block to be changed.
	 *
	 * @since 3.0
	 *
	 * @param string $icon The CSS class of the new icon. Prederably a dashicons icon.
	 * @return Gutenberg_Block
	 */
	public function set_icon( $icon ) {
		$this->icon = $icon;
		return $this;
	}

	/**
	 * Returns the icon of the block.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_icon() {
		return $this->icon;
	}

	/**
	 * Changes the category of the block.
	 *
	 * @since 3.0
	 *
	 * @param string $category A pre-existing category of Gutenberg blocks (not documented yet).
	 * @return Gutenberg_Block
	 */
	public function set_category( $category ) {
		$this->category = $category;
		return $this;
	}

	/**
	 * Returns the category of the block.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_category() {
		return $this->category;
	}
}
