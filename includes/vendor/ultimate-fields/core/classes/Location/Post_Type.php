<?php
namespace Ultimate_Fields\Location;

use Ultimate_Fields\Location;
use Ultimate_Fields\Controller\Post_Type as Controller;
use Ultimate_Fields\Datastore\Post_Meta as Datastore;
use Ultimate_Fields\Form_Object\Post as Form_Object;
use Ultimate_Fields\Helper\Callback;
use Ultimate_Fields\Helper\Data_Source;
use Ultimate_Fields\Helper\Util;
use Ultimate_Fields\Admin_Column;

/**
 * Works as a location definition within containers.
 *
 * @since 3.0
 */
class Post_Type extends Location {
	use Supports_Columns, Customizable;

	/**
	 * Holds the post types for the location.
	 *
	 * @since 3.0
	 * @var string[]
	 */
	protected $post_types = array();

	/**
	 * Holds the context of the meta box.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $context = 'normal';

	/**
	 * Holds the priority of the meta box.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $priority = 'high';

	/**
	 * Holds the templates the location applies to.
	 *
	 * @since 3.0
	 * @var mixed[]
	 */
	protected $templates = array();

	/**
	 * Holds taxonomies/terms the location applies to.
	 *
	 * @since 3.0
	 * @var mixed[]
	 */
	protected $terms = array();

	/**
	 * Holds all levels, which a container would be displayed on.
	 * Enter only numeric levels (1,2,3,etc.).
	 *
	 * @since 3.0
	 * @var mixed[]
	 */
	protected $levels = array();

	/**
	 * Holds post formats for the location.
	 *
	 * @since 3.0
	 * @var mixed[]
	 */
	protected $formats = array();

	/**
	 * Holds stati, which the location works with.
	 *
	 * @since 3.0
	 * @var mixed[]
	 */
	protected $stati = array();

	/**
	 * Holds IDs, which the location works with.
	 *
	 * @since 3.0
	 * @var mixed[]
	 */
	protected $ids = array();

	/**
	 * Holds IDs of parent pages, which we'd work with.
	 *
	 * @since 3.0
	 * @var mixed[]
	 */
	protected $parents = array();

	/**
	 * Indivates if the location should be shown below the title.
	 *
	 * @since 3.0
	 * @var bool
	 */
	protected $after_title = false;

	/**
	 * Creates an instance of the class.
	 * The parameters for this constructor are the same as the parameters of Container->add_location().
	 *
	 * @since 3.0
	 *
	 * @param string  $post_type Either a single post type or an array of post types.
	 * @param mixed[] $args      Additional arguments for the location.
	 */
	public function __construct( $post_type = null, $args = array() ) {
		if( $post_type ) {
			$this->add_post_type( $post_type );
		}

		$this->check_args_for_columns( $args );

		$this->set_and_unset( $args, array(
			'show_after_title' => 'show_after_title'
		));
		
		$this->check_args_for_customizer( $args );

		parent::__construct( $args );
	}

	/**
	 * Returns an instance of the controller, which controls the location (posts).
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\Controller\Post_Type
	 */
	public function get_controller() {
		return Controller::instance();
	}

	/**
	 * Adds a post type to the location.
	 *
	 * @since 3.0
	 *
	 * @param string $post_type The slug of the post type.
	 * @return Ultimate_Fields\Controller\Post_Type
	 */
	public function add_post_type( $post_type ) {
		foreach( (array) $post_type as $type ) {
			$this->post_types[] = $type;
		}

		return $this;
	}

	/**
	 * Returns the post types for the location.
	 *
	 * @since 3.0
	 *
	 * @return string[]
	 */
	public function get_post_types() {
		return $this->post_types;
	}

	/**
	 * Returns particular post IDs, which the location works with.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function get_ids() {
		if( empty( $this->ids ) && isset( $this->arguments[ 'ids' ] ) )  {
			$this->set_ids( $this->extract_value( $this->arguments[ 'ids' ] ) );
		}

		return $this->ids;
	}


	/**
	 * Returns particular parent IDs, which the location works with.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function get_parents() {
		if( empty( $this->parents ) && isset( $this->arguments[ 'parents' ] ) )  {
			$this->set_parents( $this->extract_value( $this->arguments[ 'parents' ] ) );
		}

		return $this->parents;
	}

	/**
	 * Overwrites the __set method in order to allow the usage taxonomies.
	 *
	 * @since 3.0
	 *
	 * @param string $key   The key that is being set.
	 * @param mixed  $value The value to use.
	 */
	public function __set( $key, $value ) {
		$taxonomies = get_taxonomies();

		if( in_array( $key, $taxonomies ) ) {
			$this->set_terms( $value, $key );
		} else {
			parent::__set( $key, $value );
		}
	}

	/**
	 * Returns the context for the location/meta box.
	 *
	 * @since 3.0
	 * @return string
	 */
	public function get_context() {
		return $this->after_title ? 'after_title' : $this->context;
	}

	/**
	 * Allows the context of the meta box.
	 *
	 * @since 3.0
	 *
	 * @param string $context The context.
	 * @return Ultimate_Fields\Location\Post_Type
	 */
	public function set_context( $context ) {
		$this->context = $context;

		return $this;
	}

	/**
	 * Returns the context for the location/meta box.
	 *
	 * @since 3.0
	 * @return string
	 */
	public function get_priority() {
		return $this->priority;
	}

	/**
	 * Allows the context of the meta box.
	 *
	 * @since 3.0
	 *
	 * @param string $priority The priority.
	 * @return Ultimate_Fields\Location\Post_Type
	 */
	public function set_priority( $priority ) {
		$this->priority = $priority;

		return $this;
	}

	/**
	 * Handles templates.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $templates The templates to show/hide the container on.
	 * @return Ultimate_Fields\Location\Post_Type
	 */
	protected function set_templates( $templates ) {
		$this->templates = $this->handle_value( $this->templates, $templates );

		return $this;
	}

	/**
	 * Handles taxonomies.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $terms The terms to apply to the location.
	 * @param string  $taxonomy The taxonomy to set.
	 * @return Ultimate_Fields\Location\Post_Type
	 */
	public function set_terms( $terms, $taxonomy ) {
		if( ! isset( $this->terms[ $taxonomy ] ) ) {
			$this->terms[ $taxonomy ] = array();
		}

		$callback = new Callback( array( Util::class, 'parse_terms' ) );
		$callback[ 'taxonomy' ] = $taxonomy;
		$callback = $callback->get_callback();

		$this->terms[ $taxonomy ] = $this->handle_value( $this->terms[ $taxonomy ], $terms, $callback );

		return $this;
	}

	/**
	 * Handles hierarchical post-type levels.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $levels The levels to show/hide the container on.
	 * @return Ultimate_Fields\Location\Post_Type
	 */
	protected function set_levels( $levels ) {
		$this->levels = $this->handle_value( $this->levels, $levels );

		return $this;
	}

	/**
	 * Handles post formats for posts.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $formats The formats to show/hide the container on.
	 * @return Ultimate_Fields\Location\Post_Type
	 */
	protected function set_formats( $formats ) {
		$this->formats = $this->handle_value( $this->formats, $formats );
		return $this;
	}

	/**
	 * Handles post stati for the location.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $stati The stati to show/hide the container on.
	 * @return Ultimate_Fields\Location\Post_Type
	 */
	protected function set_stati( $stati ) {
		$this->stati = $this->handle_value( $this->stati, $stati );
		return $this;
	}

	/**
	 * Handles the IDs which the container works with.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $ids The ids to show/hide the container on.
	 * @return Ultimate_Fields\Location\Post_Type
	 */
	protected function set_ids( $ids ) {
		$this->ids = $this->handle_value( $this->ids, $ids );

		return $this;
	}

	/**
	 * Handles particular parent IDs with hierarchical post types.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $parents The parents to show/hide the container on.
	 * @return Ultimate_Fields\Location\Post_Type
	 */
	protected function set_parents( $parents ) {
		$this->parents = $this->handle_value( $this->parents, $parents );

		return $this;
	}

	/**
	 * Moves the container after the title.
	 *
	 * @since 3.0
	 *
	 * @param bool $move Whether to actually move the location (optional).
	 * @return Ultimate_Fields\Location\Post_Type
	 */
	public function show_after_title() {
		$this->set_context( 'after_title' );

		return $this;
	}

	/**
	 * Indicates if the location should be shown after the title instead of in the normal order.
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
	public function is_shown_after_title() {
		return 'after_title' == $this->context;
	}

	/**
	 * Returns the settings for the location, sendable to JS.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export_settings( $post_type ) {
		$this->parse_arguments( $this->arguments );

		$data = array(
			'supports' => $this->get_post_type_supports( $post_type )
		);

		if( ! empty( $this->templates ) ) $data[ 'templates' ] = $this->templates;
		if( ! empty( $this->terms ) )     $data[ 'terms' ]     = $this->terms;
		if( ! empty( $this->levels ) )    $data[ 'levels' ]    = $this->levels;
		if( ! empty( $this->formats ) )   $data[ 'formats' ]   = $this->formats;
		if( ! empty( $this->stati ) )     $data[ 'stati' ]     = $this->stati;
		if( ! empty( $this->ids ) )       $data[ 'ids' ]   = $this->ids;
		if( ! empty( $this->parents ) )   $data[ 'parents' ]   = $this->parents;

		return $data;
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
		$settings[ 'post_types' ] = $this->post_types;

		# Export basic rules
		$this->export_rule( $settings, 'templates' );
		$this->export_rule( $settings, 'levels' );
		$this->export_rule( $settings, 'formats' );
		$this->export_rule( $settings, 'stati' );
		$this->export_rule( $settings, 'parents' );

		# Export taxonomy terms
		foreach( $this->terms as $taxonomy => $terms ) {
			$exported_terms = $this->export_values_for_rule( $terms );

			if( ! empty( $exported_terms ) ) {
				$settings[ $taxonomy ] = $exported_terms;
			}
		}

		# Export REST data
		$this->export_rest_data( $settings );

		# Export columns
		$this->export_column_data( $settings );

		# Export customizable data
		$this->export_customizable_data( $settings );

		# Export location attributes
		if( 'normal' != $this->context )  $settings[ 'context' ]  = $this->context;
		if( 'high'   != $this->priority ) $settings[ 'priority' ] = $this->priority;

		return $settings;
	}

	public function import( $args ) {
		$this->add_post_type( $args[ 'post_types' ] );

		# Go ahead with basic properties
		if( isset( $args[ 'templates' ] ) )  $this->__set( 'templates', $args[ 'templates' ] );
		if( isset( $args[ 'levels' ] ) )     $this->__set( 'levels', $args[ 'levels' ] );
		if( isset( $args[ 'formats' ] ) )    $this->__set( 'formats', $args[ 'formats' ] );
		if( isset( $args[ 'stati' ] ) )      $this->__set( 'stati', $args[ 'stati' ] );
		if( isset( $args[ 'context' ] ) )    $this->set_context( $args[ 'context' ] );
		if( isset( $args[ 'priority' ] ) )   $this->set_priority( $args[ 'priority' ] );
		if( isset( $args[ 'api_fields' ] ) ) $this->expose_api_fields( $args[ 'api_fields' ] );

		# Check for taxonomies
		foreach( get_taxonomies() as $slug ) {
			if( isset( $args[ $slug ] ) ) {
				$this->__set( $slug, $args[ $slug ] );
			}
		}

		# Check for columns
		$this->import_column_data( $args );

		# Check for rest data
		$this->import_rest_data( $args );

		# Check for the customizer
		$this->import_customizable_data( $args );
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
		if( ! is_a( $object, 'WP_Post' ) && ! is_int( $object ) ) {
			return false;
		}

		$datastore = new Datastore;
		$datastore->set_id( is_int( $object ) ? $object : $object->ID );

		return $datastore;
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
		# Initialize arguments if needed
		$this->parse_arguments( $this->arguments );

		# If there is no object, then we don't work with it
		if( ! is_object( $source ) ) {
			return false;
		}

		# Convert to a proper post
		if( is_a( $source, 'WP_Post' ) ) {
			$post = $source;
		} elseif( is_a( $source, Data_Source::class ) && 'post_meta' == $source->type && $existing = get_post( $source->item ) ) {
			$post = $existing;
		} else {
			return false;
		}

		if( ! empty( $this->ids ) ) {
			return $this->check_single_value( $post->ID, $this->ids )
				? $post
				: false;
		}

		if( ! empty( $this->parents ) ) {
			return $this->check_single_value( $post->ID, $this->parents )
				? $post
				: false;
		}

		# Check the post type
		if( ! in_array( $post->post_type, $this->post_types ) ) {
			return false;
		}

		# Check for particular IDs
		if( ! empty( $this->ids ) ) {
			if( ! $this->check_single_value( $post->ID, $this->ids ) ) {
				return false;
			}
		}

		# Check for terms
		if( ! empty( $this->terms ) ) {
			foreach( $this->terms as $taxonomy => $data ) {
				$terms    = get_the_terms( $post->ID, $taxonomy );
				if( ! $terms ) {
					continue;
				}

				$term_ids = wp_list_pluck( $terms, 'term_id' );

				foreach( $data[ 'hidden' ] as $term_id ) {
					if( in_array( $term_id, $term_ids ) ) {
						return false;
					}
				}

				$visible = 0 === count( $data[ 'visible' ] );
				foreach( $data[ 'visible' ] as $term_id ) {
					if( in_array( $term_id, $term_ids ) ) {
						$visible = true;
						break;
					}
				}

				if( ! $visible ) {
					return false;
				}
			}
		}

		# Check for formats
		if( ! empty( $this->formats ) ) {
			if( ! $this->check_single_value( get_post_format( $post->ID ), $this->formats ) ) {
				return false;
			}
		}

		# Check for parents
		if( ! empty( $this->parents ) ) {
			if( ! $this->check_single_value( $post->post_parent, $this->parents ) ) {
				return false;
			}
		}

		# Check for stati
		if( ! empty( $this->stati ) ) {
			if( ! $this->check_single_value( $post->post_status, $this->stati ) ) {
				return false;
			}
		}

		return $post;
	}

	/**
	 * Checks if the location works with a front-end forms object.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Form_Object $object The object to check.
	 * @return bool
	 */
	public function works_with_object( $object ) {
		if( ! is_a( $object, Form_Object::class ) ) {
			return false;
		}

		// ToDo: Check actual rules
		$raw = $object->get_raw_object();
		if( ! in_array( $object->get_type(), $this->post_types ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Checks if the location should be displayed in the customizer based on the current page.
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
	public function customizer_active_callback() {
		if( is_admin() ) {
			return false;
		}

		if( get_queried_object() && is_a( get_queried_object(), 'WP_Post' ) ) {
			$post = get_queried_object();
			$source = Data_Source::parse( $post->ID, 'customizer' );
			return $this->works_with( $source );
		}

		return false;
	}

	/**
	 * Adds the needed filters and actions for admin columns.
	 *
	 * @since 3.0
	 */
	public function init_admin_columns() {
		foreach( $this->post_types as $slug ) {
			add_filter( "manage_{$slug}_posts_columns",         array( $this, 'change_columns' ) );
			add_action( "manage_{$slug}_posts_custom_column" ,  array( $this, 'output_column' ), 10, 2 );
			add_filter( "manage_edit-{$slug}_sortable_columns", array( $this, 'change_sortable_columns' ) );
			add_action( 'pre_get_posts',                        array( $this, 'sort_query_by_columns' ) );
		}
	}

	/**
	 * Modifies a query to sort based on sortable columns.
	 *
	 * @since 3.0
	 *
	 * @param WP_Query $query The query to modify.
	 */
	public function sort_query_by_columns( $query ) {
		if( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if( empty( $this->get_admin_columns() ) || ! isset( $_GET[ 'orderby' ] ) ) {
			return;
		}

		$orderby = $_GET[ 'orderby' ];
		$order   = false;
		foreach( $this->get_admin_columns() as $column ) {
			if( $column->get_name() != $orderby )
				continue;

			$order = 'ASC' == strtoupper( $_GET[ 'order' ] ) ? 'ASC' : 'DESC';
		}

		if( ! $order ) {
			return;
		}

		$query->set( 'orderby', 'meta_value' );
		$query->set( 'meta_key', $orderby );
		$query->set( 'order', $order );
	}

	/**
	 * Outputs the value of a column.
	 *
	 * @since 3.0
	 *
	 * @param string $column_name The name of the column to display.
	 * @param int    $item_id     The ID of the item that is being displayed.
	 */
	public function output_column( $column_name, $item_id ) {
		echo $this->render_column( $column_name, $item_id );
	}

	/**
	 * Returns the rules, supported for a post type.
	 *
	 * @since 3.0
	 *
	 * @return mixed
	 */
	public function get_post_type_supports( $slug ) {
		static $cached;

		if( is_null( $cached ) ) {
			$cached = array();
		}

		if( isset( $cached[ $slug ] ) ) {
			return $cached[ $slug ];
		}

		$templates = array_keys( wp_get_theme()->get_page_templates( null, $slug ) );

		$supports = array(
			'templates'  => count( $templates ) > 1,
			'formats'    => post_type_supports( $slug, 'post-formats' ),
			'taxonomies' => array_keys( get_taxonomies( array( 'object_type' => array( $slug ) ) ) ),
			'levels'     => is_post_type_hierarchical( $slug ),
			'parents'    => is_post_type_hierarchical( $slug )
				? get_post_type_object( $slug )->rest_base
				: false
		);

		return $cached[ $slug ] = $supports;
	}
}
