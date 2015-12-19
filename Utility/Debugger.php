<?php

namespace Fewlines\Core\Utility;

class Debugger
{
	/**
	 * @param * $input
	 */
	public static function pr($input) {
		$bckt = debug_backtrace();
		$file = $bckt[0]['file'] . ':<b style="font-weight: bold;">' . $bckt[0]['line'] . '</b>';

		echo '<div style="position: relative;z-index: 10000;display: block;background: white;border: 2px solid red;margin: 10px;font-family: Arial;">';
			echo '<div style="display: block;padding: 10px;color: gray;background: #2e2e2e;margin-bottom: 10px;">' . $file . '<div onclick="javascript:
			var node = this.parentNode.nextSibling;
			if(node.style.display == \'block\') {node.style.display = \'none\';this.parentNode.style.marginBottom = 0;this.innerHTML = \'+\';}
			else{node.style.display = \'block\';this.parentNode.style.marginBottom = \'10px\';this.innerHTML = \'-\';}
			" style="float: right; cursor: pointer;">-</div></div>';
			echo '<pre style="padding: 0 10px 10px; display: block;">';
				if (is_bool($input) || empty($input)) {
					var_dump($input);
				}
				else {
					echo static::replaceDebugKeys(htmlspecialchars(print_r($input, true)), array(
						':private\]' => '<span style="color: gray;">:private]</span>',
						':protected\]' => '<span style="color: #2e2e2e;">:private]</span>',
						'\=\>' => '<span style="color: darkred;">=></span>',
						'Array' => '<span style="color: purple;">[...]</span>',
						'\(' => '<span style="font-weight: bold">(</span>',
						'\)' => '<span style="font-weight: bold">)</span>'
					));
				}
			echo '</pre>';
		echo '</div>';
	}

	/**
	 * @param string $str
	 * @param array $keys
	 * @return string
	 */
	private static function replaceDebugKeys($str, $keys) {
		foreach ($keys as $search => $replace) {
			$str = preg_replace('/' . $search . '/', $replace, $str);
		}

		return $str;
	}
}