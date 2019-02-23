<?php
namespace Ultimate_Fields\Field;

/**
 * Allows radio-based selects to be created quickly, without having to call
 * ->set_input_type() as it's already preset.
 *
 * @since 3.0
 */
class Radio extends Select {
	/**
	 * Holds the display type of the field.
	 *
	 * @since 3.0
	 * @var string.
	 */
	protected $input_type = 'radio';

	/**
	 * Returns the type of the field for JS. Since we're using the select
	 * field model/view, we'll just return 'radio' as the type.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_type() {
		return 'Select';
	}
}
