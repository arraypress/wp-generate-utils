<?php
/**
 * Generate Utilities
 *
 * This class provides essential generation utilities for creating unique identifiers,
 * codes, slugs, and random data. Focuses on practical, frequently-used operations
 * with flexible options and smart defaults.
 *
 * @package ArrayPress\GenerateUtils
 * @since   1.0.0
 * @author  ArrayPress
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\GenerateUtils;

use Exception;

class Generate {

	/**
	 * Generate a UUID v4.
	 *
	 * @return string Standard UUID v4 format (xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx).
	 */
	public static function uuid(): string {
		try {
			$data = random_bytes( 16 );
		} catch ( Exception $e ) {
			// Fallback to less secure but functional method
			$data = '';
			for ( $i = 0; $i < 16; $i ++ ) {
				$data .= chr( wp_rand( 0, 255 ) );
			}
		}

		// Set version to 0100 (UUID version 4)
		$data[6] = chr( ord( $data[6] ) & 0x0f | 0x40 );
		// Set bits 6-7 to 10 (UUID variant 1)
		$data[8] = chr( ord( $data[8] ) & 0x3f | 0x80 );

		return vsprintf( '%s%s-%s-%s-%s-%s%s%s', str_split( bin2hex( $data ), 4 ) );
	}

	/**
	 * Generate a unique key with prefix.
	 *
	 * @param string $prefix Prefix for the key (default: 'id').
	 * @param int    $length Length of random part (default: 9).
	 *
	 * @return string Unique key in format: prefix_randomstring
	 */
	public static function key( string $prefix = 'id', int $length = 9 ): string {
		$prefix = $prefix ?: 'id';
		$length = max( 1, $length );

		// Use alphanumeric lowercase for clean, URL-safe keys
		$random = self::string( $length, '0123456789abcdefghijklmnopqrstuvwxyz' );

		return $prefix . '_' . $random;
	}

	/**
	 * Generate short URL-safe IDs (like YouTube/Bitly).
	 *
	 * @param int $length Length of the ID (default: 7).
	 *
	 * @return string Short URL-safe ID.
	 */
	public static function short_id( int $length = 7 ): string {
		// URL-safe characters without confusing ones (removed 0, O, 1, I, l)
		$chars = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';

		return self::string( $length, $chars );
	}

	/**
	 * Generate sequential IDs with optional prefix.
	 *
	 * @param string $prefix  Prefix for the ID (default: '').
	 * @param int    $padding Zero-padding length (default: 8).
	 * @param string $context Context for the sequence counter (default: 'default').
	 *
	 * @return string Sequential ID like "INV-00001001".
	 */
	public static function sequential_id( string $prefix = '', int $padding = 8, string $context = 'default' ): string {
		$option_name = 'arraypress_seq_' . sanitize_key( $context );
		$sequence    = get_option( $option_name, 1000 );
		update_option( $option_name, $sequence + 1, false );

		$id = str_pad( (string) $sequence, $padding, '0', STR_PAD_LEFT );

		return $prefix ? $prefix . $id : $id;
	}

	/**
	 * Generate codes (discount codes, license keys, etc.) with flexible options.
	 *
	 * @param array $options   {
	 *                         Code generation options.
	 *
	 * @type int    $length    Length of each segment (default: 4).
	 * @type int    $segments  Number of segments (default: 1).
	 * @type string $separator Segment separator (default: '').
	 * @type bool   $uppercase Use uppercase letters (default: true).
	 * @type bool   $numbers   Include numbers (default: true).
	 * @type array  $exclude   Characters to exclude (default: ['0','O','1','I']).
	 * @type string $prefix    Code prefix (default: '').
	 * @type string $suffix    Code suffix (default: '').
	 *                         }
	 *
	 * @return string Generated code.
	 */
	public static function code( array $options = [] ): string {
		$defaults = [
			'length'    => 4,
			'segments'  => 1,
			'separator' => '',
			'uppercase' => true,
			'numbers'   => true,
			'exclude'   => [ '0', 'O', '1', 'I' ],
			'prefix'    => '',
			'suffix'    => ''
		];

		$options = array_merge( $defaults, $options );

		// Build character set
		$chars = '';
		if ( $options['uppercase'] ) {
			$chars .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		} else {
			$chars .= 'abcdefghijklmnopqrstuvwxyz';
		}

		if ( $options['numbers'] ) {
			$chars .= '0123456789';
		}

		// Remove excluded characters
		if ( ! empty( $options['exclude'] ) ) {
			$chars = str_replace( $options['exclude'], '', $chars );
		}

		// Generate segments
		$segments     = [];
		$chars_length = strlen( $chars ) - 1;

		for ( $i = 0; $i < $options['segments']; $i ++ ) {
			$segment = '';
			for ( $j = 0; $j < $options['length']; $j ++ ) {
				$segment .= $chars[ wp_rand( 0, $chars_length ) ];
			}
			$segments[] = $segment;
		}

		// Join segments
		$code = implode( $options['separator'], $segments );

		// Add prefix and suffix
		return $options['prefix'] . $code . $options['suffix'];
	}

	/**
	 * Generate random strings with high entropy.
	 *
	 * @param int    $length  Length of the string (default: 16).
	 * @param string $charset Character set to use ('alnum', 'alpha', 'numeric', 'hex') or custom string.
	 * @param bool   $secure  Use cryptographically secure generation (default: true).
	 *
	 * @return string Random string.
	 */
	public static function string( int $length = 16, string $charset = 'alnum', bool $secure = true ): string {
		$length = max( 1, $length );

		// Define character sets
		$charsets = [
			'alnum'   => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
			'alpha'   => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
			'numeric' => '0123456789',
			'hex'     => '0123456789abcdef'
		];

		$chars  = $charsets[ $charset ] ?? $charset;
		$max    = strlen( $chars ) - 1;
		$string = '';

		if ( $secure ) {
			try {
				for ( $i = 0; $i < $length; $i ++ ) {
					$string .= $chars[ random_int( 0, $max ) ];
				}

				return $string;
			} catch ( Exception $e ) {
				// Fall back to WordPress method
			}
		}

		// WordPress fallback for non-secure or if random_int fails
		for ( $i = 0; $i < $length; $i ++ ) {
			$string .= $chars[ wp_rand( 0, $max ) ];
		}

		return $string;
	}

	/**
	 * Generate security tokens for verification, sessions, etc.
	 *
	 * @param int    $length Length of the token (default: 32).
	 * @param string $action Optional action context for additional entropy.
	 * @param string $format Output format: 'alnum' or 'hex' (default: 'alnum').
	 *
	 * @return string Security token.
	 */
	public static function token( int $length = 32, string $action = '', string $format = 'alnum' ): string {
		$length = max( 8, $length );

		// For hex format, use binary generation
		if ( $format === 'hex' ) {
			try {
				return bin2hex( random_bytes( (int) ceil( $length / 2 ) ) );
			} catch ( Exception $e ) {
				$random = wp_generate_password( $length, false, false );

				return substr( md5( $random . AUTH_KEY ), 0, $length );
			}
		}

		// Generate base random string (alphanumeric, secure)
		$random = self::string( $length, 'alnum', true );

		// Add WordPress-specific entropy if action provided
		if ( $action ) {
			$nonce = wp_create_nonce( $action );
			$raw   = $random . '|' . $nonce . '|' . time() . '|' . AUTH_KEY;
			$token = wp_hash( $raw );

			return substr( $token, 0, $length );
		}

		return $random;
	}

	/**
	 * Generate a magic link token with expiration.
	 *
	 * Creates a secure token with metadata for magic link authentication,
	 * password resets, or one-time verification links.
	 *
	 * @param int    $expires_in Seconds until expiration (default: 24 hours).
	 * @param string $context    Optional context for the token (e.g., 'login', 'reset').
	 * @param int    $length     Token length in bytes (default: 32, outputs 64 hex chars).
	 *
	 * @return array {
	 *     Token data for magic link.
	 *
	 * @type string  $token      The secure token string.
	 * @type string  $expires    UTC expiration datetime.
	 * @type int     $expires_at Unix timestamp of expiration.
	 * @type string  $context    Token context/purpose.
	 *                           }
	 */
	public static function magic_token( int $expires_in = DAY_IN_SECONDS, string $context = '', int $length = 32 ): array {
		try {
			$token = bin2hex( random_bytes( $length ) );
		} catch ( Exception $e ) {
			$token = wp_generate_password( $length * 2, false, false );
		}

		$expires_at = time() + $expires_in;

		return [
			'token'      => $token,
			'expires'    => gmdate( 'Y-m-d H:i:s', $expires_at ),
			'expires_at' => $expires_at,
			'context'    => $context
		];
	}

	/**
	 * Generate unique WordPress slugs.
	 *
	 * @param string $title   Title to base slug on.
	 * @param string $context Context for uniqueness check ('post', 'term', 'user').
	 * @param string $type    Post type or taxonomy (when context is 'post' or 'term').
	 *
	 * @return string Unique slug.
	 */
	public static function slug( string $title, string $context = 'post', string $type = 'post' ): string {
		$slug          = sanitize_title( $title );
		$original_slug = $slug;
		$counter       = 1;

		switch ( $context ) {
			case 'post':
				while ( get_page_by_path( $slug, OBJECT, $type ) ) {
					$slug = $original_slug . '-' . $counter;
					$counter ++;
				}
				break;

			case 'term':
				while ( term_exists( $slug, $type ) ) {
					$slug = $original_slug . '-' . $counter;
					$counter ++;
				}
				break;

			case 'user':
				while ( username_exists( $slug ) ) {
					$slug = $original_slug . $counter;
					$counter ++;
				}
				break;

			default:
				// For custom contexts, just return sanitized slug
				break;
		}

		return $slug;
	}

}