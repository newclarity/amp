<?php

namespace NewClarity\AMP\Shared;

use DOMDocument;
use DOMElement;
use DOMNode;
use NewClarity\AMP\HtmlElements\HtmlElement;
use NewClarity\AMP\AmpElements\AmpElement;

class Util {

	/**
	 * @param string $html
	 * @param DOMElement|null $node
	 *
	 * @see https://www.ampproject.org/docs/reference/spec
	 *
	 * @return string
	 */
	static function convert_to_amphtml( $html, $node = null ) {

		if ( is_null( $node ) ) {
			$node = Util::get_html_dom_element( $html, 'body' );
		}

		self::_convert_attributes_to_amphtml( $node );
		self::_convert_element_to_amphtml( $node );

		return self::get_domelement_html( $node );

	}

	/**
	 * @param DOMElement|null $node
	 * @param string[] $attributes
	 */
	private static function whitelist_nodes_with_attributes( $node, $attributes ) {
		// @todo
	}

	/**
	 * @param DOMElement|null $node
	 * @param string[] $attributes
	 */
	private static function remove_node_attributes( $node, $attributes ) {
		// @todo
	}

	/**
	 * @param DOMElement|null $node
	 * @param string[] $attributes
	 */
	private static function blacklist_node_attribute_values( $node, $attributes ) {
		// @todo
	}

	/**
	 * @param DOMElement|null $node
	 * @param string[] $attributes
	 */
	private static function whitelist_node_attribute_values( $node, $attributes ) {
		// @todo
	}

	private static function force_node_attribute_values( $node, $attributes ) {
		// @todo
	}

	/**
	 * @param DOMElement|null $node
	 */
	private static function _convert_element_to_amphtml( $node ) {

		foreach( $node->childNodes as $tag_name => $child_node ) {

			switch ( strtolower( $tag_name ) ) {

				case 'a':
					self::blacklist_node_attribute_values( $child_node, array(
						'href' => array( 'javascript:*' )
					));
					self::force_node_attribute_values( $child_node, array(
						'target' => '_blank'
					));
					break;

				case 'script':
					// @todo strip out all but just this one
					self::whitelist_nodes_with_attributes( $child_node, array(
						'type' => 'application/ld+json'
					));
					break;

				case 'style':
					// @todo strip all but these two.
					self::whitelist_nodes_with_attributes( $child_node, array(
						'amp-boilerplate' => true,
						'value'           => '@todo',
					));
					break;

				case 'input':
					self::blacklist_node_attribute_values( $child_node, array(
						'type' => array(
							'image',
							'button',
							'password',
							'file',
						)
					));
					break;

				case 'link':
					self::remove_node_attributes( $child_node, array(
						'preconnect',
						'prerender',
						'prefetch',
					));
					self::whitelist_node_attribute_values( $child_node, array(
						'rel' => array(
							'@todo: http://microformats.org/wiki/existing-rel-values'
						),
					));
					break;

				case 'meta':
					self::whitelist_node_attribute_values( $child_node, array(
						'http-equiv' => array(
							'X-UA-Compatible',
							'Content-Type',
							'content-language',
							'pics-label',
							'imagetoolbar',
							'Content-Style-Type',
							'Content-Script-Type',
							'origin-trial',
							'resource-type',
						),
					));
					break;

				case 'base':
				case 'frame':
				case 'frameset':
				case 'object':
				case 'param':
				case 'applet':
				case 'embed':
					$child_node->attributes->removeNamedItem( $tag_name );
					continue;

				case 'img':
				case 'video':
				case 'audio':
				case 'iframe':
				case 'form':
					$amp_node = self::convert_html_node_to_amp( $child_node );
					if ( is_null( $amp_node ) ) {
						$node->removeChild( $child_node );
						continue;
					}
					$node->replaceChild( $child_node, $amp_node );
					$child_node = $amp_node;
					break;
			}

			if ( $child_node instanceof DOMElement ) {
				self::_convert_element_to_amphtml( $child_node );
			}

		}
	}

	/**
	 * @param DOMElement $node
	 * @return DOMElement|null
	 */
	private static function convert_html_node_to_amp( $node ) {
		do {

			$amp_node  = null;
			$tag_class = HtmlElement::get_classname( $node->nodeName );
			if ( ! class_exists( $tag_class ) ) {
				break;
			}
			$amp_tag_name   = HtmlElement::get_amphtml_element_name( $node->nodeName );
			if ( is_null( $amp_tag_name ) ) {
				break;
			}
			$amp_class_name = AmpElement::get_classname( $amp_tag_name );
			if ( ! class_exists( $amp_class_name ) ) {
				break;
			}
			/**
			 * @var AmpElement $amp_element
			 */
			$amp_element = new $amp_class_name();
			$amp_element->import_html_node( $node );

		} while ( false );

		return $amp_node;
	}

	/**
	 * @param DOMElement|null $node
	 */
	private static function _convert_attributes_to_amphtml( $node ) {

		foreach( $node->attributes as $tag_name => $attribute ) {

			/**
			 * The style attribute must not be used.
			 *
			 * XML-related attributes, such as xmlns, xml:lang,
			 * xml:base, and xml:space are disallowed in AMP HTML.
			 *
			 * Attribute names starting with 'on' (such as onclick or
			 * onmouseover) are disallowed in AMP HTML. The attribute
			 * with the literal name 'on' (no suffix) is allowed.
			 *
			 * Internal AMP attributes prefixed with -amp- and i-amp-
			 * are disallowed in AMP HTML.
			 */
			if ( preg_match( '#^(style|xml.+|on.+|i?-amp-.+)$#i', $tag_name ) ) {
				$node->attributes->removeNamedItem( $tag_name );
			}

		}

	}

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


	/**
	 * Checks an array of $args to see if it has the specified element
	 *
	 * Can also check to see that is it correct type and if object that is has a given method.
	 *
	 * @param array $args {
	 *     @type string $type Defaults to 'object', type compared to gettype($args[$element])
	 *     @type string $method Name of method to check if exists for an object type
	 *     @type mixed $default value
	 * }
	 *
	 * @param string $element
	 * @param array $options
	 *
	 * @return bool
	 */
	static function check_args( &$args, $element, $options = array() ) {

		do {

			$result = false;

			$options = wp_parse_args( $options, array(
				'type' => 'object',
				'method' => null,
				'default' => null,

			));

			if ( ! isset( $args[ $element ] ) ) {
				$args[ $element ] = $args[ 'default' ];
				break;
			}

			$object = $args[ $element ];

			if ( $options[ 'type' ] !== gettype( $object ) ) {
				$args[ $element ] = $args[ 'default' ];
				break;
			}

			if ( $options[ 'method' ] && ! method_exists( $object, $options[ 'method' ] ) ) {
				$args[ $element ] = $args[ 'default' ];
				break;
			}

			$result = true;

		} while ( false );

		return $result;


	}

	/**
	 * Return directory path for site.
	 *
	 * If site is hosted in a subdirectory, e.g. example.com/blog/:
	 *   - Provides a slash prefixed directory path, e.g. `/blog`
	 *   - Empty string if not
	 *
	 * @return string
	 */
	static function site_directory() {
		$path = preg_quote( trim( parse_url( home_url(), PHP_URL_PATH ), '/' ) );
		if ( ! empty( $path ) ) {
			$path = "/{$path}";
		}
		return $path;
	}

	/**
	 * Return a valid DOMDocument representing the full web page
	 *
	 * @param string $html
	 *
	 * @return DOMDocument|null Returns DOMDocument, or a null value if document not loaded.
	 */
	static function get_html_dom( $html ) {

		$result = null;

		$libxml_previous_state = libxml_use_internal_errors( true );

		$dom = new DOMDocument;

		$result = $dom->loadHTML( $html );

		if ( ! $result ) {
			$dom = null;
		}

		libxml_clear_errors();
		libxml_use_internal_errors( $libxml_previous_state );

		return $dom;

	}


	/**
	 * Return a valid DOMDocument representing the full web page
	 *
	 * @param string|DOMDocument $html
	 * @param string $tag
	 *
	 * @return DOMElement|null Returns DOMDocument, or a null value if document not loaded.
	 */
	static function get_html_dom_element( $html, $tag = 'body' ) {

		do {
			$body = null;

			$dom = is_string( $html )
				? self::get_html_dom( $html )
				: $html;

			$dom_node_list = $dom->getElementsByTagName( $tag );
			if ( 0 === $dom_node_list->length ) {
				break;
			}

			$body = $dom_node_list->item( 0 );

		} while ( false );

		return $body;

	}

}
