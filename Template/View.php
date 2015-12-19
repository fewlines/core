<?php
namespace Fewlines\Core\Template;

use Fewlines\Core\Template\Template;
use Fewlines\Core\Helper\PathHelper;
use Fewlines\Core\Helper\ArrayHelper;
use Fewlines\Core\Helper\NamespaceHelper;
use Fewlines\Core\Application\Registry;
use Fewlines\Core\Application\ProjectManager;
use Fewlines\Core\Http\Header as HttpHeader;
use Fewlines\Core\Http\Router;

class View
{
    /**
     * @var string
     */
    const ACTION_POSTFIX = 'Action';

    /**
     * All assigned variables to use
     * in the view
     *
     * @var array
     */
    private $vars = array();

    /**
     * The name of the view (could be overwritten
     * by anything e.g. a 404 error)
     *
     * @var string
     */
    private $name;

    /**
     * The real viewname as in the url
     *
     * @var string
     */
    private $realName;

    /**
     * Current action
     *
     * @var string
     */
    private $action;

    /**
     * The filename of the view templates
     *
     * @var string
     */
    private $path;

    /**
     * Controller class of the current view
     *
     * @var string
     */
    private $controllerClass;

    /**
     * Enables when a route is given instead
     * of the default method
     *
     * @var \Fewlines\Core\Http\Router\Routes\Route
     */
    private $activeRoute;

    /**
     * Controller instance of the current view
     *
     * @var \Fewlines\Core\Controller\View
     */
    public $viewController;

    /**
     * Controller of the route
     *
     * @var \Fewlines\Core\Controller\View
     */
    private $routeController;

    /**
     * Init the view with some options
     * called from the layout
     *
     * @param array $config
     */
    public function __construct($config) {
        if (is_string($config) && preg_match('/\//', $config)) {
            $this->setPath($config);
        }
        else if (is_array($config) && array_key_exists('view', $config) && array_key_exists('action', $config)) {
            // Set components by default layout
            $this->setAction($config['action']);
            $this->setName($config['view']);
            $this->setPath($config['view']);
            $this->setViewControllerClass();
        }
        else if (true == ($config instanceof \Fewlines\Core\Http\Router\Routes\Route)) {
            $this->activeRoute = $config;
            $this->setRouteControllerClass($this->activeRoute->getToClass());
        }
    }

    /**
     * @param {string} $name
     * @param {mixed} $content
     * @return self
     */
    public function assign($name, $content) {
        if (is_string($name)) {
            $this->vars[$name] = $content;
        }
        else {
            throw new View\Exception\InvalidOffsetTypeAssignmentException(
                'The name of the variable you want to assign must be the
                type "string"'
            );
        }

        return $this;
    }

    /**
     * @param {array} $values
     * @return self
     */
    public function assignMultiple($values) {
        if (ArrayHelper::isAssociative($values)) {
            foreach ($values as $name => $content) {
                $this->assign($name, $content);
            }
        }
        else {
            throw new View\Exception\MultipleValuesNotAssociativeException(
                'The array given is not associative. You need to pass a
                name for each variable content you want to assign to the
                view'
            );
        }

        return $this;
    }

    /**
     * @return {mixed}
     */
    public function getVar($name) {
        if (array_key_exists($name, $this->vars)) {
            return $this->vars[$name];
        }
        else {
            throw new View\Exception\VarNotFoundException(
                'The variable "' . $name . '"" was not found
                in the view'
            );
        }
    }

    /**
     * Checks if the variable exists
     *
     * @param  string  $name
     * @return boolean
     */
    public function hasVar($name) {
        return array_key_exists($name, $this->vars);
    }

    /**
     * Returns the name of the rendered view
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Gets the name of the first view
     * (no overwrites)
     *
     * @return string
     */
    public function getRealName() {
        return $this->realName;
    }

    /**
     * Returns the view action
     *
     * @return string
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * Sets the view action
     *
     * @param string $action
     */
    public function setAction($action) {
        $this->action = $action;
    }

    /**
     * Returns the path to the current view
     *
     * @return string
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * Sets the view path
     *
     * @param string $view
     */
    public function setPath($view) {
        $layout = Template::getInstance()->getLayout()->getName();
        $viewFile = PathHelper::getRealViewPath($view, $this->getAction(), $layout);

        $this->path = $viewFile;
    }

    /**
     * Sets the name of the view
     *
     * @param string $name
     */
    private function setName($name) {
        if (true == is_null($this->realName)) {
            $this->realName = $name;
        }

        $this->name = $name;
    }

    /**
     * @return boolean
     */
    public function isRouteActive() {
        return false == is_null($this->activeRoute);
    }

    /**
     * Sets the controller by the active namespace
     */
    private function setViewControllerClass() {
        $project = ProjectManager::getActiveProject();
        $class = '\\';

        if ($project && $project->hasNsName() && Template::getInstance()->getLayout()->getName() != EXCEPTION_LAYOUT) {
            $class.= $project->getNsName();
        }
        else {
            $class.= ProjectManager::getDefaultProject()->getNsName();
        }

        $class.= CONTROLLER_V_RL_NS . '\\' . ucfirst($this->name);

        if ( ! class_exists($class)) {
            HttpHeader::set(404, false);
            throw new View\Exception\ControllerClassNotFoundException(
                'The class "' . $class . '", for the
                controller was not found.');
        }

        $this->controllerClass = $class;
    }

    /**
     * Set the class of the controller
     * which to call
     *
     * @param string $class
     */
    public function setControllerClass($class) {
        if (true == class_exists($class)) {
            $this->controllerClass = $class;
        }
        else {
            HttpHeader::set(404, false);
            throw new View\Exception\ControllerClassNotFoundException(
                'The class "' . $this->controllerClass . '" for the
                controller was not found.'
            );
        }
    }

    /**
     * Set the controller
     * for the route
     *
     * @param string $class
     */
    public function setRouteControllerClass($class) {
        $method = strtolower(Router::getInstance()->getRequest()->getHttpMethod());
        $routeMethod = strtolower($this->activeRoute->getType());

        if ($routeMethod == 'any' || $method == $routeMethod) {
            if (true == class_exists($class)) {
                $this->controllerClass = $class;
            }
            else {
                HttpHeader::set(404, false);
                throw new View\Exception\ControllerClassNotFoundException(
                    'The class "' . $this->controllerClass . '" for the
                    controller was not found.'
                );
            }
        }
        else {
            HttpHeader::set(404, false);
            throw new View\Exception\InvalidHttpMethodException(
                'Invalid HTTP method found'
            );
        }
    }

    /**
     * Returns the instantiated view
     * controller if exists
     *
     * @return \Fewlines\Core\Controller\View
     */
    public function getViewController() {
        return $this->viewController;
    }

    /**
     * Returns the instantiated route
     * controller if exists
     *
     * @return \Fewlines\Core\Controller\View
     */
    public function getRouteController() {
        return $this->routeController;
    }

    /**
     * Inits the active controller
     * (view or route)
     *
     * @return null|*
     */
    public function initController() {
        if (false == is_null($this->activeRoute)) {
            return $this->initRouteController();
        }

        return $this->initViewController();
    }

    /**
     * Init the controller of the current view
     * (if exists)
     *
     * @return null|*
     */
    public function initViewController() {
        $this->viewController = new $this->controllerClass;

        if (true == ($this->viewController instanceof \Fewlines\Core\Controller\View)) {
            $this->viewController->init(Template::getInstance());
            return $this->callViewAction($this->getAction() . self::ACTION_POSTFIX);
        }
        else {
            throw new View\Exception\ControllerInitialisationGoneWrongException(
                'The view controller couldn\'t be initialized.
                Must inherit from \Fewlines\Core\Controller\View'
            );
        }

        return null;
    }

    /**
     * Init the controller of a route
     *
     * @return null|*
     */
    public function initRouteController() {
        $this->routeController = new $this->controllerClass;

        if (true == ($this->routeController instanceof \Fewlines\Core\Controller\View)) {
            $this->routeController->init(Template::getInstance());
            return $this->callRouteMethod($this->activeRoute->getToMethod(), $this->activeRoute->getVarsRecursive());
        }
        else {
            throw new View\Exception\ControllerInitialisationGoneWrongException(
                'The route controller could not be initialized.
                Must be instance of \Fewlines\Controller\View'
            );
        }

        return null;
    }

    /**
     * Calls the action of the
     * current controller
     *
     * @param string $method
     * @return *
     */
    private function callViewAction($method) {
        if (false == method_exists($this->viewController, $method)) {
            HttpHeader::set(404, false);
            throw new View\Exception\ActionNotFoundException('Could not found the action (method) "' . $method . '"
                - Check the controller "' . $this->controllerClass . '"
                for it');
        }

        return $this->viewController->{$method}();
    }

    /**
     * Calls the method
     * of the current route
     * controller
     *
     * @param string $method
     * @param array $arguments
     * @return *
     */
    private function callRouteMethod($method, $arguments) {
        if (false == method_exists($this->routeController, $method)) {
            HttpHeader::set(404, false);
            throw new View\Exception\MethodNotFoundException(
                'Could not found the method "' . $method . '"
                - Check the controller "' . $this->controllerClass . '"
                for it'
            );
        }

        return call_user_func_array(array($this->routeController, $method), $arguments);
    }

    /**
     * Throws a 404 error and a exception
     * which defines this error
     */
    public function viewNotFound() {
        HttpHeader::set(404, false);

        throw new View\Exception\ViewNotFoundException(
            'The view "' . $this->getPath() . '" was not found.'
        );
    }
}
