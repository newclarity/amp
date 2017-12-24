<?php

namespace NewClarity\AMP\HtmlElements;

use NewClarity\AMP\Shared\Base;
use DOMElement;

class HtmlElement extends Base {
	const ELEMENT_NAME = null;

	/**
	 * @param DOMElement $node
	 *
	 * @return null
	 */
	static function import_html_node( $node ) {
		return $node;
	}

}

