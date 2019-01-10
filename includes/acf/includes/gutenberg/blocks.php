<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('ACF_Blocks') ) :

class ACF_Blocks {
	
	/** @var array Storage for registered blocks */
	var $blocks = array();
	
	/** @var array Storage for the current block */
	var $block = false;
		
	/**
	*  __construct
	*
	*  Sets up the class functionality.
	*
	*  @date	10/4/18
	*  @since	5.6.9
	*
	*  @param	void
	*  @return	void
	*/
	function __construct() {
		
		// includes
		include('class-acf-location-block.php');
		
		// actions
		add_action('acf/enqueue_scripts', array($this, 'enqueue_scripts'));
		
		// ajax
		acf_register_ajax('render_block_edit', array($this, 'ajax_render_block_edit'));
		acf_register_ajax('render_block_preview', array($this, 'ajax_render_block_preview'));
	}
	
	/**
	*  validate_block
	*
	*  Validates a block ensuring all settings exist.
	*
	*  @date	10/4/18
	*  @since	5.6.9
	*
	*  @param	array $block
	*  @return	array
	*/
	function validate_block( $block ) {
		
		// validate
		$block = wp_parse_args($block, array(
			'name'				=> '',
			'title'				=> '',
			'description'		=> '',
			'category'			=> 'common',
			'icon'				=> 'welcome-widgets-menus',
			'mode'				=> 'preview',
			'data'				=> array(),
			'keywords'			=> array(),
			'supports'			=> array(),
			'post_types'		=> array(),
			'render_template'	=> '',
			'render_callback'	=> false
		));
		
		// generate name
		$block['name'] = acf_slugify( 'acf/' . $block['name'] );
		
		// apply default supports array
		$block['supports'] = wp_parse_args($block['supports'], array(
			'align'		=> true,
			'html'		=> false,
			'mode'		=> true,
		));
		
		// return
		return $block;
	}
	
	/**
	*  add_block
	*
	*  Adds a new block to storage or returns false if already exists.
	*
	*  @date	10/4/18
	*  @since	5.6.9
	*
	*  @param	array $block The block settings.
	*  @return	array|false
	*/
	function add_block( $block ) {
		
		// bail early if function does not exist
		if( !function_exists('register_block_type') ) {
			return false;
		}
		
		// vaidate
		$block = $this->validate_block( $block );
		
		// bail early if already exists
		if( $this->has_block($block['name']) ) {
			return false;
		}
		
		// append
		$this->blocks[ $block['name'] ] = $block;
		
		// register
		register_block_type( $block['name'], array(
			'attributes' => array(
				'className'	=> array(
					'type'		=> 'string',
					'default'	=> '',
				),
				'align'		=> array(
					'type'		=> 'string',
					'default'	=> '',
				),

				'data'		=> array(
					'type'		=> 'object',
					'default'	=> '',
				),
				'id'		=> array(
					'type'		=> 'string',
					'default'	=> '',
				),
				'name'		=> array(
					'type'		=> 'string',
					'default'	=> '',
				),
				'mode'		=> array(
					'type'		=> 'mode',
					'default'	=> '',
				),
			),
			'render_callback' => array($this, 'render_callback'),
		));
		
		// return
		return $block;
	}
	
	/**
	*  render_callback
	*
	*  Renders the block HTML (frontend + preview)
	*
	*  @date	11/4/18
	*  @since	5.6.9
	*
	*  @param	array $block The block props.
	*  @param	string $content The block content (emtpy string).
	*  @param	bool $is_preview True during AJAX preview.
	*  @return	string The block HTML.
	*/
	function render_callback( $block, $content = '', $is_preview = false ) {
		
		// get block type
		$block_type = acf_get_block( $block['name'] );
		if( !$block_type ) {
			die();
		}
		
		// merge together
		$block = array_merge($block_type, $block);
		
		// setup postdata
		acf_setup_postdata( $block['data'], $block['id'], true );
		
		// capture output
		ob_start();
		
		// call render_callback
		if( is_callable( $block['render_callback'] ) ) {
			call_user_func( $block['render_callback'], $block, $content, $is_preview );
		
		// include template
		} elseif( $block['render_template'] ) {
			
			// locate template
			if( file_exists($block['render_template']) ) {
				$path = $block['render_template'];
		    } else {
			    $path = locate_template( $block['render_template'] );
		    }
		    
		    // include
		    if( file_exists($path) ) {
			    include( $path );
		    }
		}
		
		// store output
		$html = ob_get_contents();
		ob_end_clean();
		
		// reset postdata
		acf_reset_postdata( $block['id'] );
		
		// return
		return $html;
	}
	
	/**
	*  has_block
	*
	*  Returns true if a block exists for the given name.
	*
	*  @date	10/4/18
	*  @since	5.6.9
	*
	*  @param	string $name The block name.
	*  @return	boolean
	*/
	function has_block( $name ) {
		return isset( $this->blocks[ $name ] );
	}
	
	/**
	*  get_block
	*
	*  Returns a block for the given name.
	*
	*  @date	10/4/18
	*  @since	5.6.9
	*
	*  @param	string $name The block name.
	*  @return	array|false
	*/
	function get_block( $name ) {
		return isset( $this->blocks[ $name ] ) ? $this->blocks[ $name ] : false;
	}
	
	/**
	*  remove_block
	*
	*  Removes a block for the given name.
	*
	*  @date	10/4/18
	*  @since	5.6.9
	*
	*  @param	string $name The block name.
	*  @return	boolean
	*/
	function remove_block( $name ) {
		
		// return false if desn't exist
		if( !$this->has_block($name) ) {
			return false;
		
		// unset and return true
		} else {
			unset( $this->blocks[ $name ] );
			return true;
		}
	}
	
	/**
	*  get_blocks
	*
	*  Returns all block for the given args.
	*
	*  @date	10/4/18
	*  @since	5.6.9
	*
	*  @param	array $args
	*  @return	array
	*/
	function get_blocks() {
		return $this->blocks;
	}
	
	/**
	*  get_block_fields
	*
	*  Returns an array of all fields for the given block.
	*
	*  @date	24/10/18
	*  @since	5.7.8
	*
	*  @param	array $block The block props.
	*  @return	array
	*/
	function get_block_fields( $block ) {
		
		// vars
		$fields = array();
		
		// get field groups
		$field_groups = acf_get_field_groups( array(
			'block'	=> $block['name']
		));
				
		// loop
		if( $field_groups ) {
		foreach( $field_groups as $field_group ) {
			$fields += acf_get_fields( $field_group );
		}}
		
		// return
		return $fields;
	}
	
	/**
	*  ajax_render_block_edit
	*
	*  AJAX callback to render block fields.
	*
	*  @date	10/4/18
	*  @since	5.6.9
	*
	*  @param	void
	*  @return	void
	*/
	function ajax_render_block_edit() {
		
		// validate
		if( !acf_verify_ajax() ) {
			 die();
		}
		
   		// get block
   		$block = acf_maybe_get_POST('block');
   		if( !$block ) {
	   		die();
   		}
   		
   		// unslash $_POST data
   		$block = wp_unslash($block);
   		
   		// get block type
		$block_type = acf_get_block( $block['name'] );
		if( !$block_type ) {
			die();
		}
		
		// merge together
		$block = array_merge($block_type, $block);
		
		// setup postdata
		acf_setup_postdata( $block['data'], $block['id'], true );
		
		// get fields
		$fields = $this->get_block_fields( $block );
		
		// prefix field inputs to avoid multiple blocks using the same name/id attributes
		acf_prefix_fields( $fields, "acf-{$block['id']}" );
				
		// render fields
		acf_render_fields( $fields, $block['id'], 'div', 'field' );
		
		// reset postdata
		acf_reset_postdata( $block['id'] );
		
		// return
		die;
	}
	
	/**
	*  ajax_render_block_preview
	*
	*  AJAX callback to render block preview.
	*
	*  @date	10/4/18
	*  @since	5.6.9
	*
	*  @param	void
	*  @return	void
	*/
	function ajax_render_block_preview() {
		
		// validate
		if( !acf_verify_ajax() ) {
			 die();
		}
		
   		// get block
   		$block = acf_maybe_get_POST('block');
   		if( !$block ) {
	   		die();
   		}
   		
   		// unslash $_POST data
   		$block = wp_unslash($block);
   		
   		// when first previewing block no data exists.
   		// create data using default_value settings.
   		if( empty($block['data']) ) {
	   		$block['data'] = array();
	   		$fields = $this->get_block_fields( $block );
	   		if( $fields ) {
		   	foreach( $fields as $field ) {
		   		$block['data'][ $field['key'] ]	= isset($field['default_value']) ? $field['default_value'] : '';
	   		}}
   		}
		
   		// render_callback vars
   		$content = '';
   		$is_preview = true;
   		
   		// render
   		echo $this->render_callback( $block, $content, $is_preview );
   		die;
	}
		
	/**
	*  enqueue_scripts
	*
	*  Enqueues scripts, styles and localizes data.
	*
	*  @date	10/4/18
	*  @since	5.6.9
	*
	*  @param	void
	*  @return	void
	*/
	function enqueue_scripts() {
		
		// enqueue
		wp_enqueue_script('acf-blocks', acf_get_url('includes/gutenberg/assets/js/acf-blocks.js'), array('acf-input'), ACF_VERSION );
		wp_enqueue_style('acf-blocks', acf_get_url('includes/gutenberg/assets/css/acf-blocks.css'), array('acf-input'), ACF_VERSION );
		
		// localize text
		acf_localize_text(array(
			'Switch to Edit'		=> __('Switch to Edit', 'acf'),
			'Switch to Preview'		=> __('Switch to Preview', 'acf'),
		));
		
		// localize data
		acf_localize_data(array(
			'blocks'	=> array_values($this->get_blocks())
		));
	}
}

// instantiate
acf_new_instance('ACF_Blocks');

endif; // class_exists check

function acf_register_block( $block ) {
	return acf_get_instance('ACF_Blocks')->add_block( $block );
}

function acf_get_blocks() {
	return acf_get_instance('ACF_Blocks')->get_blocks();
}

function acf_get_block( $name ) {
	return acf_get_instance('ACF_Blocks')->get_block( $name );
}

function acf_has_block( $name ) {
	return acf_get_instance('ACF_Blocks')->has_block( $name );
}

function acf_remove_block( $name ) {
	return acf_get_instance('ACF_Blocks')->remove_block( $name );
}

/**
*  acf_validate_block
*
*  description
*
*  @date	22/10/18
*  @since	5.7.8
*
*  @param	type $var Description. Default.
*  @return	type Description.
*/


?>