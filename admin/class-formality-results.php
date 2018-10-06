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

class Formality_Results {

	private $formality;
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $formality       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $formality, $version ) {
		$this->formality = $formality;
		$this->version = $version;
	}
	
	public function metaboxes() {
		add_meta_box('result_data', 'Result data', array( $this, 'metabox_content' ), 'formality_result', 'normal', 'default');
	}
	
	public function metabox_content() {
		$header = '<table class="wp-list-table widefat fixed striped tags">
			<thead>
			    <tr>
			        <th style="" class="manage-column column-[name]" id="[name]" scope="col">Field</th>
			        <th style="" class="manage-column column-[name2]" id="[name2]" scope="col">Value</th>
			    </tr>
			</thead><tbody>';
		$footer = '</tbody><tfoot>
		</tfoot>
		</table>';
		
		echo $header;
		$result_id = get_the_ID();
		$args = array(
			'post_type' => 'formality_form',
			'p'		=> get_field("id"),
			'posts_per_page' => 1
		);
		$the_query = new WP_Query( $args );
		while ( $the_query->have_posts() ) : $the_query->the_post();
			if( have_rows('formality_fields') ):
		  	while ( have_rows('formality_fields') ) : the_row();
		  		$this->field($result_id);
				endwhile;
			endif;
		endwhile;
		wp_reset_query();
		wp_reset_postdata();
		echo $footer;
	}
	
	public function field($result_id) {
		$fieldname = "field_" . get_sub_field('name');
		echo '<tr><td>' . get_sub_field("label") . '</td>';
		echo '<td>' . get_field($fieldname, $result_id) . '</td></tr>';
	}
	

}
