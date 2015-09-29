<?php
namespace Fewlines\Core\Controller\View;

use Fewlines\Core\Http\Header;

class Error extends \Fewlines\Core\Controller\View
{
    public function indexAction() {

    }

    /**
     * @return string
     */
    public function getErrorCode() {
    	return Header::getStatusCode();
    }

    /**
     * @return string
     */
    public function getErrorMessage() {
    	return Header::getStatusMessage();
    }
}