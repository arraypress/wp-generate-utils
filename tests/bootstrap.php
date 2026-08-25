<?php
/**
 * PHPUnit bootstrap.
 *
 * @package ArrayPress\Generate
 */

declare( strict_types=1 );

if ( ! function_exists( 'remove_accents' ) ) {
	/**
	 * Core's transliteration, abbreviated.
	 *
	 * Enough of the table to exercise the branch. The real one is several
	 * hundred characters; the point of the test is that this path is taken at
	 * all, not that the stub is complete.
	 *
	 * @param string $text Text.
	 *
	 * @return string
	 */
	function remove_accents( string $text ): string {
		return strtr(
			$text,
			array(
				'á' => 'a', 'à' => 'a', 'â' => 'a', 'ä' => 'a', 'å' => 'a', 'ã' => 'a',
				'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
				'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
				'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'ö' => 'o', 'õ' => 'o', 'ø' => 'o',
				'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
				'ñ' => 'n', 'ç' => 'c', 'ß' => 'ss', 'æ' => 'ae',

				// Upper case as well. Leaving it out was a stub deficiency
				// that read as a library bug: "Übungsstück" lost its first
				// character, because the Ü survived transliteration and was
				// then stripped by the a-z filter.
				'Á' => 'A', 'À' => 'A', 'Â' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Ã' => 'A',
				'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
				'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
				'Ó' => 'O', 'Ò' => 'O', 'Ô' => 'O', 'Ö' => 'O', 'Õ' => 'O', 'Ø' => 'O',
				'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
				'Ñ' => 'N', 'Ç' => 'C', 'Æ' => 'AE',
			)
		);
	}
}

require_once dirname( __DIR__ ) . '/vendor/autoload.php';
