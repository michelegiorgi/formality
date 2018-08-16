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
		$fields .= '</section>';
    return $fields;
	}
	
	public function buttons() {
		$buttons = '<input type="submit" class="formality__submit" value="Send" />';
		return $buttons;
	}

	public function header() {
		$header = '<div class="formality__header"><h3>'.get_the_title().'</h3></div>';
    return $header;
	}
	
	public function body() {
		$body = '<div class="formality__body">' . $this->fields() . '</div>';
		return $body;
	}
	
	public function footer() {
		$footer = '<div class="formality__footer">' . $this->buttons() . '</div>';
    return $footer;
	}
	
	public function print($embed=false) {
		$form = '<form id="formality-'.get_the_ID().'" class="formality" autocomplete="off"><div class="formality__wrap">' . $this->header() . $this->body() . $this->footer() . '</div></form>';
		return $form;
	}	

}
