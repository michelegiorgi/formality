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
	
	public function field($type, $options, $form_type, $index) {
  	$defaults = array(
    	"name" => (__("Field ", "formality") . $index),
    	"label" => "",
    	"halfwidth" => false,
    	"required" => false,
    	"placeholder" => ($type=="select" ? "Select your choice" : "Type your answer here")
  	);  	
  	$options = $options + $defaults;
		$wrap = '<div class="formality__field formality__field--'.$type. ($options["halfwidth"] ? " formality__field--half" : "" ) . ($options["required"] ? " formality__field--required" : "").'">%s</div>';
		if(($type=="step")&&($index==1)) {
			$wrap = '<section class="formality__section formality__section--active">%s';
		} else if($index==1) {
			$wrap = '<section class="formality__section formality__section--active">'.$wrap;
		} else if($type=="step") {
			if($form_type=="conversational") {
				$wrap = '%s';
			} else {
				$wrap = '</section><section class="formality__section">%s';
			}
		}
		return sprintf($wrap, $this->$type($options));
	}
	
	public function attr_name($uid) {
		return 'id="'.$uid.'" name="'.$uid.'"';
	}
	
	public function attr_required($print) {
		return ($print ? ' required=""' : '');
	}

	public function attr_placeholder($placeholder, $label_only = false) {
    return ($label_only ? $placeholder : ' placeholder="' . $placeholder . '"');
	}

/*	
	public function print_options() {
  	$options = "";  	
  	$options .= '<option disabled selected value="">' . $this->placeholder(true, "select") . '</option>';
  	while( have_groups( 'options' ) ) : the_group();
      $options .= '<option value="'. get_the_sub_value( 'value' ) .'">' . get_the_sub_value( 'label' ) . '</option>';
    endwhile;
  	return $options;
	}
*/	
	
	public function label($options) {
		$label = $options['label'] ? $options['label'] : $options["name"];
		$label = '<label class="formality__label" for="'.$options['uid'].'">' . $label . '</label>';
		return $label;
	}
	
	public function step($options) {
		$step = ($options["name"] ? ('<h4>'.$options["name"].'</h4>') : '' );
		$step .= ($options["description"] ? ('<p>'.$options["description"].'</p>') : '' );
		if($step) { $step = '<div class="formality__section__header">'.$step.'</div>'; }
		return $step;
	}
	
	public function text($options) {
		$field = $this->label($options) . '<div class="formality__input"><input type="text" ' . $this->attr_name($options['uid']) . $this->attr_required($options['required']) . $this->attr_placeholder($options['placeholder']) .' /></div>';
    return $field;
	}

	public function email($options) {
		$field = $this->label($options) . '<div class="formality__input"><input type="email" ' . $this->attr_name($options['uid']) . $this->attr_required($options['required']) . $this->attr_placeholder($options['placeholder']) .' /></div>';
    return $field;
	}
	
	public function textarea($options) {
		$field = $this->label($options) . '<div class="formality__input"><textarea ' . $this->attr_name($options['uid']) . $this->attr_required($options['required']) . $this->attr_placeholder($options['placeholder']) .'></textarea></div>';
    return $field;
	}
	
	public function select($options) {
		$field = $this->label($options) . '<div class="formality__input"><select ' . $this->attr_name($options['uid']) . $this->attr_required($options['required']) . $this->attr_placeholder($options['placeholder']) .'>' . /*$this->print_options() .*/ '</select></div>';
    return $field;
	}
	

}