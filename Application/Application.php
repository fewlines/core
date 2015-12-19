<?php
namespace Fewlines\Core\Application;

class Application extends Renderer
{
    /**
     * Determinates if the application
     * was shut down
     *
     * @var boolean
     */
    private static $shutdown = false;

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

        // Setup application
        $this->setup();
    }

    /**
     * Bootstrap the application and
     * call the other bootstrap classes
     * from the projects (if they exist)
     *
     * @return self
     */
    public function bootstrap() {
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
           self::renderException(array($err));
        }

        return $this;
    }

    /**
     * Runs the application
     *
     * @return boolean
     */
    public function run() {
        try {
            $this->running = true;
            self::renderTemplate(DEFAULT_LAYOUT);
        }
        catch(\Exception $err) {
            self::renderException(array($err));
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
        self::renderException(array($err));
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

        define('LAYOUT_FILETYPE',      'phtml');
        define('VIEW_FILETYPE',        'phtml');
        define('DEFAULT_ERROR_VIEW',   'error');
        define('DEFAULT_LAYOUT',       'default');
        define('EXCEPTION_LAYOUT',     'exception');

        define('DEFAULT_PROJECT_ROOT', realpath(ROOT_DIR . './../vendor/fewlines/project'));
        define('DEFAULT_PROJECT_ID',   'fewlines');
        define('DEFAULT_PROJECT_NAME', 'Fewlines framework');
        define('DEFAULT_PROJECT_NS',   'Fewlines\Core');

        define('BOOTSTRAP_RL_NS',      '\Application\Bootstrap');
        define('CONTROLLER_V_RL_NS',   '\Controller\View');
        define('VIEW_HELPER_RL_NS',    '\Helper\View');

        define('HTTP_METHODS_PATTERN', '/get|post|put|delete|any/');
        define('URL_LAYOUT_ROUTE',     '/view:index/action:index');

        define('DEVELOPER_DEBUG',      true);
        define('ERROR_HANDLER',        true);
        define('DEFAULT_LOCALE',       'en');
        define('DR_SP',                '/');

        /**
         * Paths relative to the
         * user projects
         */

        define('PROJECT_CONFIG_FILE', ROOT_DIR . DR_SP . 'projects' . DR_SP . 'projects.xml');

        define('PROJECT_ROOT_PATH', ROOT_DIR . DR_SP . 'projects');
        define('PROJECT_CLASS_PATH', 'classes');
        define('PROJECT_RESOURCE_PATH', 'resources');
        define('PROJECT_TEMPLATE_PATH', 'templates');
        define('PROJECT_TRANSLATION_PATH', 'translations');
        define('PROJECT_CONFIG_PATH', 'config');

        define('PROJECT_VIEW_PATH', PROJECT_TEMPLATE_PATH . DR_SP . 'views');
        define('PROJECT_LAYOUT_PATH', PROJECT_TEMPLATE_PATH . DR_SP . 'layouts');
    }
}