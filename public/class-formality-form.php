<?php

/**
 * Form rendering functions
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Formality
 * @subpackage Formality/public
 */

class Formality_Form {

	private $formality;
	private $version;

	public function __construct( $formality, $version ) {
		$this->formality = $formality;
		$this->version = $version;
		$this->form_id = get_the_ID();
		$this->form_meta = get_post_meta($this->form_id);
	}

  public function option($single) {
    return $single ? ( isset($this->form_meta["_formality_" . $single]) ? $this->form_meta["_formality_" . $single][0] : "" ) : $this->form_meta;
  }
  
	public function fields() {
		$render = new Formality_Fields($this->formality, $this->version);
		$fields = "";
		$index = 0;
		if(has_blocks()) {
      $blocks = parse_blocks(get_the_content());
      foreach ( $blocks as $block ) {
        if($block['blockName']) {
          $index++;
          $type = str_replace("formality/","",$block['blockName']);
          $field = $render->field($type, $block["attrs"], $this->option("type"), $index);
          $fields .= $field;
      	}
      }
    }
		$fields = '<div class="formality__main">' . $fields . '</section></div>';
    return $fields;
	}
	
	public function actions() {
  	$buttons = '<div class="formality__actions">';
		$buttons .= '<button type="button" class="formality__btn formality__btn--prev">Previous</button>';
		$buttons .= '<button type="button" class="formality__btn formality__btn--next">Next</button>';
		$buttons .= '<button type="submit" class="formality__btn formality__btn--submit">Send</button>';
		$buttons .= '</div>';
		return $buttons;
	}

	public function nav() {
		$nav = '<nav class="formality__nav"><ul class="formality__nav__list"></ul></nav>';
		return $nav;
	}
	
	public function result() {
		$result = '<div class="formality__result">';
		$result .= '<div class="formality__result__success">' . $this->option("thankyou") . '</div>';
		$result .= '<div class="formality__result__error">' . $this->option("error") . '</div>';
		$result .= '</div>';
		return $result;
	}

	public function header() {
  	$logo = $this->option("logo_id");
  	$logo = $logo ? wp_get_attachment_image($logo, "full") : file_get_contents(plugin_dir_url(__DIR__) . "assets/images/logo.svg");
		$header = '<header class="formality__header">';
		$header .= '<div class="formality__logo">' . $logo . '</div>';
		$header .= '<h3 class="formality__title">' . get_the_title() . '</h3>';
		$header .= '</header>';
    return $header;
	}
	
	public function body() {
		$body = '<div class="formality__body">' . $this->fields() . $this->nav() . '</div>';
		return $body;
	}
		
	public function footer() {
		$footer = '<footer class="formality__footer">' . $this->actions() . $this->result() . '</footer>';
    return $footer;
	}
	
	public function style() {
		$style = '<style>:root { --formality_col1: ' . $this->option("color1") . ';';
		$style .= '--formality_col2: ' . $this->option("color2") . ';';
		$style .= '--formality_bg: ' . $this->option("color2") . ';';
		$style .= '--formality_fontsize: ' . $this->option("fontsize") . 'px;';
		$style .= '--formality_border: ' . ($this->option("fontsize") < 18 ? 1 : 2) . 'px; }';
		$bg = $this->option("bg_id");
		if($bg) {
  		$bg = wp_get_attachment_image_src($bg, "full");
  		if($bg) {
    		$style .= '.formality__bg { background-image: url(' . $bg[0] . '); }';
    		$style .= '.formality__bg:before { opacity: 0.' . $this->option("overlay_opacity") . '; }';
      }
      $style .= '</style><div class="formality__bg"></div>';
    } else {
		  $style .= '</style>';
		}
		return $style;
	}
	
	public function print($embed=false) {
		$form = '<form id="formality-' . $this->form_id . '" data-id="' . $this->form_id . '" data-uid="' . uniqid() . '" class="formality formality--' . $this->option("type") . '" autocomplete="off" novalidate><div class="formality__wrap">' . $this->header() . $this->body() . $this->footer() . '</div></form>' . $this->style();
		return $form;
	}	

}
