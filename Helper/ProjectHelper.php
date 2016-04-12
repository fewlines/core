<?php

namespace Fewlines\Core\Helper;

use Fewlines\Core\Application\ProjectManager;
use Fewlines\Core\Helper\PathHelper;

class ProjectHelper
{
    /**
     * @param  string $path
     * @param  boolean $absolute
     * @return string
     */
    public static function getPath($path, $absolute = false) {
        $project = ProjectManager::getActiveProject();
        $rootPath = '';

        if ($project) {
            $rootPath.= $project->getRoot();
        }
        else {
            $rootPath.= ProjectManager::getDefaultProject()->getRoot();
        }

        $rootPath.= $path;

        if ( ! $absolute)  {
            $rootPath = str_replace(PathHelper::getBasePath(), '', $rootPath);
        }

        return PathHelper::normalizePath($rootPath);
    }
}