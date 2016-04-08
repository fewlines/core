<?php
namespace Fewlines\Core\Helper\View;

use Fewlines\Core\Helper\ResourceHelper;

class Resource extends \Fewlines\Core\Helper\AbstractViewHelper
{
    public function init() {
    }

    /**
     * @param  string $path
     * @param  boolean $absolute
     * @return string
     */
    public function resource($path, $absolute = false) {
        return ResourceHelper::getPath($path, $absolute);
    }
}
