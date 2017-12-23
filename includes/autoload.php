<?php

/**
 * Implement an autoloader with a closure
 */
spl_autoload_register( function ( $class_name ) {
	do {

		/**
		 * Match `NewClarity\AMP\AmpElements\amp_img`,`NewClarity\AMP\HtmlElements\img`, etc.
		 *
		 * On match $match[1] will have class name with `NewClarity\AMP\` removed from front.
		 *
		 * @example:
		 *
		 *      `NewClarity\AMP\AmpElements\amp_img` => `AmpElements\amp_img`
		 *      `NewClarity\AMP\HtmlElements\img`    => `HtmlElements\img`
		 *
		 * @see http://www.developwebsites.net/match-backslash-preg_match-php/
		 */
		if ( ! preg_match( '#^NewClarity\\\\AMP\\\\(.+)$#', $class_name, $match ) ) {
			break;
		}

		/**
		 * Convert namespace separators ('\') to path separators ('/').
		 *
		 * @note This _should_ also work on Windows with backslash paths.
		 */
		$relative_class_name = str_replace( '\\', '/', $match[ 1 ] );

		/**
		 * Compose the file name and check to see if it exists.  If not, break out.
		 */
		if ( ! is_file( $filepath = __DIR__ . "/{$relative_class_name}.php" ) ) {
			break;
		}

		/**
		 * And finally load the class file.
		 */
		require( $filepath );

	} while ( false );

},

	/**
	 * Do not throw an error if not found
	 */
	$throw = false,

	/**
	 * If debugging, call before other autoloaders.
	 */
	$prepend = defined( 'WP_DEBUG' ) && WP_DEBUG
);
