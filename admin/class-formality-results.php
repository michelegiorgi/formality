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
		
	public function auto_publish() {
		global $pagenow;
    if ( 'post.php' === $pagenow && isset($_GET['post']) ) {
	    if('formality_result' === get_post_type( $_GET['post'] )) {
		    if('unread' === get_post_status($_GET['post'])) {
			    $my_post = array( 'ID' => $_GET['post'], 'post_status' => 'publish');
  				wp_update_post( $my_post );
		    }
	    }
    }
	}	
	
	public function unread_bubble( $menu ) {
		$count = 0;
		$status = "unread";
		$num_posts = wp_count_posts( "formality_result", 'readable' );
		if ( !empty($num_posts->$status) ) { $count = $num_posts->$status; }
		foreach( $menu as $menu_key => $menu_data ) {
	    if( "formality_menu" != $menu_data[2] ) { continue; }
	    if($count) { $menu[$menu_key][4] .= " unread"; }
	    $menu[$menu_key][0] .= " <span class='update-plugins count-$count'><span class='plugin-count'>" . number_format_i18n($count) . '</span></span>';
		}
	  return $menu;
	}
	
	public function unread_status(){
		register_post_status( 'unread', array(
			'label'                     => _x( 'Unread', 'formality_result' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Unread <span class="count">(%s)</span>', 'Unread <span class="count">(%s)</span>' ),
		));
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
		$form_id = get_post_meta( $result_id, "id", true);
		$args = array(
			'post_type' => 'formality_form',
			'p'		=> $form_id,
			'posts_per_page' => 1
		);

		$the_query = new WP_Query( $args );
		while ( $the_query->have_posts() ) : $the_query->the_post();
			while( have_layout_rows( 'formality_fields' ) ): the_layout_row();
		    while( have_groups( 'formality_fields' ) ): the_group();
		  		$this->field($result_id);
				endwhile;
			endwhile;
		endwhile;
		wp_reset_query();
		wp_reset_postdata();
		echo $footer;
	}
	
	public function field($result_id) {
		$fieldname = "field_" . get_the_sub_value('uid');
		echo '<tr><td>' . (get_the_sub_value("name") ? get_the_sub_value("name") : get_the_sub_value("label")) . '</td>';
		echo '<td>' . get_post_meta( $result_id, $fieldname, true ) . '</td></tr>';
	}
	

}
