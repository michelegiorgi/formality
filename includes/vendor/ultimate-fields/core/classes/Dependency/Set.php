<?php
namespace Ultimate_Fields\Dependency;

use Ultimate_Fields\Dependency\Group;
use Countable;
use ArrayAccess;

/**
 * Handles a set of dependency groups.
 *
 * @since 3.0
 */
class Set implements Countable, ArrayAccess {
	/**
	 * Holds all groups that are used within the set.
	 *
	 * @since 3.0
	 * @var Rule[]
	 */
	protected $groups;

	/**
	 * Constructor for the class.
	 *
	 * @since 3.0
	 * @param mixed[] $groups An array of rules to add to the group.
	 */
	public function __construct( $groups = array() ) {
		foreach( $groups as $group ) {
			$this->add_group( $group );
		}
	}

	/**
	 * Adds a group to the set.
	 *
	 * @since 3.0
	 *
	 * @param  mixed $group The group to add.
	 * @return Set The instance of the set.
	 */
	public function add_group( $group, $index = false ) {
		$group = is_a( $group, Group::class )
			? $group
			: new Group( $group );

		if( false == $index || is_null( $index ) ) {
			$this->groups[] = $group;
		} else {
			$this->groups[ $index ] = $group;
		}
	}

	/**
	 * Checks all internal groups and rules based on a datastore.
	 *
	 * This method requires a single group to be matched in order to return true (ANDOR logic).
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Datastore $datastore The datastore that contains all values.
	 * @return bool
	 */
	public function check( $datastore ) {
		foreach( $this->groups as $group ) {
			if( ! $group->check( $datastore ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns the count of existing groups.
	 *
	 * @since 3.0
	 * @return int
	 */
	public function count() {
		return count( $this->groups );
	}

	/**
	 * Adds a new group to the set.
	 *
	 * @since 3.0
	 *
	 * @param string               $offset The offset of the group.
	 * @param Ultimate_Fields\Dependency\Group $group  The group that is being added.
	 */
	public function offsetSet( $offset, $group ) {
		$this->add_group( $group, $offset );
	}

	/**
	 * Checks if a certain offset exists.
	 *
	 * @since 3.0
	 *
	 * @param string $offset The offset of the group that is needed.
	 * @return bool
	 */
	public function offsetExists( $offset ) {
		return isset( $this->groups[ $offset ] );
	}

	/**
	 * Unsets an offset.
	 *
	 * @since 3.0
	 *
	 * @param string $offset The offset of the group which is to be removed from the array.
	 */
	public function offsetUnset( $offset ) {
		unset( $this->groups[ $offset ] );
	}

	/**
	 * Returns the group with a specific offset.
	 *
	 * @since 3.0
	 *
	 * @param string $offset The offset of the group.
	 * @return Ultimate_Fields\Dependency\Group
	 */
	public function offsetGet( $offset ) {
		return isset( $this->groups[ $offset ] ) ? $this->groups[ $offset ] : null;
	}

	/**
	 * Exports the dependencies for JavaScript.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function json() {
		$dependencies = array();

		foreach( $this->groups as $group ) {
			$dependencies[] = $group->json();
		}

		return $dependencies;
	}
}