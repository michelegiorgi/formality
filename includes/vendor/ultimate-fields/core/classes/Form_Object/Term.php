<?php
namespace Ultimate_Fields\Form_Object;

use Ultimate_Fields\Form_Object;
use Ultimate_Fields\Datastore\Term_Meta as Datastore;
use Ultimate_Fields\Datastore\Values;
use Ultimate_Fields\Field;
use Ultimate_Fields\Fields_Collection;

/**
 * Handles terms in front-end forms.
 *
 * @since 3.0
 */
class Term extends Form_Object {
	/**
	 * Holds the taxonomy that the form is working with.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $type = 'category';

	/**
	 * Returns the keywords, which the class works with.
	 * Those can be used for the 'create_new' argument of uf_head();
	 *
	 * @since 3.0
	 *
	 * @return string[]
	 */
	public static function get_keywords() {
		$keywords = array(
			'WP_term' => get_class()
		);

		foreach( get_taxonomies() as $tax ) {
			$keywords[ $tax ] = get_class();
		}

		return $keywords;
	}

	/**
	 * Loads the term for an object.
	 *
	 * @since 3.0
	 *
	 * @param mixed $item The item that is supposed to be loaded/wrapped.
	 */
	protected function __construct( $item = null ) {
		# If there is no item, don't proceed
		if( is_null( $item ) ) {
			return;
		}

		if( ! is_object( $item ) ) {
			$item = get_term( $item );
		}

		if( ! $item ) {
			return;
		}

		$this->item = $item;
		$this->type = $item->taxonomy;
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
			$datastore->set_id( $this->item->term_id );
		}

		return $datastore;
	}

	/**
	 * Saves the object if needed.
	 *
	 * @since 3.0
	 */
	public function save( $args = array() ) {
		$store       = $this->get_fields_datastore();
		$fields      = $this->get_fields();
		$name        = isset( $fields[ 'name' ] ) ? $store->get( 'name' ) : '';
		$description = isset( $fields[ 'description' ] ) ? $store->get( 'description' ) : '';

		$args = wp_parse_args( array(
			'description' => $description
		), $args );

		if( $this->item ) {
			$args[ 'name' ] = $name;
			wp_update_term( $this->item->term_id, $this->type, $args );
		} else {
			$item = wp_insert_term( $name, $this->type, $args );
			$this->item = get_term( $item[ 'term_id' ] );
			$this->get_datastore()->set_id( $item[ 'term_id' ] );
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
		return get_term_link( $this->item->ID );
	}

	/**
	 * Returns the field for the container.
	 *
	 * @since 3.0
	 *
	 * @param mixed $include Which fields to include?
	 */
	public function setup_fields( $include = 'all' ) {
		$fields = new Fields_Collection;

		$fields[] = Field::create( 'text', 'name', __( 'Title', 'ultimate-fields' ) )
			->required()
			->set_validation_message( __( 'A the title is required!', 'ultimate-fields' ) );

		$fields[] = Field::create( 'textarea', 'description', __( 'Content', 'ultimate-fields' ) );

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
				'name'        => $this->item ? $this->item->name        : '',
				'description' => $this->item ? $this->item->description : ''
			);

			$datastore = new Values( $data );
		}

		return $datastore;
	}
}
