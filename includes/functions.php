<?php
/**
 * Functions to support NewClarity AMP
 */

/**
 * @return \NewClarity\AMP
 */
function newclarity_amp() {
	return \NewClarity\AMP::instance();
}

add_action( 'setup_theme', function() {
	if ( ! function_exists( 'amp' ) ) {
		/**
		 * @return \NewClarity\AMP
		 */
		function amp() {
			return \NewClarity\AMP::instance();
		}
	}
});
