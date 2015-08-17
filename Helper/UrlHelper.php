<?php
namespace Fewlines\Core\Helper;

use Fewlines\Core\Http\Router;
use Fewlines\Core\Helper\ArrayHelper;

class UrlHelper extends \Fewlines\Core\Helper\View\BaseUrl
{
    /**
     * Returns the base url
     *
     * @param  string|array $parts
     * @return string
     */
    public static function getBaseUrl($parts = "") {
        if (is_array($parts)) {
            if (ArrayHelper::isAssociative($parts)) {
                /**
                 * Match the keys given
                 * with the url from
                 * the router.
                 *
                 * Rebuilding the url parts
                 */

                $tmpParts = array();
                $urlParts = Router::getInstance()->getRouteUrlParts();
                $counter = 0;

                foreach ($urlParts as $part => $value) {
                    if (is_array($value)) {
                        continue;
                    }

                    if (array_key_exists($part, $parts)) {
                        $tmpParts[] = $parts[$part];
                    }
                    else if ($counter != count($urlParts)-1) {
                        /**
                         * If it's not the last DEFAULT value
                         * it will be added to the list otherwise
                         * it will be ignored, because the router
                         * takes the default value automatically
                         */

                        $tmpParts[] = $value;
                    }

                    $counter++;
                }

                $parts = $tmpParts;
            }

            $parts = implode("/", $parts);
        }

        return Router::getInstance()->getBaseUrl() . ltrim($parts, "/");
    }
}
