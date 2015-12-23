<?php
namespace Fewlines\Core\Helper\View;

use Fewlines\Core\Application\ProjectManager;
use Fewlines\Core\Helper\PathHelper;

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
    	$project = ProjectManager::getActiveProject();
    	$resPath = '';

    	if ($project) {
    		$resPath.= $project->getResourcePath();
    	}
    	else {
    		$resPath.= ProjectManager::getDefaultProject()->getResourcePath();
    	}

    	$resPath.= $path;

        if ( ! $absolute)  {
            $resPath = str_replace(PathHelper::getBasePath(), '', $resPath);
        }

        return PathHelper::normalizePath($resPath);
    }
}
