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
	const VAR_MASK = '/\{(.*?)\}/';

	/**
	 * @var string
	 */
	private $id = '';

	/**
	 * @var string
	 */
	private $type = '';

	/**
	 * @var string
	 */
	private $from = '';

	/**
	 * @var string
	 */
	private $fromFull = '';

	/**
	 * @var string
	 */
	private $to = '';

	/**
	 * @var array
	 */
	private $vars = array();

	/**
	 * @var Route
	 */
	private $parent;

	/**
	 * @param string $type
	 * @param string $from
	 * @param string $to
	 */
	public function __construct($type, $from, $to) {
		$this->type = $type;
		$this->from = '/' . trim($from, '/') . '/';
		$this->to = $to;

		// Init vars
		$this->initVars();
	}

	/**
	 * Init the variables and removes them from
	 * the routing url
	 */
	private function initVars() {
		$this->from = UrlHelper::cleanUrl($this->from);
		$this->fullFrom = $this->from;

		preg_match_all(self::VAR_MASK, $this->fullFrom, $matches);

		// Add vars
		foreach ($matches[1] as $name) {
			$this->vars[] = new Variable($name);
		}
	}

	/**
	 * Sets the parent route and automatically
	 * updates the url so it will be combined
	 * recusively with the ones in the parent
	 * routes
	 *
	 * @param Route $route
	 * @return self
	 */
	public function setParent(Route &$route) {
		$this->parent = $route;

		$current = $this->parent;
		$fromTmp = array();

		while( ! is_null($current)) {
			$fromTmp[] = $current->getFrom();
			$current = $current->getParent();
		}

		$fromTmp = array_reverse($fromTmp);
		$fromTmp = implode('/', $fromTmp);
		$fromTmp = UrlHelper::cleanUrl($fromTmp);

		$this->fullFrom = UrlHelper::cleanUrl($fromTmp . $this->fullFrom);

		return $this;
	}

	/**
	 * @return Route
	 */
	public function getParent() {
		return $this->parent;
	}

	/**
	 * Checks if the route has variables set
	 *
	 * @return boolean
	 */
	public function hasVars() {
		return ! empty($this->getVarsRecursive());
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
	 * Gets all vars also from
	 * it's parent
	 *
	 * @return array
	 */
	public function getVarsRecursive() {
		$vars = array();

		$vars[] = $this->getVars();
		$current = $this->parent;

		while( ! is_null($current)) {
			$vars[] = array_reverse($current->getVars());
			$current = $current->getParent();
		}

		$vars = ArrayHelper::flatten($vars);
		$vars = ArrayHelper::clean($vars);
		$vars = array_reverse($vars);

		return $vars;
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
	 * @return string
	 */
	public function getFullFrom() {
		return $this->fullFrom;
	}

	/**
	 * @return array
	 */
	public function getParts() {
		return ArrayHelper::clean(explode("/", $this->fullFrom));
	}

	/**
	 * @param string $id
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function getId() {
		return $this->id;
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
