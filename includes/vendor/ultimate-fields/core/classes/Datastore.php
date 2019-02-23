<?php
namespace Ultimate_Fields;

use ArrayAccess;

/**
 * Datastores are objects, which provide a connection between the database and fields.
 * Each container type has it's own default datastore, which should extend this one.
 *
 * @since 3.0
 */
abstract class Datastore implements ArrayAccess {
    /**
	 * Holds values which will be commited later.
	 *
	 * @since 2.0
	 * @var mixed[]
	 */
	protected $values = array();

	/**
	 * Holds the keys of values that should be deleted.
	 *
	 * @since 2.0
	 * @var string[]
	 */

	protected $deleted = array();

    /**
     * Initializes the datastore, eventually with some values.
     *
     * @since 3.0
     *
     * @param mixed[] $data The values for the datastore.
     */
    public function __construct( $data = array() ) {
        $this->values = $data;
    }

	/**
	 * Retrieve a single value,
	 *
	 * @since 2.0
	 *
	 * @param string $key The key of the value
	 * @return mixed An empty string if the value is not available or the value itself
	 */
	function get( $key ) {
		$value = null;

		if( isset( $this->deleted[ $key ] ) ) {
			$value = null;
		} elseif( isset( $this->values[ $key ] ) ) {
			$value = $this->values[ $key ];
		} else {
			$value = $this->get_value_from_db( $key );
		}

		return $value;
	}

	/**
	 * Saves values. Might as well update existing ones
	 *
	 * @since 2.0
	 *
	 * @param string $key The key which the value is saved with
	 * @param mixed $value The value to be saved
	 */
	function set( $key, $value ) {
		$this->values[ $key ] = $value;

		if( isset( $this->deleted[ $key ] ) ) {
			unset( $this->deleted[ $key ] );
		}
	}

	/**
	 * Deletes values based on their key, no matter if they are multiple or not
	 *
	 * @since 2.0
	 *
	 * @param string $key The key of the setting
	 */
	function delete( $key ) {
		$this->deleted[ $key ] = 1;
	}

    /**
     * Spawns a new datastore from the content of a single value.
     *
     * @param  string $key  The key of the needed value.
     * @param  string $type The class name of the new repository.
     * @return Datastore    The newly created datastore.
     */
	public function export( $key, $type = null ) {
		if( ! $type ) {
			$type = Datastore\Group::class;
		}

		$datastore = new $type( $this->get( $key ) );
		$datastore->parent = $this;

		return $datastore;
	}

    /**
     * Sets a value within the datastore.
     *
     * @since 3.0
     *
     * @param string $key   The key for the value.
     * @param mixed  $value The value that is being added.
     */
    public function offsetSet( $key, $value ) {
    	$this->set( $key, $value );
    }

    /**
     * Checks if a certain offset exists.
     *
     * @since 3.0
     *
     * @param string $key The key of the value that is needed.
     * @return bool
     */
    public function offsetExists( $key ) {
    	return isset( $this->values[ $key ] );
    }

    /**
     * Unsets an offset.
     *
     * @since 3.0
     *
     * @param string $key The key of the value which is to be removed from the array.
     */
    public function offsetUnset( $key ) {
    	$this->delete( $key );
    }

    /**
     * Returns the value with a specific key.
     *
     * @since 3.0
     *
     * @param string $key The key of the value.
     * @return mixed
     */
    public function offsetGet( $key ) {
    	return $this->get( $key );
    }

    /**
     * Returns all of the values, held within the datastore.
     *
     * If the datastore was initialized with an array, or is still not not sent
     * to the database, this method allows acccess to its values. Also used when
     * the datastore does not provide an actuall connection to the DB (ex. repeaters).
     *
     * @since 3.0
     *
     * @return mixed[]
     */
    public function get_values() {
		return $this->values;
	}

	/**
	 * Returns a string, which uniquely identifies the object of the datastore.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_item_string() {
		$item_string = '';

		foreach( call_user_func( array( get_class( $this ), 'get_data_api_options' ) ) as $option ) {
			if( $option[ 'keyword' ] ) {
				$item_string = $option[ 'keyword' ];
				break;
			}
		}

		if( method_exists( $this, 'get_id' ) ) {
			$item_string .= '_' . $this->get_id();
		}

		return $item_string;
	}
}
