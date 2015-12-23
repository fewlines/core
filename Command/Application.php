<?php

namespace Fewlines\Core\Command;

use Fewlines\Core\Application\Config;
use Symfony\Component\Console\Application as SymfonyApplication;

class Application
{
	/**
	 * @var SymfonyApplication
	 */
	protected $app;

	public function __construct() {
		$this->app = new SymfonyApplication();
	}

	/**
	 * Adds all commands for the core project
	 * and active project
	 */
	public function addAll() {
		$this->add(new Project\Create);
		$this->add(new Project\Update);
		$this->add(new Project\Activate);
		$this->add(new Project\Deactivate);

		/**
		 * Add commands registered in the config
		 */

		$commands = Config::getInstance()->getElementByPath('commands');

		if ($commands) {
			foreach ($commands->getChildren() as $child) {
				$name = $child->getAttribute('name');
				$controller = $child->getAttribute('controller');
				$description = $child->getAttribute('description');

				if ($name && $controller) {
					if (class_exists($controller)) {
						$command = new $controller($name);

						if ( ! $command instanceof \Fewlines\Core\Command\Command) {
							throw new Exception\InvalidControllerException(
								sprintf('Controller must inherit from "%s"', '\Fewlines\Core\Command\Command')
							);
						}

						if ($description) {
							$command->setDescription($description);
						}

						$this->add($command);
					}
					else {
						throw new Exception\ControllerNotFoundException(
							sprintf('Controller "%s" not found', $controller)
						);
					}
				}
			}
		}
	}

	/**
	 * Adds a command
	 *
	 * @param \Fewlines\Core\Command\Command $command
	 */
	public function add(\Fewlines\Core\Command\Command $command) {
		$this->app->add($command);
	}

	/**
	 * Runs the Symfony Application
	 */
	public function run() {
		$this->app->run();
	}
}