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
	
	public function wrap($type, $count) {
		$wrap = '<div class="formality__field formality__field--'.$type.' formality__field--width'.get_group_width(). (get_the_sub_value("required") ? " formality__field--required" : "").'">%s%s</div>';
		if(($type=="step")&&($count==1)) {
			$wrap = '<section class="formality__section formality__section--active">%s%s';
		} else if($count==1) {
			$wrap = '<section class="formality__section formality__section--active">'.$wrap;
		} else if($type=="step") {
			if(get_field("formality_type")=="conversational") {
				$wrap = '%s%s';
			} else {
				$wrap = '</section><section class="formality__section">%s%s';
			}
		}
		return $wrap;
	}
	
	public function print_name($uid) {
		return 'id="'.$uid.'" name="'.$uid.'"';
	}
	
	public function print_required() {
		return (get_the_sub_value("required") ? ' required=""' : '');
	}
	
	public function step() {
		$step = (get_the_sub_value("name") ? ('<h4>'.get_the_sub_value("name").'</h4>') : '' );
		$step .= (get_the_sub_value("description") ? ('<p>'.get_the_sub_value("description").'</p>') : '' );
		if($step) { $step = '<div class="formality__section__header">'.$step.'</div>'; }
		return $step;
	}
	
	public function label($type, $uid) {
		$label = "";
		if ($type!=="step") {
			$label = (get_the_sub_value("label") ? get_the_sub_value("label") : get_the_sub_value("name"));
			$label = '<label class="formality__label" for="'.$uid.'">' . $label . '</label>';
		};
		return $label;
	}
	
	public function placeholder() {
  	$placeholder = get_the_sub_value("placeholder");
  	if(!$placeholder) {
    	$placeholder = "Type your answer here";
  	}
  	return ' placeholder="' . $placeholder . '"';
	}
	
	public function text($uid) {
		$field = '<div class="formality__input"><input type="text" ' . $this->print_name($uid) . $this->print_required() . $this->placeholder() .' /></div>';
    return $field;
	}

	public function email($uid) {
		$field = '<div class="formality__input"><input type="email" ' . $this->print_name($uid) . $this->print_required() . $this->placeholder() .' /></div>';
    return $field;
	}
	
	public function textarea($uid) {
		$field = '<div class="formality__input"><textarea ' . $this->print_name($uid) . $this->print_required() . $this->placeholder() .'></textarea></div>';
    return $field;
	}

}