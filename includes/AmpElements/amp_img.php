<?php

namespace NewClarity\AMP\AmpElements;

class amp_img extends AmpElement {

	const ELEMENT_NAME = 'amp-img';

	use LayoutTrait;

	/**
	 * @var string
	 */
	public $src;

	/**
	 * @var array
	 */
	public $srcset;

	/**
	 * @var string
	 */
	public $alt;

	/**
	 * @return string
	 */
	function element_prototype() {
		return '<element src="url!" srcset="string?" alt="string?" class="string?"><any></any></element>';
	}

}
