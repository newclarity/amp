<?php

namespace NewClarity\AMP\Shared;

use NewClarity\AMP\HtmlElements\HtmlElement;
use NewClarity\AMP\AmpElements\AmpElement;

use Exception;

abstract class Base {

	/**
	 * @var array Capture any extra $args passed for which there are no properties.
	 */
	var $extra_args = array();

	/**
	 * @param array|string|object $args
	 */
	function __construct( $args = array() ) {

		$this->set_state( $args );

	}

	/**
	 * Set the object state given an array of $args with elements that match property names.
	 *
	 * @not Array elements not found as properties will be assigned to $this->extra_args.
	 *
	 * @param array|string|object $args
	 */
	function set_state( $args ) {

		$args = wp_parse_args( $args );

		foreach ( $args as $name => $value ) {

			if ( 'extra_args' !== $name && property_exists( $this, $name ) ) {

				$this->{$name} = $value;

			} else if ( property_exists( $this, $protected_name = "_{$name}" ) ) {

				$this->{$protected_name} = $value;

			} else {

				$this->extra_args[ $name ] = $value;

			}

		}

	}

	/**
	 * Get the full classname for a given HTML or AMP tag
	 *
	 * @note The classes are not validated. They may not exist, which is expected.
	 *
	 * @param string $tag_name
	 *
	 * @return string
	 * @throws Exception
	 */
	static function get_classname( $tag_name ) {

		$called_class = get_called_class();

		if ( ! preg_match( '#(Amp|Html|Element)$#', $called_class ) ) {
			$message = sprintf(
				'%s is only intended to be called by classes %s or %s',
				__METHOD__,
				HtmlElement::class,
				AmpElement::class
			);
			throw new Exception( $message );
		}

		return "{$called_class}\\\\{$tag_name}";

	}

}
