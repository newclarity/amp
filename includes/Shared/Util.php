<?php

namespace NewClarity\AMP\Shared;

use DOMDocument;
use DOMElement;
use DOMNode;

class Util {
	/**
	 * Extract a DOMElement node's HTML element attributes and return as an array.
	 *
	 * @param DOMNode $node Represents an HTML element for which to extract attributes.
	 *
	 * @return string[] The attributes for the passed node, or an
	 *                  empty array if it has no attributes.
	 */
	static function get_node_attributes( $node ) {
		$attributes = array();
		if ( ! $node->hasAttributes() ) {
			return $attributes;
		}

		foreach ( $node->attributes as $attribute ) {
			$attributes[ $attribute->nodeName ] = $attribute->nodeValue;
		}

		return $attributes;
	}


	/**
	 * @param DOMElement $dom_element
	 * @return string
	 */
	static function get_domelement_html( $dom_element ) {
		$dom = new DOMDocument();
		$cloned_element = $dom_element->cloneNode( true );
		$dom->appendChild( $dom->importNode( $cloned_element, true ) );
		$html = $dom->saveHTML();
		return $html;
	}

}
