<?php
namespace Fewlines\Core\Http\Router\Routes;

use Fewlines\Core\Helper\UrlHelper;
use Fewlines\Core\Helper\ArrayHelper;

class Route
{
	/**
	 * @var string
	 */
	const TO_SEPERATOR = ':';

	/**
	 * @var string
	 */
	private $type;

	/**
	 * @var string
	 */
	private $from;

	/**
	 * @var array
	 */
	private $vars = array();

	/**
	 * @var string
	 */
	private $to;

	/**
	 * @var array
	 */
	private $routes = array();

	/**
	 * @param string $type
	 * @param string $from
	 * @param string $to
	 */
	public function __construct($type, $from, $to) {
		$this->type = $type;
		$this->from = $from;
		$this->to = $to;

		// Init vars
		$this->initVars();
	}

	/**
	 * Init the variables and removes them from
	 * the routing url
	 */
	private function initVars() {
		preg_match_all('/\{(.*?)\}/', $this->from, $matches);
		$this->from = preg_replace('/\{.*\}/', '', $this->from);
		$this->from = UrlHelper::cleanUrl($this->from);

		// Add vars
		foreach ($matches[1] as $name) {
			$this->vars[] = new Variable($name);
		}
	}

	/**
	 * @param Route $route
	 * @return self
	 */
	public function addRoute(Route $route) {
		$this->routes[] = $route;
		return $this;
	}

	/**
	 * Checks if the route has variables set
	 *
	 * @return boolean
	 */
	public function hasVars() {
		return ! empty($this->vars);
	}

	/**
	 * Get the vars set from the url
	 *
	 * @return array
	 */
	public function getVars() {
		return $this->vars;
	}

	/**
	 * @param string $to
	 */
	public function setType($type) {
		$this->type = $type;
	}

	/**
	 * @param string $form
	 */
	public function setFrom($from) {
		$this->from = $from;
	}

	/**
	 * @param string $to
	 */
	public function setTo($to) {
		$this->to = $to;
	}

	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @return string
	 */
	public function getFrom() {
		return $this->from;
	}

	/**
	 * @return array
	 */
	public function getParts() {
		return ArrayHelper::clean(explode("/", $this->from));
	}

	/**
	 * @return string
	 */
	public function getToClass() {
		$parts = explode(self::TO_SEPERATOR, $this->to);
		return $parts[0];
	}

	/**
	 * @return string
	 */
	public function getToMethod() {
		$parts = explode(self::TO_SEPERATOR, $this->to);
		return $parts[1];
	}
}
