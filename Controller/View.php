<?php
namespace Fewlines\Core\Controller;

use Fewlines\Core\Helper\UrlHelper;
use Fewlines\Core\Helper\PathHelper;
use Fewlines\Core\Application\Config;
use Fewlines\Core\Http\Request as HttpRequest;
use Fewlines\Core\Application\ProjectManager;
use Fewlines\Core\Application\Registry;
use Fewlines\Core\Locale\Locale;
use Fewlines\Core\Http\Router;

class View implements IView
{
	/**
	 * Holds the whole
	 * template instance
	 *
	 * @var \Fewlines\Core\Template\Template
	 */
	protected $template;

	/**
	 * @var \Fewlines\Core\Template\View
	 */
	protected $view;

	/**
	 * @var \Fewlines\Core\Template\Layout
	 */
	protected $layout;

	/**
	 * @var \Fewlines\Core\Http\Request
	 */
	protected $httpRequest;

	/**
	 * @var \Fewlines\Core\Http\Response
	 */
	protected $httpResponse;

	/**
	 * Inits with the template
	 *
	 * @param  \Fewlines\Core\Template\Template $template
	 */
	public function init(\Fewlines\Core\Template\Template &$template) {
		$this->template = $template;
		$this->httpRequest = Router::getInstance()->getRequest();
		$this->httpResponse = $this->httpRequest->getResponse();

		$this->view = $this->template->getView();
		$this->layout = $this->template->getLayout();

		if (method_exists($this, 'postInit')) {
			call_user_func(array($this, 'postInit'));
		}
	}

	/**
	 * Redirects
	 *
	 * @param string $url
	 */
	public function redirect($url) {
		HttpHeader::redirect($url);
	}

	/**
	 * Returns the base url
	 *
	 * @param  string|array $parts
	 * @return string
	 */
	public function getBaseUrl($parts = "") {
		return UrlHelper::getBaseUrl($parts);
	}

    /**
     * @param  string $append
     * @return sting
     */
    public function getResourcePath($append) {
        $project = $this->getProject() ? $this->getProject() : ProjectManager::getDefaultProject();
        return PathHelper::createPath(array($project->getResourcePath())) . $append;
    }

	/**
	 * @param  string $view
	 * @return string
	 */
	public function render($view) {
		return $this->template->renderView($view);
	}

	/**
     * Returns the active project
     *
     * @return \Fewlines\Core\Application\ProjectManager\Project
     */
    public function getProject() {
        return ProjectManager::getActiveProject();
    }

    /**
     * Checks if the view contains this variable
     *
     * @param  string $name
     * @return boolean
     */
    public function has($name) {
    	return $this->view->hasVar($name);
    }

    /**
     * Translates a path to a translation
     * string
     *
     * @param  string $path
     * @return string
     */
    public function translate($path) {
        return Locale::get($path);
    }

    /**
     * Returns the current locale
     * key from the locale component
     *
     * @return string
     */
    public function getLocaleKey() {
    	return Locale::getKey();
    }

    /**
     * Gets a config element by a given
     * path
     *
     * @param  string $path
     * @return \Fewlines\Core\Xml\Element|false
     */
    public function getConfig($path) {
        return Config::getInstance()->getElementByPath($path);
    }

    /**
     * Gets config elements from a element
     *
     * @param  string $path
     * @return array
     */
    public function getConfigs($path) {
        return Config::getInstance()->getElementsByPath($path);
    }

    /**
     * @return \Fewlines\Core\Application\Environment
     */
    public function getEnvironment() {
        return Registry::get('environment');
    }

    /**
     * Gets a value from the registry
     *
     * @param  string $name
     * @return mixed
     */
    public function getFromRegistry($name) {
        return Registry::get($name);
    }
}
