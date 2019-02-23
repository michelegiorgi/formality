<?php
namespace Ultimate_Fields\Form_Object;

use Ultimate_Fields\Form_Object;
use Ultimate_Fields\Datastore\Comment_Meta as Datastore;
use Ultimate_Fields\Datastore\Values;
use Ultimate_Fields\Field;
use Ultimate_Fields\Fields_Collection;

/**
 * Handles comments in front-end forms.
 *
 * @since 3.0
 */
class Comment extends Form_Object {
	/**
	 * Returns the keywords, which the class works with.
	 * Those can be used for the 'create_new' argument of uf_head().
	 *
	 * @since 3.0
	 *
	 * @return string[]
	 */
	public static function get_keywords() {
		return array(
			'WP_Comment' => get_class(),
			'comment'    => get_class()
		);
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
			$item = get_comment( $item );
		}

		if( ! $item ) {
			return;
		}

		$this->item = $item;
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
			$datastore->set_id( $this->item->comment_ID );
		}

		return $datastore;
	}

	/**
	 * Saves the object if needed.
	 *
	 * @since 3.0
	 */
	public function save( $args = array() ) {
		$store  = $this->get_fields_datastore();
		$fields = $this->get_fields();

		$data = array(
			'comment_approved' => 1,
			'comment_content' => isset( $fields[ 'comment_content' ] )
				? $store->get( 'comment_content' )
				: ''
		);

		$object = get_queried_object();
		if( $object && is_a( $object, 'WP_Post' ) ) {
			$data[ 'comment_post_ID' ] = $object->ID;
		}

		if( is_user_logged_in() ) {
			$user = wp_get_current_user();

			$data[ 'user_id' ]              = $user->ID;
			$data[ 'comment_author' ]       = $user->data->display_name;
			$data[ 'comment_author_email' ] = $user->data->user_email;
			$data[ 'comment_author_url' ]   = $user->data->user_url;
		} else {
			$pairs = array(
				'comment_author'       => 'comment_author',
				'comment_author_email' => 'comment_author_email',
				'comment_author_url'   => 'comment_author_url'
			);

			foreach( $pairs as $target => $source ) {
				if( isset( $fields[ $source ] ) ) {
					$data[ $target ] = $store[ $source ];
				}
			}
		}

		# Use the additional arguments
		$data = wp_parse_args( $args, $data );

		# If there is an existing comment, use it
		if( $this->item ) {
			$data[ 'comment_ID' ] = $this->item->comment_ID;
			wp_update_comment( $data );
		} else {
			$comment_id = wp_insert_comment( $data );
			$this->item = get_comment( $comment_id );
			$this->get_datastore()->set_id( $comment_id );
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
		return get_comment_link( $this->item->comment_ID );
	}

	/**
	 * Returns the fields for the container.
	 *
	 * @since 3.0
	 *
	 * @param mixed $include Which fields to include?
	 */
	public function setup_fields( $include = 'all' ) {
		$fields  = $this->fields = new Fields_Collection;
		$all     = 'all' === $include;
		$include = is_array( $include ) ? $include : array();

		if( $all || in_array( 'comment_content', $include ) )
			$fields[] = Field::create( 'textarea', 'comment_content', __( 'Content', 'ultimate-fields' ) )
				->required();

		if( is_user_logged_in() ) {
			return;
		}

		if( $all || in_array( 'comment_author', $include ) )
			$fields[] = Field::create( 'text', 'comment_author', __( 'Your name', 'ultimate-fields' ) )
				->required();
		if( $all || in_array( 'comment_author_email', $include ) )
			$fields[] = Field::create( 'text', 'comment_author_email', __( 'Email', 'ultimate-fields' ) )
				->set_validation_rule( Field\Text::VALIDATION_RULE_EMAIL );
		if( $all || in_array( 'comment_author_url', $include ) )
			$fields[] = Field::create( 'text', 'comment_author_url', __( 'Website', 'ultimate-fields' ) );
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
				'comment_content'   => $this->item
					? $this->item->comment_content
					: '',
			);

			$datastore = new Values( $data );
		}

		return $datastore;
	}
}
