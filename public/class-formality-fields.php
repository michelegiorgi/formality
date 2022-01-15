<?php

/**
 * Fields rendering functions
 *
 * @link       https://formality.dev
 * @since      1.0
 * @package    Formality
 * @subpackage Formality/public
 * @author     Michele Giorgi <hi@giorgi.io>
 */

class Formality_Fields {

  private $formality;
  private $version;

  public function __construct( $formality, $version ) {
    $this->formality = $formality;
    $this->version = $version;
  }

  /**
   * Base field rendering
   *
   * @since    1.0
   */
  public function field($type, $options, $form_type, $index) {
    $defaults = array(
      "name" => __("Field name", "formality"),
      "label" => "",
      "exclude" => 0,
      "halfwidth" => false,
      "required" => false,
      "value" => "",
      "placeholder" => $this->default_placeholder($type),
      "rules" => [],
      "dbg" => []
    );
    $options = $options + $defaults;
    $options["value"] = $this->prefill($options, $type);
    $input_wrap = $options["exclude"] ? "%s" : ($this->label($options) . '<div class="fo__input">%s</div>');
    $wrap = '<div ' . $this->field_classes($type, $options) . $this->conditional($options["rules"]) . $this->dbg($options["dbg"]) . '>'.$input_wrap.'</div>';
    if($type=="step" && $index==1) {
      $wrap = '<section class="fo__section fo__section--active">%s';
    } else if($index==1) {
      $wrap = '<section class="fo__section fo__section--active">'.$wrap;
    } else if($type=="step") {
      $wrap = $form_type=="conversational" ? '%s' : '</section><section class="fo__section">%s';
    }
    $field = apply_filters('formality_form_field', $this->$type($options), $options);
    return sprintf($wrap, $field);
  }

  /**
   * Get field classes
   *
   * @since    1.2
   */
  public function field_classes($type, $options) {
    $classes = 'class="' . ( $type == "message" || $type == "media" ? "fo__" . $type : ( "fo__field fo__field--" . $type) );
    $classes .= $options["halfwidth"] ? " fo__field--half" : "";
    $classes .= $options["required"] ? " fo__field--required" : "";
    $classes .= $options["value"] ? " fo__field--filled" : "";
    $classes .= '" data-type="'.$type.'"';
    return $classes;
  }

  /**
   * Get default placeholders
   *
   * @since    1.0
   */
  public function default_placeholder($type) {
    if($type=="select") {
      $placeholder = __("Select your choice", "formality");
    } else if($type=="multiple") {
      $placeholder = "";
    } else if($type=="rating") {
      $placeholder = "";
    } else if($type=="switch") {
      $placeholder = __("Click to confirm", "formality");
    } else if($type=="upload") {
      $placeholder = __("Choose file or drag here", "formality");
    } else {
      $placeholder = __("Type your answer here", "formality");
    }
    return $placeholder;
  }

  /**
   * Get input name attribute
   *
   * @since    1.0
   */
  public function attr_name($uid, $index = 0) {
    return 'id="' . $uid . ( $index ? ("_" . $index) : "" ) . '" name="'.$uid.'"';
  }

  /**
   * Get input required attribute
   *
   * @since    1.0
   */
  public function attr_required($print) {
    return ($print ? ' required=""' : '');
  }

  /**
   * Get input placeholder attribute
   *
   * @since    1.0
   */
  public function attr_placeholder($placeholder, $label_only = false) {
    return ($label_only ? $placeholder : ' placeholder="' . $placeholder . '"');
  }

  /**
   * Build select options
   *
   * @since    1.0
   */
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

  /**
   * Build radio/checkbox options
   *
   * @since    1.0
   */
  public function print_multiples($options) {
    $initval = $options['value'];
    $options['single'] = (isset($options['single']) && $options['single']) ? "radio" : "checkbox";
    $options['uid'] = $options['single']=="checkbox" ? ( $options['uid'] . "[]" ) : $options['uid'];
    $style = " fo__label--" . $options['single'];
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

  /**
   * Prefill field
   *
   * @since    1.0
   */
  public function prefill($options, $type) {
    $value = $options['value'];
    if(isset($options['uid'])) {
      $uid = $options['uid'];
      $raw = isset($_GET[$uid]) && $_GET[$uid] ? $_GET[$uid] : '';
      if($raw) {
        switch($type) {
          case 'email':
            $value = sanitize_email($raw);
            break;
          case 'textarea':
            $value = sanitize_textarea_field($raw);
            break;
          case 'rating':
          case 'switch':
            $value = absint($raw);
            break;
          default:
            $value = sanitize_text_field($raw);
        }
      }
    }
    return $value;
  }

  /**
   * Build input conditional attribute
   *
   * @since    1.0
   */
  public function conditional($rules) {
    if($rules && isset($rules[0]['field'])) {
      $conditions = htmlspecialchars(json_encode($rules), ENT_QUOTES, get_bloginfo( 'charset' ));
      return ' data-conditional="'.esc_attr($conditions).'"';
    }
  }

  /**
   * Build input dynamic background attributes
   *
   * @since    1.2
   */
  public function dbg($dbg) {
    $attrs = '';
    if(isset($dbg['image']) && $dbg['image']) { $attrs .= ' data-dbg-image="'.esc_attr($dbg['image']).'"'; }
    if(isset($dbg['color']) && $dbg['color']) { $attrs .= ' data-dbg-color="'.esc_attr($dbg['color']).'"'; }
    return $attrs;
  }

  /**
   * Build input label
   *
   * @since    1.0
   */
  public function label($options, $label="", $before = "", $after = "", $class = "", $index = 0) {
    if(!$label) { $label = $options["name"]; }
    $label = '<label class="fo__label' . $class . '" for="' . $options['uid'] . ( $index ? ("_" . $index) : "" ) . '">' . $before . $label . $after . '</label>';
    return $label;
  }

  /**
   * Render form step
   *
   * @since    1.0
   */
  public function step($options) {
    $step = (isset($options["name"]) && $options["name"] ? ('<h4>'.$options["name"].'</h4>') : '' );
    $step .= (isset($options["description"]) && $options["description"] ? ('<p>'.$options["description"].'</p>') : '' );
    if($step) { $step = '<div class="fo__section__header">'.$step.'</div>'; }
    return $step;
  }

  /**
   * Render text field
   *
   * @since    1.0
   */
  public function text($options) {
    $field = '<input type="text" ' . $this->attr_name($options['uid']) . $this->attr_required($options['required']) . $this->attr_placeholder($options['placeholder']) .' value="'. $options["value"] .'" />';
    return $field;
  }

  /**
   * Render email field
   *
   * @since    1.0
   */
  public function email($options) {
    $field = '<input type="email" ' . $this->attr_name($options['uid']) . $this->attr_required($options['required']) . $this->attr_placeholder($options['placeholder']) .' value="'. $options["value"] .'" />';
    return $field;
  }

  /**
   * Render textarea field
   *
   * @since    1.0
   */
  public function textarea($options) {
    $field = '<textarea ' . $this->attr_name($options['uid']) . $this->attr_required($options['required']) . $this->attr_placeholder($options['placeholder']) .' rows="'. (isset($options["rows"]) ? $options["rows"] : 3) .'" maxlength="'. (isset($options["max_length"]) ? $options["max_length"] : 500 ) .'">'. $options["value"] .'</textarea>';
    return $field;
  }

  /**
   * Render number field
   *
   * @since    1.0
   */
  public function number($options) {
    $field = '<input type="number" ' . $this->attr_name($options['uid']) . $this->attr_required($options['required']) . $this->attr_placeholder($options['placeholder']) .' value="'. $options["value"] . '"' . (isset($options["value_min"]) ? ' min="' . $options["value_min"] . '"' : "") . (isset($options["value_max"]) ? ' max="' . $options["value_max"] . '"' : "") .' step="'. (isset($options["value_step"]) ? $options["value_step"] : "") .'" />';
    return $field;
  }

  /**
   * Render select field
   *
   * @since    1.0
   */
  public function select($options) {
    $field = '<select ' . $this->attr_name($options['uid']) . $this->attr_required($options['required']) . $this->attr_placeholder($options['placeholder']) .'>' . $this->print_options($options) . '</select>';
    return $field;
  }

  /**
   * Render switch field
   *
   * @since    1.0
   */
  public function switch($options) {
    $style = isset($options['style']) ? ( " fo__label--" . $options['style'] ) : "";
    $field = '<input'. (( isset($options['value']) && $options['value'] ) ? " checked" : "" ) .' type="checkbox" ' . $this->attr_name($options['uid']) . $this->attr_required($options['required']) .' value="1" />' . $this->label($options, $options["placeholder"], "<i></i><span>", "</span>", $style);
    return $field;
  }

  /**
   * Render multiple field
   *
   * @since    1.0
   */
  public function multiple($options) {
    $style = isset($options['style']) ? ( " fo__input__grid--" . $options['style'] ) : "";
    $field = '<div class="fo__note">' . $options['placeholder'] . '</div>';
    $field .= '<div class="fo__input__grid' . $style . ' fo__input__grid--' . ( isset($options['option_grid']) ? $options['option_grid'] : 2 ) . '">' . $this->print_multiples($options) . '</div>';
    return $field;
  }

  /**
   * Render rating field
   *
   * @since    1.0
   */
  public function rating($options) {
    $field = '<div class="fo__note">' . $options['placeholder'] . '</div><div class="fo__input__rating">';
    $max = isset($options["value_max"]) ? $options["value_max"] : 10;
    $icon = isset($options["icon"]) ? $options["icon"] : 'star';
    $svg_base = '<svg width="36px" height="36px" viewBox="0 0 36 36" version="1.1" xmlns="http://www.w3.org/2000/svg"><defs>{ICON}</defs><use href="#{UID}" class="border" x="0" y="0"/><use href="#{UID}" class="fill" x="0" y="0"/></svg>';
    switch ($icon) {
      case "rhombus":
        $path = '<polygon id="{UID}" points="18.1999422 5 7.2 17.9205212 18.1999422 30.8410424 29.2 17.9205212"></polygon>';
        break;
      case "heart":
        $path = '<path id="{UID}" d="M32.0986828,15.8148901 C31.5106966,18.3076928 30.1504164,20.5749448 28.1682144,22.3705128 L18.1089854,31.3452349 L17.4426481,30.7405168 L8.21654492,22.3675927 C6.23737493,20.5743969 4.87721216,18.3079698 4.2885129,15.8145146 C4.02009809,14.6783011 3.95884311,13.7499203 4.02040361,13.0541562 C4.02888654,12.9582811 4.03856491,12.883729 4.04387212,12.8532568 L4.04518984,12.8561723 C4.44604138,8.34258388 7.64314976,5 11.6840824,5 C14.3711363,5 16.7648309,6.48146822 18.1140301,8.89689812 C19.4736879,6.50616597 21.9695846,5.00054977 24.7036219,5.00054977 C28.7515265,5.00054977 31.9420839,8.35121088 32.3458597,12.8733768 C32.3486736,12.8864297 32.3580683,12.9583798 32.3663732,13.0515773 C32.4284391,13.7480763 32.3673452,14.6775602 32.0986828,15.8148901 Z"></path>';
        break;
      default:
        $path = '<path id="{UID}" d="M13.0544771,12.9863905 L17.6180178,3.74071968 L18.5147516,5.55736935 L22.1816172,12.9858961 L32.3850081,14.4686292 L30.9341159,15.8829352 L25.0018304,21.6656272 L26.7445065,31.8279604 L24.9511102,30.8851353 L17.6182868,27.0301201 L8.49097086,31.8278906 L8.83353892,29.8309254 L10.2342519,21.6656174 L2.85097924,14.4685241 L4.85625116,14.1772434 L13.0544771,12.9863905 Z"></path>';
    }
    $svg = str_replace("{ICON}", $path, $svg_base);
    $svg = str_replace("{UID}", 'glyph_' . $options['uid'], $svg);
    for ($n = 1; $n <= $max; $n++) {
      $field .= '<input type="radio" ' . $this->attr_name($options['uid'], $n) . $this->attr_required($options['required']) .' value="' . $n . '" />' . $this->label($options, $n, $svg, "", "", $n);
    }
    $field .= '</div>';
    return $field;
  }

  /**
   * Render message
   *
   * @since    1.0
   */
  public function message($options) {
    $field = isset($options['text']) ? '<p>' . $options['text'] . '<p>' : '';
    return $field;
  }

  /**
   * Render media
   *
   * @since    1.0
   */
  public function media($options) {
    $field = "";
    if(isset($options['media'])) {
      if($options['media_type']=='video') {
        $field = '<video loop><source src="' . $options['media'] . '" type="video/mp4"></video>';
        $field .= '<a href="#"><svg width="24px" height="24px" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg"><path d="M7.77051563,5.42042187 L0.77053125,9.92042187 C0.6885,9.973625 0.593765625,10.0000156 0.500015625,10.0000156 C0.417984375,10.0000156 0.33496875,9.97948437 0.260765625,9.93898437 C0.099609375,9.85109375 0,9.68309375 0,9.5 L0,0.5 C0,0.31690625 0.099609375,0.14890625 0.260765625,0.061015625 C0.41896875,-0.025890625 0.617203125,-0.020546875 0.77053125,0.079578125 L7.77051562,4.57957812 C7.91310937,4.67135937 8.00001562,4.83007812 8.00001562,5 C8.00001562,5.16992187 7.91310938,5.32859375 7.77051563,5.42042187 Z" transform="translate(9.000000, 7.000000)"></path></svg></a>';
      } else {
        $field = wp_get_attachment_image($options['media_id'], 'full');
      }
    }
    return $field;
  }

  /**
   * Render upload field
   *
   * @since    1.3.0
   */
  public function upload($options) {
    if(!isset($options['formats'])) { $options['formats'] = array('jpg', 'jpeg', 'gif', 'png', 'pdf'); }
    if(!isset($options['maxsize'])) { $options['maxsize'] = 3; }
    $field = '<input type="file" ' . $this->attr_name($options['uid']) . $this->attr_required($options['required']) . $this->attr_placeholder($options['placeholder']) .' accept=".' . ( count($options['formats']) ? implode(", .", $options['formats']) : 'nnnn' ) . '" data-file="" data-max-size="' . ($options['maxsize'] * 1048576) .'" />';
    $field .= '<label class="fo__upload" for="' . $options['uid'] . '">';
    $field .= '<div class="fo__upload__toggle"><p>' . $options['placeholder'] . '</p>';
    $field .= '<span>' . __("Size limit", "formality") . ' <strong>' . $options['maxsize'] . 'MB</strong></span>';
    $field .= '<span>' . __("Allowed types", "formality") . ' <strong>' . ( count($options['formats']) ? implode(", ", $options['formats']) : __('None', 'formality') ) . '</strong></span></div><div class="fo__upload__info"></div></label>';
    return $field;
  }

}
