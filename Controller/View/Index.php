<?php
namespace Fewlines\Core\Controller\View;

class Index extends \Fewlines\Core\Controller\View
{
    public function indexAction() {
		$this->view->assign('version', $this->getConfig()->getElementByPath('application/version'));
    }
}
