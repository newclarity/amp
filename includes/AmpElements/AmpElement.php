<?php

namespace NewClarity\AMP\AmpElements;

use NewClarity\AMP\Shared\Base;

class AmpElement extends Base {

	const ELEMENT_NAME = null;
	const IS_CUSTOM = false;

	/**
	 * Flag to specify if AMP element is self closing.
	 *
	 * @note Override in child class if needed.
	 *
	 * @var bool
	 */
	const SELF_CLOSING = false;

	/**
	 * Stores the javascript file used for <script> tag output for the current AMP element.
	 *
	 * @var string
	 */
	private $_javascript_url;

	/**
	 * The AMP version for the Javascript URL;
	 *
	 * @default Value of newclarity_amp()->amp_version.
	 *
	 * @var string
	 */
	private $_amp_version;

	/**
	 * The version of the AMP element to use.
	 *
	 * Used when composing the Javascript URL.
	 *
	 * @var string
	 */
	private $_element_version = '0.1';

	/**
	 * Name of the AMP Element.
	 *
	 * Must begin with `amp-` and be lower case.
	 *
	 * Used when composing the Javascript URL and the AMP HTML element.
	 *
	 * @var string
	 */
	public $element_name;

	/**
	 * Custom element name for the AMP Element, if needed.
	 *
	 * This information is generated when the Javascript is rendered.
	 *
	 * @var string
	 */
	public $custom_element;

	public $is_builtin = false;

	/**
	 * Shared constructor.
	 */
	function __construct( $args = array() ) {

		$amp = newclarity_amp();

		$amp->check_amp_element( static::ELEMENT_NAME );

		$this->element_name = static::ELEMENT_NAME;

		$this->_amp_version = $amp->amp_version;

		parent::__construct( $args );

		$this->setup();

	}

	/**
	 * To be implemented by child class, if needed.
	 *
	 * @note Not declared `abstract` because we don't want to require children to create one.
	 */
	function setup() {}

	/**
	 * Allows developer to set the javascript URL used for <script> tag output, if ever needed.
	 *
	 * @param string $javascript_url
	 */
	function set_javascript_url( $javascript_url ) {
		$this->_javascript_url = $javascript_url;
	}

	/**
	 * Composes URL to javascript file used for <script> tag output for the current AMP element.
	 *
	 * @return string
	 */
	function javascript_url() {
		if ( ! isset( $this->_javascript_url ) ) {
			$amp = newclarity_amp();
			$this->_javascript_url = "https://{$amp->javascript_domain}/{$this->_amp_version}/{$this->element_name}-{$this->_element_version}.js";
		}
		return $this->_javascript_url;
	}

	/**
	 * Renders URL to javascript file used for <script> tag output for the current AMP element.
	 *
	 * @return string
	 */
	function javascript_html() {
		$javascript_url = esc_url( $this->javascript_url() );
		$script =<<<HTML
<script async src="{$javascript_url}"></script>
HTML;
		return $script;
	}

	/**
	 * Renders <script> tag output for javascript to support the current AMP element.
	 */
	function the_javascript_html() {
		echo wp_kses(
			$this->javascript_html(),
			$this->javascript_allowed_html()
		);
	}

	/**
	 * Array of allowable WP_KSES HTML tags for the <script> tag output for javascript.
	 *
	 * @return array[] {
	 *      @type array[] $script {
	 *           @type array $async
	 *           @type array $src
	 *      }
	 * }
	 */
	function javascript_allowed_html() {
		return array(
			'script' => array(
				'async' => array(),
				'src'   => array(),
			)
		);
	}

	/**
	 * @return string
	 */
	function element_content() {
		return '';
	}

	/**
	 * @return string
	 */
	function attributes_html() {
		return '';
	}

	/**
	 * @return string
	 */
	function closing_html() {
		return ! static::SELF_CLOSING
			? ">{$this->element_content()}</{$this->element_name}>"
			: ' />';
	}

	/**
	 * @return string
	 */
	function element_html() {
		$custom_element = static::IS_CUSTOM
			? " custom-element=\"{$this->element_name}\""
			: '';
		$html =<<<HTML
<{$this->element_name}{$custom_element}{$this->attributes_html()}{$this->closing_html()}
HTML;
		return $html;
	}

	/**
	 *
	 */
	function the_element_html() {
		echo wp_kses(
			$this->element_html(),
			$this->element_allowed_html()
		);
	}

	/**
	 * Array of allowable WP_KSES HTML tags for the AMP Element itself.
	 *
	 * @return array[] {
	 *      @type array[] ${$this->custom_element}
	 * }
	 */
	function element_allowed_html() {
		return array(
			$this->element_name => array()
		);
	}

	/**
	 * @param string $prototype;
	 * @param string $defaults;
	 * @return string
	 */
	function merge_prototypes( $prototype, $defaults ) {
		return $prototype;
	}

	/**
	 * @return string
	 */
	function element_prototype() {
		return '<element></element>';
	}

}