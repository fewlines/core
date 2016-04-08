<?php

namespace Fewlines\Core\Helper;

use Fewlines\Core\Application\ProjectManager;
use Fewlines\Core\Helper\PathHelper;

class ResourceHelper
{
	/**
     * @param  string $path
     * @param  boolean $absolute
     * @return string
     */
	public static function getPath($path, $absolute = false) {
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