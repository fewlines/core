<?php

namespace Fewlines\Core\Application\ProjectManager;

use Fewlines\Core\Helper\PathHelper;
use Fewlines\Core\Application\Config;
use Fewlines\Core\Http\Router;

class Project
{
	/**
	 * @var string
	 */
	private $id;

    /**
     * @var string
     */
    private $root;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $description;

	/**
	 * @var string
	 */
	private $nsName;

    /**
     * @var string
     */
    private $nsPath;

    /**
     * @var boolean
     */
    private $active = false;

    /**
     * @var string
     */
    private $configPath;

    /**
     * @var string
     */
    private $viewPath;

    /**
     * @var string
     */
    private $layoutPath;

    /**
     * @var string
     */
    private $translationPath;

    /**
     * @var string
     */
    private $resourcePath;

	/**
	 * Holds the bootstrap instance of
	 * from the given namespace - if exists
	 *
	 * @var {$ns}\Application\Bootstrap
	 */
	private $bootstrap;

	/**
	 * @param string $id
	 * @param string $name
	 * @param string $description
	 * @param string $nsName
     * @param string $root
	 */
	public function __construct($id, $name, $description, $nsName, $root = '') {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->nsName = $nsName;

        // Set paths
        $this->root = empty($root) ? PathHelper::createPath(array(PROJECT_ROOT_PATH, $id)) : $root;
        $this->nsPath = PathHelper::createPath(array($this->root, PROJECT_CLASS_PATH));
        $this->configPath = PathHelper::createPath(array($this->root, PROJECT_CONFIG_PATH));
        $this->viewPath = PathHelper::createPath(array($this->root, PROJECT_VIEW_PATH));
        $this->layoutPath = PathHelper::createPath(array($this->root, PROJECT_LAYOUT_PATH));
        $this->translationPath = PathHelper::createPath(array($this->root, PROJECT_TRANSLATION_PATH));
        $this->resourcePath = PathHelper::createPath(array($this->root, PROJECT_RESOURCE_PATH));
	}

    /**
     * Calls the bootstrap of the project
     * with the given namespace
     *
     * @param  \Fewlines\Core\Application\Application $app
     * @return {lib/php/$ns}\Application\Bootstrap
     */
    public function bootstrap(\Fewlines\Core\Application\Application $app) {
        if ($this->hasNsName()) {
            // Get bootstrap class
            $class = $this->getNsName() . BOOTSTRAP_RL_NS;

            // Create and call bootstrap
            if (class_exists($class)) {
                $this->bootstrap = new $class($app);
                $this->bootstrap->autoCall();
            }

            Router::getInstance()->update();
            Config::getInstance()->applyShortcuts();
        }

        return $this->bootstrap;
    }

    /**
     * Gets the value of id.
     *
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Gets the value of name.
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Gets the value of description.
     *
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @return boolean
     */
    public function isActive() {
    	return $this->active;
    }

    /**
     * @param  boolean $isActive
     * @return boolean
     */
    public function setActive($isActive) {
    	return $this->active = $isActive;
    }

    /**
     * Gets the value of nsName.
     *
     * @return string
     */
    public function getNsName() {
        return $this->nsName;
    }

    /**
     * @return string
     */
    public function getNsPath() {
        return $this->nsPath;
    }

    /**
     * Tells if this project has
     * a namespace
     *
     * @return boolean
     */
    public function hasNsName() {
        return ! empty($this->nsName);
    }

    /**
     * @return string
     */
    public function getRoot() {
        return $this->root;
    }

    /**
     * @return string
     */
    public function getConfigPath() {
        return $this->configPath;
    }

    /**
     * @return string
     */
    public function getViewPath() {
        return $this->viewPath;
    }

    /**
     * @return string
     */
    public function getLayoutPath() {
        return $this->layoutPath;
    }

    /**
     * @return string
     */
    public function getTranslationPath() {
        return $this->translationPath;
    }

    /**
     * @return string
     */
    public function getResourcePath() {
        return $this->resourcePath;
    }

    /**
     * Returns the bootstrap (if it was called)
     *
     * @return {lib/php/$ns}\Application\Bootstrap
     */
    public function getBootstrap() {
    	return $this->bootstrap;
    }
}