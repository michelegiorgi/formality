<?php

/**
 * Fields rendering functions
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Formality
 * @subpackage Formality/public
 */

class Formality_Fields {

	private $formality;
	private $version;

	public function __construct( $formality, $version ) {
		$this->formality = $formality;
		$this->version = $version;
	}
	
	public function wrap($type) {
		$count = get_row_index();
		if(($type=="step")&&($count==1)) {
			$wrap = '<section class="formality__section"><div class="formality__field formality__field--step">%s%s</div>';
		} else if($count==1) {
			$wrap = '<section class="formality__section"><div class="formality__field formality__field--'.$type.' formality__field--'.get_sub_field("width").'">%s%s</div>';
		} else if ($type=="step") {
			$wrap = '</section><section class="formality__section"><div class="formality__field formality__field--step">%s%s</div>';
		} else {
			$wrap = '<div class="formality__field formality__field--'.$type.' formality__field--'.get_sub_field("width").'">%s%s</div>';
		}
		return $wrap;
	}
	
	public function label($type, $name) {
		$label = "";
		if ($type!=="step") {
			$label = '<label class="formality__label" for="'.$name.'">'.get_sub_field("label").'</label>';
		};
		return $label;
	}
	
	public function text($name) {
		$field = '<div class="formality__input"><input type="text" id="'.$name.'" name="'.$name.'" placeholder="'.get_sub_field("placeholder").'" /></div>';
    return $field;
	}

	public function email($name) {
		$field = '<div class="formality__input"><input type="email" id="'.$name.'" name="'.$name.'" placeholder="'.get_sub_field("placeholder").'"/></div>';
    return $field;
	}
	
	public function textarea($name) {
		$field = '<div class="formality__input"><textarea id="'.$name.'" name="'.$name.'" placeholder="'.get_sub_field("placeholder").'"></textarea></div>';
    return $field;
	}

}
