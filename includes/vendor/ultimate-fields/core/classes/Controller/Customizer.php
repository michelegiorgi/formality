<?php
namespace Ultimate_Fields\Controller;

use Ultimate_Fields\Controller;
use Ultimate_Fields\Template;
use Ultimate_Fields\Helper\Customize_Control;
use Ultimate_Fields\Helper\Callback;
use Ultimate_Fields\Helper\Data_Source;
use Ultimate_Fields\Location\Options as Options_Location;

/**
 * Handles the user location.
 *
 * @since 3.0
 */
class Customizer extends Controller {
	/**
	 * Indicates which class will be used for the setting models within PHP.
	 *
	 * @since 3.0
	 *
	 * @var string
	 */
	protected $setting_class = 'WP_Customize_Setting';

	/**
	 * Adds the neccessary hooks for the controller.
	 *
	 * @since 3.0
	 */
	protected function __construct() {
		add_action( 'customize_register', array( $this, 'register' ) );
		add_action( 'customize_preview_init', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'customize_preview_init', array( $this, 'enqueue_front_end_scripts' ), 9 );
		add_action( 'customize_save_after', array( $this, 'save' ) );
		add_filter( 'customize_dynamic_setting_args', array( $this, 'create_dynamic_settings' ), 10, 2 );

		if( get_current_user() ) {
			add_action( 'wp_footer', array( $this, 'output_data_in_footer' ) );
		}
	}

	/**
	 * Registers everything neccessary for the customizer.
	 *
	 * @since 3.0
	 *
	 * @param WP_Customize $wp_customize The customizer object.
	 */
	public function register( $wp_customize ) {
		foreach( $this->combinations as $combination ) {
			$this->register_combination( $wp_customize, $combination );
		}

		# Overwrite values in the frontend if needed
		if( ! is_admin() ) {
			add_action( 'wp', array( $this, 'overwrite_data' ) );
		}

		# Register the neccessary templates
		Template::add( 'customizer', 'container/customizer' );

		# Check for AJAX calls
		ultimate_fields()->ajax();
	}

	/**
	 * Registers a particular combination of a container and a location.
	 *
	 * @since 3.0
	 *
	 * @param WP_Customize $wp_customize The customizer object.
	 * @param mixed[] $combination The required combination.
	 */
	protected function register_combination( $wp_customize, $combination ) {
		$container = $combination[ 'container' ];
		$dynamic   = array();
		$priority  = -1;

		foreach( $combination[ 'locations' ] as $location ) {
			$dynamic = array_merge( $dynamic, $location->get_dynamic_fields() );
			$priority = max( $priority, $location->get_customizer_priority() );
		}

		# Prepare IDs
		$control_id = str_replace( '-', '_', $container->get_id() ) . '_control';
		$section_id = 'uf_section_' . str_replace( '-', '_', $container->get_id() );

		# Prepare a callback that checks if the container should be displayed
		$active_callback = new Callback( array( $this, 'active_callback' ) );
		$active_callback[ 'combination' ] = $combination;

		# Create a new section for the container
		$section = $wp_customize->add_section( $section_id, array(
			'title'           => $container->get_title(),
			'priority'        => $priority,
			'active_callback' => $active_callback->get_callback()
		));

		# Add the whole container as a setting to force WP to show the section.
		$wp_customize->add_setting( $control_id );

		# Use the container as the only control in the section
		$control = new Customize_Control( $wp_customize, $control_id, array(
			'label'    => $container->get_title(),
			'section'  => $section_id,
			'settings' => $control_id
		));

		# Create a callback for the containers' rendering
		$callback = new Callback( array( $this, 'display_container' ) );
		$callback[ 'combination' ] = $combination;

		$control->set_display_callback( $callback->get_callback() );

		# Finally, add the control to the section
		$wp_customize->add_control( $control );
	}

	/**
	 * Checks if a combination should be displayed in the customizer.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Callback $callback The callback that is handling the function.
	 * @return bool
	 */
	public function active_callback( $callback ) {
		foreach( $callback[ 'combination' ][ 'locations' ] as $location ) {
			if( $location->customizer_active_callback() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Displays a container.
	 *
	 * @since 3.0
	 *
	 * @param Callback $callback The callback that is handling the container.
	 */
	public function display_container( $callback ) {
		$combination    = $callback[ 'combination' ];
		$container      = $combination[ 'container' ];
		$datastore      = false;

		foreach( $combination[ 'locations' ] as $location ) {
			if( ! $datastore = $location->get_datastore( get_queried_object() ) ) {
				continue;
			}

			$container->set_datastore( $datastore );
			break;
		}

		# Prepare the needed dynamic fields
		$dynamic_fields = array();
		foreach( $combination[ 'locations' ] as $location )
			$dynamic_fields = array_merge( $dynamic_fields, $location->get_dynamic_fields() );

		# Generate settings
		$settings = $container->export_settings();
		$settings[ 'dynamic_fields' ] = $dynamic_fields;
		$settings[ 'layout' ] = 'grid';

		$json = array(
			'type'      => 'Customizer',
			'settings'  => $settings,
		);

		# Prepare data if any
		if( $datastore ) {
			$json[ 'data' ] = $container->export_data();
			$json[ 'item' ] = $datastore->get_item_string();
		} else {
			$json[ 'data' ] = array();
		}

        printf(
            '<div class="uf-customizer-container"><script type="text/json">%s</script></div>',
            json_encode( $json )
        );
	}

	/**
	 * Enqueues the scripts for the container.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		if( ! isset( $GLOBALS[ 'wp_customize' ] ) ) {
			return;
		}

		wp_enqueue_script( 'uf-container-customizer' );

		foreach( $this->combinations as $combination ) {
			$combination[ 'container' ]->enqueue_scripts();
		}

		# Attach translations
		ultimate_fields()->l10n()->enqueue();
	}

	/**
	 * Enqueues scripts in the front-end.
	 *
	 * @since 3.0
	 */
	public function enqueue_front_end_scripts() {
		wp_enqueue_script( 'uf-customize-preview' );
	}

	/**
	 * Combines all error messages and displays them as an error.
	 *
	 * @since 3.0
	 *
	 * @param string[] $errors The errors that should be displayed.
	 */
	protected function error( $errors ) {
		return wp_send_json_error(array(
			'success' => false,
			'message' => self::generate_error_html( $errors )
		));
	}

	/**
	 * Perform a datastore-based soft save without committing values into the database.
	 *
	 * This function should receive arguments, processed by the "simplify_changes" function,
	 * which separates values from their sources. Once that is done, each container will put
	 * it's values into a new datastore, but will not save them in the database.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $data An array that contains all "POST" values, combined with the changeset.
	 * @return array {
	 *     A combination of datastores and generated errors.
	 *
	 *     @type Ultimate_Fields\Datastore[] $datastores The generated (non-saved) datastores.
	 *     @type string[]        $errors     All errors, generated during saving.
	 * }
	 */
	protected function soft_save( $data ) {
		$datastores = array();
		$errors     = array();

		foreach( $data as $keyword => $items ) {
			foreach( $items as $id => $values ) {
				$object = $id ? $keyword . '_' . $id : $keyword;
				$source = Data_Source::parse( $object );
				$queue  = array();

				foreach( $this->combinations as $combination ) {
					$container    = $combination[ 'container' ];
					$add_to_queue = false;

					foreach( $combination[ 'locations' ] as $location ) {
						if( $location->works_with( $source ) ) {
							$add_to_queue = true;
							break;
						}
					}

					if( $add_to_queue ) {
						$queue[] = $container;
					}
				}

				if( empty( $queue ) ) {
					continue;
				}

				$datastore = $source->generate_datastore();
				$datastores[] = $datastore;

				foreach( $queue as $container ) {
					$container->set_datastore( $datastore );
					$errors = array_merge( $errors, $container->save( $values ) );
				}
			}
		}

		return compact( 'errors', 'datastores' );;
	}

	/**
	 * Simplifies an array of changes.
	 *
	 * This method will go through every setting in the array and if it's an ultimate-fields
	 * value, extract it.
	 *
	 * @since 3.0
	 *
	 * @param mixed[]  $source The source array.
	 * @return mixed[]         The simplified version/
	 */
	function simplify_changes( $raw ) {
		$data = array();

		foreach( $raw as $key => $setting ) {
			if( ! isset( $setting[ 'type' ] ) || 'ULTIMATE_FIELDS_value' != $setting[ 'type' ] )
				continue;

			$details = $this->extract_data_from_setting_id( $key );

			if( ! $details ) {
				continue;
			}

			# Extract the value
			$value = $setting[ 'value' ];
			if( is_array( $value ) && 1 == count( $value ) && array_key_exists( 'value', $value ) ) {
				$value = $value[ 'value' ];
			}

			# Put the value in the right place§
			if( ! isset( $data[ $details[ 'keyword' ] ] ) ) {
				$data[ $details[ 'keyword' ] ] = array();
			}

			$id = $details[ 'id' ];
			if( ! isset( $data[ $details[ 'keyword' ] ][ $id ] ) ) {
				$data[ $details[ 'keyword' ] ][ $id ] = array();
			}

			$data[ $details[ 'keyword' ] ][ $id ][ $details[ 'name' ] ] = $value;
		}

		return $data;
	}

	/**
	 * Overwrites data with changes in the front-end.
	 *
	 * When a page is being previewed, this method will check for any changes, saved in the current
	 * changeset and combine them with the values that are currently submitted directly.
	 *
	 * Those changes will be "saved" into datastores, which will then overwrite global values
	 * with the temporary changes. This way, in the front-end, the temporary data will be visible,
	 * instead of the permanently saved one.
	 *
	 * @since 3.0
	 */
	public function overwrite_data() {
		if( ! is_customize_preview() ) {
			return;
		}

		# Get existing changeset data
		$changeset_data = $GLOBALS[ 'wp_customize' ]->changeset_data();
		if( isset( $_POST[ 'customized' ] ) ) {
			$customized = json_decode( stripslashes( $_POST[ 'customized' ] ), true );
			foreach( $customized as $key => $value ) {
				if( $settings = $this->extract_data_from_setting_id( $key ) ) {
					$changeset_data[ $key ] = array(
						'value' => $value,
						'type'  => 'ULTIMATE_FIELDS_value'
					);
				}
			}
		}
		$data = $this->simplify_changes( $changeset_data );

		# Attempt a save and overwrite values
		$result = $this->soft_save( $data );
		if( empty( $result[ 'errors' ] ) ) {
			foreach( $result[ 'datastores' ] as $datastore ) {
				$datastore->set_temporary_values();
			}
		}
	}

	/**
	 * Saves all changes from Ultimate Fields and deletes dummy options.
	 *
	 * When the customizer is being saved, all data that has been kept in a changeset is being
	 * transitioned to its proper place. Normally, this only applies to mods and theme options,
	 * however Ultimate Fields will generate all necessary datastores and let them save their data
	 * in the database.
	 *
	 * After the changes are saved, the method will go through the data from the changeset and delete
	 * the all un-needed options and theme mods.
	 *
	 * @since 3.0
	 *
	 * @param WP_Customize_Manager $manager WP_Customize_Manager instance.
	 */
	public function save( $manager ) {
		# Collect data
		$data = $this->simplify_changes( $manager->changeset_data() );

		# Prepare variables for the result
		extract( $this->soft_save( $data ) );

		/**
		 * Save the data if the fields are valid.
		 *
		 * There are no validation messages here, because the data and model in the front-end
		 * can change, leading to a pretty much impossible error message/reporting.
		 */
		if( empty( $errors ) ) {
			# Save the data
			foreach( $datastores as $datastore ) {
				$datastore->commit();
			}
		}
	}

	/**
	 * Lets the containers in the customizer know about page changes whenever they happen.
	 *
	 * When a new page is being loaded in the customizer preview, the customizer is oblivious to
	 * the data that is used on that page and the containers, which work with that data.
	 *
	 * This method gathers all information, which is not in the customizer already and sends it there,
	 * ensuring that the containers, shown in the customizer, will display the correct data.
	 *
	 * @since 3.0
	 */
	public function output_data_in_footer() {
		if( ! is_customize_preview() ) {
			return;
		}

		# If there is no object, there are no values to load
		if( ! $object = get_queried_object() )  {
			return;
		}

		# Go through each combination and get items and/or data
		$values        = array();
		$the_datastore = false;
		foreach( $this->combinations as $combination ) {
			$container = $combination[ 'container' ];
			$datastore = false;

			foreach( $combination[ 'locations' ] as $location ) {
				if( ! $location->works_with( $object ) || ! $datastore = $location->get_datastore( $object ) ) {
					continue;
				}

				break;
			}

			# No datastore, no fun
			if( $datastore ) {
				$container->set_datastore( $datastore );
				$values[ $container->get_id() ] = $container->export_data();
				$the_datastore = $datastore;
			}
		}

		# If there is nothing to output, don't output it
		if( empty( $values ) ) {
			return;
		}

		# Prepare the final data
		$data = array(
			'object' => $the_datastore->get_item_string(),
			'values' => $values
		);

		$json = addslashes( json_encode( $data ) );
		?>
		<script>
		window.parent.UltimateFields.Container.Customizer.loadValuesForScreen( '<?php echo $json ?>' );
		</script>
		<?php
	}

	/**
	 * Extracts values from a setting name.
	 *
	 * @since 3.0
	 *
	 * @param string $setting_id The setting ID to check.
	 * @return mixed[]|bool
	 */
	protected function extract_data_from_setting_id( $setting_id ) {
		static $expression, $field_ids;

		if( is_null( $expression ) ) {
			# Prepare an expression that will match setting ids
			$identifiers = array();

			foreach( Data_Source::get_available_types() as $type ) {
				// The post_meta no-keyword option doesn't work here.
				if( ! $type[ 'keyword' ] ) {
					continue;
				}

				$id = preg_quote( $type[ 'keyword' ] );

				$identifiers[] = $id;
			}

			$expression = '~^(?P<name>.*)\[(?P<keyword>' . implode( $identifiers, '|' ) . ')(_(?P<id>\d+))?\]$~';

			# Prepare a list of plain field names
			$field_ids = array();

			foreach( $this->combinations as $combination ) {
				$has_options_location = false;

				foreach( $combination[ 'locations' ] as $location ) {
					if( is_a( $location, Options_Location::class ) ) {
						$has_options_location = true;
						break;
					}
				}

				if( ! $has_options_location ) {
					continue;
				}

				foreach( $combination[ 'container' ]->get_fields() as $field ) {
					$field_ids[] = $field->get_name();
				}
			}
		}

		if( preg_match( $expression, $setting_id, $matches ) ) {
			return array(
				'type'    => 'ULTIMATE_FIELDS_value',
				'name'    => $matches[ 'name' ],
				'keyword' => $matches[ 'keyword' ],
				'id'      => isset( $matches[ 'id' ] ) ? $matches[ 'id' ] : false,
			);
		}

		if( in_array( $setting_id, $field_ids  ) ) {
			return array(
				'type'    => 'ULTIMATE_FIELDS_value',
				'name'    => $setting_id,
				'keyword' => 'option',
				'id'      => false
			);
		}

		return false;
	}

	/**
	 * Creates dynamic settings based on JS-generated values.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $args The args for the setting.
	 * @param string  $setting_id The ID of the setting.
	 * @return mixed[]
	 */
	public function create_dynamic_settings( $args, $setting_id ) {
		if( $extracted = $this->extract_data_from_setting_id( $setting_id ) ) {
			return $extracted;
		} else {
			return $args;
		}
	}
}
