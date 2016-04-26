<?php
namespace Fewlines\Core\Application;

class Buffer
{
	/**
	 * Starts a buffer
	 */
	public static function start() {
		ob_start();
	}

	/**
	 * Clears the buffer
	 *
	 * @param  boolean $force Clear all output
	 */
	public static function clear($force = false) {
		if (true == $force) {
			$content = ob_get_contents();

			while ( ! empty($content)) {
            	ob_end_clean();
        	}
		}
		else {
			ob_end_clean();
		}
	}
}