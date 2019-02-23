<?php
namespace Ultimate_Fields\Helper;

use WP_Customize_Control;

class Customize_Control extends WP_Customize_Control {
	protected $callback;
	public $type = 'uf_customizer_control';

	public function render_content() {
		call_user_func( $this->callback );
	}

	public function set_display_callback( $callback ) {
		$this->callback = $callback;
	}
}
