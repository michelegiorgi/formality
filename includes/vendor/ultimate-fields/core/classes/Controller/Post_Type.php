<?php
namespace Ultimate_Fields\Controller;

use Ultimate_Fields\Controller;
use Ultimate_Fields\Template;
use Ultimate_Fields\Data_API;
use Ultimate_Fields\Helper\Callback;
use Ultimate_Fields\Field;

/**
 * Handles post meta locations.
 *
 * @since 3.0
 */
class Post_Type extends Controller {
	use REST_API, Admin_Column_Manager;

	/**
	 * Indicates if after saving a post, it's revision should be updated too.
	 *
	 * @since 3.0
	 * @var int|bool
	 */
	protected $should_save_revision = false;

	/**
	 * Indicates if the current post is in preview mode.
	 *
	 * When this is on, values will be replaced by the datastores, instead of being saved.
	 *
	 * @since 3.0
	 * @var bool
	 */
	protected $preview_mode = false;

	/**
	 * Indicates if a post is being saved/updated right now.
	 *
	 * @since 3.0
	 * @var int
	 */
	protected $saving_post;

	/**
	 * Adds the neccessary hooks for the controller.
	 *
	 * @since 3.0
	 */
	protected function __construct() {
		# Switch to preview mode if needed
		if( isset( $_POST[ 'wp-preview' ] ) && 'dopreview' == $_POST[ 'wp-preview' ] ) {
			$this->preview_mode = true;
		}

		add_action( 'add_meta_boxes', array( $this, 'do_ajax' ), 8, 2 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 10, 2 );
		add_action( 'save_post', array( $this, 'save_post' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'current_screen', array( $this, 'initialize_admin_columns' ) );
		add_action( 'save_post', array( $this, 'save_revision' ) );
		add_action( '_wp_post_revision_fields',   array( $this, 'revision_fields' ), 10, 2 );
		add_action( 'wp_restore_post_revision',   array( $this, 'restore_fields' ), 10, 2 );
		add_filter( 'wp_save_post_revision_check_for_changes', array( $this, 'check_for_changes' ), 10, 3 );
		add_action( 'edit_form_after_title', array( $this, 'output_sortables_after_title' ) );
		add_action( 'pre_post_update', array( $this, 'enter_saving_mode' ) );
		add_action( 'post_updated', array( $this, 'quit_saving_mode' ) );

		$this->rest();
	}

	/**
	 * Attaches metaboxes when neccessary.
	 *
	 * @since 3.0
	 */
	public function add_meta_boxes( $post_type, $post = null ) {
		foreach( $this->combinations as $combination ) {
			if( $post || is_a( $post, 'WP_Post' ) ) {
				$this->handle_container( $combination, $post_type, $post );
			}
		}
	}

	/**
	 * Handles a specific container-locations combination.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $combination The processable combination.
	 */
	protected function handle_container( $combination, $post_type, $post ) {
		$container  = $combination[ 'container' ];
		$post_types = array();
		$ids        = array();
		$parents    = array();
		$context    = $default_context  = 'normal';
		$priority   = $default_priority = 'high';

		# Go through each combination and extract post types + context and priority.
		foreach( $combination[ 'locations' ] as $location ) {
			$post_types = array_merge( $post_types, $location->get_post_types() );
			$ids        = array_merge_recursive( $ids, $location->get_ids() );
			$parents    = array_merge_recursive( $parents, $location->get_parents() );

			if( ( $location_context = $location->get_context() ) != $default_context ) {
				$context = $location_context;
			}

			if( ( $location_priority = $location->get_priority() ) != $default_priority ) {
				$priority = $location_priority;
			}
		}

		# Create a custom callback that will include the conainer.
		$callback = new Callback( array( $this, 'display' ) );
		$callback[ 'container' ] = $container;

		# Check for particular post IDs
		$id_found = false;
		if( ! empty( $ids ) ) {
			if( in_array( $post->ID, $ids[ 'hidden' ] ) ) return;
			if( ! empty( $ids[ 'visible' ] ) && ! in_array( $post->ID, $ids[ 'visible' ] ) ) return;
			$id_found = true;
		}

		# Check for parent-based logic
		if( empty( $post_types ) && ! empty( $parents ) ) {
			$post_types = $post_type;
		}

		# If there are no post types assigned, don't add the meta box!
		if( empty( $post_types ) && ! $id_found ) {
			return;
		} elseif( $id_found ) {
			$post_types = $post_type;
		}

		# Add the meta box
		add_meta_box(
			$container->get_id(),
			$container->get_title(),
			$callback->get_callback(),
			$post_types,
			$context,
			$priority
		);
	}

	/**
	 * Displays a container.
	 *
	 * @since 3.0
	 *
	 * @param Callback $callback The callback that is being called.
	 * @param WP_Post  $post     The post that is being displayed.
	 */
	public function display( $callback, $post ) {
		$container = $callback[ 'container' ];

		$locations     = array();
		$datastore_set = false;

		foreach( $container->get_locations() as $location ) {
			if( ! is_a( $location, 'Ultimate_Fields\\Location\\Post_Type' ) ) {
				continue;
			}

			$post_types = $location->get_post_types();

			if( ! in_array( $post->post_type, $post_types ) ) {
				$continue;
			}

			# Use the first available location to get a proper datastore
			if( ! $datastore_set ) {
				$container->set_datastore( $location->get_datastore( $post ) );
				$datastore_set = true;
			}

			$locations[] = $location->export_settings( $post->post_type );
		}

		$settings = $container->export_settings();
		$settings[ 'locations' ] = $locations;

		$json = array(
			'type'      => 'Post_Type',
			'settings'  => $settings,
			'data'      => $container->export_data()
		);

        echo sprintf(
            '<div class="uf-container" data-type="%s">
				<script type="text/json">%s</script>
				' . $this->get_no_js_message() . '
				<span class="spinner hide-if-no-js"></span>
			</div>',
            'Post_Type',
            json_encode( $json )
        );

		if( 'seamless' == $settings[ 'style' ] ) {
			$this->unbox();
		}

		# Register the neccessary templates
		Template::add( 'post-type', 'container/post-type' );
		Template::add( 'container-error', 'container/error' );
		Template::instance()->output_templates();

		# Force-initialize
		?>
		<script type="text/javascript">
		UltimateFields.initializeContainers()
		</script>
		<?php
	}

	/**
	 * Enqueues scripts when needed.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		# Post meta containers don't work in the front-end
		if( ! is_admin() || ! function_exists( 'get_current_screen' ) ) {
			return false;
		}

		# Check if a post type is being edited.
		$screen_id = get_current_screen()->id;
		$combos    = array();

		foreach( $this->combinations as $combination ) {
			$post_types = array();

			# Gather all post types
			foreach( $combination[ 'locations' ] as $location ) {
				$post_types = array_merge( $post_types, $location->get_post_types() );
				$this->setup_admin_columns( $location, $combination[ 'container' ] );
			}

			# Check if the container is being displayed
			if( ! empty( $post_types ) && ! in_array( $screen_id, $post_types ) ) {
				continue;
			}

			# Enqueue post-meta scripts and then let the container include fields.
			wp_enqueue_script( 'uf-container-post-type' );
			wp_enqueue_script( 'ultimate-fields-min' );

			$combos[] = $combination;
		}

		if( empty( $combos ) ) {
			return;
		}

		# Ensure unique field names for the needed combinations
		$this->ensure_unique_field_names( $combos );

		# Finally, enqueue the scripts
		foreach( $combos as $combination ) {
			$combination[ 'container' ]->enqueue_scripts();
		}

		# Attach translations
		ultimate_fields()->l10n()->enqueue();

		return false;
	}

	/**
	 * Handles post saving.
	 *
	 * @since 3.0
	 *
	 * @param int $post_id The post to save.
	 */
	public function save_post( $post_id ) {
		// Don't interfere with other sites
		if( is_multisite() && ms_is_switched() ) {
			return;
		}

		// Autosaves are crippled in terms of meta, so we want to avoid them
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if( ! $this->preview_mode && ( ! is_admin() || defined( 'UF_UI_IMPORTING' ) ) ) {
			return;
		}

		$queue      = array();
		$datastores = array();
		$post       = get_post( $post_id );

		# Simply save a revision
		if(
			( ! $this->preview_mode && wp_is_post_revision( $post_id ) )
			|| 'auto-draft' == $post->post_status
			|| 'trash' == $post->post_status
		) {
			return;
		}

		# In preview mode, switch to the real post first
		if( $this->preview_mode ) {
			$revision = $post;
			$post     = get_post( $post->post_parent );
		}

		# Add containers to the saving queue
		foreach( $this->combinations as $combination ) {
			$post_types = array();
			$locations  = array();

			# Gather all post types
			foreach( $combination[ 'locations' ] as $location ) {
				$enqueue = false;

				$post_types = $location->get_post_types();
				$ids        = $location->get_ids();
				$parents    = $location->get_parents();

				# Check if the container is being saved
				if( ! empty( $post_types ) && ! in_array( $post->post_type, $post_types ) ) {
					continue;
				}

				# Check for IDs
				if( empty( $post_types ) && empty( $ids ) && empty( $parents ) ) {
					continue;
				}

				if( ! empty( $ids ) && ! $location->check_single_value( $post_id, $ids ) ) {
					continue;
				}

				if( ! empty( $parents ) && ! $location->check_single_value( $post_id, $parents ) ) {
					continue;
				}

				$datastore = $location->get_datastore( $post );
				$combination[ 'container' ]->set_datastore( $datastore );

				$datastores[] = $datastore;
				$locations[] =  $location;
			}

			if( ! empty( $locations ) ) {
				$queue[] = array(
					'container' => $combination[ 'container' ],
					'locations' => $locations
				);
			}
		}

		# If there are no matching containers, don't proceed
		if( empty( $queue ) ) {
			return;
		}

		# This will hold all messages
		$errors = array();

		# Ensure unique field names for the needed combinations
		$this->ensure_unique_field_names( $queue );

		# Try saving each of the containers in the queue
		foreach( $queue as $combination ) {
			$container = $combination[ 'container' ];

			# Prepare the data for the container
			$data_key = 'uf_post_type_' . $container->get_id();
			$data = isset( $_POST[ $data_key ] )
				? json_decode( stripslashes( $_POST[ $data_key ] ), true )
				: array();

			# Save the container into the datastore
			$messages = $container->save( $data );

			# If the container is visible for the post, get errors
			$visible = false;
			foreach( $combination[ 'locations' ] as $location ) {
				if( $location->works_with( $post ) ) {
					$visible = true;
					break;
				}
			}
			if( $visible ) {
				$errors = array_merge( $errors, $messages );
			}
		}

		# If in preview mode, don't complain, just replace values
		if( $this->preview_mode ) {
			foreach( $datastores as $datastore ) {
				# Switch to the previewed thing
				$datastore->set_id( $revision->ID );
				$datastore->commit();
			}

			return;
		}

		# Die if there are errors
		if( ! empty( $errors ) ) {
			$this->error( $errors );
		}

		# Commit the datastores
		foreach( $datastores as $datastore ) {
			$datastore->commit();
		}

		# Save revision data if needed
		if( false !== $this->should_save_revision ) {
			$revisions = wp_get_post_revisions( $post_id );
			$revision  = array_shift( $revisions );

			# Let the datastores add data for the revision
			foreach( $datastores as $datastore ) {
				foreach( $datastore->get_values() as $key => $value ) {
					add_metadata( 'post', $revision->ID, $key, $value );
				}
			}

			$this->should_save_revision = false;
		}
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
		foreach( $location->get_post_types() as $slug ) {
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
		return Data_API::instance()->get_value( $field->get_name(), $item[ 'id' ] );
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
		return Data_API::instance()->update_value( $field->get_name(), $value, $item->ID );
	}

	/**
	 * Returns all combinations, which work with a certain post.
	 *
	 * @since 3.0
	 *
	 * @param WP_Post $post The post to check upon.
	 * @return mixed[]
	 */
	protected function get_combinations_for_post( $post ) {
		$combinations = array();

		foreach( $this->combinations as $combination ) {
			$works = false;

			foreach( $combination[ 'locations' ] as $location ) {
				if( $location->works_with( $post ) ) {
					$works = true;
					break;
				}
			}

			if( $works ) {
				$combinations[] = $combination;
				continue;
			}
		}

		return $combinations;
	}

	/**
	 * When a revisition is being saved, this will save a flag that indicates it should be cloned.
	 *
	 * @since 3.0
	 *
	 * @param int $revision_id The ID of the revision that is being saved.
	 */
	public function save_revision( $revision_id ) {
		if( $parent_id = wp_is_post_revision( $revision_id ) ) {
			$this->should_save_revision = $revision_id;
		}
	}

	/**
	 * Adds the neccessary hooks for managing revision fields.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $fields A simple set of field_name => label pairs.
	 * @param WP_post $post   The post that will be revised.
	 * @return mixed[]
	 */
	public function revision_fields( $revision_fields, $post = null ) {
		// Fix WP-API inconsistencies regarding the particular post
		if( is_null( $post ) ) {
			return $revision_fields;
		}

		# Switch to a proper post
		if( is_array( $post ) ) {
			$post = get_post( $post[ 'ID' ] );
		}

		# If there is a change indicator, add it as a field to let WP know there is something new.
		if( isset( $_POST[ 'uf_has_changed' ] ) && '1' === $_POST[ 'uf_has_changed' ] ) {
			$revision_fields[ 'uf_has_changed' ] = true;
		} elseif( $this->saving_post ) {
			return $revision_fields;
		}

		# Get all combinations, which work with the post
		$combinations = $this->get_combinations_for_post( $post );

		# Add fields to the mix
		foreach( $combinations as $combination ) {
			$container = $combination[ 'container' ];

			foreach( $container->get_fields() as $field ) {
				# Tabs and sections don't need revisions
				if( is_a( $field, Field\Tab::class ) || is_a( $field, Field\Section::class ) ) {
					continue;
				}

				# Add as a revision field
				$revision_fields[ $field->get_name() ] = $field->get_label();

				# Add a filter for the rendering of the column
				$callback = new Callback( array( $this, 'display_revision_value' ) );
				$callback[ 'field' ] = $field;

				add_filter(
					'_wp_post_revision_field_' . $field->get_name(),
					$callback->get_callback(),
					10, 3
				);
			}
		}

		return $revision_fields;
	}

	/**
	 * Displays the value of a field for a certain revision.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Callback $callback A callback that contains the field object to work with.
	 * @param mixed        $value    The value to work with.
	 * @param string       $field    The name of the field.
	 * @param WP_Post      $revision The revision that is being displayed.
	 * @return mixed
	 */
	public function display_revision_value( $callback, $value, $field, $revision ) {
		$field = $callback[ 'field' ];

		if( ( $processed = $field->process( $value ) ) && ! is_object( $processed ) ) {
			$value = $processed;
		}

		if( is_array( $value ) ) {
			$args = 0;
			if( defined( 'JSON_PRETTY_PRINT' ) ) {
				$args |= JSON_PRETTY_PRINT;
			}
			$value = json_encode( $value, $args );
		}

		return $value;
	}

	/**
	 * Restores the values for a revision.
	 *
	 * @since 3.0
	 *
	 * @param int $post_id     The post whose data should be updated.
	 * @param int $revision_id The ID of the revision.
	 */
	public function restore_fields( $post_id, $revision_id ) {
		if( ! $parent_id = wp_is_post_revision( $revision_id ) ) {
			return;
		}

		# Get the real post
		$post     = get_post( $parent_id );
		$revision = get_post( $revision_id );

		# If an autosave is being restored, ignore this, in order not to remove any data
		if( false !== stripos( $revision->post_name, 'autosave-' ) ) {
			return;
		}

		# Get the combinations that work with the post
		$combinations = $this->get_combinations_for_post( $post );
		if( empty( $combinations ) ) {
			return;
		}

		# Get a datastore
		foreach( $combinations as $combination ) {
			$container = $combination[ 'container' ];
			foreach( $container->get_fields() as $field ) {
				foreach( $field->get_datastore_keys() as $key ) {
					$value = get_metadata( 'post', $revision_id, $key, true );
					update_post_meta( $post_id, $key, $value );
				}
			}
		}
	}

	/**
	 * Checks whether a post has any changes when saving.
	 *
	 * @since 3.0
	 *
	 * @param bool    $has_changes A pre-determined value based on content and title.
	 * @param WP_Post $revision    The last available revision.
	 * @param WP_Post $post        The post that has the new data.
	 * @return bool
	 */
	public function check_for_changes( $has_changed, $revision, $post ) {
		if( isset( $_POST[ 'uf_has_changed' ] ) && '1' === $_POST[ 'uf_has_changed' ] ) {
			$post->uf_has_changed = time();
			return true;
		} else {
			$post->uf_has_changed = '';
			return $has_changed;
		}
	}

	/**
	 * Does meta boxes after the title of a post.
	 *
	 * @since 3.0
	 *
	 * @param WP_Post $post The post object.
	 */
	public function output_sortables_after_title( $post ) {
		static $done;

		if( $done ) {
			return;
		}

		?>
		<div class="uf-after-title">
			<?php do_meta_boxes( $post->post_type, 'after_title', $post ); ?>
		</div>
		<?php

		$done = true;
	}

	/**
	 * Allows AJAX actions to be performed.
	 *
	 * @since 3.0
	 *
	 * @param string  $post_type The post type that is being edited.
	 * @param WP_Post $post      The post type that is being edited.
	 */
	public function do_ajax( $post_type, $post = null ) {
		if( is_a( $post, 'WP_Post' ) ) {
			ultimate_fields()->ajax( $post );
		}
	}

	/**
	 * Enters saving mode.
	 *
	 * @since 3.0
	 *
	 * @param int $post_id The ID of the post that is being saved.
	 */
	public function enter_saving_mode( $post_id ) {
		$this->saving_post = $post_id;
	}

	/**
	 * Quits the saving mode.
	 *
	 * @since 3.0
	 *
	 * @param int $post_id The ID of the post that is being saved.
	 */
	public function quit_saving_mode( $post_id ) {
		if( $post_id == $this->saving_post ) {
			$this->saving_post = false;
		}
	}
}
