<?php
namespace Ultimate_Fields\Helper\Object\Type;

use Ultimate_Fields\Helper\Object\Type;
use Ultimate_Fields\Helper\Object\Item\User;
use Ultimate_Fields\Template;

/**
 * Works with users whithin the object chooser.
 *
 * @since 3.0
 */
class Users extends Type {
	/**
	 * Returns the slug of the type.
	 *
	 * @since 3.0
	 * @return string
	 */
	public function get_slug() {
		return 'user';
	}

	/**
	 * Parses arguments for retrieving terms.
	 *
	 * @since 3.0
	 */
	protected function parse_args() {
		static $done;

		if( ! is_null( $done ) ) {
			return $this->args;
		}

		$done = true;
		$args = wp_parse_args( $this->args );

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
		
		$roles = array();

		if( isset( $filters['filters'] ) ) foreach( $filters['filters'] as $filter => $values ) {
			# Check for primitive types
			if( 'type' == $filter ) {
				foreach( $values as $type ) {
					if( $this->get_slug() != $type ) {
						$this->impossible = true;
						return;
					}
				}

				continue;
			}

			# Check for user roles
			if( 'user-role' == $filter ) {
				$all_roles = array_keys( $this->get_roles() );
				
				foreach( $values as $role ) {
					if( in_array( $role, $all_roles ) ) {
						$roles[] = $role;
					} else {
						$this->impossible = true;
						break;
					}
				}

				continue;
			}

			$this->impossible = true;
		}

		if( ! empty( $roles ) ) {
			$this->args[ 'role' ] = $roles;
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
	 * With terms, this simply loads all available terms.
	 *
	 * @since 3.0
	 *
	 * @return int
	 */
	public function load_counts( $per_page, $page ) {
		$args      = $this->parse_args();
		$counts    = count_users();
		$available = 0;
		$roles     = isset( $args[ 'role' ] ) ? (array) $args[ 'role' ] : 'all';

		# Check each individual count
		foreach( $counts[ 'avail_roles' ] as $name => $count ) {
			if( 'all' != $roles && ! in_array( $name, $roles ) )
				continue;

			$available += $count;
		}

		# Save the offset and count
		$this->args[ 'offset' ] = $per_page * ( max( 1, $page ) -1 );
		$this->args[ 'number' ] = $per_page;

		return $available;
	}

	/**
	 * Loads users based on the arguments, which the type was created with.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function load() {
		$items = array();
		$args  = $this->parse_args();

		if( isset( $args[ 'role' ] ) ) {
			$args[ 'role__in' ] = (array) $args[ 'role' ];
			unset( $args[ 'role' ] );
		}

		foreach( get_users( $args ) as $user ) {
			$items[] = $this->prepare_item( $user );
		}

		return $items;
	}

	/**
	 * Exports users based on their IDs.
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
			'include'    => $ids
		);

		foreach( get_users( $args ) as $user ) {
			$data[ 'user_' . $user->ID ] = new User( $user );
		}

		return $data;
	}

	/**
	 * Returns users based on their IDs.
	 *
	 * @since 3.0
	 *
	 * @param int[]    $ids The IDs of the items to retrieve.
	 * @return mixed[]      The prepared original-type terms.
	 */
	public function prepare( $ids ) {
		$data = array();

		# Directly query what is needed
		$args = array(
			'include'    => $ids
		);

		foreach( get_users( $args ) as $user ) {
			$data[ 'user_' . $user->ID ] = $this->prepare_item( $user );
		}

		return $data;
	}

	/**
	 * Exports a user, eventually locating it by ID.
	 *
	 * @since 3.0
	 *
	 * @param mixed $item Either the ID of the item or one that is already found/prepared.
	 * @return mixed[]    The exported item, whose data is ready for JavaScript.
	 */
	public function prepare_item( $item ) {
		$description = array();
		$all_roles   = $this->get_roles();

		foreach( $item->roles as $role ) {
			$description[] = '<strong>' . $all_roles[ $role ] . '</strong>';
		}

		$template_args = array(
			'link'        => get_edit_user_link( $item->ID ),
			'title'       => $item->data->display_name,
			'description' => array_shift( $description ) . implode( ', ', $description )
		);

		return array(
			'id'      => 'user_' . $item->ID,
			'item_id' => $item->ID,
			'type'    => 'user',
			'title'   => $item->data->display_name,
			'html'    => Template::instance()->include_template( 'field/object-user', $template_args, false ),
			'url'     => get_author_posts_url( $item->ID )
		);
	}

	/**
	 * Returns the existing user roles.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	protected function get_roles() {
		static $roles;
		global $wp_roles;

		if( ! is_null( $roles ) ) {
			return $roles;
		}

	   foreach( $wp_roles->roles as $slug => $role ) {
		   $roles[ $slug ] = $role[ 'name' ];
	   }

	   return $roles;
	}

	/**
	 * Returns all available filters, by default just the taxonomy.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function get_filters() {
		$filters[ 'Type' ] = array(
			'type:user' => __( 'User', 'ultimate-fields' )
		);

		$filters[ 'Role' ] = array();
		foreach( $this->get_roles() as $slug => $name ) {
			$filters[ 'Role' ][ 'user-role:' . $slug ] = $name;
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
		return get_author_posts_url( $item->ID );
	}
}
