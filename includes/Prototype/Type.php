<?php

namespace NewClarity\AMP\Prototype;

class Type {

	static function parse( $type ) {
		do {

			$result = null;
			if ( ! is_string( $type ) ) {
				break;
			}

			if ( empty( $type ) ) {
				break;
			}

			$type = preg_replace( '#[?!]#', '', $type );

			switch ( $type ) {
				case 'integer':
					$result = 'int';
					break;
				case 'boolean':
					$result = 'bool';
					break;
				case 'amphtml':
					$result = 'amp';
					break;
				case '*':
					$result = 'any';
					break;
				case 'string':
				case 'int':
				case 'bool':
				case 'null':
				case 'html':
				case 'amp':
				case 'mixed':
				case 'url':
				case 'any':
					$result = $type;
					break;
				default:
					$result = "literal:{$type}";
			}

		} while ( false );

		return $result;

	}

}