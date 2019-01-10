<?php

/**
*  ACF_Local_Meta
*
*  Class for registering postmeta into memory and bypassing the database.
*
*  @package	ACF
*  @date	8/10/18
*  @since	5.7.8
*/

if( ! class_exists('ACF_Local_Meta') ) :

class ACF_Local_Meta {
	
	/** @var array Storage for meta data. */
	var $meta = array();
	
	/** @var mixed Storage for registered post_id. */
	var $post_id = 0;
	
	/** @var int Counter used to initialize only once. */
	var $initialized = 0;
	
	/**
	*  __construct
	*
	*  Sets up the class functionality.
	*
	*  @date	8/10/18
	*  @since	5.7.8
	*
	*  @param	void
	*  @return	void
	*/
	function __construct() {}
	
	/**
	*  initialize
	*
	*  Called during add() to setup filters. Only runs once.
	*
	*  @date	8/10/18
	*  @since	5.7.8
	*
	*  @param	void
	*  @return	void
	*/
	function initialize() {
		
		// only once
		if( $this->initialized++ ) return;
		
		// add filters
		add_filter( 'acf/pre_load_value', 		array($this, 'pre_load_value'), 	10, 3 );
		add_filter( 'acf/pre_load_reference', 	array($this, 'pre_load_reference'), 10, 3 );
		add_filter( 'acf/pre_load_post_id', 	array($this, 'pre_load_post_id'), 	10, 2 );
		add_filter( 'acf/pre_load_meta', 		array($this, 'pre_load_meta'), 		10, 2 );
	}
	
	/**
	*  add
	*
	*  Adds postmeta to storage.
	*
	*  @date	8/10/18
	*  @since	5.7.8
	*
	*  @param	array $values An array of data to store in the format $field_key => $field_value.
	*  @param	mixed $post_id The post_id for this data.
	*  @param	bool $is_main Makes this postmeta visible to get_field() without a $post_id value.
	*  @return	void
	*/
	function add( $values = array(), $post_id = 0, $is_main = false ) {
		
		// loop over values
		if( is_array($values) ) {
		foreach( $values as $key => $value ) {
			
			// get field
			$field = acf_get_field( $key );
			
			// check
			if( $field ) {
				
				// vars
				$name = $field['name'];
				
				// append values
				$this->meta[ $post_id ][ $name ] = $value;
				
				// append reference
				$this->meta[ $post_id ][ "_$name" ] = $key;
			}
		}}
		
		// set $post_id reference when is the main postmeta
		if( $is_main ) {
			$this->post_id = $post_id;
		}
		
		// initialize filters
		$this->initialize();
	}
	
	/**
	*  remove
	*
	*  Removes postmeta from storage.
	*
	*  @date	8/10/18
	*  @since	5.7.8
	*
	*  @param	mixed $post_id The post_id for this data.
	*  @return	void
	*/
	
	function remove( $post_id = 0 ) {
		
		// unset meta
		unset( $this->meta[ $post_id ] );
		
		// reset post_id
		if( $post_id === $this->post_id ) {
			$this->post_id = 0;
		}
	}
	
	/**
	*  pre_load_value
	*
	*  Injects the local value.
	*
	*  @date	8/10/18
	*  @since	5.7.8
	*
	*  @param	null $null An empty parameter. Return a non null value to short-circuit the function.
	*  @param	mixed $post_id The post_id for this data.
	*  @param	array $field The field array.
	*  @return	mixed
	*/
	function pre_load_value( $null, $post_id, $field ) {
		if( isset($this->meta[ $post_id ][ $field['name'] ]) ) {
			return $this->meta[ $post_id ][ $field['name'] ];
		}
		return $null;
	}
	
	/**
	*  pre_load_reference
	*
	*  Injects the local reference.
	*
	*  @date	8/10/18
	*  @since	5.7.8
	*
	*  @param	null $null An empty parameter. Return a non null value to short-circuit the function.
	*  @param	array $field_name The field's name (meta key).
	*  @param	mixed $post_id The post_id for this data.
	*  @return	mixed
	*/
	function pre_load_reference( $null, $field_name, $post_id ) {
		if( isset($this->meta[ $post_id ][ "_$field_name" ]) ) {
			return $this->meta[ $post_id ][ "_$field_name" ];
		}
		return $null;
	}
	
	/**
	*  pre_load_post_id
	*
	*  Injects the local post_id.
	*
	*  @date	8/10/18
	*  @since	5.7.8
	*
	*  @param	null $null An empty parameter. Return a non null value to short-circuit the function.
	*  @param	mixed $post_id The post_id for this data.
	*  @return	mixed
	*/
	function pre_load_post_id( $null, $post_id ) {
		if( !$post_id && $this->post_id ) {
			return $this->post_id;
		}
		return $null;
	}
	
	/**
	*  pre_load_meta
	*
	*  Injects the local meta.
	*
	*  @date	8/10/18
	*  @since	5.7.8
	*
	*  @param	null $null An empty parameter. Return a non null value to short-circuit the function.
	*  @param	mixed $post_id The post_id for this data.
	*  @return	mixed
	*/
	function pre_load_meta( $null, $post_id ) {
		if( isset($this->meta[ $post_id ]) ) {
			return $this->meta[ $post_id ];
		}
		return $null;
	}
}

// instantiate
acf_new_instance('ACF_Local_Meta');

endif; // class_exists check

/**
*  acf_setup_postdata
*
*  Adds postmeta to storage.
*
*  @date	8/10/18
*  @since	5.7.8
*  @see		ACF_Local_Meta::add() for list of parameters.
*
*  @return	void
*/
function acf_setup_postdata( $values = array(), $post_id = 0, $is_main = false ) {
	return acf_get_instance('ACF_Local_Meta')->add( $values, $post_id, $is_main );
}

/**
*  acf_reset_postdata
*
*  Removes postmeta to storage.
*
*  @date	8/10/18
*  @since	5.7.8
*  @see		ACF_Local_Meta::remove() for list of parameters.
*
*  @return	void
*/
function acf_reset_postdata( $post_id = 0 ) {
	return acf_get_instance('ACF_Local_Meta')->remove( $post_id );
}

?>