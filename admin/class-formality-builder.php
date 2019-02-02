<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Formality
 * @subpackage Formality/admin
 */

class Formality_Builder {

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

  public function init() {
    Ultimate_Fields\Container::create_from_array( array (
  		'type' => 'container',
  		'id' => '2d44a80f86',
  		'title' => __( 'Form builder', 'Formality' ),
  		'layout' => 'grid',
  		'locations' => array(
  			array(
  				'type' => 'Post_Type',
  				'post_types' => array(
  					0 => 'formality_form',
  				),
  			),
  		),
  		'fields' => array(
  			array(
  				'name' => __( 'formality_fields', 'Formality' ),
  				'label' => __( 'Form builder', 'Formality' ),
  				'type' => 'Layout',
  				'hide_label' => true,
  				'layout_columns' => 2,
  				'layout_placeholder_text' => 'Test',
  				'layout_background_color' => '',
  				'layout_groups' => array(
  					array(
  						'type' => 'text',
  						'title' => __( 'Text', 'Formality' ),
  						'style' => 'auto',
  						'fields' => array(
  							array(
  								'name' => __( 'name', 'Formality' ),
  								'label' => __( 'Name', 'Formality' ),
  								'type' => 'Text',
  								'required' => true,
  								'field_width' => 50,
  								'output_format' => 'none',
  							),
  							array(
  								'name' => __( 'label', 'Formality' ),
  								'label' => __( 'Label', 'Formality' ),
  								'type' => 'Text',
  								'field_width' => 50,
  							),
  							array(
  								'name' => __( 'placeholder', 'Formality' ),
  								'label' => __( 'Placeholder', 'Formality' ),
  								'type' => 'Text',
  								'field_width' => 50,
  							),
  							array(
  								'name' => __( 'value', 'Formality' ),
  								'label' => __( 'Value', 'Formality' ),
  								'type' => 'Text',
  								'field_width' => 50,
  							),
  							array(
  								'name' => __( 'uid', 'Formality' ),
  								'label' => __( 'uid', 'Formality' ),
  								'type' => 'Text',
  							),
  							array(
  								'name' => __( 'required', 'Formality' ),
  								'label' => __( 'Required?', 'Formality' ),
  								'type' => 'Checkbox',
  							),
  						),
  						'border_color' => '#dddddd',
  						'title_template' => '<% if (name) { %><span class="fake-input"><%= name %></span><% } %>',
  						'minimum_width' => 1,
  						'maximum_width' => 2,
  					),
  					array(
  						'type' => 'email',
  						'title' => __( 'Email', 'Formality' ),
  						'style' => 'auto',
  						'fields' => array(
  							array(
  								'name' => __( 'name', 'Formality' ),
  								'label' => __( 'Name', 'Formality' ),
  								'type' => 'Text',
  								'required' => true,
  								'field_width' => 50,
  								'output_format' => 'none',
  							),
  							array(
  								'name' => __( 'label', 'Formality' ),
  								'label' => __( 'Label', 'Formality' ),
  								'type' => 'Text',
  								'field_width' => 50,
  							),
  							array(
  								'name' => __( 'placeholder', 'Formality' ),
  								'label' => __( 'Placeholder', 'Formality' ),
  								'type' => 'Text',
  								'field_width' => 50,
  							),
  							array(
  								'name' => __( 'value', 'Formality' ),
  								'label' => __( 'Value', 'Formality' ),
  								'type' => 'Text',
  								'field_width' => 50,
  							),
  							array(
  								'name' => __( 'uid', 'Formality' ),
  								'label' => __( 'uid', 'Formality' ),
  								'type' => 'Text',
  							),
  							array(
  								'name' => __( 'required', 'Formality' ),
  								'label' => __( 'Required?', 'Formality' ),
  								'type' => 'Checkbox',
  							),
  						),
  						'border_color' => '#dddddd',
  						'title_template' => '<% if (name) { %><span class="fake-input"><%= name %></span><% } %>',
  						'minimum_width' => 1,
  						'maximum_width' => 2,
  					),
  					array(
  						'type' => 'textarea',
  						'title' => __( 'Textarea', 'Formality' ),
  						'style' => 'auto',
  						'fields' => array(
  							array(
  								'name' => __( 'name', 'Formality' ),
  								'label' => __( 'Name', 'Formality' ),
  								'type' => 'Text',
  								'required' => true,
  								'field_width' => 50,
  								'output_format' => 'none',
  							),
  							array(
  								'name' => __( 'label', 'Formality' ),
  								'label' => __( 'Label', 'Formality' ),
  								'type' => 'Text',
  								'field_width' => 50,
  							),
  							array(
  								'name' => __( 'placeholder', 'Formality' ),
  								'label' => __( 'Placeholder', 'Formality' ),
  								'type' => 'Text',
  								'field_width' => 50,
  							),
  							array(
  								'name' => __( 'value', 'Formality' ),
  								'label' => __( 'Value', 'Formality' ),
  								'type' => 'Text',
  								'field_width' => 50,
  							),
  							array(
  								'name' => __( 'uid', 'Formality' ),
  								'label' => __( 'uid', 'Formality' ),
  								'type' => 'Text',
  							),
  							array(
  								'name' => __( 'required', 'Formality' ),
  								'label' => __( 'Required?', 'Formality' ),
  								'type' => 'Checkbox',
  							),
  						),
  						'border_color' => '#dddddd',
  						'title_template' => '<% if (name) { %><span class="fake-input"><%= name %></span><% } %>',
  						'minimum_width' => 2,
  						'maximum_width' => 2,
  					),
  					array(
  						'type' => 'step',
  						'title' => __( 'Step', 'Formality' ),
  						'style' => 'auto',
  						'fields' => array(
  							array(
  								'name' => __( 'name', 'Formality' ),
  								'label' => __( 'Name', 'Formality' ),
  								'type' => 'Text',
  								'required' => true,
  								'field_width' => '50',
  								'output_format' => 'none',
  							),
  							array(
  								'name' => __( 'description', 'Formality' ),
  								'label' => __( 'Description', 'Formality' ),
  								'type' => 'Text',
  								'field_width' => '50',
  							),
  							array(
  								'name' => __( 'uid', 'Formality' ),
  								'label' => __( 'uid', 'Formality' ),
  								'type' => 'Text',
  							),
  						),
  						'border_color' => '#dddddd',
  						'title_template' => '<% if (name) { %><span class="fake-input"><%= name %></span><% } %>',
  						'minimum_width' => 2,
  						'maximum_width' => 2,
  					),
  				),
  			),
  		),
  		'hash' => '2d44a80f86',
  	) );
  
  	Ultimate_Fields\Container::create_from_array( array (
  		'type' => 'container',
  		'id' => 'f87d900822',
  		'title' => __( 'Form options', 'Formality' ),
  		'layout' => 'grid',
  		'locations' => array(
  			array(
  				'type' => 'Post_Type',
  				'post_types' => array(
  					0 => 'formality_form',
  				),
  			),
  		),
  		'fields' => array(
  			array(
  				'name' => __( 'general', 'Formality' ),
  				'label' => __( 'General', 'Formality' ),
  				'type' => 'Tab',
  			),
  			array(
  				'name' => __( 'formality_type', 'Formality' ),
  				'label' => __( 'Form type', 'Formality' ),
  				'type' => 'Select',
  				'hide_label' => true,
  				'select_input_type' => 'radio',
  				'select_orientation' => 'horizontal',
  				'select_options' => array(
  					'standard' => 'Standard form',
  					'conversational' => 'Conversational form',
  				),
  				'select_output_data_type' => '',
  			),
  			array(
  				'name' => __( 'formality_color1', 'Formality' ),
  				'label' => __( 'Primary color', 'Formality' ),
  				'type' => 'Color',
  				'field_width' => 33,
  				'description' => __( 'Texts, Labels, Input values, Input borders', 'Formality' ),
  			),
  			array(
  				'name' => __( 'formality_color2', 'Formality' ),
  				'label' => __( 'Secondary color', 'Formality' ),
  				'type' => 'Color',
  				'default_value' => '#ffffff',
  				'field_width' => 33,
  				'description' => __( 'Input backgrounds, Input text suggestions.', 'Formality' ),
  			),
  			array(
  				'name' => __( 'formality_fontsize', 'Formality' ),
  				'label' => __( 'Font size', 'Formality' ),
  				'type' => 'Number',
  				'default_value' => 20,
  				'field_width' => 33,
  				'description' => __( 'Align this value to your theme\'s fontsize', 'Formality' ),
  				'number_minimum' => 16.0,
  				'number_maximum' => 24.0,
  				'number_slider' => true,
  			),
  			array(
  				'name' => __( 'standalone', 'Formality' ),
  				'label' => __( 'Standalone', 'Formality' ),
  				'type' => 'Tab',
  			),
  			array(
  				'name' => __( 'formality_logo', 'Formality' ),
  				'label' => __( 'Logo', 'Formality' ),
  				'type' => 'Image',
  				'field_width' => 33,
  				'allowed_filetype' => 'image',
  				'file_output_type' => '',
  				'image_output_type' => '',
  				'image_size' => '',
  			),
  			array(
  				'name' => __( 'formality_bg', 'Formality' ),
  				'label' => __( 'Background image', 'Formality' ),
  				'type' => 'Image',
  				'field_width' => 33,
  				'allowed_filetype' => 'image',
  				'file_output_type' => '',
  				'image_output_type' => '',
  				'image_size' => '',
  			),
  			array(
  				'name' => __( 'formality_bgcolor', 'Formality' ),
  				'label' => __( 'Background color', 'Formality' ),
  				'type' => 'Color',
  				'default_value' => '#ffffff',
  				'field_width' => 33,
  			),
  			array(
  				'name' => __( 'thankyou', 'Formality' ),
  				'label' => __( 'Thankyou', 'Formality' ),
  				'type' => 'Tab',
  			),
  			array(
  				'name' => __( 'formality_thankyou', 'Formality' ),
  				'label' => __( 'Thankyou message', 'Formality' ),
  				'type' => 'WYSIWYG',
  				'default_value' => '<h3>Thank you</h3>
  	<p>Your data has been successfully submitted. You are very important to us, all information received will always remain confidential. We will contact you as soon as possible.</p>',
  				'rows' => 10,
  				'apply_wpautop' => true,
  			),
  			array(
  				'name' => __( 'error', 'Formality' ),
  				'label' => __( 'Error', 'Formality' ),
  				'type' => 'Tab',
  			),
  			array(
  				'name' => __( 'formality_error', 'Formality' ),
  				'label' => __( 'Error message', 'Formality' ),
  				'type' => 'WYSIWYG',
  				'default_value' => '<h3>Error</h3>
  	<p>Oops! Something went wrong and we couldn\'t save your data. Please retry later or contact us by e-mail or phone.</p>',
  				'rows' => 10,
  				'apply_wpautop' => true,
  			),
  		),
  		'hash' => 'f87d900822',
  	) );
  
  	Ultimate_Fields\Container::create_from_array( array (
  		'type' => 'container',
  		'id' => 'd3802c6f70',
  		'title' => __( 'Information', 'Formality' ),
  		'locations' => array(
  			array(
  				'type' => 'Post_Type',
  				'post_types' => array(
  					0 => 'formality_form',
  				),
  				'context' => 'side',
  				'priority' => 'low',
  			),
  		),
  		'fields' => array(
  			array(
  				'name' => __( 'information', 'Formality' ),
  				'label' => __( ' ', 'Formality' ),
  				'type' => 'Message',
  				'description' => __( '<h4 style="margin:0">Standalone version</h4>This is an independent form, that are not tied to your posts or pages, and you can visit here: <a class="formality-admin-info-permalink" target="_blank" href=""></a><h4 style="margin-bottom:0">Embedded version</h4>But you can also embed it, into your post or pages with Formality Gutenberg block or with this specific shortcode:<input class="formality-admin-info-shortcode" type="text" readonly value="">', 'Formality' ),
  			),
  		),
  		'hash' => 'd3802c6f70',
  	) );
  }
}
