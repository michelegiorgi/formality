<?php

/**
 * Fields rendering functions
 *
 * @link       https://formality.dev
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
    	"exclude" => 0,
    	"halfwidth" => false,
    	"required" => false,
    	"value" => "",
    	"placeholder" => $this->default_placeholder($type),
    	"rules" => []
  	);  	
  	$options = $options + $defaults;
  	$options["value"] = $this->prefill($options);
  	$class = $type == "message" ? "formality__message" : ( "formality__field formality__field--" . $type);
  	$input_wrap = $options["exclude"] ? "%s" : ($this->label($options) . '<div class="formality__input">%s</div>');
		$wrap = '<div class="' . $class . ($options["halfwidth"] ? " formality__field--half" : "" ) . ($options["required"] ? " formality__field--required" : "") . ($options["value"] ? " formality__field--filled" : "") . '"' . $this->conditional($options["rules"]) . ' data-type="' . $type . '">'.$input_wrap.'</div>';
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
	
	public function default_placeholder($type) {
  	if($type=="select") {
      $placeholder = __("Select your choice", "formality");	
  	} else if($type=="multiple") {
    	$placeholder = "";
  	} else if($type=="rating") {
    	$placeholder = "";
  	} else if ($type=="switch") {
      $placeholder = __("Click to confirm", "formality");
    } else {
      $placeholder = __("Type your answer here", "formality");
    }
    return $placeholder;
	}
	
	public function attr_name($uid, $index = 0) {
		return 'id="' . $uid . ( $index ? ("_" . $index) : "" ) . '" name="'.$uid.'"';
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

	public function print_multiples($options) {
  	$initval = $options['value'];
  	$options['single'] = (isset($options['single']) && $options['single']) ? "radio" : "checkbox";
  	$options['uid'] = $options['single']=="checkbox" ? ( $options['uid'] . "[]" ) : $options['uid'];
  	$style = " formality__label--" . $options['single'];
  	$index = 0;
  	$multiples = "";
  	foreach ($options['options'] as $multiple){
      if(isset($multiple['value']) && $multiple['value']) {
        $index++;
        $label_key = (isset($multiple['label']) && $multiple['label']) ? $multiple['label'] : $multiple['value'];
        $multiples .= '<input'. ( $multiple['value'] == $initval ? " checked" : "" ) .' type="'.$options['single'].'" ' . $this->attr_name($options['uid'], $index) . $this->attr_required($options['required']) .' value="'. $multiple['value'] .'" />' . $this->label($options, $label_key, "<i></i><span>", "</span>", $style, $index);        
      }
    };
  	return $multiples;
	}
	
	public function prefill($options) {
  	$value = $options['value'];
  	if(isset($options['uid'])) {
    	$uid = $options['uid'];
    	if(isset($_GET[$uid])&&$_GET[$uid]) {
        $value = $_GET[$uid];	
    	}
    }
  	return $value;
	}
	
	public function conditional($rules) {
    if($rules && isset($rules[0]['field'])) {
      $conditions = htmlspecialchars(json_encode($rules), ENT_QUOTES, get_bloginfo( 'charset' ));
      return ' data-conditional="'.esc_attr($conditions).'"';
    }
	}
	
	public function label($options, $label="", $before = "", $after = "", $class = "", $index = 0) {
		if(!$label) { $label = $options["name"]; }
		$label = '<label class="formality__label' . $class . '" for="' . $options['uid'] . ( $index ? ("_" . $index) : "" ) . '">' . $before . $label . $after . '</label>';
		return $label;
	}
	
	public function step($options) {
		$step = ($options["name"] ? ('<h4>'.$options["name"].'</h4>') : '' );
		$step .= ($options["description"] ? ('<p>'.$options["description"].'</p>') : '' );
		if($step) { $step = '<div class="formality__section__header">'.$step.'</div>'; }
		return $step;
	}
	
	public function text($options) {
		$field = '<input type="text" ' . $this->attr_name($options['uid']) . $this->attr_required($options['required']) . $this->attr_placeholder($options['placeholder']) .' value="'. $options["value"] .'" />';
    return $field;
	}

	public function email($options) {
		$field = '<input type="email" ' . $this->attr_name($options['uid']) . $this->attr_required($options['required']) . $this->attr_placeholder($options['placeholder']) .' value="'. $options["value"] .'" />';
    return $field;
	}
	
	public function textarea($options) {
		$field = '<textarea ' . $this->attr_name($options['uid']) . $this->attr_required($options['required']) . $this->attr_placeholder($options['placeholder']) .' rows="'. (isset($options["rows"]) ? $options["rows"] : 3) .'" maxlength="'. (isset($options["max_length"]) ? $options["max_length"] : 500 ) .'">'. $options["value"] .'</textarea>';
    return $field;
	}

	public function number($options) {
		$field = '<input type="number" ' . $this->attr_name($options['uid']) . $this->attr_required($options['required']) . $this->attr_placeholder($options['placeholder']) .' value="'. $options["value"] . '"' . (isset($options["value_min"]) ? ' min="' . $options["value_min"] . '"' : "") . (isset($options["value_max"]) ? ' max="' . $options["value_max"] . '"' : "") .' step="'. (isset($options["value_step"]) ? $options["value_step"] : "") .'" />';
    return $field;
	}
	
	public function select($options) {
		$field = '<select ' . $this->attr_name($options['uid']) . $this->attr_required($options['required']) . $this->attr_placeholder($options['placeholder']) .'>' . $this->print_options($options) . '</select>';
    return $field;
	}

	public function switch($options) {
  	$style = isset($options['style']) ? ( " formality__label--" . $options['style'] ) : "";
		$field = '<input'. (( isset($options['value']) && $options['value'] ) ? " checked" : "" ) .' type="checkbox" ' . $this->attr_name($options['uid']) . $this->attr_required($options['required']) .' value="1" />' . $this->label($options, $options["placeholder"], "<i></i><span>", "</span>", $style);
    return $field;
	}

	public function multiple($options) {
  	$field = '<div class="formality__note">' . $options['placeholder'] . '</div>';
  	$field .= '<div class="formality__input__grid formality__input__grid--' . ( isset($options['option_grid']) ? $options['option_grid'] : 1 ) . '">' . $this->print_multiples($options) . '</div>';
    return $field;
	}

	public function rating($options) {
  	$field = '<div class="formality__note">' . $options['placeholder'] . '</div>';
  	$max = isset($options["value_max"]) ? $options["value_max"] : 10;
  	$icon = isset($options["icon"]) ? $options["icon"] : 'star';
  	$svg = file_get_contents(plugin_dir_url(__DIR__) . "assets/images/" . $icon . ".svg");
  	for ($n = 1; $n <= $max; $n++) {
		  $field .= '<input type="radio" ' . $this->attr_name($options['uid'], $n) . $this->attr_required($options['required']) .' value="' . $n . '" />' . $this->label($options, $n, $svg, "", "", $n);
    }
    return $field;
	}

	public function message($options) {
		$field = '<p>' . $options['text'] . '<p>';
    return $field;
	}	

}