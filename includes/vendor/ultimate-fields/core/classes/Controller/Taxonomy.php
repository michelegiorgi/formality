<?php
namespace Ultimate_Fields\Controller;

use Ultimate_Fields\Controller;
use Ultimate_Fields\Template;
use Ultimate_Fields\Data_API;
use Ultimate_Fields\Datastore\Term_Meta as Datastore;
use Ultimate_Fields\Location\Taxonomy as Taxonomy_Location;
use Ultimate_Fields\Controller\REST_API;
use Ultimate_Fields\Controller\Admin_Column_Manager;

/**
 * Handles taxonomy locations.
 *
 * @since 3.0
 */
class Taxonomy extends Controller {
	use REST_API, Admin_Column_Manager;

	/**
	 * Adds the neccessary hooks for the controller.
	 *
	 * @since 3.0
	 */
	protected function __construct() {
		add_action( 'wp_loaded', array( $this, 'attach_to_wp' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'current_screen', array( $this, 'initialize_admin_columns' ) );
		add_action( 'current_screen', array( $this, 'do_ajax' ) );
		$this->rest();
	}

	/**
	 * Gathers the taxonomies to use from within a combination.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $combination The container-location combination.
	 * @return string[] A list of taxonomy names.
	 */
	protected function get_combination_taxonomies( $combination ) {
		$taxonomies = array();

		foreach( $combination[ 'locations' ] as $location ) {
			$taxonomies = array_merge( $taxonomies, $location->get_taxonomies() );
		}

		return $taxonomies;
	}

	/**
	 * Attaches hooks for displaying and saving terms.
	 *
	 * @since 3.0
	 */
	public function attach_to_wp() {
		$taxonomies = array();

		if( ! is_admin() ) {
			return;
		}

		# Gather all taxonomies
		foreach( $this->combinations as $combination ) {
			foreach( $this->get_combination_taxonomies( $combination ) as $tax ) {
				$taxonomies[] = $tax;
			}
		}

		# Only use taxonomies once
		$taxonomies = array_unique( $taxonomies );

		# Add hooks for each taxonomy
		foreach( $taxonomies as $taxonomy ) {
			add_action( $taxonomy . '_edit_form',       array( $this, 'display_edit' ) );
			add_action( $taxonomy . '_add_form_fields', array( $this, 'display_add' ) );
		}

		# Add general actions
		add_action( 'edited_term', array( $this, 'save' ), 10, 3 );
		add_action( 'create_term', array( $this, 'term_created' ), 10, 3 );
	}

	/**
	 * Displays the edit form.
	 *
	 * @since 3.0
	 *
 	 * @param WP_Term $term The term which the container is attached to.
	 */
	public function display_edit( $term ) {
		$this->display( $term );
	}

	/**
	 * Displays the container in the form when a term is added.
	 *
	 * @since 3.0
	 *
	 * @param string $taxonomy The taxonomy that is being displayed.
	 */
	public function display_add( $taxonomy ) {
		$this->display( $taxonomy, 'grid' );
	}

	/**
	 * Displays containers.
	 *
	 * @since 3.0
	 *
	 * @param WP_Term $term   The term to display locations for.
	 * @param string  $layout A forced layout.
	 */
	public function display( $term, $layout = null ) {
		if( is_string( $term ) ) {
			$taxonomy = $term;
		} else {
			$taxonomy = $term->taxonomy;
		}

		# Prepare a datastore
		$datastore = new Datastore();
		if( ! is_string( $term ) ) {
			$datastore->set_id( $term->term_id );
		}

		# Output each container
		foreach( $this->combinations as $combination ) {
			if( ! in_array( $taxonomy, $this->get_combination_taxonomies( $combination ) ) ) {
				continue;
			}

			$container = $combination[ 'container' ];
			$container->set_datastore( $datastore );

			# Export the containers' locations
			$locations = array();
			$usable    = false;
			foreach( $combination[ 'locations' ] as $location ) {
				$locations[] = $location->export_settings();
				if( $location->works_with_term( $term ) ) {
					$usable = true;
				}
			}

			# If the location does not work with a specific term, ignore it
			if( ! $usable ) {
				continue;
			}

			# Export settings and add locations
			$settings = $container->export_settings();
			$settings[ 'locations' ] = $locations;
			$settings[ 'view' ] = is_string( $term ) ? 'add' : 'edit';

			# Force seamless view when adding terms
			if( is_string( $term ) ) {
				$settings[ 'style' ] = 'seamless';
			}

			if( $layout ) {
				$settings['layout'] = $layout;
			}

			$json = array(
				'type'      => 'Taxonomy',
				'settings'  => $settings,
				'data'      => $container->export_data()
			);

			$flags = 0;
			if( WP_DEBUG && defined( 'JSON_PRETTY_PRINT' ) ) {
				$flags = JSON_PRETTY_PRINT;
			}

	        echo sprintf(
	            '<div class="uf-container" data-type="%s"><script type="text/json">%s</script></div>',
	            'Taxonomy',
	            json_encode( $json, $flags )
	        );
		}

		echo $this->get_no_js_message();

		# Register the neccessary templates
		$style = $container->get_style();

		if( is_string( $term ) ) {
			Template::add( 'taxonomy-add', 'container/taxonomy-add' );
		} else {
			Template::add( 'taxonomy-edit', 'container/taxonomy-edit' );
			Template::add( 'taxonomy-edit-boxed', 'container/taxonomy-edit-boxed' );
		}

		Template::add( 'container-error', 'container/error' );
	}

	/**
	 * Enqueues all scripts for the container.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		if( ! in_array( get_current_screen()->base, array( 'edit-tags', 'term' ) ) ) {
			return;
		}

		# Check for a proper taxonomy
		$taxonomy     = str_replace( 'edit-', '', get_current_screen()->id );
		$combinations = array();

		foreach( $this->combinations as $combination ) {
			$enqueue = false;

			foreach( $combination[ 'locations' ] as $location ) {
				if( ! in_array( $taxonomy, $location->get_taxonomies() ) ) {
					continue;
				}

				$enqueue = true;
				break;
			}

			if( $enqueue )  {
				$combinations[] = $combination;
			}
		}

		# If there are no containers, bail
		if( empty( $combinations ) ) {
			return;
		}

		# Check for unique field names
		$this->ensure_unique_field_names( $combinations );

		# Enqueue scripts
		wp_enqueue_script( 'uf-container-taxonomy' );
		foreach( $combinations as $combination ) {
			$combination[ 'container' ]->enqueue_scripts();
		}

		# Attach translations
		ultimate_fields()->l10n()->enqueue();
	}

	/**
	 * Attempts to and eventually saves a term.
	 *
	 * @since 3.0
	 *
	 * @param WP_Post $post The post that is being saved.
	 * @param int     $tt_id The term-taxonomy ID.
	 * @param string  $taxonomy The name of the taxonomy.
	 */
	public function save( $term, $tt_id = 0, $taxonomy = '' ) {
		# Bail on multisites
		if( is_multisite() && ms_is_switched() ) {
			return;
		}

		if( defined( 'UF_UI_IMPORTING' ) ) {
			return;
		}

		# Ensure that we are working with an object
		if( ! is_object( $term ) ) {
			$term = get_term_by( 'id', $term, $taxonomy );
		}

		# Create a datastore to use
		$datastore = new Datastore();
		$datastore->set_id( $term->term_id );

		# Prepare a queue of active containers
		$queue = array();
		foreach( $this->combinations as $combination ) {
			if( ! in_array( $taxonomy, $this->get_combination_taxonomies( $combination ) ) ) {
				continue;
			}

			# If there are particular IDs we need, check for them
			$usable  = false;
			foreach( $combination[ 'locations' ] as $location ) {
				if( $location->works_with_term( $term ) ) {
					$usable = true;
					break;
				}
			}

			if( ! $usable ) {
				continue;
			}

			$queue[] = $combination;
		}

		# Check for unique field names
		$this->ensure_unique_field_names( $queue );

		# Go through each container and try saving
		$errors = array();
		foreach( $queue as $combination ) {

			# Collect data
			$container = $combination[ 'container' ];
			$data_key  = 'uf_term_meta_' . $container->get_id();
			$data = isset( $_POST[ $data_key ] )
				? json_decode( stripslashes( $_POST[ $data_key ] ), true )
				: array();

			$container->set_datastore( $datastore );

			# Perform a save
			$messages = $container->save( $data );

			# Check if there is a visiblt location
			$visible = false;
			foreach( $combination[ 'locations' ] as $location ) {
				if( $location->works_with( $term ) ) {
					$visible = true;
					break;
				}
			}

			if( $visible ) {
				$errors = array_merge( $errors, $messages );
			}
		}

		# Save or die
		if( empty( $errors ) ) {
			$datastore->commit();
		} else {
			$this->error( $errors );
		}
	}

	/**
	 * Handles term creation.
	 *
	 * @since 3.0
	 *
	 * @param int $term_id     Term ID.
	 * @param int $tt_id       Term taxonomy ID.
	 * @param string $taxonomy The slug of the taxonomy.
	 */
	public function term_created( $term_id, $tt_id, $taxonomy ) {
		$term = get_term( $term_id, $taxonomy	 );
		$this->save( $term, $tt_id, $taxonomy );
	}

	/**
	 * Registers a location with the REST API.
	 *
	 * @since 3.0
	 *
	 * @param  Ultimate_Fields\Location  $location  The location that is being linked.
	 * @param  Ultimate_Fields\Container $container The container the location belongs to.
	 * @param  mixed[]       $fields    The fields that should be exposed.
	 */
	protected function register_rest_location( $location, $container, $fields ) {
		foreach( $location->taxonomies() as $slug ) {
			$this->add_fields_to_endpoint( $slug, $fields, $container );
		}
	}

	/**
	 * Reads out a value from the data API.
	 *
	 * @since 3.0
	 *
	 * @param  Ultimate_Fields\Field $field The field whose value is needed.
	 * @param  mixed     $item  The API item (post).
	 * @return mixed
	 */
	protected function get_api_value( $field, $item ) {
		return Data_API::instance()->get_value(
			$field->get_name(),
			'term_' . $item[ 'id' ]
		);
	}

	/**
	 * Updates a REST value through the data API.
	 *
	 * @since 3.0
	 *
	 * @param  Ultimate_Fields\Field $field The field whose value is being saved.
	 * @param  mixed     $value The value to save.
	 * @param  WP_Post   $item  The item that the value should be associated with.
	 * @return bool
	 */
	protected function save_api_value( $field, $value, $item ) {
		return Data_API::instance()->update_value(
			$field->get_name(),
			$value,
			'term_' . $item->term_ID
		);
	}

	/**
	 * Performs AJAX when needed.
	 *
	 * @since 3.0
	 */
	public function do_ajax( $screen ) {
		if( in_array( $screen->base, array( 'edit-tags', 'term' ) ) ) {
			ultimate_fields()->ajax( $screen->taxonomy );
		}
	}
}
