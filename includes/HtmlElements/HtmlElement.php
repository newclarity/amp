<?php

namespace NewClarity\AMP\HtmlElements;

use NewClarity\AMP\Shared\Element;
use DOMElement;

class HtmlElement extends Element {
	const ELEMENT_NAME = null;
	const AMP_EQUIVALENT = null;

	/**
	 *
	 * @note Intended to be implemented by child class.
	 *
	 * @param string $tag_name
	 *
	 * @return string|null
	 */
	static function get_amphtml_element_name( $tag_name ) {
		return self::AMP_EQUIVALENT;
	}

	/**
	 * @param DOMElement $node
	 *
	 * @return null
	 */
	static function import_html_node( $node ) {
		return $node;
	}

}