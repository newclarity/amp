<?php

namespace NewClarity\AMP\Shared;

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

}
