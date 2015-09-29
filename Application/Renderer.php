<?php
namespace Fewlines\Core\Application;

use Fewlines\Core\Template\Template;
use Fewlines\Core\Application\ProjectManager;
use Fewlines\Core\Locale\Locale;
use Fewlines\Core\Http\Header;

abstract class Renderer
{
	/**
	 * Renders a template
	 *
	 * @param string $layout
	 * @param array $args
	 * @param boolean $force
	 */
	final protected static function renderTemplate($layout, $args = array(), $force = false) {
		Buffer::start();

		$template = Template::getInstance();
		$hasLayout = $template->getLayout() instanceof \Fewlines\Core\Template\Layout;

		if ($force == true || ($force == false && ! $hasLayout)) {
			$template->setLayout($layout)->setAutoView()->renderAll($args);
		}
		else if ($hasLayout) {
			$template->setAutoView()->renderAll($args);
		}
	}

	/**
	 * Renders a exception/error template
	 *
	 * @param array $args
	 */
	final protected static function renderException($args) {
		Buffer::clear(true);

		/**
		 * Set header status code
		 */

        Header::set(500, false);

		/**
		 * Reset all to the default
		 */

		$activeProject = ProjectManager::getActiveProject();

		if ($activeProject) {
			$activeProject->setActive(false);
		}

		Locale::set(DEFAULT_LOCALE);

		/**
		 * Render with the exception layout
		 */

		self::renderTemplate(EXCEPTION_LAYOUT, $args, true);
		exit;
	}
};