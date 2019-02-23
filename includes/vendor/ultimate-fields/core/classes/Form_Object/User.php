<?php
namespace Ultimate_Fields\Form_Object;

use Ultimate_Fields\Form_Object;
use Ultimate_Fields\Datastore\User_Meta as Datastore;
use Ultimate_Fields\Datastore\Values;
use Ultimate_Fields\Field;
use Ultimate_Fields\Fields_Collection;

/**
 * Handles user forms within the front-end.
 *
 * @since 3.0
 */
class User extends Form_Object {
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
			'WP_User' => get_class(),
			'user'    => get_class()
		);

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
		if( 'user' === $item ) {
			$this->item = wp_get_current_user();
		} elseif( $user = get_user_by( 'id', $item ) ) {
			$this->item = $user;
		}
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
		$data   = is_array( $args ) ? $args : array();
		$store  = $this->get_fields_datastore();
		$fields = $this->get_fields();

		$pairs = array(
			'first_name'  => 'first_name',
			'last_name'   => 'last_name',
			'nickname'    => 'nickname',
			'user_email'  => 'email',
			'user_login'  => 'email',
			'user_url'    => 'url',
			'description' => 'description',
			'user_pass'   => 'password'
		);

		foreach( $pairs as $target => $source ) {
			if( ! isset( $fields[ $source ] ) )
				continue;

			$data[ $target ] = $store[ $source ];
		}

		# Don't set empty passwords
		if( isset( $data[ 'user_pass' ] ) ) {
			if( ! trim( $data[ 'user_pass' ] ) ) {
				unset( $data[ 'user_pass' ] );
			} elseif( $this->item ) {
				$data[ 'user_pass' ] = wp_hash_password( $data[ 'user_pass' ] );
			}
		}

		if( $this->item ) {
			$data[ 'ID' ] = $this->item->ID;
			wp_update_user( $data );
		} else {
			$user_id = wp_insert_user( $data );
			$this->item = get_user_by( 'id', $user_id );
			$this->get_datastore()->set_id( $user_id );
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
		return get_author_posts_url( $this->item->ID );
	}

	/**
	 * Returns the field for the container.
	 *
	 * @since 3.0
	 *
	 * @param mixed $include Which fields to include?
	 */
	public function setup_fields( $include = 'all' ) {
		$all    = 'all' === $include;
		$fields = new Fields_Collection;

		if( $all || in_array( 'first_name', $include ) )
			$fields[] = Field::create( 'text', 'first_name', __( 'First Name', 'ultimate-fields' ) );
		if( $all || in_array( 'last_name', $include ) )
			$fields[] = Field::create( 'text', 'last_name', __( 'Last Name', 'ultimate-fields' ) );
		if( $all || in_array( 'nickname', $include ) )
			$fields[] = Field::create( 'text', 'nickname', __( 'Nickname', 'ultimate-fields' ) );
		if( ! $this->item && ( $all || in_array( 'email', $include ) ) )
			$fields[] = Field::create( 'text', 'email', __( 'E-Mail', 'ultimate-fields' ) )
				->required()
				->set_validation_rule( Field\Text::VALIDATION_RULE_EMAIL )
				->set_validation_callback( array( $this, 'validate_email' ) );
		if( $all || in_array( 'password', $include ) )
			$fields[] = Field::create( 'password', 'password', __( 'Password', 'ultimate-fields' ) );
		if( $all || in_array( 'url', $include ) )
			$fields[] = Field::create( 'text', 'url', __( 'Website', 'ultimate-fields' ) );
		if( $all || in_array( 'description', $include ) )
			$fields[] = Field::create( 'textarea', 'description', __( 'Biographical Info', 'ultimate-fields' ) );

		if( ! $this->item ) {
			$fields[ 'password' ]->required();
		}

		$this->fields = $fields;
	}

	/**
	 * Checks if an email is free.
	 *
	 * @since 3.0
	 *
	 * @param string $value The value to check as an email.
	 * @return bool
	 */
	public function validate_email( $value ) {
		# The field is required already, so don't "validate" unless there is something to validate
		if( ! $value ) {
			return false;
		}

		# Check the email
		if( ! is_email( $value ) ) {
			return __( 'Please enter a valid email address!', 'ultimate-fields' );
		}

		# Check if the email has already been registered
		if( (bool) get_user_by( 'email', $value ) ) {
			return __( 'This email is already in use.', 'ultimate-fields' );
		}

		return false;
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
			if( $this->item && $this->item->ID ) {
				$data = array(
					'first_name'  => get_user_meta( $this->item->ID, 'first_name', true ),
					'last_name'   => get_user_meta( $this->item->ID, 'last_name', true ),
					'nickname'    => get_user_meta( $this->item->ID, 'nickname', true ),
					'email'       => $this->item->data->user_email,
					'url'         => $this->item->data->user_url,
					'description' => get_user_meta( $this->item->ID, 'description', true ),
				);
			} else {
				$data = array();
			}

			$datastore = new Values( $data );
		}

		return $datastore;
	}
}
