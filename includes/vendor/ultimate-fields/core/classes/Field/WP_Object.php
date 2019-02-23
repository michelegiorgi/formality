<?php
namespace Ultimate_Fields\Field;

use Ultimate_Fields\Field;
use Ultimate_Fields\Template;

/**
 * Handles the object field.
 *
 * @since 3.0
 */
class WP_Object extends Field {
	/**
	 * Holds all added types.
	 *
	 * @since 3.0
	 * @var Ultimate_Fields\Helper\Object\Type
	 */
	protected $types = array();

	/**
	 * Indicates that only a single item can be selected.
	 *
	 * @since 3.0
	 * @var bool
	 */
	protected $multiple = false;

	/**
	 * Contains the text for the "Select" button.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $button_text;

	/**
	 * Contains the output type for when using the_value()
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $output_type = 'id';

	/**
	 * Holds the text which will be displayed when $output_type is set to 'link'.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $link_text = '';

	/**
	 * Works as a local cache, that saves values from `handle` and uses them in `process`.
	 *
	 * @since 3.0
	 * @var Ultimate_Fields\Helper\Object\Item[]
	 */
	protected $item_cache = array();

	/**
	 * Indicates whether filters should be shown.
	 *
	 * @since 3.0
	 * @var bool
	 */
	protected $hide_filters = false;

	/**
	 * Returns the types, used by the field.
	 *
	 * If nothing is added, this function will allow all available types to be used.
	 *
	 * @since 3.0
	 *
	 * @return moxed[]
	 */
	public function get_types() {
		if( ! empty( $this->types ) ) {
			return $this->types;
		}

		$types = array(
			'posts' => array(),
			'terms' => array(),
			'users' => array()
		);

		/**
		 * Allows the default data types of the object field to be changed.
		 *
		 * @since 3.0
		 *
		 * @param mixed[]          $types Types and their arguments.
		 * @param Ultimate_Fields\Field\Object $field The field that is being modified.
		 * @return mixed[]
		 */
		$types = apply_filters( 'uf.object.default_types', $types, $this );

		foreach( $types as $type => $args ) {
			$this->add( $type, $args );
		}

		return $this->types;
	}

	/**
	 * Allows the visiblity of the filters to be toggled.
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\Field\Object The instance of the field.
	 */
	public function hide_filters() {
		$this->hide_filters = true;
		return $this;
	}

	/**
	 * Allows the visiblity of the filters to be toggled.
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\Field\Object The instance of the field.
	 */
	public function show_filters() {
		$this->hide_filters = false;
		return $this;
	}

	/**
	 * Reteurns the visiblity of the filters.
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
	public function are_filters_visible() {
		return ! $this->hide_filters;
	}

	/**
	 * Enqueues the scripts for the field.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		# Add the basic js
		wp_enqueue_style( 'uf-select2-css' );
		wp_enqueue_script( 'uf-field-wp-object' );

		# Add templates
		Template::add( 'object-chooser', 'field/object-chooser' );
		Template::add( 'object-item', 'field/object-item' );
		Template::add( 'object-preview', 'field/object-preview' );
		Template::add( 'object-chooser-empty', 'field/object-chooser-empty' );

		# Localize
		ultimate_fields()
			->localize( 'cancel',        __( 'Cancel', 'ultimate-fields' ) )
			->localize( 'remove',        __( 'Remove', 'ultimate-fields' ) )
			->localize( 'remove-all',    __( 'Remove All', 'ultimate-fields' ) )
			->localize( 'add-items',     __( 'Add items', 'ultimate-fields' ) )
			->localize( 'select',        _x( 'Select', 'object', 'ultimate-fields' ) )
			->localize( 'select-item',   __( 'Select item', 'ultimate-fields' ) )
			->localize( 'select-items',  __( 'Select items', 'ultimate-fields' ) )
			->localize( 'object-filter', __( 'Filter...', 'ultimate-fields' ) );
	}

	/**
	 * Adds additional data for JavaScript.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export_field() {
		$settings = parent::export_field();

		$settings[ 'nonce' ]        = wp_create_nonce( $this->get_nonce_action() );
		$settings[ 'multiple' ]     = $this->multiple;
		$settings[ 'button_text' ]  = $this->button_text ? $this->button_text : __( 'Select item', 'ultimate-fields' );
		$settings[ 'hide_filters' ] = $this->hide_filters;

		return $settings;
	}

	/**
	 * Prepares data for the field.
	 *
	 * Except the normal value of the field, this function will also retrieve the data about the
	 * options, which are already selected in order to prevent AJAX calls later.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export_data() {
		$value = $this->datastore->get( $this->name );

		if( is_null( $value ) && $this->default_value ) {
			$value = $this->default_value;
		}

		# For compatability reasons, if there is a single integer saved and a single type, combine them.
		if( is_string( $value ) && 1 === count( $this->get_types() ) && preg_match( '~^\d+$~', $value ) ) {
			$keys  = array_keys( $this->get_types() );
			$value = array_shift( $keys ) . '_' . $value;
		}

		$prepared = $this->export_objects( array( $value ) );

		return array(
			$this->name               => $value,
			$this->name . '_prepared' => $prepared
		);
	}

	/**
	 * Returns the action for a nonce field.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	protected function get_nonce_action() {
		return 'uf_objects_get_' . $this->name;
	}

	/**
	 * Performs AJAX.
	 *
	 * @since 3.0
	 *
	 * @param string $action The action that is being performed.
	 * @param mixed  $item   The item that is being edited.
	 */
	public function perform_ajax( $action, $item ) {
		if( 'get_objects_' . $this->name != $action ) {
			return;
		}

		$this->ajax_objects();
	}

	/**
	 * Generates data for the field when requested through AJAX.
	 *
	 * @since 3.0
	 */
	public function ajax_objects() {
		if(
			! isset( $_POST[ 'filters' ] )
			|| ! isset( $_POST[ 'nonce' ] )
			|| ! wp_verify_nonce( $_POST[ 'nonce' ], $this->get_nonce_action() )
		) {
			exit;
		}

		# All final data will be put here
		$result = array(
			'items' => array()
		);

		# Prepare some flags/modes
		$mode = isset( $_POST[ 'mode' ] ) && 'initial' == $_POST[ 'mode' ]
			? 'initial'
			: 'search';

		$selected = isset( $_POST[ 'selected' ] ) && is_array( $_POST['selected' ] )
			? $_POST[ 'selected' ]
			: array();

		$filters = array(
			'filters' => array(),
			'search'  => ''
		);

		if( isset( $_POST[ 'filters' ] ) && is_array( $_POST[ 'filters' ] ) && isset( $_POST[ 'filters' ][ 'filter' ] ) ) {
			$raw = $_POST[ 'filters' ];

			if( isset( $raw['search'] ) ) {
				$filters['search'] = $raw['search'];
			}

			if( isset( $raw['filters'] ) ) foreach( $raw['filters'] as $filter ) {
				list( $name, $value ) = explode( ':', $filter );

				if( ! isset( $filters['filters'][ $name ] ) ) {
					$filters['filters'][ $name ] = array();
				}

				$filters['filters'][ $name ][] = $value;
			}
		}

		$page    = isset( $_POST[ 'page' ] ) ? $_POST[ 'page' ] : 1;
		$max     = 20;
		$exclude = array();

		# Prepare the selected items
		if( 'initial' == $mode && ! empty( $selected ) && 1 == $page ) {
			$items = $this->export_objects( $selected );

			if( ! $this->multiple ) {
				$result[ 'items' ] = $items;
			}

			foreach( $items as $item ) {
				$type = $item[ 'type' ];

				if( ! isset( $exclude[ $type ] ) ) {
					$exclude[ $type ] = array();
				}

				$exclude[ $type ][] = $item[ 'item_id' ];
			}
		}

		# Load and apply filters
		foreach( $this->get_types() as $type ) {
			$type->apply_filters( $filters );
			$args = $type->get_args();

			/**
			* Allows the arguments for a type of items in the objects field to be changed.
			*
			* @since 3.0
			*
			* @param mixed[]                $args      The arguments that are already prepared.
			* @param Ultimate_Fields\Field\Object       $field     The field that is loading data.
			* @param mixed[]                $selection The filters, search and pagination that are applied.
			* @param Ultimate_Fields\Helper\Object\Type $type      The type of items that is being loaded.
			* @return mixed[]
			*/
			$args = apply_filters( 'uf.object.type_args', $args, $this, $filters, $type );
			$args = apply_filters( 'uf.object.' . $type->get_slug() . '.args', $args, $this, $filters, $type );
			$type->set_args( $args );
		}

		$result[ 'filters' ] = $this->get_filters();

		# Let types load their counts in order to be able to paginate later
		$offset    = $max * ( $page - 1 );
		$needed    = $max;
		$fetched   = 0;
		$available = 0;

		# Load data
		foreach( $this->get_types() as $type_slug => $type ) {
			# If there are invalid filers, don't use the type
			if( $type->impossible )  {
				continue;
			}

			# Make sure existing items are excluded
			if( isset( $exclude[ $type_slug ] ) ) {
				$type->exclude( $exclude[ $type_slug ] );
			}

			# Calculate what to load
			$next_page = $page;
			$next_page -= ceil( $fetched / $max );

			# Load counts & prepare
			$total = $type->load_counts( $max, $next_page );
			$available += $total;

			# Get as many items as needed
			if( $needed > 0 ) {
				foreach( $type->load() as $item ) {
					$result[ 'items' ][] = $item;
					$needed--;
					$fetched++;
				}
			}
		}

		$result[ 'total' ]  = $available;
		$result[ 'offset' ] = $offset;
		$result[ 'page' ]   = $page;
		$result[ 'more' ]   = $offset + $max < $available;

		# Output and prettify if needed
		if( WP_DEBUG && defined( 'JSON_PRETTY_PRINT' ) ) {
			echo json_encode( $result, JSON_PRETTY_PRINT );
		} else {
			echo json_encode( $result );
		}

		exit;
	}

	/**
	 * Adds a certain type of objects to the field.
	 *
	 * @since 3.0
	 *
	 * @param string $type      The name of the type or directly the needed class name.
	 * @param mixed[] $args     Arguments for loading objects fromm the type. (Optional)
	 * @return Ultimate_Fields\Field\Object The field that is being modified.
	 */
	public function add( $type_slug, $args = array() ) {
		if( class_exists( $type_slug ) ) {
			$class_name = $type_slug;
		} else {
			$class_name = ultimate_fields()->generate_class_name( "Helper/Object/Type/$type_slug" );
		}

		$type = new $class_name( $args );
		$this->types[ $type->get_slug() ] = $type;

		return $this;
	}

	/**
	 * Retrieves the type and ID of an item from a value.
	 *
	 * @since 3.0
	 *
	 * @param string $value The value to check.
	 * @return array An array that contains `type` as it's first element and `id` as the second one.
	 */
	public function extract_pairs_from_value( $value ) {
		$types = $this->get_types();

		if( @preg_match( '~^\w+_\d+$~', $value ) ) {
			list( $item_type, $item_id ) = explode( '_', $value );
		} elseif( @preg_match( '~^\d+$~', $value ) && 1 == count( $types ) ) {
			$keys      = array_keys( $types );
			$item_type = array_shift( $keys );
			$item_id   = intval( $item );
		} else {
			return false;
		}

		return array( $item_type, $item_id );
	}

	/**
	 * Exports objects by their IDs.
	 *
	 * @since 3.0
	 *
	 * @param int[] $ids The IDs to export.
	 * @return mixed[]
	 */
	public function export_objects( $objects ) {
		$queue    = array();
		$prepared = array();
		$types    = $this->get_types();

		if( ! $objects || empty( $objects ) ) {
			return array();
		}

		// Prepare the queue
		foreach( $objects as $item ) {
			if( $pair = $this->extract_pairs_from_value( $item ) ) {
				list( $item_type, $item_id ) = $pair;
			} else {
				continue;
			}

			if( ! isset( $queue[ $item_type ] ) ) {
				$queue[ $item_type ] = array();
			}

			$queue[ $item_type ][] = intval( $item_id );
		}

		// Prepare items
		foreach( $queue as $slug => $ids ) {
			$type = $types[ $slug ];

			foreach( $type->prepare( $ids ) as $key => $object ) {
				$prepared[] = $object;
			}
		}

		return $prepared;
	}

	/**
	 * Returns all available filters.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function get_filters() {
		$filters = array();

		foreach( $this->get_types() as $slug => $type ) {
			foreach( $type->get_filters() as $label => $options ) {
				if( ! isset( $filters[ $label ] ) ) {
					$filters[ $label ] = array();
				}

				foreach( $options as $key => $value ) {
					$filters[ $label ][ $key ] = $value;
				}
			}
		}

		/**
		 * Allows more filters to be added to the object chooser.
		 *
		 * @since 3.0
		 *
		 * @param mixed[]          $filters The existing filters, in format $label => $options.
		 * @param Ultimate_Fields\Field\Object $field   The field that is being modified.
		 * @return mixed[]
		 */
		$filters = apply_filters( 'uf.object.filters', $filters, $this );

		$usable = array();
		foreach( $filters as $label => $options ) {
			if( ! empty( $options ) ) {
				$usable[ $label ] = $options;
			}
		}

		return $usable;
	}

	/**
	 * Changes the text of the "Select item" button.
	 *
	 * @since 3.0
	 *
	 * @param string $text The new text for the button.
	 * @return Ultimate_Fields\Field\Object
	 */
	public function set_button_text( $text ) {
		$this->button_text = $text;

		return $this;
	}

	/**
	 * Changes the output type of the field.
	 *
	 * @since 3.0
	 *
	 * @param string $type The output type ('id', 'title', 'url', 'link').
	 * @return Ultimate_Fields\Field\Object
	 */
	public function set_output_type( $type ) {
		$this->output_type = $type;

		return $this;
	}

	/**
	 * Changes the text that would be displayed when using the_value() and the
	 * output type is set to 'link'.
	 *
	 * @since 3.0
	 *
	 * @param string $text The text for the link.
	 * @return Ultimate_Fields\Field\Object
	 */
	public function set_link_text( $text ) {
		$this->link_text = $text;

		return $this;
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

		# Proxy the normal data
		$this->proxy_data_to_setters( $data, array(
			'wp_object_text'        => 'set_button_text',
			'wp_object_output_type' => 'set_output_type',
			'wp_object_link_text'   => 'set_link_text'
		));

		if( isset( $data[ 'hide_filters' ] ) && $data[ 'hide_filters' ] ) {
			$this->hide_filters();
		}

		if( ! isset( $data[ 'wp_object_types' ] ) || ! is_array( $data[ 'wp_object_types' ] ) ) {
			return;
		}

		foreach( $data[ 'wp_object_types' ] as $type ) {
			if( 'posts' == $type ) {
				# Add posts to the chooser
				if( isset( $data[ 'wp_object_post_types' ] ) && $post_types = $data[ 'wp_object_post_types' ] ) {
					$this->add( 'posts', array(
						'post_type' => $post_types
					));
				} else {
					$this->add( 'posts' );
				}
			} elseif( 'terms' == $type ) {
				# Add terms to the chooser
				if( isset( $data[ 'wp_object_taxonomies' ] ) && $taxonomies = $data[ 'wp_object_taxonomies' ] ) {
					$this->add( 'terms', array(
						'taxonomy' => $taxonomies
					));
				} else {
					$this->add( 'terms' );
				}
			} elseif( 'users' == $type ) {
				# Add users to the chooser
				$this->add( 'users' );
			}
		}
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
			'button_text'  => array( 'wp_object_text', '' ),
			'output_type'  => array( 'wp_object_output_type', 'id' ),
			'link_text'    => array( 'wp_object_link_text', '' ),
			'hide_filters' => array( 'hide_filters', false )
		));

		# Add post types and etc.
		$settings[ 'wp_object_types' ] = array();

		foreach( $this->get_types() as $slug => $type ) {
			if( 'post' == $slug ) $slug = 'posts';
			if( 'term' == $slug ) $slug = 'terms';
			if( 'user' == $slug ) $slug = 'users';

			$settings[ 'wp_object_types' ][] = $slug;

			if( 'posts' == $slug ) {
				$args = $type->parse_args();
				if( isset( $args[ 'post_type' ] ) ) {
					$settings[ 'wp_object_post_types' ] = $args[ 'post_type' ];
				}
			}

			if( 'terms' == $slug ) {
				$args = $type->parse_args();
				if( isset( $args[ 'taxonomy' ] ) ) {
					$settings[ 'wp_object_taxonomies' ] = $args[ 'taxonomy' ];
				}
			}
		}

		return $settings;
	}

	/**
	 * Extracts values into a proper format.
	 *
	 * @since 3.0
	 *
	 * @param mixed $value The value(s) to parse.
	 * @return mixed[]
	 */
	public static function extract( $value ) {
		$values = array();

		foreach( (array) $value as $entry ) {
			if( is_string( $entry ) ) {
				$entry = array_combine(
					array( 'type', 'item' ),
					explode( '_', $entry )
				);
			}

			$values[] = $entry;
		}

		return $values;
	}

	/**
	 * Handles a value for the front-end.
	 *
	 * @since 3.0
	 *
	 * @param mixed                  $value  The value to parse.
	 * @param Ultimate_Fields\Helper\Data_Source $source The context of the value.
	 * @return mixed
	 */
	public function handle( $value, $source = null ) {
		// Without a value at all, there is nothing to process
		if( ! $value ) {
			return false;
		}

		// For existing objects, just return
		if( is_object( $value ) ) {
			return $value;
		}

		// Extract the ID/type from the value
		if( $pair = $this->extract_pairs_from_value( $value ) ) {
			list( $item_type, $item_id ) = $pair;
		} else {
			return false;
		}

		// Get data from the type
		$types = $this->get_types();
		$prepared = $types[ $item_type ]->export_items( array( $item_id ) );

		foreach( $prepared as $key => $item ) {
			$this->item_cache[ $key ] = $item;

			$real = $item->get_original();
			$real->uf_object_key = $key;

			return $real;
		}

		return false;
	}

	/**
	 * Processes an already handled value to the format the field has for it's output.
	 *
	 * @since 3.0
	 *
	 * @param mixed $value The value to process.
	 * @return string
	 */
	public function process( $value ) {
		if( ! $value || ! is_object( $value ) ) {
			return '';
		}

		// Check for the item in the cache
		if( ! isset( $value->uf_object_key ) || ! isset( $this->item_cache[ $value->uf_object_key ] ) ) {
			return $value;
		}

		// Load the item and returnw hat's needed
		$item = $this->item_cache[ $value->uf_object_key ];
		switch( $this->output_type ) {
			case 'id':
				return $item->get_id();
			case 'title':
				return $item->get_title();
			case 'url':
				return $item->get_url();
			case 'link':
				$url    = $item->get_url();
				$title = $this->link_text ? $this->link_text : $item->get_title();
				return sprintf( '<a href="%s">%s</a>', $url, $title );
		}

		return false;
	}
}
