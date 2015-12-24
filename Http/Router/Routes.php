<?php
namespace Fewlines\Core\Http\Router;

use Fewlines\Core\Xml\Tree\Element as XmlElement;

class Routes
{
	/**
	 * @var array
	 */
	protected $routes = array();

	/**
	 * Adding the route to the collection (recursive).
	 * All subroutes will be flattenend and also added
	 * to this collection, to save the parent element in
	 * which they are nested in it will given to the
	 * route
	 *
	 * @param XmlElement $element
	 * @param Routes\Route $parent
	 */
	public function addRouteByElement(XmlElement $element, Routes\Route $parent = null) {
		$name = strtolower($element->getName());

		// Add route to parent
		if (true == preg_match(HTTP_METHODS_PATTERN, $name)) {
			$route = static::createRoute($name, $element->getAttribute('from'), $element->getAttribute('to'));

			// Add optional attributes
			if ($element->hasAttribute('id')) {
				$route->setId($element->getAttribute('id'));
			}

			$this->add($route);

			if ( ! is_null($parent)) {
				$route->setParent($parent);
			}

			// Add children
			if ($element->hasChildren()) {
				foreach ($element->getChildren() as $child) {
					$this->addRouteByElement($child, $route);
				}
			}
		}
	}

	/**
	 * Add a route manually
	 *
	 * @param string $type
	 * @param string $from
	 * @param string $to
	 * @return Routes\Route
	 */
	public static function createRoute($type, $from, $to) {
		return new Routes\Route($type, $from, $to);
	}

	/**
	 * Checks if a route exists
	 *
	 * @param Routes\Route $route
	 * @return integer
	 */
	public function routeExists(Routes\Route $route) {
		for ($i = 0, $len = count($this->routes); $i < $len; $i++) {
			if ($this->routes[$i]->equals($route)) {
				return $i;
			}
		}

		return -1;
	}

	/**
	 * Adds a route
	 *
	 * @param Routes\Route $route
	 * @return self
	 */
	public function add(Routes\Route $route) {
		$index = $this->routeExists($route);

		// Overwrite the old route
		if ($index > -1) {
			$this->routes[$index] = $route;
		}
		else {
			$this->routes[] = $route;
		}

		return $this;
	}

	/**
	 * Returns all routes defined
	 * by the user in the config
	 *
	 * @return array
	 */
    public function getRoutes() {
    	return $this->routes;
    }

    /**
     * Reset the saved routes
     */
    public function resetRoutes() {
    	$this->routes = [];
    }
}