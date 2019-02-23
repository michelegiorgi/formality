<?php
namespace Ultimate_Fields;

use Ultimate_Fields\Datastore;
use Ultimate_Fields\Datastore\Values as Values_Datastore;
use Ultimate_Fields\Container;
use Ultimate_Fields\Field;
use Ultimate_Fields\Template;
use Ultimate_Fields\Helper\Callback;
use Ultimate_Fields\Controller;

/**
 * Handles forms, displayed in the front-end.
 *
 * @since 3.0
 */
class Form {
	/**
	 * Indicates if uf_head() has been called already.
	 *
	 * :}
	 *
	 * @since 3.0
	 * @var bool
	 */
	protected $did_head = false;

	/**
	 * Holds the settings for when forms need to be displayed.
	 *
	 * @since 3.0
	 * @var mixed[]
	 */
	protected $args = array();

	/**
	 * Holds the object that is active and being edited.
	 *
	 * @since 3.0
	 * @var Ultimate_Fields\Form_Object
	 */
	protected $object;

	/**
	 * Holds the ID of the form, which would be used for generating nonces, input names and etc.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $id;

	/**
	 * Holds all containers, which would be used.
	 *
	 * @since 3.0
	 * @var Container[]
	 */
	protected $containers = array();

	/**
	 * Holds the container, which is used for editing native attributes.
	 *
	 * @since 3.0
	 * @var Container
	 */
	protected $item_container;

	/**
	 * Creates a new instance of the class.
	 *
	 * This method will be used for uf_head() and uf_form() when
	 * only a single form will be displayed on the page.
	 *
	 * If you need to display multiple forms, please use something like this:
	 * <?php
	 * $form = new Ultimate_Fields\Form( $settings );
	 *
	 * // Use before the header
	 * $form->head();
	 *
	 * // Use to display the form
	 * $form->form();
	 *
	 * @since 3.0
	 *
	 * @return Form
	 */
	public static function instance() {
		static $instance;

		if( is_null( $instance ) ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Creates a new instance of the class.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $args The arguments for the form.
	 */
	public function __construct( $args = array() ) {
		$this->id = $this->generate_form_id();

		if( ! empty( $args ) ) {
			$this->setup( $args );
		}
	}

	/**
	 * Generates an ID for the form.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	protected function generate_form_id() {
		static $existing;

		if( is_null( $existing ) ) {
			$existing = array();
		}

		$id_base   = 'uf_form_';
		$id_suffix = 0;

		do {
			$id_suffix++;
		} while( in_array( $id_base . $id_suffix, $existing ) );

		$id = $id_base . $id_suffix;
		$existing[] = $id;

		return $id;
	}

	/**
	 * Sets arguments and labels up.
	 *
	 * @since 3.0
	 * @param mixed[] $args Described below.
	 */
	protected function setup( $args ) {
		$this->args = wp_parse_args( $args, array(
			/**
			 * This must be a list of containers to include in the form.
			 *
			 * @since 3.0
			 * @var mixed Either instances of Ultimate_Fields\Container or IDs.
			 */
			'containers' => array(),

			/**
			 * Indicates which containers to exclude when loading automatically.
			 *
			 * @since 3.0
			 * @var string[] The IDs of the containers or their instances.
			 */
			'exclude_containers' => array(),

			/**
			 * Indicates where to load values from.
			 *
			 * You can either pass a normal object (WP_Post, WP_Term, WP_User),
			 * leave this blank in order to automatically load the current item.
			 *
			 * @since 3.0
			 * @var mixed
			 */
			'item' => false,

			/**
			 * Indicates if a new object must be created.
			 *
			 * Rather than being a boolean true, this should contain either
			 * a post type slug/name, a taxonomy slug/name or simply "user" when
			 * you want to create a new user.
			 *
			 * @since 3.0
			 * @var mixed
			 */
			'create_new' => false,

			/**
			 * Holds arguments for creating new items through functions
			 * like wp_insert_post(), wp_insert_term() and etc.
			 *
			 * @since 3.0
			 * @var mixed
			 */
			'save_arguments' => array(),

			/**
			 * Controls what object-native fields to be shown.
			 *
			 * Can be either string 'all' on an array of fields to show.
			 * Fields very between object types. To see what fields are supported
			 * for which object type, open core/classes/Form_Object/* and locate
			 * the 'get_fields' method.
			 *
			 * @since 3.0
			 * @var mixed
			 */
			'item_fields' => 'all',

			/**
			 * Provides additional labels.
			 *
			 * @since 3.0
			 * @var string[]
			 */
			'labels' => array(),

			/**
			 * This is an array of fields which should not be visible int he front-end.
			 * You can use both IDs here or actual fields.
			 *
			 * @since 3.0
			 * @var mixed[]
			 */
			'exclude_fields' => array(),

			/**
			 * This is an array of fields which should be visible int he front-end.
			 * Once you give values here, those will be the only fields that would be shown.
			 *
			 * @since 3.0
			 * @var mixed[]
			 */
			'include_fields' => array(),

			/**
			 * Indicates where to redirect the user to after submitting a form.
			 *
			 * Possible options:
			 * - boolean(false):   Redirect back to the current URL.
			 * - string(item):     Redirect to the URL of the edited item.
			 * - any other string: Used as a URL.
			 *
			 * @since 3.0
			 * @var mixed[]
			 */
			'redirect_to' => false
		));

		/**
		 * Set labels up.
		 */
		$this->labels = wp_parse_args( $this->args[ 'labels' ], array(
			'save'            => esc_html( __( 'Save', 'ultimate-fields' ) ),
			'success_title'   => __( 'Your data is now saved.', 'ultimate-fields' ),
			'success_message' => ''
		));
	}

	/**
	 * Determines which object is being used.
	 *
	 * @since 3.0
	 */
	protected function locate_object() {
		$object = Form_Object::create( $this->args );

		if( ! $object ) {
			$msg = __( 'uf_head() and uf_form() can only be called when there is a clearly defined item being loaded!', 'ultimate-fields' );
			wp_die( $msg );
		}

		$this->object = $object;
	}

	/**
	 * Load all containers which are to be used.
	 *
	 * @since 3.0
	 */
	protected function load_containers() {
		$containers = array();
		$registered = Container::get_registered();

		/**
		 * Locate the containers that will be displayed.
		 */
		if( $this->args[ 'containers' ] ) {
			# Manual containers
			foreach( $this->args[ 'containers' ] as $container ) {
				if( is_a( $container, Container::class ) ) {
					$containers[] = $container;
				} elseif( is_string( $container ) ) {
					if( isset( $registered[ $container ] ) ) {
						$containers[] = $registered[ $container ];
					} else {
						$msg = __( 'Container %s does not exist!', 'ultimate-fields' );
						$msg = sprintf( $msg, $container );
						wp_die( $msg );
					}
				} else {
					wp_die( __( 'uf_head(): Unknown kontainer type!', 'ultimate-fields' ) );
				}
			}
		} else {
			$ignored = (array) $this->args[ 'exclude_containers' ];

			# Auto-load containers
			foreach( $registered as $key => $container ) {
				if( in_array( $container->get_id(), $ignored ) || in_array( $key, $ignored ) ) {
					continue;
				}

				if( $container->works_with( $this->object ) ) {
					$containers[] = $container;
				}
			}
		}

		/**
		 * Mirror the normal containers as front-end containers.
		 */
		foreach( $containers as $source ) {
			$this->containers[] = $this->clone_container( $source );
		}

		# Generate with the title/content editor if needed.
		$this->get_item_container();
	}

	/**
	 * Clones a container for use in the front end.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Container $source The source container.
	 * @return Ultimate_Fields\Container
	 */
	protected function clone_container( $source ) {
		$container = clone $source;
		$container->set_id( $source->get_id() . '-front-end' );

		# Ensure proper arguments
		$this->args[ 'exclude_fields' ] = isset( $this->args[ 'exclude_fields' ] )
			? (array) $this->args[ 'exclude_fields' ]
			: array();
		$this->args[ 'include_fields' ] = isset( $this->args[ 'include_fields' ] )
			? (array) $this->args[ 'include_fields' ]
			: array();

		# Map the fields through a filter
		$container->get_fields()->filter( array( $this, 'control_container_field' ), true );

		return $container;
	}

	/**
	 * Checks if a field should be used or not.
	 * Used when filtering the collection of a cloned container.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Field $field The field to check.
	 * @return bool
	 */
	public function control_container_field( $field ) {
		if(
			in_array( $field, $this->args[ 'exclude_fields' ] )
			|| in_array( $field->get_name(), $this->args[ 'exclude_fields' ] )
		) {
			return false;
		}

		if(
			! empty( $this->args[ 'include_fields' ] )
			&& ! (
				in_array( $field, $this->args[ 'include_fields' ] )
				|| in_array( $field->get_name(), $this->args[ 'include_fields' ] )
			)
		) {
			return false;
		}

		return true;
	}

	/**
	 * Generates a container for the title and/or editor.
	 *
	 * This container will be used to let the title and content editor
	 * work similarly to the other fields that would be included on the
	 * screen, including validation, CSS classes and etc.
	 *
	 * @since 3.0
	 */
	protected function get_item_container() {
		# Don't double-generate the container
		if( ! is_null( $this->item_container ) ) {
			return $this->item_container;
		}

		# Get fields from the item that is being edited
		$this->object->setup_fields( $this->args[ 'item_fields' ] );
		$fields = $this->object->get_fields();

		/**
		 * ALlows the fields, which are used within a front-end form to be modified.
		 *
		 * @since 3.0
		 *
		 * @param Ultimate_Fields\Fields_Collection $fields The collection with all object-native fields,
		 *                                      which will be used within the form.
		 * @param Ultimate_Fields\Form_Object       $object The object whose fields are displayed.
		 * @param Ultimate_Fields\Form              $form   The form that will display the messages.
		 * @return Ultimate_Fields\Fields_Collection
		 */
		$fields = apply_filters( 'uf.form.object_fields', $fields, $this->object, $this );

		# If there are no fields to display, don't create a container
		if( ! $fields ) {
			return $this->item_container = false;
		}

		# We need a simple container, without any locations
		$container = Container::create( __( 'Item Data', 'ultimate-fields' ) )
			->set_datastore( $this->object->get_fields_datastore() )
			->add_fields( $fields );

		# Get the layout from the first container
		if( empty( $this->containers ) ) {
			$container->set_layout( 'grid' );
		} else {
			$container->set_layout( $this->containers[ 0 ]->get_layout() );
		}

		return $this->item_container = $container;
	}

	/**
	 * Prepares all needed containers and their data, enqueues scripts,
	 * saves submitted data and much more.
	 *
	 * Its paramount to use this function in your templates before calling get_header();
	 *
	 * @since 3.0
	 * @param mixed[] $args Arguments for displaying the containers/forms.
	 */
	public function head( $args = array() ) {
		if( ! empty( $args ) || ! $this->args ) {
			# Perform a basic setup
			$this->setup( $args );
		}

		# Determine what we're working with
		$this->locate_object();

		# Locate containers
		$this->load_containers();

		# Perform AJAX when needed
		$this->maybe_ajax();

		# Submit the form if possible
		$this->maybe_save();

		# Indicate that head() has already been called
		$this->did_head = true;

		# Add actions
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Attempts saving the values of forms.
	 *
	 * @since 3.0
	 */
	protected function maybe_save() {
		$nonce_name = $this->id . '_nonce';
		$nonce_act  = 'uf-form-' . md5( $this->id );

		if( 'POST' != $_SERVER[ 'REQUEST_METHOD' ] || ! isset( $_POST[ $nonce_name ] ) ) {
			return;
		}

		if( ! wp_verify_nonce( $_POST[ $nonce_name ], $nonce_act ) ) {
			wp_die( __( 'Your form has expired. Please try again!' ) );
		}

		# Honeypot
		if( ! isset( $_POST[ $this->id ] ) || $_POST[ $this->id ] ) {
			wp_die( __( 'Cheatin’ uh?' ) );
		}

		# Get a datastore for all of the actions
		$datastore = $this->object->get_datastore();

		# Prepare containers to process
		$containers = $this->get_containers();
		if( $item_container = $this->get_item_container() ) {
			$special = array( $item_container );
		} else {
			$special = array();
		}

		# Dump data in datastores
		$errors     = array();
		foreach( array_merge( $special, $containers ) as $container ) {
			$key = $this->id . '_' . $container->get_id();

			$data = isset( $_POST[ $key ] )
				? json_decode( stripslashes( $_POST[ $key ] ), true )
				: array();

			$errors = array_merge( $errors, $container->save( $data ) );
		}

		# If there are errors, die
		if( ! empty( $errors ) ) {
			wp_die( Controller::generate_error_html( $errors ) );
		}

		# If there is an item container, let the item work with it
		$this->object->save( $this->args[ 'save_arguments' ] );

		# Save the rest of the data
		$datastore->commit();

		# Redirect back to the form
		if( false == $this->args[ 'redirect_to' ] ) {
			$url = $_SERVER[ 'REQUEST_URI' ];
		} elseif( 'item' == $this->args[ 'redirect_to' ] ) {
			$url = $this->object->get_url();
		} else {
			$url = $this->args[ 'redirect_to' ];
		}

		# If there is a message to display, save in in the session
		if( $this->labels[ 'success_title' ] ) {
			$url = add_query_arg( 'uf-form-success', str_replace( 'uf_form_', '', $this->id ), $url );
		}

		# Finally, redirect
		wp_redirect( $url );
		exit;
	}

	/**
	 * Performs AJAX actions if/when needed.
	 *
	 * @since 3.0
	 */
	protected function maybe_ajax() {
		if( $action = ultimate_fields()->is_ajax( $this ) ) {
			foreach( $this->containers as $container ) {
				foreach( $container->get_fields() as $field ) {
					$field->perform_ajax( $action, $this );
				}
			}
		}
	}

	/**
	 * Returns the containers that should be used for display and saving.
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\Container
	 */
	public function get_containers() {
		$containers = array();
		foreach( $this->containers as $container ) {
			$container->set_datastore( $this->object->get_datastore() );
			$containers[] = $container;
		}

		return $containers;
	}

	/**
	 * Displays the body of the form.
	 *
	 * @since 3.0
	 */
	public function form() {
		if( ! $this->did_head ) {
			echo wpautop( __( 'The uf_head() function must be called first in order for uf_form() to function!' ) );
			return;
		}

		if( empty( $this->get_containers() ) ) {
			echo '<div class="uf-no-forms">';
				echo wpautop( __( 'No containers to display', 'ultimate-fields' ) );
			echo '</div>';
			return;
		}

		$args = array(
			'url'          => $_SERVER[ 'REQUEST_URI' ],
			'object'       => $this->object,
			'button_text'  => $this->labels[ 'save' ],
			'fields'       => new Callback( array( $this, 'display_fields' ) ),
			'success'      => false,
			'nonce_action' => 'uf-form-' . md5( $this->id ),
			'nonce_name'   => $this->id . '_nonce',
			'form_id'      => $this->id
		);

		$raw_id = str_replace( 'uf_form_', '', $this->id );
		if( isset( $_GET[ 'uf-form-success' ] ) && $raw_id == $_GET[ 'uf-form-success' ] ) {
			$args[ 'success' ] = array(
				'title'   => $this->labels[ 'success_title' ],
				'message' => $this->labels[ 'success_message' ]
			);

			/**
			 * Allows the success arguments to be modified (title and message).
			 *
			 * @since 3.0
			 *
			 * @param Array $args {
			 *     @param string $title   The title of the success message.
			 *     @param string $message The text of the success message.
			 * }
			 * @param Ultimate_Fields\Form $form The form that is processing the data.
			 * @return mixed[]
			 */
			$args[ 'success' ] = apply_filters( 'uf.form.success', $args[ 'success' ], $this );
		}

		Template::instance()->include_template( 'form', $args );
	}

	/**
	 * Displays the fields of the container.
	 *
	 * @since 3.0
	 */
	public function display_fields() {
		$containers = $this->containers;
		if( $this->item_container ) {
			$containers = array_merge( array( $this->item_container ), $containers );
		}

		foreach( $containers as $container ) {
			$settings = $container->export_settings();
			$settings[ 'form' ] = $this->id;

			$json = array(
				'type'      => 'Front_End',
				'settings'  => $settings,
				'data'      => $container->export_data()
			);

	        echo sprintf(
	            '<div class="uf-form-container"><script type="text/json">%s</script></div>',
	            json_encode( $json )
	        );
		}

		Template::add( 'front-end', 'container/front-end' );
		Template::add( 'container-error', 'container/error' );
	}

	/**
	 * Enqueues the scripts for each container.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'uf-container-front-end' );

		if( $this->get_item_container() ) {
			$this->get_item_container()->enqueue_scripts();
		}

		foreach( $this->get_containers() as $container ) {
			$container->enqueue_scripts();
		}

		# Attach translations
		ultimate_fields()->l10n()->enqueue();
	}
}
