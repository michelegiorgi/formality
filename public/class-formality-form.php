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

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $formality       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $formality, $version ) {

		$this->formality = $formality;
		$this->version = $version;

	}
	
	public function field() {
		$field = '<div class="formality__field">
      				<label class="formality__label">Name</label>
      				<input class="formality__input" type="text"/>
      			</div>';
    return $field;
	}

	public function build() {
		$form = '<form class="formality">
      	<div class="formality__wrap">
      		<section class="formality__section">' . $this->field() . '</section> 
      	</div>
      </form>';
		return $form;
	}
	

}
