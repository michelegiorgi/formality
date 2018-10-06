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
		if( have_rows('formality_fields') ) {
		  while ( have_rows('formality_fields') ) : the_row();
		    $type = get_row_layout();
		    $name = get_sub_field("name");
		    $field = sprintf($render->wrap($type), $render->label($type, $name), $render->$type($name));
		    $fields .= $field;
		  endwhile;
		} else { 
			//No fields, no party.
		};
		$fields = '<div class="formality__main">' . $fields . '</section></div>';
    return $fields;
	}
	
	public function buttons() {
		$buttons = '<button type="button" class="formality__btn formality__btn--prev">Previous</button>';
		$buttons .= '<button type="button" class="formality__btn formality__btn--next">Next</button>';
		$buttons .= '<button type="submit" class="formality__btn formality__btn--submit">Send</button>';
		return $buttons;
	}

	public function nav() {
		$nav = '<nav class="formality__nav"><ul class="formality__nav__list"></ul></nav>';
		return $nav;
	}

	public function header() {
		$badge = file_get_contents(plugin_dir_url(__DIR__) . "assets/images/logo.svg");
		$header = '<header class="formality__header">';
		$header .= '<div class="formality__logo">'.$badge.'</div>';
		$header .= '<h3 class="formality__title">'.get_the_title().'</h3>';
		$header .= '</header>';
    return $header;
	}
	
	public function body() {
		$body = '<div class="formality__body">'. $this->fields() . $this->nav() . '</div>';
		return $body;
	}
		
	public function footer() {
		$footer = '<footer class="formality__footer"><div class="formality__actions">' . $this->buttons() . '</div></footer>';
    return $footer;
	}
	
	public function print($embed=false) {
		$form = '<form id="formality-'.get_the_ID().'" data-id="'.get_the_ID().'" data-uid="'.uniqid().'" class="formality" autocomplete="off" novalidate><div class="formality__wrap">' . $this->header() . $this->body() . $this->footer() . '</div></form>';
		return $form;
	}	

}
