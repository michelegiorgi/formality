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
  	$thankyou_default = __("<h3>Thank you</h3><p>Your data has been successfully submitted. You are very important to us, all information received will always remain confidential. We will contact you as soon as possible.</p>", "formality");
  	$error_default = __("<h3>Error</h3><p>Oops! Something went wrong and we couldn't save your data. Please retry later or contact us by e-mail or phone.</p>", "formality");
		$result = '<div class="formality__result">';
		$result .= '<div class="formality__result__success">' . ( $this->option("thankyou") ? $this->option("thankyou") : $thankyou_default ) . '</div>';
		$result .= '<div class="formality__result__error">' . ( $this->option("error")  ? $this->option("error") : $error_default ) . '</div>';
		$result .= '</div>';
		return $result;
	}

	public function header() {
  	$logo = $this->option("logo_id");
  	//$logo = $logo ? wp_get_attachment_image($logo, "full") : file_get_contents(plugin_dir_url(__DIR__) . "assets/images/logo.svg");
  	$logo = $logo ? wp_get_attachment_image($logo, "full") : '';
		$header = '<header class="formality__header">';
		$header .= '<div class="formality__logo">' . $logo . '</div>';
		$header .= '<h2 class="formality__title">' . get_the_title() . '</h2>';
		$header .= '</header>';
    return $header;
	}
	
	public function body() {
		$body = '<div class="formality__body">' . $this->fields() . $this->nav() . '</div>';
		return $body;
	}
		
	public function footer() {
		$footer = '<footer class="formality__footer">' . $this->actions() . $this->result() . $this->credits() . '</footer>';
    return $footer;
	}

	public function credits() {
  	$credits = '<div class="formality__credits">';
  	$credits .= 'Made with <a target="_blank" href="https://formality.dev"><svg width="45px" height="30px" viewBox="0 0 45 30" version="1.1"><path d="M26.2434082,6 L36.7565918,6 C41.3093004,6 45,9.69069957 45,14.2434082 C45,18.7961168 41.3093004,22.4868164 36.7565918,22.4868164 L26.2434082,22.4868164 C21.6906996,22.4868164 18,18.7961168 18,14.2434082 C18,9.69069957 21.6906996,6 26.2434082,6 Z M26.2212891,19.3434082 C29.0379413,19.3434082 31.3212891,17.0600604 31.3212891,14.2434082 C31.3212891,11.426756 29.0379413,9.1434082 26.2212891,9.1434082 C23.4046368,9.1434082 21.1212891,11.426756 21.1212891,14.2434082 C21.1212891,17.0600604 23.4046368,19.3434082 26.2212891,19.3434082 Z"></path><polygon points="0 22.078125 0 0 15.65625 0 15.65625 3.640625 3.96875 3.640625 3.96875 9.21875 14.28125 9.21875 14.28125 12.859375 3.96875 12.859375 3.96875 22.078125"></polygon></svg></a>';
  	$credits .= $this->option("credits") ? ( '&nbsp; — &nbsp;' . $this->option("credits") ) : '';
		$credits .= '</div>';
		return $credits;
	}

	public function style() {
		$style = '<style>:root { --formality_col1: ' . $this->option("color1") . ';';
		$style .= '--formality_col2: ' . $this->option("color2") . ';';
		$style .= '--formality_bg: ' . $this->option("color2") . ';';
		$style .= '--formality_fontsize: ' . $this->option("fontsize") . 'px;';
		$style .= '--formality_border: ' . ($this->option("fontsize") < 18 ? 1 : 2) . 'px; }';
		$bg = $this->option("bg_id");
		if($bg && (!$this->option("template"))) {
      $bg = wp_get_attachment_image_src($bg, "full");
      $bg_url = $bg[0];
    } else {
      $bg_url = $this->option("bg");
    }
		if($bg_url) {
      $style .= '.formality__bg { background-image: url(' . $bg_url . '); background-position: '. $this->option("position") .'; }';
      $style .= '.formality__bg:before { opacity: 0.' . sprintf('%02d',$this->option("overlay_opacity")) . '; }';
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
