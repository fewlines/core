<?php

namespace Fewlines\Core\Application;

use Fewlines\Core\Helper\PathHelper;
use Fewlines\Core\Xml\Xml;
use Fewlines\Core\Xml\Tree\Element as XmlElement;
use Symfony\Component\Console\Output\OutputInterface;

class ProjectManager
{
	/**
	 * Holds all created projects
	 *
	 * @var array
	 */
	private static $projects = array();

	/**
	 * The default project (fewlines)
	 *
	 * @var ProjectManager\Project
	 */
	private static $default;

	/**
	 * Adds a new project to the list
	 *
	 * @param string $name
	 * @param string $description
	 * @param string $id
	 * @param string $nsName
	 * @return ProjectManager\Project
	 */
	public static function addProject($id, $name, $description, $nsName) {
		return self::$projects[] = new ProjectManager\Project($id, $name, $description, $nsName);
	}

	/**
	 * Set the default project
	 *
	 * @param string $id
	 * @param string $name
	 * @param string $nsName
	 * @param string $root
	 */
	public static function setDefaultProject($id, $name, $nsName, $root) {
		self::$default = new ProjectManager\Project($id, $name, "", $nsName, $root);
	}

	/**
	 * @param string $id
	 * @return boolean
	 */
	public function deactivateProject($id) {
		if (file_exists(PROJECT_CONFIG_FILE)) {
			$xml = new Xml(PROJECT_CONFIG_FILE);
			$projects = $xml->getElementByPath('projects');
			$found = false;

			foreach ($projects->getChildren() as $project) {
				if ($project->getName() == $id) {
					$found = true;
					$project->addAttribute('active', 'false');
				}
			}

			// Disable all other projects if the project was found
			if ( ! $found) {
				throw new ProjectManager\Exception\ProjectNotFoundException(
					sprintf('Project with the id "%s" was not found', $id)
				);

				return false;
			}

			$xml->save();
		}
		else {
			throw new ProjectManager\Exception\NoProjectsFoundException(
				'No registered projects found'
			);

			return false;
		}

		return true;
	}

	/**
	 * @param string $id
	 * @return boolean
	 */
	public static function activateProject($id) {
		if (file_exists(PROJECT_CONFIG_FILE)) {
			$xml = new Xml(PROJECT_CONFIG_FILE);
			$projects = $xml->getElementByPath('projects');
			$disableProjects = array();
			$found = false;

			foreach ($projects->getChildren() as $project) {
				if ($project->getName() == $id) {
					$found = true;
					$project->addAttribute('active', 'true');
				}
				else {
					$disableProjects[] = $project;
				}
			}

			// Disable all other projects if the project was found
			if ($found) {
				foreach ($disableProjects as $project) {
					$project->addAttribute('active', 'false');
				}
			}
			else {
				throw new ProjectManager\Exception\ProjectNotFoundException(
					sprintf('Project with the id "%s" was not found', $id)
				);

				return false;
			}

			$xml->save();
		}
		else {
			throw new ProjectManager\Exception\NoProjectsFoundException(
				'No registered projects found'
			);

			return false;
		}

		return true;
	}

	/**
	 * Updates a project configurations
	 *
	 * @param string $id
	 * @param string $property
	 * @param string $value
	 * @return boolean
	 */
	public static function updateProject($id, $property, $value) {
		if (file_exists(PROJECT_CONFIG_FILE)) {
			$xml = new Xml(PROJECT_CONFIG_FILE);
			$project = $xml->getElementByPath('projects/' . $id);

			if ($project) {
				$element = $project->getChildByName($property);

				if ($element) {
					$element->setContent($value);
				}
				else {
					throw new ProjectManager\Exception\PropertyNotFoundException(
						sprintf('The property "%s" was not found in the config of this project', $property)
					);

					return false;
				}
			}
			else {
				throw new ProjectManager\Exception\ProjectNotFoundException(
					sprintf('Project with the id "%s" was not found', $id)
				);

				return false;
			}

			$xml->save();
		}
		else {
			throw new ProjectManager\Exception\NoProjectsFoundException(
				'No registered projects found'
			);

			return false;
		}

		return true;
	}

	/**
	 * Creates a new project by registering it
	 * in the config file provided. It if doesn't exist
	 * it will be created
	 *
	 * @param string $id
	 * @param string $name
	 * @param string $description
	 * @param string $namespace
	 * @param boolean $active
	 * @param OutputInterface &$output
	 */
	public static function createProject($id, $name, $description, $namespace, $active, OutputInterface &$output = null) {
		/**
		 * Create xml config entry
		 */

		if ( ! is_dir(PROJECT_ROOT_PATH)) {
			mkdir(PROJECT_ROOT_PATH);
		}

		if ( ! file_exists(PROJECT_CONFIG_FILE)) {
			fclose(fopen(PROJECT_CONFIG_FILE, "w"));

			if ($output) {
				$output->writeln(sprintf('<comment>Project config file not found. Created %s</comment>', PROJECT_CONFIG_FILE));
			}
		}

		$xml = new Xml(PROJECT_CONFIG_FILE);

		if ($xml->isValid() && $xml->getTreeElement()->getChildByName($id, true)) {
			throw new \Exception("A project with the id " . $id . " already exists!");
		}

		// Deactivate all
		if ($xml->isValid() && $xml->hasRoot() && $active) {
			if ($output) {
				$output->writeln("<comment>Deactivating other projects...</comment>");
			}

			foreach ($xml->getTreeElement()->getChildren() as $child) {
				$child->addAttribute('active', 'false');
			}
		}

		$projectElement = new XmlElement($id, array('active' => $active ? 'true' : 'false'), '', array(
			new XmlElement('name', array(), $name),
			new XmlElement('description', array(), $description),
			new XmlElement('namespace', array(), $namespace)
		));

		if ($xml->hasRoot()) {
			$xml->addChild($projectElement);
		}
		else {
			$root = new XmlElement('projects', array(), '', array($projectElement));
			$xml->addChild($root);
		}

		$xml->save();

		if ($output) {
			$output->writeln("<info>Registered project in the config file</info>");
		}

		/**
		 * Create base folder structure
		 */

		$folders = array(
			PROJECT_CLASS_PATH,
			PROJECT_RESOURCE_PATH,
			PROJECT_TEMPLATE_PATH,
			PROJECT_TRANSLATION_PATH,
			PROJECT_CONFIG_PATH
		);

		$projectRoot = PathHelper::createPath(array(PROJECT_ROOT_PATH, $id));

		if ( ! is_dir($projectRoot)) {
			mkdir($projectRoot);

			if ($output) {
				$output->writeln(sprintf('<comment>Created %s</comment>', $projectRoot));
			}
		}

		foreach ($folders as $folder) {
			$projectComponentFolder = PathHelper::createPath(array(PROJECT_ROOT_PATH, $id, $folder));

			if ( ! is_dir($projectComponentFolder)) {
				mkdir($projectComponentFolder);

				if ($output) {
					$output->writeln(sprintf('<comment>Created %s</comment>', $projectComponentFolder));
				}
			}
		}

		$layoutPath = PathHelper::createPath(array(PROJECT_ROOT_PATH, $id, PROJECT_TEMPLATE_PATH, 'layouts'));
		$viewPath = PathHelper::createPath(array(PROJECT_ROOT_PATH, $id, PROJECT_TEMPLATE_PATH, 'views'));

		if ( ! is_dir($layoutPath)) {
			mkdir($layoutPath);

			if ($output) {
				$output->writeln(sprintf('<comment>Created %s</comment>', $layoutPath));
			}
		}

		if ( ! is_dir($viewPath)) {
			mkdir($viewPath);

			if ($output) {
				$output->writeln(sprintf('<comment>Created %s</comment>', $viewPath));
			}
		}

		/**
		 * Create base config files:
		 *
		 * - routes.xml
		 * - environment.xml
		 */

		// routes.xml
		$routesXmlPath = PathHelper::createPath(array(PROJECT_ROOT_PATH, $id, PROJECT_CONFIG_PATH)) . 'routes.xml';

		if ( ! file_exists($routesXmlPath)) {
			fclose(fopen($routesXmlPath, "w"));

			$routesXml = new Xml($routesXmlPath);
			$routesXml->addChild(
				new XmlElement('routes', array(), "\n")
			);
			$routesXml->save();

			if ($output) {
				$output->writeln(sprintf('<comment>Created %s</comment>', $routesXmlPath));
			}
		}

		// environment.xml
		$environemntXmlPath = PathHelper::createPath(array(PROJECT_ROOT_PATH, $id, PROJECT_CONFIG_PATH)) . 'environment.xml';

		if ( ! file_exists($environemntXmlPath)) {
			fclose(fopen($environemntXmlPath, "w"));

			$routesXml = new Xml($environemntXmlPath);
			$routesXml->addChild(
				new XmlElement('environment', array(), '', array(
					new XmlElement('type', array('name' => 'production', 'flags' => 'live')),
					new XmlElement('type', array('name' => 'staging', 'flags' => 'testing')),
					new XmlElement('type', array('name' => 'development', 'flags' => 'local'), '', array(
						new XmlElement('condition', array('type' => 'url', 'value' => '/\.local/'))
					))
				))
			);
			$routesXml->save();

			if ($output) {
				$output->writeln(sprintf('<comment>Created %s with default options</comment>', $environemntXmlPath));
			}
		}

		/**
		 * Create default layout
		 */

		$defaultLayoutFile = $layoutPath . DEFAULT_LAYOUT . '.' . LAYOUT_FILETYPE;

		if ( ! file_exists($defaultLayoutFile)) {
			$handle = fopen($defaultLayoutFile, 'w');
			fwrite($handle, "Your project \"" . $id . "\" is now available! \r\n<hr />\r\n" . '<?= $this->renderView() ?>');
			fclose($handle);

			if ($output) {
				$output->writeln(sprintf('<comment>Created %s</comment>', $defaultLayoutFile));
			}
		}

		/**
		 * Create index view
		 */

		$defaultViewPath = PathHelper::createPath(array($viewPath, DEFAULT_LAYOUT));
		$indexViewPath = PathHelper::createPath(array($viewPath, DEFAULT_LAYOUT, 'index'));

		if ( ! is_dir($defaultViewPath)) {
			mkdir($defaultViewPath);

			if ($output) {
				$output->writeln(sprintf('<comment>Created %s</comment>', $defaultViewPath));
			}
		}

		if ( ! is_dir($indexViewPath)) {
			mkdir($indexViewPath);

			if ($output) {
				$output->writeln(sprintf('<comment>Created %s</comment>', $indexViewPath));
			}
		}

		$indexViewFile = $indexViewPath . 'index.' . VIEW_FILETYPE;

		if ( ! file_exists($indexViewFile)) {
			$handle = fopen($indexViewFile, 'w');
			fwrite($handle, 'Index view');
			fclose($handle);

			if ($output) {
				$output->writeln(sprintf('<comment>Created %s</comment>', $indexViewFile));
			}
		}

		/**
		 * Create index controller
		 */

		$controllerPath = PathHelper::createPath(array(PROJECT_ROOT_PATH, $id, PROJECT_CLASS_PATH, 'Controller'));
		$controllerViewPath = PathHelper::createPath(array(PROJECT_ROOT_PATH, $id, PROJECT_CLASS_PATH, 'Controller', 'View'));

		if ( ! is_dir($controllerPath)) {
			mkdir($controllerPath);

			if ($output) {
				$output->writeln(sprintf('<comment>Created %s</comment>', $controllerPath));
			}
		}

		if ( ! is_dir($controllerViewPath)) {
			mkdir($controllerViewPath);

			if ($output) {
				$output->writeln(sprintf('<comment>Created %s</comment>', $controllerViewPath));
			}
		}

		$controllerViewFile = $controllerViewPath . 'Index.php';

		if ( ! file_exists($controllerViewFile)) {
			$handle = fopen($controllerViewFile, 'w');
			fwrite($handle,
				trim(sprintf('<?php

namespace %s\Controller\View;

class Index extends \Fewlines\Core\Controller\View
{
	public function indexAction() {
		// The default index action
	}
}', $namespace))
			);
			fclose($handle);

			if ($output) {
				$output->writeln(sprintf('<comment>Created %s with default class</comment>', $controllerViewFile));
			}
		}
	}

	/**
	 * Get's the active project.
	 * Note that only one project can
	 * be active at once.
	 *
	 * @return ProjectManager\Project
	 */
	public static function getActiveProject() {
		foreach (self::$projects as $project) {
			if ($project->isActive()) {
				return $project;
			}
		}

		return null;
	}

    /**
     * Gets all projects
     *
     * @return array
     */
    public static function getProjects() {
        return self::$projects;
    }

    /**
     * Returns the default project id
     * it should be the id defined in
     * the init file
     *
     * @return string
     */
    public static function getDefaultProject() {
    	return self::$default;
    }
}