<?php
namespace Ultimate_Fields\Helper\Object\Type;

use Ultimate_Fields\Helper\Object\Type;
use Ultimate_Fields\Helper\Object\Item\Term;
use Ultimate_Fields\Template;

/**
 * Works with terms whithin the object chooser.
 *
 * @since 3.0
 */
class Terms extends Type {
	/**
	 * Returns the slug of the type.
	 *
	 * @since 3.0
	 * @return string
	 */
	public function get_slug() {
		return 'term';
	}

	/**
	 * Parses arguments for retrieving terms.
	 *
	 * @since 3.0
	 */
	public function parse_args() {
		static $done;

		if( ! is_null( $done ) ) {
			return $this->args;
		}

		$done = true;

		$args = wp_parse_args( $this->args, array(
			'hide_empty' => false,
			'taxonomy'   => array_keys( $this->get_taxonomies() )
		));

		return $this->args = $args;
	}

	/**
	 * Applies filters to the arguments if needed.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $filters The filters that are currently being applied.
	 */
	public function apply_filters( $filters ) {
		$this->parse_args();

		# Check if there is a search
		if( isset( $filters[ 'search' ] ) && $filters[ 'search' ] ) {
			$this->args[ 'search' ] = strip_tags( $filters[ 'search' ] );
		}

		# Check for taxonomy type
		$taxonomies = array();

		if( isset( $filters['filters'] ) ) foreach( $filters['filters'] as $filter => $values ) {
			if( 'type' == $filter ) {
				$all = array_keys( $this->get_taxonomies() );

				foreach( $values as $type ) {
					if( in_array( $type, $all ) ) {
						$taxonomies[] = $type;
					} else {
						$this->impossible = true;
						break;
					}
				}

				continue;
			}

			$this->impossible = true;
		}

		if( ! empty( $taxonomies ) ) {
			$this->args[ 'taxonomy' ] = $taxonomies;
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

		if( isset( $this->args[ 'exclude' ] ) ) {
			$this->args[ 'exclude' ] = array_merge( $this->args[ 'exclude' ], $ids );
		} else {
			$this->args[ 'exclude' ] = $ids;
		}
	}

	/**
	 * Loads the count of available items.
	 *
	 * @since 3.0
	 *
	 * @return int
	 */
	public function load_counts( $per_page, $page ) {
		$args = $this->parse_args();

		// Save the page and per page
		$this->page     = $page;
		$this->per_page = $per_page;

		return wp_count_terms( $args[ 'taxonomy' ], $args );
	}

	/**
	 * Loads terms based on the arguments, which the type was created with.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function load() {
		$args = $this->parse_args();
		$args[ 'number' ] = $this->per_page;
		$args[ 'offset' ] = $this->per_page * ( $this->page - 1 );
		$terms = get_terms( $args );

		$items = array();
		foreach( $terms as $term ) {
			$items[] = $this->prepare_item( $term );
		}

		return $items;
	}

	/**
	 * Exports terms based on their IDs.
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
			'hide_empty' => false,
			'include'    => $ids
		);

		foreach( get_terms( $args ) as $term ) {
			$data[ 'term_' . $term->term_id] = new Term( $term );
		}

		return $data;
	}

	/**
	 * Returns terms based on their IDs.
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
			'hide_empty' => false,
			'include'    => $ids
		);

		foreach( get_terms( $args ) as $term ) {
			$data[ 'term_' . $term->term_id] = $this->prepare_item( $term );
		}

		return $data;
	}

	/**
	 * Exports a term, eventually locating it by ID.
	 *
	 * @since 3.0
	 *
	 * @param mixed $item Either the ID of the item or one that is already found/prepared.
	 * @return mixed[]    The exported item, whose data is ready for JavaScript.
	 */
	public function prepare_item( $item ) {
		$taxonomies = $this->get_taxonomies();

		if( 1 === $item->count ) {
			$count = '1 item';
		} else {
			$count = $item->count . ' items';
		}

		$description = array(
			'<strong>' . $taxonomies[ $item->taxonomy ]. '</strong>',
			'Contains ' . $count
		);

		$args = array(
			'title'       => esc_html( $item->name ),
			'link'        => get_edit_term_link( $item->term_id ),
			'description' => array_shift( $description ) . implode( ', ', $description )
		);

		return array(
			'id'      => 'term_' . $item->term_id,
			'item_id' => $item->term_id,
			'type'    => 'term',
			'title'   => $item->name,
			'url'     => get_term_link( $item ),
			'html'    => Template::instance()->include_template( 'field/object-term', $args, false )
		);
	}

	/**
	 * Returns the registered taxonomies.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	protected function get_taxonomies() {
		static $taxonomies;

		if( ! is_null( $taxonomies ) ) {
			return $taxonomies;
		}

		$args = array(
			'show_ui' => true
		);

		foreach( get_taxonomies( $args, 'objects' ) as $slug => $taxonomy ) {
			$taxonomies[ $slug ] = $taxonomy->labels->singular_name;
		}

		return $taxonomies;
	}

	/**
	 * Returns all available filters, by default just the taxonomy.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function get_filters() {
		# Add the basic type filter
		$filters[ 'Type' ] = array();
		$taxonomies        = $this->get_taxonomies();

		foreach( $taxonomies as $slug => $name ) {
			$filters[ 'Type' ][ 'type:' . $slug ] = $name;
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
		return get_term_link( intval( $item ) );
	}
}
