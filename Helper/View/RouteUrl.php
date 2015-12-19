<?php
namespace Fewlines\Core\Helper\View;

use Fewlines\Core\Helper\UrlHelper;
use Fewlines\Core\Http\Router;

class RouteUrl extends \Fewlines\Core\Helper\AbstractViewHelper
{
    public function init() {
    }

    /**
     * Returns the baseurl with the optional
     * part, which will be appended
     *
     * @param  string $id
     * @param  array $arguments
     * @return string
     */
    public function routeUrl($id, $arguments = array()) {
        return UrlHelper::getRouteUrl($id, $arguments);
    }
}
