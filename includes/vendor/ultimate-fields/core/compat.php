<?php
/**
 * This file contains compatability functions for version 1's PHP and export interface.
 *
 * @since 3.0
 */
add_action( 'uf.init', 'uf_compat_init' );
function uf_compat_init() {
    /**
     * An alternative method for registering containers, compatible with version 1.
     * Please don't use this method in new projects, as it might be dropped at some point.
     *
     * @since 3.0
     * @deprecated
     */
    do_action( 'uf_setup_containers' );
}

/**
 * Sets up a container through a PHP array.
 *
 * @since 3.0
 * @deprecated
 *
 * @param mixed[] $data All of the data about the container.
 * @return Ultimate_Fields\Container
 */
function uf_setup_container( $data ) {
	if( ! defined( 'ULTIMATE_FIELDS_MIGRATING' ) || ! ULTIMATE_FIELDS_MIGRATING ) {
		_doing_it_wrong( 'uf_setup_container', __( 'This function only exists for compatability with Ultimate Fields 1. Please migrate your container settings to the format, used by Ultimate Fields 3.', 'ultimate-fields' ), '3.0' );
	}

    $helper = new Ultimate_Fields\Helper\V1_Migrator( $data );
    return $helper->get_container();
}

/**
 * Displays a value in the front-end.
 *
 * This function is deprecated, please use get_value() with the same arguments, except $echo.
 *
 * @since 1.0
 *
 * @param string $key  The key for the needed value.
 * @param mixed  $type The context of the value. @see get_value() in api.php
 * @param bool   $echo An indicator if the data should be displayed.
 * @return mixed
 */
function uf( $key, $type = null, $echo = true ) {
    _doing_it_wrong( 'uf', __( 'This function only exists for compatability with Ultimate Fields 1. Please use the_value() instead!', 'ultimate-fields' ), '3.0' );

    if( $echo ) {
        return the_value( $key, $type );
    } else {
        return get_the_value( $key, $type );
    }
}

/**
 * Returns the value of a field.
 *
 * This function is deprecated, please use get_value() with the same arguments.
 *
 * @since 1.0
 *
 * @param string $key  The key for the needed value.
 * @param mixed  $type The context of the value. @see get_value() in api.php
 * @return mixed
 */
function get_uf( $key, $type = null ) {
    _doing_it_wrong( 'get_uf', __( 'This function only exists for compatability with Ultimate Fields 1. Please use get_the_value() instead!', 'ultimate-fields' ), '3.0' );

    return get_the_value( $key, $type );
}

/**
 * Returns the parsed value of a repeater.
 *
 * @since 3.0
 *
 * @param  string $key  The name of the field.
 * @param  mixed  $type The context of the value.
 * @return mixed        The values of the repeater.
 */
function get_uf_repeater( $key, $type = null ) {
    _doing_it_wrong( 'get_uf_repeater', __( 'This function only exists for compatability with Ultimate Fields 1. Please use have_groups(), the_group(), get_sub_value() and the_sub_value() instead!', 'ultimate-fields' ), '3.0' );

    $value = get_value( $key, $type );

    if( ! $value || empty( $value ) ) {
        return array();
    }

    $data = array();
    foreach( $value as $row ) {
        $data[] = $row->export();
    }

    return $data;
}
