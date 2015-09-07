<?php
namespace Fewlines\Core\Application;

use Fewlines\Core\Template\Template;

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
		self::renderTemplate(EXCEPTION_LAYOUT, $args, true);
		exit;
	}
};