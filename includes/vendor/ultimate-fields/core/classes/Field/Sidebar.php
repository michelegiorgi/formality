<?php
namespace Ultimate_Fields\Field;

use Ultimate_Fields\Field;
use Ultimate_Fields\Template;
use Ultimate_Fields\Helper\Sidebar_Manager;

/**
 * Allows users to select and eventually manage custom sidebars.
 *
 * @since 3.0
 */
class Sidebar extends Field {
	/**
	 * Indicates if the field is editable in terms of sidebars.
	 *
	 * @since 3.0
	 * @var bool
	 */
	protected $editable = false;

	/**
	 * Hold register sidebar args.
	 *
	 * @since 3.0
	 * @var mixed[]
	 */
	protected $sidebar_args = array(
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h2 class="widgettitle">',
		'after_title'   => '</h2>'
	);

	/**
	 * When the field is already constructed, register it with the sidebar manager.
	 *
	 * @since 3.0
	 */
	protected function __constructed() {
		$manager = Sidebar_Manager::instance();
		$manager->add_field( $this );
	}

	/**
	 * Exports the settings for the field.
	 *
	 * @since 3.0
	 * @return mixed[]
	 */
	public function export_field() {
		$settings = parent::export_field();

		$settings[ 'editable' ] = $this->editable;

		return $settings;
	}

	/**
	 * Toggles the editor functionality of the field.
	 *
	 * @since 3.0
	 *
	 * @param bool $yes A flag that indicates if users can create custom sidebars.
	 * @return Ultimate_Fields\Field\Sidebar
	 */
	public function make_editable( $yes = true ) {
		$this->editable = $yes;

		return $this;
	}

	/**
	 * Enqueues the scripts and templates for the field.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'uf-field-sidebar' );

		# Add templates
		Template::add( 'sidebar-base', 'field/sidebar-base' );
		Template::add( 'sidebar-row', 'field/sidebar-row' );

		# Tell the sidebar manager to output the settings for the field name.
		Sidebar_Manager::instance()->add_to_queue( $this );
	}

	/**
	 * Set sidebar arguments, before/after widget/title are the only available.
	 *
	 * @link http://codex.wordpress.org/Function_Reference/register_sidebar See for args reference.
	 * @since 3.0
	 *
	 * @param array $args The arguments array.
	 * @return Ultimate_Fields\Field\Sidebar The field for chaining
	 */
	public function set_sidebar_args( $args ) {
		if( ! $args || ! is_array( $args ) ) {
			return $this;
		}

		$possible_keys = array( 'before_widget', 'after_widget', 'before_title', 'after_title' );
		$args          = array_intersect_key( $args, array_flip( $possible_keys) );

		/**
		 * Allows the arguments for creating a widget through a sidebar field to be changed.
		 *
		 * @since 3.0
		 *
		 * @param string[] $args The WordPress sidebar registration args.
		 * @param Ultimate_Fields\Field\Sidebar $field The field that is being edited.
		 */
	 	$this->sidebar_args = apply_filters( 'uf.field.sidebar.args', $args, $this );

	 	return $this;
	 }

	/**
	 * Returns the currently set sidebar arguments.
	 *
	 * @since 3.0
	 *
	 * @return string[]
	 */
	public function get_sidebar_args() {
	 	return $this->sidebar_args;
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
			'sidebar_editable' => 'make_editable',
			'sidebar_args'     => 'set_sidebar_args'
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
			'editable'     => array( 'sidebar_editable', false ),
			'sidebar_args' => array( 'sidebar_args', array() )
		));

		return $settings;
	}

	/**
	 * Handles the value of the field before sending it to get_value().
	 *
	 * @since 3.0
	 *
	 * @param mixed                  $value  The value to handle.
	 * @param Ultimate_Fields\Helper\Data_Source $source The source the value is coming from.
	 * @return mixed
	 */
	 public function handle( $value, $source = null ) {
 		$value = parent::handle( $value, $source );

		return ! empty( $value ) && is_active_sidebar( sanitize_title( $value ) )
			? sanitize_title( $value )
			: $this->default_value;
	}

	/**
	 * Processes the value of the field for front-end display.
	 *
	 * @since 3.0
	 *
	 * @param mixed $value The value to process (turn into a sidebar).
	 * @return string
	 */
	public function process( $value ) {
		ob_start();
		dynamic_sidebar( $value );
		return ob_get_clean();
	}
}
