<?php
namespace Fewlines\Core\Http;

use Fewlines\Core\Template\Template;
use Fewlines\Core\Application\Buffer;
use Fewlines\Core\Application\ProjectManager;

class Header
{
	use Messages;

	/**
	 * @var integer
	 */
	const DEFAULT_ERROR_CODE = 500;

	/**
	 * @type boolean
	 */
	private static $running = false;

	/**
	 * @var array
	 */
	private static $codeViews = array();

	/**
	 * Returns all current headers
	 *
	 * @return array
	 */
	public static function getHeaders() {
		return getallheaders();
	}

	/**
	 * Returns the active status code
	 *
	 * @return integer
	 */
	public static function getStatusCode() {
		return http_response_code();
	}

	/**
	 * Returns the status message of the current
	 * status code
	 *
	 * @param  boolean $real if set the default message wil be returned
	 * @return string
	 */
	public static function getStatusMessage($real = false) {
		$code = self::getStatusCode();
		$message = '';

		if (array_key_exists($code, self::$messages)) {
			if (true == $real) {
				if ( ! empty(self::$messages[$code]['status'])) {
					$message = self::$messages[$code]['status'];
				}
			}
			else {
				if (empty(self::$messages[$code]['message'])) {
					$message = self::$messages[$code]['status'];
				}
				else {
					$message = self::$messages[$code]['message'];
				}
			}
		}

		return $message;
	}

	/**
	 * Sets the http status of the current
	 * request/response
	 */
	private static function setStatus($str) {
		header('HTTP/1.0 ' . $str);
		header('Status: ' . $str);
	}

	/**
	 * Sets the defined code
	 *
	 * @param number $code
	 */
	public static function set($code, $throw = true) {
		// Check for recursion
		if (self::$running == true) {
			throw new Header\Exception\StillRunningException('
				Could not (re)set the header, another process
				is still running. Recursion?
			');
		}

		self::$running = true;

		// Check if status message is given
		if ( ! array_key_exists($code, self::$messages)) {
			$code = self::DEFAULT_ERROR_CODE;
		}

		// Build message
		$message = self::$messages[$code]['status'];

		if ( ! empty(self::$messages[$code]['message'])) {
			$message = self::$messages[$code]['message'];
		}

		// Set status to the header
		self::setStatus(self::$messages[$code]['status']);

		// Check if a view was set
		if (array_key_exists($code, self::$codeViews)) {
			$throw = false;

			/**
			 * Clear previous outputs
			 * completely
			 */

			Buffer::clear(true);

			/**
			 * Render new template
			 * with the given view path
			 * and layout
			 */

			$template = Template::getInstance();
			$template->setLayout(self::$codeViews[$code]['layout']);
			$template->setView(self::$codeViews[$code]['routing']['view']);

			$project = ProjectManager::getActiveProject();
			$ctrlClass = '\\' . $project->getNsName() . CONTROLLER_V_RL_NS . '\\' . self::$codeViews[$code]['routing']['ctrl'];

			if ( ! class_exists($ctrlClass) || ! is_string(self::$codeViews[$code]['routing']['ctrl'])) {
				$project = ProjectManager::getDefaultProject();
				$ctrlClass = '\\' . $project->getNsName() . CONTROLLER_V_RL_NS . '\\Error';
			}

			$template->getView()->setControllerClass($ctrlClass);
			$template->getView()->setAction(self::$codeViews[$code]['routing']['action']);

			$template->renderAll();

			// Check end to recognize recursion
			self::$running = false;

			// Abort to prevent further actions
			exit;
		}
		else {
			if(true == $throw) {
				self::$running = false;

				// Throw HTTP exception
				throw new Header\Exception\HttpException($message);
			}
		}

		self::$running = false;
	}

	/**
	 * Sets the url of a code so it will be rendered
	 * instead of the exception
	 *
	 * @param number $code
	 * @param string|array $routing View OR array(Controller:optionalAction, View)
	 * @param boolean $condition
	 */
	public static function setCodeView($code, $routing, $condition = true, $layout = '') {
		if (true == $condition) {
			$controller = '';
			$action = 'index';
			$view = '';

			if (is_array($routing)) {
				$controller = array_key_exists(0, $routing) ? $routing[0] : '';

				// Check if controller string contains view
				if (preg_match_all('/:/', $controller)) {
					$parts = explode(':', $controller);
					$controller = $parts[0];
					$action = $parts[1];
				}

				$view = array_key_exists(1, $routing) ? $routing[1] : '';
			}
			else if (is_string($routing)){
				$view = $routing;
			}

			// Build code array
			self::$codeViews[$code] = array(
				'routing' => array(
					'ctrl' => $controller,
					'action' => $action,
					'view' => $view
				),
				'layout' => empty($layout) ? DEFAULT_LAYOUT : $layout,
			);
		}
	}

	/**
	 * Redirects the user
	 *
	 * @param  string $location
	 */
	public static function redirect($location) {
		Buffer::clear(true);
		header("Location: " . $location);
		exit;
	}
}
