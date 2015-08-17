<?php
namespace Fewlines\Core\Helper;

use Fewlines\Core\Locale\Locale;

class ShortcutHelper
{
	/**
	 * @var string
	 */
	const SHORTCUT_IDENTIFIER_PATTERN = '/(.*)\{(.*)\-\>\((.*)\)\}(.*)/';

	/**
	 * @var string
	 */
	const SHORTUCT_ARRAY_SYNTAX = '/(.*)\:(.*),/';

	/**
	 * @var string
	 */
	const SHORTCUT_FUNCTION_PREFIX = 'exec';

	/**
	 * Parse a string to get the string
	 * with executed functions
	 *
	 * @param  string $str
	 * @return string
	 */
	public static function parse($str) {
		$explode = explode("{", $str);
		$shortcuts = array();

		foreach ($explode as $i => $exp) {
			if (preg_match('/\}/', $exp)) {
				$shCut = explode("}", $exp);
				$shortcuts[] = "{" . $shCut[0] . "}";
				$shortcuts[] = $shCut[1];
			}
			else {
				$shortcuts[] = $exp;
			}
		}

		$shortcuts = ArrayHelper::clean($shortcuts);
		$result = "";

		// Execute shortcuts and build result string
		for ($i = 0, $len = count($shortcuts); $i < $len; $i++) {
			if (self::isShortcut($shortcuts[$i])) {
				$name = strtolower(self::getStringArguments($shortcuts[$i], 1));
				$value = self::getStringArguments($shortcuts[$i], 2);
				$result .= self::executeShortcut($name, $value);
			}
			else {
				$result .= $shortcuts[$i];
			}
		}

		return $result;;
	}

	/**
	 * Executes the shortcut function and
	 * returns it value (must be a string)
	 *
	 * @param  string $name
	 * @param  string $value
	 * @return string
	 */
	public static function executeShortcut($name, $value) {
		$method = self::SHORTCUT_FUNCTION_PREFIX . strtoupper($name);
		$class = '\\' . __CLASS__;
		$return = '';

		if (method_exists($class, $method)) {
			$return = call_user_func_array($class . '::' . $method, array($value));
		}

		return $return;
	}

	/**
	 * Gets the variable from the match
	 * of the pattern and the string
	 *
	 * @param  string $str
	 * @param  integer $index
	 * @return string|array
	 */
	private static function getStringArguments($str, $index) {
		preg_match(self::SHORTCUT_IDENTIFIER_PATTERN, $str, $matches);

		$value = '';
		$index += 1; // Increase to avoid selecting the full $str

		if (array_key_exists($index, $matches)) {
			$value = trim($matches[$index]);
		}

		/**
		 * Check if the value
		 * is an array or not
		 */

		if (preg_match(self::SHORTUCT_ARRAY_SYNTAX, $value)) {
			/**
			 * Get the values of the array
			 * sperated from the string
			 */

			$arrayValues = ArrayHelper::trimExplode(',', $value);

			/**
			 * Build associative array
			 * from the given values
			 */

			$value = array();

			foreach ($arrayValues as $val) {
				$expVal = ArrayHelper::trimExplode(':', $val);
				$value[$expVal[0]] = $expVal[1];
 			}

		}

		return $value;
	}

	/**
	 * Checks if the given string contains
	 * at least one shortcut
	 *
	 * @param  string  $str
	 * @return boolean
	 */
	public static function containsShortcut($str) {
		return (bool)preg_match_all(self::SHORTCUT_IDENTIFIER_PATTERN, trim($str));
	}

	/**
	 * Checks if the given string is a valid
	 * shortcut to be executed
	 *
	 * @param  string  $str
	 * @return boolean
	 */
	public static function isShortcut($str) {
		return (bool)preg_match(self::SHORTCUT_IDENTIFIER_PATTERN, trim($str));
	}

	/**
	 * Returns the base url
	 *
	 * @param  string $value
	 * @return string
	 */
	public static function execURL($value) {
		return UrlHelper::getBaseUrl($value);
	}

	/**
	 * Returns a translation string
	 *
	 * @param  string $value
	 * @return string
	 */
	public static function execLANG($value) {
		$translation = Locale::get($value);
		return ! is_array($translation) ? $translation : '';
	}
}