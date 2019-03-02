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
	}
		
	public function fields() {
		$render = new Formality_Fields($this->formality, $this->version);
		$fields = "";
		$index = 0;
		while( have_layout_rows( 'formality_fields' ) ): the_layout_row();
		  while( have_groups( 'formality_fields' ) ): the_group();
		    $index++;
		    $type = get_group_type();
		    $uid = get_the_sub_value("uid");
		    $field = sprintf($render->wrap($type, $index), $render->label($type, $uid), $render->$type($uid));
		    $fields .= $field;
		  endwhile;
		endwhile;
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
		$result .= '<div class="formality__result__success">' . get_value("formality_thankyou") . '</div>';
		$result .= '<div class="formality__result__error">' . get_value("formality_error") . '</div>';
		$result .= '</div>';
		return $result;
	}

	public function header() {
  	$logo = get_value("formality_logo");
  	if($logo) {
      $logo = wp_get_attachment_image($logo, "full");
    } else {
    	$logo = file_get_contents(plugin_dir_url(__DIR__) . "assets/images/logo.svg");
  	}
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
		$style = '<style>:root { --formality_col1: ' . get_value("formality_color1") . ';';
		$style .= '--formality_col2: ' . get_value("formality_color2") . ';';
		$style .= '--formality_bg: ' . get_value("formality_color2") . ';';
		$style .= '--formality_fontsize: ' . get_value("formality_fontsize") . 'px;';
		$style .= '--formality_border: ' . (get_value("formality_fontsize") < 18 ? 1 : 2) . 'px; }';
		$bg = get_value("formality_bg");
		if($bg) { 
  		$bg = wp_get_attachment_image_src($bg, "full");
  		if($bg) {
    		$style .= '.formality__bg { background-image: url(' . $bg[0] . '); }';
    		$style .= '.formality__bg:before { opacity: 0.' . get_value("formality_overlay_opacity") . '; }';
      }
    }
		$style .= '</style>';
		return $style;
	}
	
	public function print($embed=false) {
		$form = '<form id="formality-' . get_the_ID() . '" data-id="' . get_the_ID() . '" data-uid="' . uniqid() . '" class="formality formality--' . get_value("formality_type") . '" autocomplete="off" novalidate><div class="formality__wrap">' . $this->header() . $this->body() . $this->footer() . '</div></form>' . $this->style();
		return $form;
	}	

}
