<?php

namespace NewClarity\AMP\Prototype;

use DOMDocument;

abstract class Prototype {

	const REQUIRED = 1;
	const OPTIONAL = 2;
	const DISALLOW = 3;

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var Type[]
	 */
	public $valid_types = array();

	/**
	 * @var int
	 */
	public $constraint;

	/**
	 * Base constructor.
	 *
	 * @param string $name
	 */
	function __construct( $name ) {
		$this->name = $name;
	}

	/**
	 * Return a valid DOMDocument representing the element prototype
	 *
	 * @param string $prototype
	 *
	 * @return DOMDocument|false Returns DOMDocument, or false if conversion failed.
	 */
	static function get_prototype_dom( $prototype ) {

		$result = null;

		$libxml_previous_state = libxml_use_internal_errors( true );

		/**
		 * Remove trailing whitespace to ensure all text nodes are relevant.
		 */
		$prototype = preg_replace( '#>\s+#', '>', $prototype );

		$dom = new DOMDocument;

		/*
		 * Wrap in dummy tags, since XML needs one parent node.
		 * It also makes it easier to loop through nodes.
		 * We can later use this to extract our nodes.
		 * Add utf-8 charset so loadHTML does not have problems parsing it.
		 * See: http://php.net/manual/en/domdocument.loadhtml.php#78243
		 */
		$head   = '<head><meta http-equiv="content-type" content="text/html; charset=utf-8"></head>';
		$result = $dom->loadHTML( "<html>{$head}<body>{$prototype}</body></html>" );

		if ( ! $result ) {
			$dom = null;
		}

		libxml_clear_errors();
		libxml_use_internal_errors( $libxml_previous_state );

		return $dom;
	}
}