<?php
namespace Ultimate_Fields\Helper;

/**
 * Handles the iterations over layout rows.
 *
 * @since 3.0
 */
class Layout_Rows_Iterator_Loop extends Layout_Rows_Iterator {
	/**
	 * Holds the current index.
	 *
	 * @since 3.0
	 * @var integer
	 */
	protected $position = -1;

	/**
	 * Rewinds the iterator to its beginning.
	 *
	 * @since 3.0
	 */
    public function rewind() {
        $this->position = -1;
    }

	/**
	 * Indicates if there is a group at the current position.
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
    public function valid() {
        return $this->position < count( $this->rows ) - 1;
    }

	/**
	 * Indicates if the iterator is ready for data retrival.
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
	public function ready() {
		return $this->position > -1 && $this->position < count( $this->rows ) + 1;
	}
}
