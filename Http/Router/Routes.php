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
			$route = $this->createRoute($name, $element->getAttribute('from'), $element->getAttribute('to'));

			// Add optional attributes
			if ($element->hasAttribute('id')) {
				$route->setId($element->getAttribute('id'));
			}

			$this->routes[] = $route;

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
	public function createRoute($type, $from, $to) {
		return new Routes\Route($type, $from, $to);
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
