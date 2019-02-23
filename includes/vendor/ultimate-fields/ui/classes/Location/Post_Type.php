<?php
namespace Ultimate_Fields\UI\Location;

use Ultimate_Fields\UI\Location;
use Ultimate_Fields\Field;
use Ultimate_Fields\UI\Post_Type as UI_Post_Type;
use Ultimate_Fields\Location\Post_Type as Core_Location;
use Ultimate_Fields\Helper\Util;

/**
 * Handles the post meta locations.
 *
 * @since 3.0
 */
class Post_Type extends Location {
	/**
	 * Returns the type of the location (e.g. post_type).
	 *
	 * @since 3.0
	 * @return string
	 */
	public static function get_type() {
		return 'post_type';
	}

	/**
	 * Returns the name of the location.
	 *
	 * @since 3.0
	 * @return string
	 */
	public static function get_name() {
		return __( 'Post Type', 'ultimate-fields' );
	}

	/**
	 * Returns the fields for the location.
	 *
	 * @since 3.0
	 * @return Ultimate_Fields\Field[]
	 */
	public static function get_fields() {
		$fields = array();

		# Prepare the options for the post types multiselect
		$post_types   = array();
		$hierarchical = array();

		/**
		 * Allows post types to be excluded from the UI.
		 *
		 * @since 3.0
		 *
		 * @param string[] $post_types The post types to ignore.
		 * @return string[]
		 */
		$excluded = apply_filters( 'uf.excluded_post_types', array( 'attachment', UI_Post_Type::instance()->get_slug() ) );

		foreach( get_post_types( array( 'show_ui' => true ), 'objects' ) as $id => $post_type ) {
			if( in_array( $id, $excluded ) ) {
				continue;
			}

			$post_types[ $id ] = __( $post_type->labels->name );
			if( is_post_type_hierarchical( $id ) ) {
				$hierarchical[ $id ] = __( $post_type->labels->name );
			}
		}

		# Prepare page templates
		$templates = array(
			'default' => __( 'Default' )
		);

		$raw = wp_get_theme()->get_page_templates();
		foreach( $raw as $template => $name ) {
			$templates[ $template ] = $name;
		}

		$fields[] = Field::create( 'tab', 'basic_settings', __( 'Basic Settings' ) )
			->set_icon( 'dashicons-admin-post' );

		# Add the choice to show the container based on rules or actual posts
		$fields[] = Field::create( 'radio', 'location_type', __( 'Location type', 'ultimate-fields' ) )
			->add_options(array(
				'location' => __( 'Show the container based on rules', 'ultimate-fields' ),
				'post'     => __( 'Show the container based on a particular post or page', 'ultimate-fields' )
			));

		$fields[] = Field::create( 'multiselect', 'post_types', __( 'Post Types', 'ultimate-fields' ) )
			->required()
			->add_options( $post_types )
			->set_input_type( 'checkbox' )
			->set_description( __( 'The container will be displayed on all of the checked post types above.', 'ultimate-fields' ) )
			->add_dependency( 'location_type', 'location' );

		if( count( $templates ) > 1 ) {
			$fields[] = Field::create( 'complex', 'templates', __( 'Templates', 'ultimate-fields' ) )
				->add_fields(array(
					Field::create( 'multiselect', 'visible', __( 'Show on', 'ultimate-fields' ) )
						->add_options( $templates )
						->set_input_type( 'checkbox' )
						->set_width( 50 ),
					Field::create( 'multiselect', 'hidden', __( 'Hide on', 'ultimate-fields' ) )
						->add_options( $templates )
						->set_input_type( 'checkbox' )
						->set_width( 50 )
				))
				->add_dependency( 'location_type', 'location' )
				->add_dependency( 'post_types', 'page', 'contains' )
				->set_description( __( 'The box will only appear on the checked templates, if any. If none are checked, the container will appear on all pages.', 'ultimate-fields' ) )
				;
		}

		# Add taxonomies
		foreach( get_taxonomies( array( 'show_ui' => true, 'hierarchical' => true ), 'objects' ) as $slug => $taxonomy ) {
			$description = __( 'Control the visiblity of the container based on the terms of the "%s" taxonomy.', 'ultimate-fields' );
			$description = sprintf( $description, $taxonomy->labels->name );
			$fields[] = Field::create( 'complex', $slug, $taxonomy->labels->name )
				->set_description( $description )
				->add_dependency( 'location_type', 'location' )
				->add_dependency( 'post_types', $taxonomy->object_type, 'contains' )
				->add_fields(array(
					Field::create( 'wp_objects', 'visible', __( 'Show on', 'ultimate-fields' ) )
						->add( 'terms', 'taxonomy=' . $slug )
						->set_width( 50 ),
					Field::create( 'wp_objects', 'hidden', __( 'Hide on', 'ultimate-fields' ) )
						->add( 'terms', 'taxonomy=' . $slug )
						->set_width( 50 )
				));
		}

		# Add formats
		$formats = get_theme_support( 'post-formats' );
		if( $formats && isset( $formats[ 0 ] ) && count( $formats[ 0 ] ) > 1 ) {
			$options = array();

			foreach( $formats[ 0 ] as $format ) {
				$options[ $format ] = get_post_format_string( $format );
			}

			$fields[] = Field::create( 'complex', 'post_formats', __( 'Formats', 'ultimate-fields' ) )
				->add_fields(array(
					Field::create( 'multiselect', 'visible', __( 'Show on', 'ultimate-fields' ) )
						->add_options( $options )
						->set_input_type( 'checkbox' )
						->set_orientation( 'horizontal' )
						->set_width( 50 ),
					Field::create( 'multiselect', 'hidden', __( 'Hide on', 'ultimate-fields' ) )
						->add_options( $options )
						->set_input_type( 'checkbox' )
						->set_orientation( 'horizontal' )
						->set_width( 50 )
				))
				->add_dependency( 'location_type', 'location' )
				->add_dependency( 'post_types', 'post', 'contains' )
				->set_description( __( 'The container will only appear on the checked formats, if any.', 'ultimate-fields' ) )
				;
		}

		$fields[] = Field::create( 'complex' , 'levels', __( 'Levels', 'ultimate-fields' ) )
			->add_dependency( 'post_types', array_keys( $hierarchical ), 'contains' )
			->add_dependency( 'location_type', 'location' )
			->set_description( __( 'Enter as numbers, separated by commas.', 'ultimate-fields' ) )
			->add_fields(array(
				Field::create( 'text', 'visible', __( 'Show on', 'ultimate-fields' ) )
					->set_default_value( '0' )
					->set_width( 50 ),
				Field::create( 'text', 'hidden', __( 'Hide on', 'ultimate-fields' ) )
					->set_default_value( '0' )
					->set_width( 50 ),
			));

		$fields[] = Field::create( 'complex', 'item', __( 'Item', 'ultimate-fields' ) )
			->add_dependency( 'location_type', 'post' )
			->set_description( __( 'Select a post(type) to use as a base for the rules of the location.', 'ultimate-fields' ) )
			->add_fields(array(
				Field::create( 'wp_object', 'post', __( 'Item', 'ultimate-fields' ) )
					->add( 'posts' )
					->set_width( 50 )
					->hide_label(),
				Field::create( 'select', 'operator', __( 'Operator', 'ultimate-fields' ) )
					->set_input_type( 'radio' )
					->add_dependency( 'post', '', 'NOT_NULL' )
					->add_options(array(
						'is'     => __( 'is', 'ultimate-fields' ),
						'is_not' => __( 'is not', 'ultimate-fields' )
					))
					->set_width( 20 ),
				Field::create( 'select', 'type', __( 'Item type', 'ultimate-fields' ) )
					->set_input_type( 'radio' )
					->add_dependency( 'post', '', 'NOT_NULL' )
					->add_options(array(
						'post'   => __( 'the current post/page', 'ultimate-fields' ),
						'parent' => __( 'the parent of the current post/page', 'ultimate-fields' )
					))
					->set_width( 30 )
			));

		$fields[] = Field::create( 'tab', 'advanced_settings', __( 'Advanced Settings', 'ultimate-fields' ) )
			->set_icon( 'dashicons-admin-generic' );

		$fields[] = Field::create( 'select', 'context', __( 'Context', 'ultimate-fields' ) )
			->set_description( __( 'Which column should the container be displayed in?', 'ultimate-fields' ) )
			->add_options(array(
				'normal'      => __( 'Normal', 'ultimate-fields' ),
				'side'        => __( 'Side', 'ultimate-fields' ),
				'after_title' => __( 'After the title', 'ultimate-fields' ),
			))
			->set_input_type( 'radio' );

		$fields[] = Field::create( 'select', 'priority', __( 'Priority', 'ultimate-fields' ) )
			->set_description( __( 'Either normal for default flow, or High to force higher position.', 'ultimate-fields' ) )
			->add_options(array(
				'high' => __( 'Default (High)', 'ultimate-fields' ),
				'low'  => __( 'Low', 'ultimate-fields' )
			))
			->set_input_type( 'radio' );

		$fields[] = Field::create( 'multiselect', 'post_stati', __( 'Post Stati', 'ultimate-fields' ) )
			->set_input_type( 'checkbox' )
			->add_options( get_post_stati() )
			->set_orientation( 'horizontal' )
 			->set_description( __( 'Listens for post status.', 'ultimate-fields' ) );

		$fields = array_merge( $fields, self::get_customizer_fields() );
		$fields = array_merge( $fields, self::get_rest_fields() );
		$fields = array_merge( $fields, self::get_column_fields() );

		return $fields;
	}

	/**
	 * Exports the location as a real, core location.
	 *
	 * @since 3.0
	 *
	 * @return Core_Location
	 */
	public static function export( $data ) {
		$location = Core_Location::create( 'post_type' );

		if( 'post' == $data['location_type'] ) {
			$item    = $data['item'];
			$post_id = intval( str_replace( 'post_', '', $item['post'] ) );

			if( 'parent' == $item['type'] ) {
				$location->parents = ( 'is' === $item['operator'] ? '' : '-' ) . $post_id;
			} else {
				$location->ids = ( 'is' === $item['operator'] ? '' : '-' ) . $post_id;
			}
		} else {
			$location->add_post_type( isset( $data[ 'post_types' ] ) ? $data[ 'post_types' ] : array( 'post' ) );

			$mappable = array(
				'templates'    => 'templates',
				'post_formats' => 'formats',
				'post_stati'   => 'stati',
				'parents'      => 'parents'
			);

			foreach( $mappable as $from => $to ) {
				if( isset( $data[ $from ] ) && $data[ $from ] ) {
					$location->$to = $data[ $from ];
				}
			}

			# Prepare and set the levels if necessary
			$levels = array(
				'visible' => array(),
				'hidden'  => array()
			);

			if( isset( $data[ 'levels' ] ) && is_array( $data[ 'levels' ] ) ) {
				if( isset( $data[ 'levels' ][ 'visible' ] ) ) {
					$levels[ 'visible' ] = Util::string_to_numbers( $data[ 'levels' ][ 'visible' ] );
				}

				if( isset( $data[ 'levels' ][ 'hidden' ] ) ) {
					$levels[ 'hidden' ] = Util::string_to_numbers( $data[ 'levels' ][ 'hidden' ] );
				}
			}

			if( ! empty( $levels['visible'] ) || ! empty( $levels['hidden'] ) ) {
				$location->levels = $levels;
			}

			# Setup taxonomies
			foreach( get_taxonomies( array( 'show_ui' => true, 'hierarchical' => true ), 'objects' ) as $slug => $taxonomy ) {
				if( ! isset( $data[ $slug ] ) ) {
					continue;
				}

				$terms = array(
					'visible' => array(),
					'hidden'  => array(),
				);

				if( ! empty( $data[ $slug ][ 'visible' ] ) ) foreach( $data[ $slug ][ 'visible' ] as $term )
					$terms[ 'visible' ][] = intval( str_replace( 'term_', '', $term ) );
				if( ! empty( $data[ $slug ][ 'hidden' ] ) ) foreach( $data[ $slug ][ 'hidden' ] as $term )
					$terms[ 'hidden' ][] = intval( str_replace( 'term_', '', $term ) );

				$location->set_terms( $terms, $slug );
			}
		}

		if( $data['context'] ) {
			$location->set_context( $data[ 'context' ] );
		}

		if( $data['priority'] ) {
			$location->set_priority( $data[ 'priority' ] );
		}

		# Setup customizer data
		self::setup_location_customizer( $location, $data );

		# Setup the rest API
		self::setup_location_rest( $location, $data );

		# Setup admin columns
		self::setup_location_columns( $location, $data );

		return $location;
	}

	/**
	 * Returns the data of a core location if it can work with it or false if not.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Location $location The location to export data from.
	 * @return mixed
	 */
	public static function get_settings_for_import( $location ) {
		if( ! is_a( $location, Core_Location::class ) ) {
			return false;
		}

		$data = array(
			'__type'     => self::get_type(),
			'post_types' => $location->get_post_types()
		);

		$simple = array(
			'templates' => 'templates',
			'formats'   => 'post_formats',
			'parents'   => 'parents'
		);

		# Import simple properties
		foreach( $simple as $from => $to ) {
			if( $temp = $location->$from ) {
				$data[ $to ] = $temp;
			}
		}

		# Import levels
		if( $levels = $location->levels ) {
			$data[ 'levels' ] = array(
				'visible' => implode( ',', $levels[ 'visible' ] ),
				'hidden'  => implode( ',', $levels[ 'hidden' ] )
			);
		}

		# Impor stati
		if( $temp = $location->stati )
			$data[ 'post_stati' ] = $temp[ 'visible' ];
		else
			$data[ 'post_stati' ] = array();

		# Get context and priority
		$data[ 'context' ]  = $location->get_context();
		$data[ 'priority' ] = $location->get_priority();

		# Get taxonomies
		$all = $location->terms;
		foreach( get_taxonomies( array( 'show_ui' => true, 'hierarchical' => true ) ) as $slug ) {
			$terms = array(
				'visible' => array(),
				'hidden'  => array()
			);

			if( isset( $all[ $slug ] ) ) {
				foreach( $all[ $slug ][ 'visible' ] as $term ) $terms[ 'visible' ][] = 'term_' . $term;
				foreach( $all[ $slug ][ 'hidden' ] as $term )  $terms[ 'hidden' ][]  = 'term_' . $term;
			}

			$data[ $slug ] = $terms;
		}

		# Check for customizer data
		self::import_customizer( $location, $data );

		# Check for REST data
		self::import_rest( $location, $data );

		# Check for admin columns
		self::import_admin_columns( $location, $data );

		return $data;
	}
}
