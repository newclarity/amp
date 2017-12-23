<?php

namespace NewClarity\AMP\AmpElements;

use NewClarity\AMP\HtmlElements\div;
use NewClarity\AMP\HtmlElements\noscript;

trait LayoutTrait {

	/**
	 * @var int
	 */
	public $width;

	/**
	 * @var int
	 */
	public $height;

	/**
	 * @var string
	 */
	public $layout;

	/**
	 * @var div
	 */
	public $fallback;

	/**
	 * @var noscript
	 */
	public $noscript;

	/**
	 * @return string
	 */
	function layout_prototype() {
		$prototype =<<<PROTOTYPE
<element height="int!" width="int|auto!" heights="string?" layout="fill|fixed|fixed-height|flex-item|nodisplay|responsive!" size="string?" media="string?" noloading="bool?">
	<any placeholder="bool!">h!</any>
	<any fallback="bool!">?*!</any>
	<noscript>?*</noscript>
</element>
PROTOTYPE;
		return $prototype;
	}



}