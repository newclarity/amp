<?php

namespace NewClarity\AMP\AmpElements;

class amp_audio extends AmpElement {

	const ELEMENT_NAME = 'amp-audio';
	const IS_CUSTOM = true;

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
		$prototype = '<element src="s!" artwork="s?" title="s?" album="s?" artist="s?" controls="b?" controlsList="s?"></element>';
		return $this->merge_prototypes( $prototype, $this->layout_prototype() );
	}

}