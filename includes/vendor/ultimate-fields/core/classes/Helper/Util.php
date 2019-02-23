<?php
namespace Ultimate_Fields\Helper;

/**
 * Wraps some basic utility functions.
 *
 * @since 3.0
 */
class Util {
	/**
	 * Explodes a list of numbers and returns them as an array.
	 *
	 * @since 3.0
	 *
	 * @param string $string    The list with the numbers, comma separated.
	 * @param bool   $filter    Whether to include null elements or not (optional).
	 * @param string $separator The separator, by default a comma.
	 * @return int[]
	 */
	public static function string_to_numbers( $string, $filter = true, $separator = ',' ) {
		$elements = explode( $separator, $string );

		# Remove empty spaces
		$elements = array_map( 'trim', $elements );

		# Remove empty elements
		$elements = array_filter( $elements );

		# Map to numbers
		$elements = array_map( 'intval', $elements );

		# If needed, filters
		if( $filter ) {
			$elements = array_filter( $elements );
		}

		return $elements;
	}

	/**
	 * Parses terms.
	 *
	 * @since 3.0
	 *
	 * @param Callback $callback The settings as a callback.
	 * @param mixed[]  $terms    The terms to process.
	 * @return int[]             IDs of terms.
	 */
	public static function parse_terms( $callback, $terms ) {
		$processed = array();
		$slugs     = array();

		foreach( $terms as $key => $value ) {
			if( is_string( $value ) && ! preg_match( '~^\d+$~', $value ) ) {
				$slugs[ $key ] = $value;
				$processed[ $key ] = false;
			} else {
				$processed[ $key ] = intval( $value );
			}
		}

		$args = array(
			'hide_empty' => false,
			'slug'       => $slugs
		);

		if( isset( $callback[ 'taxonomy' ] ) && ! empty( $callback[ 'taxonomy' ] ) ) {
			$queried = get_terms( $callback[ 'taxonomy' ], $args );
		} else {
			$queried = get_terms( $args );
		}

		foreach( $queried as $term ) {
			$processed[ $term->slug ] = $term->term_id;
		}

		return $processed;
	}

	/**
	 * Converts *stars* within a text to <strong> tags.
	 *
	 * @since 3.0
	 *
	 * @param string $text The text to parse.
	 * @return string
	 */
	public static function stars_to_bold( $text ) {
		return preg_replace( '~\*([^\*]+)\*~u', '<strong>$1</strong>', $text );
	}
}