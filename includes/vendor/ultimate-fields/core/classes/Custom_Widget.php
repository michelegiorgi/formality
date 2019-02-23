<?php
namespace Ultimate_Fields;

use WP_Widget;

/**
 * Extends WordPress widgets in order to provide an easy-to-use base for custom widgets.
 *
 * @since 3.0
 */
abstract class Custom_Widget extends WP_Widget {
	/**
	 * Outputs the options form on admin.
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		echo ' '; // Fool WordPress that the widget has a form.
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		return $new_instance;
	}
}
