<?php
namespace Ultimate_Fields\Controller;

use Ultimate_Fields\Controller;
use Ultimate_Fields\Template;
use Ultimate_Fields\Datastore\Widget as Datastore;
use Ultimate_Fields\Location\Widget as Widget_Location;

/**
 * Handles containers within widgets.
 *
 * @since 3.0
 */
class Widget extends Controller {
	/**
	 * Holds settings of containers to output in the footer.
	 *
	 * @since 3.0
	 * @var mixed[]
	 */
	protected $settings = array();

	/**
	 * Add the appropriate hooks for the controller.
	 *
	 * @since 3.0
	 */
	protected function __construct() {
		add_action( 'in_widget_form', array( $this, 'display' ), 10, 3 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_footer', array( $this, 'output_settings' ) );
		add_action( 'customize_controls_print_footer_scripts', array( $this, 'output_settings' ) );
		add_filter( 'widget_update_callback', array( $this, 'save' ), 10, 4 );

		# This will save the instance of the current widget to allow get_value( '[field_name]', 'widget' );
		add_filter( 'widget_display_callback', array( $this, 'cache_current_widget' ), 10, 3 );
		add_action( 'load-widgets.php', array( $this, 'do_ajax' ), 8, 2 );
	}

	/**
	 * Returns the containers, which work with a certain widget.
	 *
	 * @since 3.0
	 *
	 * @param string $widget The class of the widget or a widget object.
	 * @return Container[]
	 */
	protected function get_widget_containers( $widget ) {
		if( is_object( $widget ) ) {
			$widget = get_class( $widget );
		}

		$combinations = array();

		foreach( $this->combinations as $combination ) {
			$applies = false;

			foreach( $combination[ 'locations' ] as $location ) {
				$widgets = $location->get_widgets();

				if( empty( $widgets ) || in_array( $widget, $widgets ) ) {
					$applies = true;
					break;
				}
			}

			if( $applies ) {
				$combinations[] = $combination;
			}
		}

		# Ensure unique field IDs
		$this->ensure_unique_field_names( $combinations, true );

		return wp_list_pluck( $combinations, 'container' );
	}

	/**
	 * Outputs basic data about widgets within their forms.
	 *
	 * @since 3.0
	 *
	 *
	 * @param WP_Widget $widget The widget whose form is being displayed.
	 * @param mixed     $return A default argument.
	 * @param mixed     $instance The data of the current instance.
	 */
	public function display( $widget, $return, $instance ) {
		$containers = $this->get_widget_containers( $widget );

		if( empty( $containers ) ) {
			return;
		}

		foreach( $containers as $container ) {
			$id = $container->get_id();

			# Cache settings for the footer
			if( ! isset( $this->settings[ $id ] ) ) {
				$settings = $container->export_settings();
				$settings[ 'layout' ] = 'grid';
				$this->settings[ $id ] = $settings;
			}

			# Output the containers' data from a proper datastore
			$datastore = new Datastore( $instance );
			$container->set_datastore( $datastore );

			# Output the data for the widget
			$data = $container->export_data();
			$field_name = $widget->get_field_id( 'uf_widget_' . $id );

			printf(
				'<div class="uf-widget" data-type="%s" data-input-name="%s"><script type="text/json">%s</script></div>',
				$id,
				$field_name,
				json_encode( $data )
			);

			echo $this->get_no_js_message();
		}
	}

	/**
	 * Attempts saving widget values.
	 *
	 * @since 3.0
	 *
	 * @param array     $instance     The current widget instance's settings.
	 * @param array     $new_instance Array of new widget settings.
	 * @param array     $old_instance Array of old widget settings.
	 * @param WP_Widget $widget       The current widget instance.
	 * @return mixed[]
	 */
	public function save( $instance, $new_instance, $old_instance, $widget ) {
		$datastore = new Datastore( $new_instance );
		$errors    = array();

		foreach( $this->get_widget_containers( $widget ) as $container ) {
			$container->set_datastore( $datastore );

			$input_name = $widget->get_field_id( 'uf_widget_' . $container->get_id() );

			if( ! isset( $_POST[ $input_name ] ) ) {
				continue;
			}

			$data   = json_decode( stripslashes( $_POST[ $input_name ] ), true );
			$errors = array_merge( $errors, $container->save( $data, true ) );
		}

		# Save the values
		if( is_null( $instance ) ) {
			$instance = array();
		}

		$instance = array_merge( $instance, $datastore->get_values() );

		# If there are no errors, return the new instance
		if( empty( $errors ) ) {
			return $instance;
		}

		# If there are errors, handle them
		if( is_customize_preview() ) {
			$this->handle_customizer_validation( $errors );
		} else {
			$this->error( $errors );
		}
	}

	/**
	 * Generates the error message for a widget in the customizer.
	 *
	 * @since 3.0
	 *
	 * @param string[] $errors The errors to display.
	 */
	protected function handle_customizer_validation( $errors ) {
		# Generate a random ID to let the JS controller locate the widget
		$id = round( microtime( true ) * 1000 );

		$message = sprintf(
			'<div class="error uf-error uf-widget-error" data-token="%d">
				%s
				<script>UltimateFields.Container.Widget.Controller.handleValidation(\'%s\')</script>
			</div>',
			$id,
			self::generate_error_html( $errors ),
			$id
		);

		wp_send_json_error( array(
			'success' => false,
			'message' => $message
		));
	}

	/**
	 * Checks if the controller's containers are being displayed.
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
	protected function is_being_displayed() {
		if( function_exists( 'get_current_screen' ) && 'widgets' == get_current_screen()->id ) {
			return true;
		}

		if( is_customize_preview() ) {
			return true;
		}

		return false;
	}

	/**
	 * Outputs the settings for each container in the footer.
	 *
	 * @since 3.0
	 */
	public function output_settings() {
		if( ! $this->is_being_displayed() ) {
			return;
		}

		Template::add( 'widget', 'container/widget' );
		Template::add( 'container-error', 'container/error' );
		Template::instance()->output_templates();

		foreach( $this->settings as $id => $settings ) {
			printf(
				'<script type="text/json" id="uf-widget-settings-%s">%s</script>',
				esc_attr( $id ),
				json_encode( $settings )
			);
		}
	}

	/**
	 * Enqueues the needed scripts for the container.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		if( ! $this->is_being_displayed() ) {
			return;
		}

		wp_enqueue_script( 'uf-container-widget' );

		foreach( $this->combinations as $combination ) {
			$combination[ 'container' ]->enqueue_scripts();
		}

		# Attach translations
		ultimate_fields()->l10n()->enqueue();
	}

	/**
	 * Caches the current widget to allow access to it through the data API.
	 *
	 * @since 3.0
	 *
	 * @param mixed[]   $instance The values of the widget.
	 * @param WP_Widget $widget   The widget that is being displayed.
	 * @param mixed[]   $args     Args for the current sidebar.
	 * @return mixed[]
	 */
	public function cache_current_widget( $instance, $widget, $args ) {
		# Let the datastore know what the current widget is
		Datastore::set_current_widget( $instance );

		return $instance;
	}

	/**
	 * Performs AJAX for media items.
	 *
	 * @since 3.0
	 */
	public function do_ajax() {
		ultimate_fields()->ajax( Widget_Location::WORKS_WITH_KEYWORD );
	}
}
