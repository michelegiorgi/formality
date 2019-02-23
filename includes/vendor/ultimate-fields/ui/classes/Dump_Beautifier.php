<?php
namespace Ultimate_Fields\UI;

/**
 * Handles the formatting of PHP data during an export.
 *
 * @since 3.0
 */
class Dump_Beautifier {
	/**
	 * Holds the PHP code (export text) that the beautifier works with.
	 * @var string
	 */
	protected $text;

	/**
	 * Receives an array that is to be beautified.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $array The array to use.
	 */
	public function __construct( $array ) {
		$this->text = var_export( $array, true );
		$this->text = preg_replace_callback( '~\n(\s\s+)~', array( $this, 'fix_tabs' ), $this->text );

		# Fix arrays
		$this->text = preg_replace( '~\d+ => \n\t+array ~', 'array', $this->text );
		$this->text = preg_replace( '~\' => \n\t+array ~', '\' => array', $this->text );
	}

	/**
	 * Adds a textdomain for certain keys.
	 *
	 * @since 3.0
	 *
	 * @param string $textdomain The textdomain to use.
	 */
	public function add_textdomain( $textdomain ) {
		$keywords = array( 'title', 'description', 'label', 'name', 'singular_name', 'add_new', 'add_new_item', 'edit_item', 'new_item', 'view_item', 'search_items', 'not_found', 'not_found_in_trash', 'parent_item_colon', 'menu_name', 'popular_items', 'all_items', 'update_item', 'new_item_name', 'separate_items_with_commas', 'add_or_remove_items', 'choose_from_most_used' );

		$keywords = array_map( 'preg_quote', $keywords );
		$keywords = implode( '|', $keywords );

		$this->text = preg_replace(
			'~\'(' . $keywords . ')\' => \'([^\']+)\'~i',
			'\'$1\' => __( \'$2\', \'' . $textdomain . '\' )',
			$this->text
		);
	}

	/**
	 * Used by the regular expression to convert spaces in the export to tabs.
	 *
	 * @since 3.0
	 *
	 * @param string[] $matches The matches by the regular expression.
	 * @return string The generated output.
	 */
	public function fix_tabs( $matches ) {
		return "\n" . str_replace( "  ", "\t", $matches[ 1 ] );
	}

	/**
	 * Indents the content of the export by a given amount of tabs.
	 *
	 * @since 3.0
	 *
	 * @param int $tabs The amount of tabs to add.
	 */
	public function indent( $tabs = 1 ) {
		$this->text = str_replace( "\n", "\n" . str_repeat( "\t", $tabs ), $this->text );
	}

	/**
	 * Converts the dump to a string.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->text;
	}
}
