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
    	"name" => __("Field name", "formality"),
    	"label" => "",
    	"halfwidth" => false,
    	"required" => false,
    	"value" => "",
    	"placeholder" => ($type=="select" ? __("Select your choice", "formality") : __("Type your answer here", "formality")),
    	"rules" => []
  	);  	
  	$options = $options + $defaults;
  	$options["value"] = $this->prefill($options);
  	$class = $type == "message" ? "message" : "field";
		$wrap = '<div class="formality__'.$class.' formality__'.$class.'--'.$type. ($options["halfwidth"] ? " formality__field--half" : "" ) . ($options["required"] ? " formality__field--required" : "") . ($options["value"] ? " formality__field--filled" : "") . '"' . $this->conditional($options["rules"]) . '>%s</div>';
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

	public function print_options($raw_options) {
  	$initval = $raw_options['value'];
  	$options = "";
  	$options .= '<option disabled '. ( $initval ? "" : "selected" ) .' value="">' . $raw_options['placeholder'] . '</option>';
  	foreach ($raw_options['options'] as $option){
      if(isset($option['value']) && $option['value']) {
        $options .= '<option value="'. $option['value'] .'"'. ( $option['value'] == $initval ? " selected" : "" ) .'>' . ( isset($option['label']) && $option['label'] ? $option['label'] : $option['value'] ) . '</option>';
      }
    };
  	return $options;
	}
	
	public function prefill($options) {
  	$value = $options['value'];
  	$uid = $options['uid'];
  	if(isset($_GET[$uid])&&$_GET[$uid]) {
      $value = $_GET[$uid];	
  	}
  	return $value;
	}
	
	public function conditional($rules) {
    if($rules && isset($rules[0]['field'])) {
      $conditions = htmlspecialchars(json_encode($rules), ENT_QUOTES, get_bloginfo( 'charset' ));
      return ' data-conditional="'.esc_attr($conditions).'"';
    }
	}
	
	public function label($options) {
		$label = $options["name"];
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
		$field = $this->label($options) . '<div class="formality__input"><input type="text" ' . $this->attr_name($options['uid']) . $this->attr_required($options['required']) . $this->attr_placeholder($options['placeholder']) .' value="'. $options["value"] .'" /></div>';
    return $field;
	}

	public function email($options) {
		$field = $this->label($options) . '<div class="formality__input"><input type="email" ' . $this->attr_name($options['uid']) . $this->attr_required($options['required']) . $this->attr_placeholder($options['placeholder']) .' value="'. $options["value"] .'" /></div>';
    return $field;
	}
	
	public function textarea($options) {
		$field = $this->label($options) . '<div class="formality__input"><textarea ' . $this->attr_name($options['uid']) . $this->attr_required($options['required']) . $this->attr_placeholder($options['placeholder']) .'>'. $options["value"] .'</textarea></div>';
    return $field;
	}

	public function number($options) {
		$field = $this->label($options) . '<div class="formality__input"><input type="number" ' . $this->attr_name($options['uid']) . $this->attr_required($options['required']) . $this->attr_placeholder($options['placeholder']) .' value="'. $options["value"] .'" /></div>';
    return $field;
	}
	
	public function select($options) {
		$field = $this->label($options) . '<div class="formality__input"><select ' . $this->attr_name($options['uid']) . $this->attr_required($options['required']) . $this->attr_placeholder($options['placeholder']) .'>' . $this->print_options($options) . '</select></div>';
    return $field;
	}

	public function switch($options) {
		$field = $this->label($options) . '<div class="formality__input"><input type="checkbox" ' . $this->attr_name($options['uid']) . $this->attr_required($options['required']) .' value="1" /></div>';
    return $field;
	}

	public function multiple($options) {
		$field = $this->label($options) . '<div class="formality__input"><select ' . $this->attr_name($options['uid']) . $this->attr_required($options['required']) . $this->attr_placeholder($options['placeholder']) .'>' . $this->print_options($options) . '</select></div>';
    return $field;
	}

	public function message($options) {
		$field = '<p>' . $options['text'] . '<p>';
    return $field;
	}	

}