<?php
namespace Ultimate_Fields;

use Ultimate_Fields\Field\Image;

/**
 * Handles the basics for admin columns.
 *
 * @since 3.0
 */
class Admin_Column {
	/**
	 * A constant that indicates that a column should be prepended.
	 *
	 * @since 3.0
	 * @const int
	 */
	const PREPEND = 8;

	/**
	 * A constant that indicates that a column should be appended.
	 *
	 * @since 3.0
	 * @const int
	 */
	const APPEND = 16;

	/**
	 * A constant that indicates that a column should be appended.
	 *
	 * @since 3.0
	 * @const int
	 */
	const AFTER_TITLE = 32;

	/**
	 * Indicates if the column is the column is editable.
	 *
	 * @since 3.0
	 * @var bool
	 */
	protected $editable = false;

	/**
	 * Indicates if the column is sortable.
	 *
	 * @since 3.0
	 * @var bool
	 */
	protected $sortable = false;

	/**
	 * Indicates the position of the column (append, prepend or after_title).
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $position = 16;

	/**
	 * The ID of the column.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $name;

	/**
	 * The type of the column.
	 *
	 * @since 3.0
	 * @var int
	 */
	protected $type;

	/**
	 * The field, which the column works with.
	 *
	 * @since 3.0
	 * @var Ultimate_Fields\Field
	 */
	protected $field;

	/**
	 * Holds a callback, which will be used before displaying the value of the field.
	 *
	 * @since 3.0
	 * @var callable
	 */
	protected $callback = 'wpautop';

	/**
	 * Creates a new column and returns it as an object.
	 *
	 * @since 3.0
	 *
	 * @param string $name The name of the column.
	 * @return Ultimate_Fields\Admin_Column
	 */
	public static function create( $name ) {
		return new self( $name );
	}

	/**
	 * Creates a new instance of the admin.
	 *
	 * @since 3.0
	 *
	 * @param string $name The name of the column.
	 */
	public function __construct( $name ) {
		$this->name = $name;
	}

	/**
	 * Returns the name of the column.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Returns the label of the column.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_label() {
		return $this->field->get_label();
	}

	/**
	 * Makes the column editable.
	 *
	 * NB: This method is available for future implementations. At the moment, editable
	 * columns are not yet implemented.
	 *
	 * @since 3.0
	 * @param bool $editable Whether to make the column editable.
	 * @return Ultimate_Fields\Admin_Column
	 */
	public function editable( $editable = true ) {
		$this->editable = $editable;
		return $this;
	}

	/**
	 * Makes the column sortable.
	 *
	 * @since 3.0
	 * @param bool $sortable Whether to make the column sortable.
	 */
	public function sortable( $sortable = true ) {
		$this->sortable = $sortable;
		return $this;
	}

	/**
	 * Checks if the column is editable.
	 *
	 * @since 3.0
	 * @return bool
	 */
	public function is_editable() {
		return $this->editable;
	}

	/**
	 * Checks if the column is sortable.
	 *
	 * @since 3.0
	 * @return bool
	 */
	public function is_sortable() {
		return $this->sortable;
	}

	/**
	 * Returns the field that the column is associated with.
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\Field
	 */
	public function get_field() {
		return $this->field;
	}

	/**
	 * Associates a field with the column.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Field $field The field that the column works with.
	 * @return Ultimate_Fields\Admin_Column
	 */
	public function set_field( $field ) {
		$this->field = $field;

		if( false && is_a( $field, Image::class ) ) {
			$size       = $field->get_output_size();
			$dimentions = $field->get_image_size_dimentions( $size );
			$width      = $dimentions[ 'width' ] + 15;
			?>
			<style type="text/css">
			.column-<?php echo $field->get_name() ?> {
				width: <?php echo $width; ?>px;
			}
			</style>
			<?php
		}

		return $this;
	}

	/**
	 * Sets the column position to 'prepend'.
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\Admin_Column
	 */
	public function preprend() {
		$this->position = self::PREPEND;

		return $this;
	}

	/**
	 * Sets the column position to 'after_title'.
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\Admin_Column
	 */
	public function append_after_title() {
		$this->position = self::AFTER_TITLE;

		return $this;
	}

	/**
	 * Returns the position of the column.
	 *
	 * @since 3.0
	 *
	 * @return int
	 */
	public function get_position() {
		return $this->position;
	}

	/**
	 * Prepares the output of a column.
	 *
	 * @since 3.0
	 *
	 * @param mixed        $value    The value to output.
	 * @param int          $item_id  The ID of the item that is being displayed.
	 * @param Ultimate_Fields\Field    $field    The field that works with the value.
	 * @param Ultimate_Fields\Location $location The location that is displaying data.
	 * @return mixed
	 */
	public function output( $value, $item_id, $field, $location ) {
		if( false === $value ) {
			return '-';
		}

		if( is_scalar( $value ) ) {
			return wpautop( call_user_func( $this->callback, $value, $location, $item_id ) );
		} elseif( method_exists( $field, 'prepare_admin_column' ) ) {
			return $field->prepare_admin_column( $value );
		} else {
			return '-';
		}
	}

	/**
	 * Allows a custom callback to be set before displaying data.
	 *
	 * @since 3.0
	 *
	 * @param callable $callback The callback to use.
	 * @return Ultimate_Fields\Admin_Column
	 */
	public function set_callback( $callback ) {
		$this->callback = $callback;

		return $this;
	}

	/**
	 * Exports the data of the column.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export() {
		$data = array();

		if( $this->editable ) {
			$data[ 'editable' ] = $this->editable;
		}

		if( $this->sortable ) {
			$data[ 'sortable' ] = $this->sortable;
		}

		if( 16 != $this->position ) {
			$data[ 'position' ] = $this->position;
		}

		return $data;
	}

	/**
	 * Imports data about the column from an array.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $data The data to retrieve arguments from.
	 */
	public function import( $data ) {
		if( isset( $data[ 'editable' ] ) )
			$this->editable = true;

		if( isset( $data[ 'sortable' ] ) )
			$this->sortable = true;

		if( isset( $data[ 'position' ] ) )
			$this->position = intval( $data[ 'position' ] );
	}
}
