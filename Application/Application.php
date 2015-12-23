<?php
namespace Fewlines\Core\Application;

class Application extends Renderer
{
    /**
     * @var integer
     */
    const ENVIRONMENT_WEB = 0;

    /**
     * @var integer
     */
    const ENVIRONMENT_CMD = 1;

    /**
     * Determinates if the application
     * was shut down
     *
     * @var boolean
     */
    private static $shutdown = false;

    /**
     * Defines the environment the application
     * is running
     *
     * @var integer
     */
    public static $environment;

    /**
     * Tells wether the application was already
     * runned or not
     *
     * @var boolean
     */
    private $running = false;

    /**
     * The autoloader instance of the
     * Composer autoloader
     *
     * @var Composer\Autoload\ClassLoader
     */
    private $autoloader;

    /**
     * The root path from where
     * the application was
     * created
     *
     * @var string
     */
    private $root;

    /**
     * @param  \Composer\Autoload\ClassLoader $autoloader
     * @param string $root
     */
    public function __construct($root, \Composer\Autoload\ClassLoader $autoloader) {
        // Root path
        $this->root = $root;

        // Set autoloader
        $this->autoloader = $autoloader;
    }

    /**
     * Bootstrap the application and
     * call the other bootstrap classes
     * from the projects (if they exist)
     *
     * @return self
     */
    public function bootstrap($environment = Application::ENVIRONMENT_WEB) {
        static::$environment = $environment;

         // Setup application
        $this->setup();

        try {
            Buffer::start();

            // Call own bootstrap
            (new Bootstrap($this))->autoCall();

            // Call bootstrap of active project
            $project = ProjectManager::getActiveProject();

            if ($project) {
                // Save it in case for further use
                $bootstrap = $project->bootstrap($this);
            }
        }
        catch(\Exception $err) {
            switch (static::$environment) {
                case Application::ENVIRONMENT_WEB:
                    self::renderException(array($err));
                    break;

                case Application::ENVIRONMENT_CMD:
                    throw $err;
                    break;
            }
        }

        return $this;
    }

    /**
     * Runs the application
     *
     * @return boolean
     */
    public function run() {
        $this->running = true;

        try {
            switch (static::$environment) {
                case Application::ENVIRONMENT_WEB:
                    self::renderTemplate(DEFAULT_LAYOUT);
                    break;

                case Application::ENVIRONMENT_CMD:
                    self::renderCommand();
                    break;
            }
        }
        catch(\Exception $err) {
            switch (static::$environment) {
                case Application::ENVIRONMENT_WEB:
                    self::renderException(array($err));
                    break;

                case Application::ENVIRONMENT_CMD:
                    throw $err;
                    break;
            }
        }
    }

    /**
     * Renders a error manual with a new template
     *
     * @param  \ErrorException $err
     */
    public static function renderShutdownError($err) {
        if (self::$shutdown == true) {
            exit;
        }

        // Set shutdown flag
        self::$shutdown = true;

        // Render layout
        if (static::$environment == Application::ENVIRONMENT_WEB) {
            self::renderException(array($err));
        }
    }

    /**
     * Returns the state
     * of the application
     *
     * @return boolean
     */
    public function isRunning() {
        return $this->running;
    }

    /**
     * @return Composer\Autoload\ClassLoader
     */
    public function getAutoloader() {
        return $this->autoloader;
    }

    /**
     * Sets up all necessary configs
     */
    private function setup() {
        define('ROOT_DIR', $this->root);

        /**
         * Default options for the
         * views, layout, routes, ...
         */

        if (static::$environment == Application::ENVIRONMENT_WEB) {
            define('ERROR_HANDLER',        true);
            define('DEFAULT_PROJECT_ROOT', realpath(ROOT_DIR . './../vendor/fewlines/project'));
        }
        else if (static::$environment == Application::ENVIRONMENT_CMD) {
            define('ERROR_HANDLER',        false);
            define('DEFAULT_PROJECT_ROOT', realpath(ROOT_DIR . '/vendor/fewlines/project'));
        }

        define('LAYOUT_FILETYPE',      'phtml');
        define('VIEW_FILETYPE',        'phtml');
        define('DEFAULT_ERROR_VIEW',   'error');
        define('DEFAULT_LAYOUT',       'default');
        define('EXCEPTION_LAYOUT',     'exception');

        define('BOOTSTRAP_RL_NS',      '\Application\Bootstrap');
        define('CONTROLLER_V_RL_NS',   '\Controller\View');
        define('VIEW_HELPER_RL_NS',    '\Helper\View');

        define('HTTP_METHODS_PATTERN', '/get|post|put|delete|any/');
        define('URL_LAYOUT_ROUTE',     '/view:index/action:index');

        define('DEVELOPER_DEBUG',      true);
        define('DEFAULT_LOCALE',       'en');
        define('DR_SP',                '/');

        define('DEFAULT_PROJECT_ID',   'fewlines');
        define('DEFAULT_PROJECT_NAME', 'Fewlines framework');
        define('DEFAULT_PROJECT_NS',   'Fewlines\Core');

        /**
         * Paths relative to the
         * user projects
         */

        if (static::$environment == Application::ENVIRONMENT_WEB) {
            define('PROJECT_ROOT_PATH', ROOT_DIR . DR_SP . 'projects');
        }
        else if (static::$environment == Application::ENVIRONMENT_CMD) {
            define('PROJECT_ROOT_PATH', ROOT_DIR . DR_SP . 'public' . DR_SP . 'projects');
        }

        define('PROJECT_CONFIG_FILE', PROJECT_ROOT_PATH . DR_SP . 'projects.xml');
        define('PROJECT_CLASS_PATH', 'classes');
        define('PROJECT_RESOURCE_PATH', 'resources');
        define('PROJECT_TEMPLATE_PATH', 'templates');
        define('PROJECT_TRANSLATION_PATH', 'translations');
        define('PROJECT_CONFIG_PATH', 'config');

        define('PROJECT_VIEW_PATH', PROJECT_TEMPLATE_PATH . DR_SP . 'views');
        define('PROJECT_LAYOUT_PATH', PROJECT_TEMPLATE_PATH . DR_SP . 'layouts');
    }
}