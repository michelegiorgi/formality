<?php
namespace Ultimate_Fields\Helper\Object\Type;

use Ultimate_Fields\Helper\Object\Type;
use Ultimate_Fields\Helper\Object\Item\Post;
use WP_Query;
use Ultimate_Fields\Template;

/**
 * Works with posts within the object chooser.
 *
 * @since 3.0
 */
class Posts extends Type {
	/**
	 * Returns the slug of the type.
	 *
	 * @since 3.0
	 * @return string
	 */
	public function get_slug() {
		return 'post';
	}

	/**
	 * Returns the arguments for the type.
	 *
	 * @since 3.0
	 *
	 * @return mixed
	 */
	public function parse_args() {
		static $done;

		if( ! is_null( $done ) ) {
			return $this->args;
		}

		$done = true;

		$max = 5;
		$page = 1;

		$args = wp_parse_args( $this->args, array(
			'posts_per_page' => 5,
			'paged'          => 1,
			'post_type'      => array_keys( $this->get_post_types() ),
			'post_status'    => array( 'publish', 'future', 'draft' ),
			'orderby'        => 'title',
			'order'          => 'ASC'
		));

		$args[ 'posts_per_page' ] = $max;
		$args[ 'paged' ]          = $page;

		return $this->args = $args;
	}

	/**
	 * Applies filters to the type.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $filters The filters comming from JS.
	 */
	public function apply_filters( $filters ) {
		$this->parse_args();

		if( isset( $filters[ 'search' ] ) && $filters[ 'search' ] ) {
			$this->args[ 's' ] = strip_tags( $filters[ 'search' ] );
		}

		$all_post_types = array_keys( $this->get_post_types() );
		$all_taxonomies = array_keys( $this->get_taxonomies( $all_post_types ) );

		$post_types = array();
		$terms      = array();

		if( isset( $filters['filters'] ) ) foreach( $filters['filters'] as $filter => $values ) {
			if( 'type' == $filter ) {
				foreach( $values as $post_type ) {
					if( in_array( $post_type, $all_post_types ) ) {
						$post_types[] = $post_type;
					} else {
						$this->impossible = true;
						return;
					}
				}

				continue;
			}

			if( 'post-term' == $filter ) {
				foreach( $values as $term_id ) {
					$term_id = intval( $term_id );
					$term    = get_term( $term_id );

					if( $term && in_array( $term->taxonomy, $all_taxonomies ) ) {
						$terms[ $term->term_id ] = $term;
					}
				}

				continue;
			}

			$this->impossible = true;
		}

		# Apply the filters
		if( ! empty( $post_types ) ) {
			$this->args[ 'post_type' ] = $post_types;
		}

		if( ! empty( $terms ) ) {
			if( ! isset( $this->args[ 'tax_query' ] ) ) {
				$this->args[ 'tax_query' ] = array();
			}

			foreach( $terms as $term ) {
				$this->args[ 'tax_query' ][] = array(
					'taxonomy' => $term->taxonomy,
					'field'    => 'id',
					'terms'    => $term->term_id
				);
			}
		}
	}

	/**
	 * Excludes items from being in the list.
	 *
	 * @since 3.0
	 *
	 * @param int[] $ids The IDs to exclude.
	 */
	public function exclude( $ids ) {
		$this->parse_args();

		if( isset( $this->args[ 'post__not_in' ] ) ) {
			$this->args[ 'post__not_in' ] = array_merge( $this->args[ 'post__not_in' ], $ids );
		} else {
			$this->args[ 'post__not_in' ] = $ids;
		}
	}

	/**
	 * Loads the count of available items.
	 *
	 * This basically prepares the WordPress query based on the current setup.
	 *
	 * @since 3.0
	 */
	public function load_counts( $per_page, $page ) {
		$args = $this->parse_args();

		$args[ 'posts_per_page' ] = $per_page;
		$args[ 'paged' ]          = $page;

		$query = $this->query = new WP_Query( $args );

		return intval( $query->found_posts );
	}

	/**
	 * Loads posts based on the arguments, which the type was created with.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function load() {
		$items = array();

		while( $this->query->have_posts() ) {
			$this->query->the_post();

			$items[] = $this->prepare_item( get_post() );
		}

		if( ! is_admin() ) {
			wp_reset_postdata();
		}

		return $items;
	}

	/**
	 * Exports posts based on their IDs.
	 *
	 * @since 3.0
	 *
	 * @param int[]      $ids The IDs of the items to retrieve.
	 * @return WP_Term[]
	 */
	public function export_items( $ids ) {
		$data = array();

		# Directly query what is needed
		$args = array(
			'post_type'      => get_post_types(),
			'posts_per_page' => -1,
			'post__in'       => $ids
		);

		foreach( get_posts( $args ) as $post ) {
			$data[ 'post_' . $post->ID ] = new Post( $post );
		}

		return $data;
	}

	/**
	 * Returns posts based on their IDs.
	 *
	 * @since 3.0
	 *
	 * @param int[]    $ids The IDs of the item to retrieve.
	 * @return mixed[]      The prepared original-type posts.
	 */
	public function prepare( $ids ) {
		$data = array();

		# Directly query what is needed
		$args = array(
			'post_type'      => array_values( get_post_types() ),
			'posts_per_page' => -1,
			'post__in'       => array_map( 'intval', $ids )
		);

		foreach( get_posts( $args ) as $post ) {
			$data[] = $this->prepare_item( $post );
		}

		return $data;
	}

	/**
	 * Exports a post, eventually locating it by ID.
	 *
	 * @since 3.0
	 *
	 * @param mixed $item Either the ID of the item or one that is already found/prepared.
	 * @return mixed[]    The exported item, whose data is ready for JavaScript.
	 */
	public function prepare_item( $post ) {
		static $stati;

		# Prepare stati
		if( is_null( $stati ) ) {
			$stati = array(
				'publish' => __( 'Published on %s', 'ultimate-fields' ),
				'future'  => __( 'Scheduled for %s', 'ultimate-fields' ),
				'draft'   => __( 'Draft from %s', 'ultimate-fields' )
			);
		}

		if( is_int( $post ) ) {
			$post = get_post( $post );
		}

		# Prepare taxonomies and post types
		$post_type_names = $this->get_post_types();
		$taxonomies = $this->get_taxonomies( array_keys( $post_type_names ) );

		$description = array(
			'<strong>' . ( isset( $post_type_names[ $post->post_type ] ) ? $post_type_names[ $post->post_type ] : $post->post_type  ) . '</strong>',
			sprintf( $stati[ get_post_status( $post->ID ) ], get_the_date( get_option( 'date_format' ), $post->ID ) )
		);

		# Check terms
		foreach( get_object_taxonomies( $post->post_type, 'objects' ) as $slug => $taxonomy ) {
			$terms = get_the_terms( $post->ID, $slug );

			if( $terms ) {
				foreach( $terms as $term ) {
					$description[] = sprintf(
						'<a href="%s" target="_blank">%s</a>',
						get_edit_term_link( $term, $term->taxonomy ),
						esc_html( $term->name )
					);
				}
			}
		}

		# Generate template arguments
		$args = array(
			'id'          => $post->ID,
			'title'       => apply_filters( 'the_title', $post->post_title ),
			'description' => array_shift( $description ) . implode( ', ', $description ),
			'link'        => get_edit_post_link( $post->ID )
		);

		# Return the data
		return array(
			'id'      => 'post_' . $post->ID,
			'item_id' => $post->ID,
			'title'   => $post->post_title,
			'type'    => 'post',
			'html'    => Template::instance()->include_template( 'field/object-post', $args, false ),
			'url'     => get_permalink( $post->ID )
		);
	}

	/**
	 * Returns the array of post types, allowed by the field.
	 *
	 * @since 3.0
	 *
	 * @return string[]
	 */
	protected function get_allowed_post_types() {
		$args    = $this->parse_args();
		$allowed = array();

		if( isset( $args[ 'post_type' ] ) && 'any' !== $args[ 'post_type' ] ) {
			$allowed = (array) $args[ 'post_type' ];
		}

		return $allowed;
	}

	/**
	 * Returns all registered post types.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	protected function get_post_types() {
		static $all;

		$included = array();
		$args     = $this->parse_args();
		if( isset( $args[ 'post_type' ] ) ) {
			$included = $args[ 'post_type' ];

			if( ! is_array( $included ) ) {
				$included = array( $included );
			}
		}

		# Prepare all available post types
		if( is_null( $all ) ) {
			$all  = array();
			$args = array(
				'show_ui' => true
			);

			foreach( get_post_types( $args, 'objects' ) as $name => $post_type ) {
				# Skip generic types
				if( in_array( $name, array( 'attachment', 'ultimate-fields' ) ) )
					continue;

				$all[ $name ] = $post_type->labels->singular_name;
			}
		}

		$post_types = array();
		if( $allowed = $this->get_allowed_post_types() ) {
			foreach( $allowed as $slug ) {
				if( ! empty( $included ) && ! in_array( $slug, $included ) ) {
					continue;
				}

				if( isset( $all[ $slug ] ) ) {
					$post_types[ $slug ] = $all[ $slug ];
				}
			}
		} else {
			$post_types = $all;
		}

		return $post_types;
	}

	/**
	 * Returns the registered taxonomies.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	protected function get_taxonomies( $post_types ) {
		static $taxonomies;

		if( ! is_null( $taxonomies ) ) {
			return $taxonomies;
		}

		$args = array(
			'show_ui'      => true,
			'hierarchical' => true
		);

		foreach( get_taxonomies( $args, 'objects' ) as $slug => $taxonomy ) {
			$taxonomies[ $slug ] = array(
				'label'      => $taxonomy->label,
				'post_types' => $taxonomy->object_type
			);
		}

		return $taxonomies;
	}

	/**
	 * Returns the taxonomies filter.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $post_types The post types to associate taxonomies with.
	 * @return mixed[]
	 */
	public function get_taxonomy_filter_options( $post_types ) {
		$all = $this->get_taxonomies( $post_types );
		$taxonomies = array();

		foreach( $all as $slug => $taxonomy ) {
			if( empty( array_intersect( $taxonomy[ 'post_types' ], $post_types ) ) )
				continue;

			$label = $taxonomy[ 'label' ];
			$taxonomies[ $label ] = array();
			foreach( get_terms( $slug, 'hide_empty=' ) as $term ) {
				$taxonomies[ $label ][ 'post-term:' . $term->term_id ] = $term->name;
			}
		}

		return $taxonomies;
	}

	/**
	 * Returns the filters for the type.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function get_filters() {
		# Add the basic type filter
		$filters[ 'Type' ] = array();
		$post_types        = $this->get_post_types();
		foreach( $post_types as $slug => $name ) {
			$filters[ 'Type' ][ 'type:' . $slug ] = $name;
		}

		# Add taxonomy filters
		foreach( $this->get_taxonomy_filter_options( array_keys( $post_types ) ) as $label => $options ) {
			$filters[ $label ] = $options;
		}

		return $filters;
	}

	/**
	 * Returns the URL of an item.
	 *
	 * @param int $item The ID of the item.
	 * @return URL
	 */
	public function get_item_link( $item ) {
		return get_permalink( $item );
	}
}
