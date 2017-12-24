<?php

namespace NewClarity\AMP\Prototype;

use NewClarity\AMP\AmpElements\AmpElement;
use NewClarity\AMP\Shared\Util;
use DOMText;
use Exception;

class Element extends Prototype {

	/**
	 * @var string
	 */
	public $prototype = null;

	/**
	 * @var Attribute[]
	 */
	public $attributes = array();

	/**
	 * @var (Element|ElementList)[]
	 */
	public $elements = array();

	/**
	 * Base constructor.
	 *
	 * @param AmpElement|string $element
	 * @param string|null $prototype
	 */
	function __construct( $element, $prototype = null ) {
		if ( $element instanceof AmpElement ) {
			$this->prototype = $element->element_prototype();
			$element = $element->element_name;
		} elseif ( ! is_null( $prototype ) ) {
			$this->prototype = $prototype;
		}
		parent::__construct( $element );
		$this->parse();
	}

	/**
	 * @param Element $element
	 *
	 * @throws Exception
	 */
	public function merge( $element ) {

		if ( $element->name !== $this->name ) {
			$message = '%s of class %s does not match %s of class %s when calling %s.';
			$message = sprintf( $message, get_class( $element ), $element->name, get_class( $this ), $this->name, __METHOD__ );
			throw new Exception( $message );
		}

		/**
		 * Merge attributes
		 */
		$this->attributes  = array_merge( $element->attributes, $this->attributes );
		foreach( $this->attributes as $name => $attribute ) {
			if ( Prototype::DISALLOW === $attribute->constraint ) {
				unset( $this->attributes[ $name ] );
			}
		}

		$this->elements  = array_merge( $element->elements, $this->elements );

		/**
		 * @var Element $child
		 */
		foreach( $this->elements as $name => $child ) {
			if ( Prototype::DISALLOW === $child->constraint ) {
				unset( $this->elements[ $name ] );
				continue;
			}
			if ( ! isset( $element->elements[ $name ] ) ) {
				continue;
			}
			$child->merge( $element->elements[ $name ] );
		}


	}

	/**
	 * @return static
	 * @throws Exception
	 */
	public function parse() {

		do {

			if ( is_null( $this->prototype ) ) {
				$message = '%s called where %s has no $prototype value. Pass as parameter or pass a %s when instantiating a %s.';
				$message = sprintf( $message, __METHOD__, Element::class, AmpElement::class, Element::class );
				throw new Exception( $message );
			}

			/**
			 * Remove trailing whitespace to ensure all text nodes are relevant.
			 */
			$this->prototype = preg_replace( '#>\s+#', '>', $this->prototype );

			$dom = Prototype::get_dom( $this->prototype );

			/**
			 * We want the first child of the body tag.
			 */
			$node = $dom->getElementsByTagName( 'body' )->item( 0 )->firstChild;

			/**
			 * The DOMDocument may contain no first child.
			 */
			if ( ! $node ) {
				break;
			}

			/**
			 * Reset internal state, just in case.
			 */
			$this->constraint = Prototype::REQUIRED;
			$this->attributes = array();
			$this->elements = array();
			$this->valid_types = array( $this->name );

			foreach( Util::get_node_attributes( $node ) as $name => $value ) {

				$attribute = new Attribute( $name );

				list( $constraint, $value ) = $this->_parse_constraint( $value );

				$attribute->constraint = $constraint;

				$attribute->valid_types = $this->_parse_valid_types( $value );

				$this->attributes[ $name ] = $attribute;
			}

			$first_child = $node->firstChild;
			if ( $first_child instanceof DOMText ) {

				/**
				 * Check to see if this element is optional
				 * Optional is determined by a question mark immediately following the opening tag.
				 * @example
				 *
				 *      Here <foo> is required but <bar> is optional:
				 *
				 *      <foo><bar>?</bar><foo>
				 */
				if ( '?' === $first_child->textContent ) {
					$this->constraint = Prototype::OPTIONAL;

					/**
					 * Check to see if this element is disallow
					 * Exclude is determined by a double dash ('--') immediately following the opening tag.
					 * @example
					 *
					 *      Here <foo> is required but <bar> is not allowed:
					 *
					 *      <foo><bar>?</bar><foo>
					 */
				} else if ( '--' === $first_child->textContent ) {
					$this->constraint = Prototype::DISALLOW;

				} else {
					/**
					 * Then textContent must be constraint and valid types.
					 */
					list( $constraint, $valid_types ) = $this->_parse_constraint( $first_child->textContent );
					$this->constraint = $constraint;
					$this->_parse_valid_types( $valid_types );
				}

				/**
				 * We can remove it because for our parsing purposes
				 * we are done with it and it will just be in the way
				 * if we leave it.
				 */
				$node->removeChild( $node->firstChild );
			}

			$elements = array();

			foreach ( $node->childNodes as $child_node ) {

				$prototype = Util::get_domelement_html( $child_node );

				$element = new Element( $child_node->tagName, $prototype );

				$bool_attributes = implode( ':', $this->_bool_attributes_names() );

				$elements[ $element->name ][ $bool_attributes ] = $element;
			}
			foreach ( $elements as $name => $attribute_elements ) {
				$element_name = $name;
				if ( 'any' !== $element_name ) {
					$this->elements[ $element_name ] = reset( $attribute_elements );
				} else {
					foreach ( $attribute_elements as $bool_name => $attribute ) {
						if ( ! empty( $bool_name ) ) {
							$bool_name = ":{$bool_name}";
						}
						$this->elements[ "{$element_name}{$bool_name}" ] = $attribute;
					}
				}
			}

		} while ( false );

		return $this;

	}

	/**
	 * Return array of just the bool attribute names.
	 * @return array
	 */
	private function _bool_attributes_names() {
		$bool_attributes = array();
		foreach( $this->attributes as $name => $attribute ) {
			if ( isset( $attribute->valid_types[ 'bool' ] ) ) {
				$bool_attributes[] = $attribute->name;
			}
		}
		return $bool_attributes;
	}


	/**
	 * @param string[] $valid_types
	 *
	 * @return array
	 */
	private function _parse_valid_types( $valid_types ) {
		$valid_types = array_flip( explode( '|', $valid_types ) );
		$parsed_types = array();
		foreach( array_keys( $valid_types ) as $type ) {
			$parsed_types[ Type::parse( $type ) ] = $type;
		}
		return $parsed_types;
	}

	/**
	 */
	private function _parse_constraint( $value ) {
		switch ( substr( $value, -1 ) ) {
			case '?':
				$constraint = Prototype::OPTIONAL;
				break;
			case '-':
				$constraint = Prototype::DISALLOW;
				break;
			case '!':
			default:
				$constraint = Prototype::REQUIRED;
				break;
		}
		return array( $constraint, substr( $value, 0, -1 ) );
	}

}