<?php
namespace Ultimate_Fields\Form_Object;

use Ultimate_Fields\Form_Object;
use Ultimate_Fields\Datastore\Post_Meta as Datastore;
use Ultimate_Fields\Datastore\Values;
use Ultimate_Fields\Field;
use Ultimate_Fields\Fields_Collection;

/**
 * Handles post types with front-end forms.
 *
 * @since 3.0
 */
class Post extends Form_Object {
	/**
	 * Holds the post type that the form is working with.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $type = 'post';

	/**
	 * Returns the keywords, which the class works with.
	 * Those can be used for the 'create_new' argument of uf_head().
	 *
	 * @since 3.0
	 *
	 * @return string[]
	 */
	public static function get_keywords() {
		$keywords = array(
			'WP_Post' => get_class()
		);

		foreach( get_post_types() as $pt ) {
			$keywords[ $pt ] = get_class();
		}

		return $keywords;
	}

	/**
	 * Loads the item for a the object.
	 *
	 * @since 3.0
	 *
	 * @param mixed $item The item that is supposed to be loaded/wrapped.
	 */
	protected function __construct( $item = null ) {
		# If there is no item, don't load anything
		if( is_null( $item ) ) {
			return;
		}

		if( ! is_object( $item ) ) {
			$item = get_post( $item );
		}

		if( ! $item ) {
			return;
		}

		$this->item = $item;
		$this->type = $item->post_type;
	}

	/**
	 * Creates a datastore that works with the given object.
	 *
	 * @since 3.0
	 *
	 * @return Datastore The datastore that should be used with the front-end container.
	 */
	public function get_datastore() {
		static $datastore;

		if( ! is_null( $datastore ) ) {
			return $datastore;
		}

		$datastore = new Datastore;

		if( $this->item ) {
			$datastore->set_id( $this->item->ID );
		}

		return $datastore;
	}

	/**
	 * Saves the object if needed.
	 *
	 * @since 3.0
	 */
	public function save( $args = array() ) {
		# Prepare the basic data for posts
		$data = array();
		if( ! $this->item ) {
			$data[ 'post_type' ]   = $this->type;
			$data[ 'post_status' ] = 'publish';
		}
		$data = wp_parse_args( $args, $data );

		# Add the title and content
		$store  = $this->get_fields_datastore();
		$fields = $this->get_fields();

		$pairs = array(
			'post_title'   => 'post_title',
			'post_content' => 'post_content'
		);
		foreach( $pairs as $target => $source ) {
			if( ! isset( $fields[ $source ] ) )
				continue;

			$data[ $target ] = $store[ $source ];
		}

		# If there is an existing item, use its id
		if( $this->item ) {
			$data[ 'ID' ] = $this->item->ID;
			wp_update_post( $data );
		} else {
			$post_id = wp_insert_post( $data );
			$this->item = get_post( $post_id );
			$this->get_datastore()->set_id( $post_id );
		}
	}

	/**
	 * Returns the URL for the internal object.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_url() {
		return get_permalink( $this->item->ID );
	}

	/**
	 * Returns the field for the container.
	 *
	 * @since 3.0
	 *
	 * @param mixed $include Which fields to include?
	 */
	public function setup_fields( $include = 'all' ) {
		$fields  = new Fields_Collection;
		$all     = 'all' === $include;
		$include = is_array( $include ) ? $include : array();

		if( $all || in_array( 'post_title', $include ) )
			$fields[] = Field::create( 'text', 'post_title', __( 'Title', 'ultimate-fields' ) )
				->required()
				->set_validation_message( __( 'A the title is required!', 'ultimate-fields' ) );

		if( $all || in_array( 'post_content', $include ) ) {
			$field_type = user_can_richedit() ? 'wysiwyg' : 'textarea';
			$fields[] = Field::create( $field_type, 'post_content', __( 'Content', 'ultimate-fields' ) );
		}

		$this->fields = $fields;
	}

	/**
	 * Returns a datastore for the object-related fields.
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\Datastore\Values
	 */
	public function get_fields_datastore() {
		static $datastore;

		if( is_null( $datastore ) ) {
			$data = array(
				'post_title'   => $this->item ? $this->item->post_title   : '',
				'post_content' => $this->item ? $this->item->post_content : ''
			);

			$datastore = new Values( $data );
		}

		return $datastore;
	}
}
