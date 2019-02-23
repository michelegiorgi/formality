<?php
namespace Ultimate_Fields\Datastore;

use Ultimate_Fields\Datastore;

/**
 * Connects the options container with the WordPress API.
 *
 * @since 3.0
 */
class Options extends Datastore {
    /**
	 * Retrieve a single value from the database.
	 *
	 * @since 2.0
	 *
	 * @param string $key The key of the value
	 * @return mixed An empty string if the value is not available or the value itself
	 */
	function get_value_from_db( $key ) {
        $all = wp_load_alloptions();

        if( ! isset( $all[ $key ] ) ) {
            return null;
        }

        $option = get_option( $key );

		return $option ? $option : false;
	}

	/**
	 * Saves values in the dabase. Might as well update existing ones
	 *
	 * @since 2.0
	 *
	 * @param string $key The key which the value is saved with
	 * @param mixed $value The value to be saved
	 */
	function save_value_in_db( $key, $value ) {
		return update_option( $key, $value );
	}

	/**
	 * Deletes values based on their key, no matter if they are multiple or not
	 *
	 * @since 2.0
	 *
	 * @param string $key The key of the setting
	 */
	function delete_value_from_db( $key ) {
		return delete_option( $key );
	}

	/**
	 * Commits the changes
	 */
	public function commit() {
		# Delete values
		foreach( $this->deleted as $key => $value ) {
			$this->delete_value_from_db( $key );
		}

		# Add/update the other ones
		foreach( $this->values as $key => $value ) {
			$this->save_value_in_db( $key, $value );
		}
	}

    /**
	 * When used within the customizer, this will set temporary values to all overwritten fieds.
	 *
	 * @since 3.0
	 */
	public function set_temporary_values() {
        foreach( $this->values as $key => $value ) {
            add_filter( 'pre_option_' . $key, array( $this, 'overwrite_value' ), 8, 2 );
        }
	}

    /**
     * Overwrites a particular options when used in the customizer.
     *
     * @since 3.0
     *
     * @param mixed  $value  The value of the option.
     * @param string $option The name of the option.
     * @return mixed
     */
    public function overwrite_value( $value, $option ) {
        if( isset( $this->values[ $option ] ) ) {
            return $this->values[ $option ];
        }

        return $value;
    }

    /**
	 * Returns the options and keywords for the data API.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public static function get_data_api_options() {
		$options = array();

		$options[] = array(
			'type'    => 'option',
			'keyword' => 'option',
			'item'    => false
		);

		$options[] = array(
			'type'    => 'option',
			'keyword' => 'options',
			'item'    => false
		);

		return $options;
	}

	/**
	 * Returns a string, which uniquely identifies the object of the datastore.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_item_string() {
		return 'options';
	}
}
