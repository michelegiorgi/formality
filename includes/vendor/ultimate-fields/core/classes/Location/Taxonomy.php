<?php
namespace Ultimate_Fields\Location;

use Ultimate_Fields\Location;
use Ultimate_Fields\Controller\Taxonomy as Controller;
use Ultimate_Fields\Datastore\Term_Meta as Datastore;
use Ultimate_Fields\Form_Object\Term as Form_Object;
use Ultimate_Fields\Helper\Data_Source;
use Ultimate_Fields\Helper\Util;
use Ultimate_Fields\Helper\Callback;
use Ultimate_Fields\Location\Supports_Columns;

/**
 * Works as a location definition for containers within terms.
 *
 * @since 3.0
 */
class Taxonomy extends Location {
	use Customizable, Supports_Columns;

	/**
	 * Holds all taxonomies for the location.
	 *
	 * @since 3.0
	 * @var string[]
	 */
	protected $taxonomies = array();

	 /**
	  * Holds all levels, which a container would be displayed on.
	  * Enter only numeric levels (1,2,3,etc.).
	  *
	  * @since 3.0
	  * @var mixed[]
	  */
	 protected $levels = array();

	 /**
	  * Holds parent term IDs, which the container works with.
	  *
	  * @since 3.0
	  * @var int[]
	  */
	 protected $parents = array();

	 /**
	  * Holds particular term IDs, which the locationw orks with.
	  *
	  * @since 3.0
	  * @var int[]
	  */
	 protected $terms = array();

	/**
	 * Creates an instance of the class.
	 * The parameters for this constructor are the same as the parameters of Container->add_location().
	 *
	 * @since 3.0
	 *
	 * @param string  $tax  The taxonomies to show the container on.
	 * @param mixed[] $args Additional arguments for the location.
	 */
	public function __construct( $tax = array(), $args = array() ) {
		$this->add_taxonomy( $tax );

		$this->check_args_for_columns( $args );
		$this->check_args_for_customizer( $args );

		# Send all arguments to the appropriate setter.
		$this->arguments = $args;
	}

	/**
	 * Returns an instance of the controller, which controls the location (terms).
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\Controller\Term
	 */
	public function get_controller() {
		return Controller::instance();
	}

	/**
	 * Adds a taxonomy to the location.
	 *
	 * @since 3.0
	 *
	 * @param string $taxonomy The name of the taxonomy (or multiple taxonomies).
	 * @return Location The instance of the location.
	 */
	public function add_taxonomy( $taxonomy ) {
		foreach( (array) $taxonomy as $tax ) {
			$this->taxonomies[] = $tax;
		}

		return $this;
	}

	/**
	 * Returns all used taxonomies.
	 *
	 * @since 3.0
	 *
	 * @return string[]
	 */
	public function get_taxonomies() {
		return $this->taxonomies;
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
	 * Handles parents on hierarchical taxonomies.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $parents The parents to show/hide the container on.
	 * @return Ultimate_Fields\Location\Post_Type
	 */
	protected function set_parents( $parents, $taxonomy = null ) {
		# Prepare a callback for the parser
		$callback = new Callback( array( Util::class, 'parse_terms' ) );
		$callback[ 'taxonomy' ] = $this->taxonomies;

		# Convert to proper rules
		$this->parents = $this->handle_value( $this->parents, $parents, $callback->get_callback() );

		return $this;
	}

	/**
	 * Handles particular term IDs for the location.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $terms The terms to show/hide the container on.
	 * @return Ultimate_Fields\Location\Post_Type
	 */
	protected function set_terms( $terms, $taxonomy = null ) {
		# Prepare a callback for the parser
		$callback = new Callback( array( Util::class, 'parse_terms' ) );
		$callback[ 'taxonomy' ] = $this->taxonomies;

		# Convert to proper rules
		$this->terms = $this->handle_value( $this->terms, $terms, $callback->get_callback() );

		return $this;
	}

	/**
	 * Returns the settings for the location, sendable to JS.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export_settings() {
		$this->parse_arguments( $this->arguments );
		$data = array();

		if( ! empty( $this->levels ) ) {
			$data[ 'levels' ] = $this->levels;
		}

		if( ! empty( $this->parents ) ) {
			$data[ 'parents' ] = $this->parents;
		}

		return $data;
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
		if( ! is_a( $object, 'WP_Term' ) && ! is_int( $object ) ) {
			return false;
		}

		$datastore = new Datastore;
		$datastore->set_id( is_int( $object ) ? $object : $object->term_id );

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

		# Check for basic taxonomies
		if( is_string( $source ) && in_array( $source, $this->get_taxonomies() ) ) {
			return true;
		}

		# If there's no object, we don't work with it
		if( ! is_object( $source ) ) {
			return false;
		}

		# Resolve to a proper post
		if( is_a( $source, 'WP_Term' ) ) {
			$term = $source;
		} elseif( is_a( $source, Data_Source::class ) && 'term_meta' == $source->type && $existing = get_term( $source->item ) ) {
			$term = $existing;
		} else {
			return false;
		}

		# Check for IDs
		if( ! empty( $this->terms ) && ! $this->check_single_value( $term->term_id, $this->terms ) ) {
			return false;
		}

		# Check for parents
		if( ! empty( $this->parents ) && ! $this->check_single_value( $term->parent, $this->parents ) ) {
			return false;
		}

		# Check for levels
		if( ! empty( $this->levels ) ) {
			$level = 1;
			$temp  = $term;
			while( $temp->parent ) {
				$temp = get_term( $term->parent );
				$level++;
			}

			if( in_array( $level, $this->levels[ 'hidden' ] ) ) {
				return false;
			}

			if( ! empty( $this->levels[ 'visible' ] ) && ! in_array( $level, $this->levels[ 'visible' ] ) ) {
				return false;
			}
		}

		return $term;
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
		if( ! in_array( $object->get_type(), $this->taxonomies ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Checks if the location works with a specific term.
	 *
	 * This check should only be performed by controllers, in order to know if the location
	 * should be loaded at all when a particular term is being edited.
	 *
	 * @since 3.0
	 *
	 * @param mixed $term The term to check.
	 * @return bool
	 */
	public function works_with_term( $term ) {
		$this->parse_arguments( $this->arguments );

		if( empty( $this->terms ) ) {
			return true;
		}

		# If the term is a string, it's not existing
		if( is_string( $term ) ) {
			return false;
		}

		# Actually check the term
		return $this->check_single_value( $term->term_id, $this->terms );
	}

	/**
	 * Checks if the location should be displayed in the customizer based on the current category.
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
	public function customizer_active_callback() {
		if( is_admin() ) {
			return false;
		}

		if( get_queried_object() && is_a( get_queried_object(), 'WP_Term' ) ) {
			$term = get_queried_object();
			$source = Data_Source::parse( 'term_' . $term->term_id, 'customizer' );
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
		foreach( $this->taxonomies as $slug ) {
			add_filter( "manage_edit-{$slug}_columns",          array( $this, 'change_columns' ) );
			add_action( "manage_{$slug}_custom_column" ,        array( $this, 'manage_column' ), 10, 3 );
			add_filter( "manage_edit-{$slug}_sortable_columns", array( $this, 'change_sortable_columns' ) );
			add_action( 'parse_term_query',                     array( $this, 'sort_query_by_columns' ) );
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
		if( ! is_admin() || 'edit-tags' != get_current_screen()->base ) {
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

		$query->query_vars[ 'orderby' ] = 'meta_value';
		$query->query_vars[ 'meta_key' ] = $orderby;
		$query->query_vars[ 'order' ] = $order;
	}

	/**
	 * Outputs the value of a column.
	 *
	 * @since 3.0
	 *
	 * @param string $column_name The name of the column to display.
	 * @param int    $item_id     The ID of the item that is being displayed.
	 */
	public function manage_column( $output, $column_name, $item_id ) {
		return $this->render_column( $column_name, $item_id );
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

		# Add the basic taxonomies
		$settings[ 'taxonomies' ] = $this->taxonomies;

		# Add other rules
		$this->export_rule( $settings, 'levels' );
		$this->export_rule( $settings, 'parents' );
		$this->export_rule( $settings, 'terms' );

		# Export customizable data
		$this->export_customizable_data( $settings );

		# Export REST data
		$this->export_rest_data( $settings );

		# Export columns
		$this->export_column_data( $settings );

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
		if( isset( $args[ 'taxonomies' ] ) ) {
			$this->add_taxonomy( $args[ 'taxonomies' ] );
		}

		foreach( array( 'levels', 'parents', 'terms' ) as $property ) {
			if( isset( $args[ $property ] ) ) {
				$this->arguments[ $property ] = $args[ $property ];
				unset( $args[ $property ] );
			}
		}

		# Check for the customizer
		$this->import_customizable_data( $args );

		# Check for columns
		$this->import_column_data( $args );

		# Check for rest data
		$this->import_rest_data( $args );
	}
}
