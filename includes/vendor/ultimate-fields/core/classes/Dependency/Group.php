<?php
namespace Ultimate_Fields\Dependency;

use Ultimate_Fields\Dependency\Rule;

/**
 * Handles a group of dependencies.
 *
 * @since 3.0
 */
class Group {
	/**
	 * Holds all rules that are used within the group.
	 *
	 * @since 3.0
	 * @var Rule[]
	 */
	protected $rules = array();

	/**
	 * Constructor for the class.
	 *
	 * @since 3.0
	 * @param mixed[] $rules An array of rules to add to the group.
	 */
	public function __construct( $rules = array() ) {
		foreach( $rules as $rule ) {
			$this->add_rule( $rule );
		}
	}

	/**
	 * Adds a rule to the group.
	 *
	 * @since 3.0
	 *
	 * @param  mixed $rule The rule to add, either a Rule object or data for it.
	 * @return Group The instance of the group.
	 */
	public function add_rule( $rule ) {
		$this->rules[] = is_a( $rule, Rule::class )
			? $rule
			: Rule::create( $rule );
	}

	/**
	 * Checks all internal rules based on a datastore.
	 *
	 * This method requires all rules to be matched in order to return true (AND logic).
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Datastore $datastore The datastore that contains all values.
	 * @return bool
	 */
	public function check( $datastore ) {
		foreach( $this->rules as $rule ) {
			if( ! $rule->check( $datastore ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Exports all rules within the group for JSON.
	 *
	 * @since 3.0
	 *
	 * @return mixed
	 */
	public function json() {
		$rules = array();

		foreach( $this->rules as $rule ) {
			$rules[] = $rule->export();
		}

		return $rules;
	}
}
