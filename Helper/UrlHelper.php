<?php
namespace Fewlines\Core\Helper;

use Fewlines\Core\Http\Router;
use Fewlines\Core\Http\Router\Routes\Route;
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


    /**
     * Gets a route from the config
     * and parse the optional argumuntes
     * for it
     *
     * @param string $id
     * @param array $arguments
     * @return string
     */
    public static function getRouteUrl($id, $arguments = array()) {
        foreach (Router::getInstance()->getRoutes() as $route) {
            $rId = $route->getId();

            if ( ! empty($rId) && trim($rId) == trim($id)) {
                return static::parseRouteUrl($route->getFullFrom(), $arguments);
                break;
            }
        }

        return null;
    }

    /**
     * @param string $url
     * @param array $arguments
     */
    public static function parseRouteUrl($url, $arguments = array()) {
        preg_match_all(Route::VAR_MASK, $url, $matches);

        if (ArrayHelper::isAssociative($arguments)) {
            for ($i = 0, $len = count($matches[1]); $i < $len; $i++) {
                if (array_key_exists($matches[1][$i], $arguments)) {
                    $url = preg_replace('/' . $matches[0][$i] . '/', $arguments[$matches[1][$i]], $url);
                }
            }
        }
        else {
            for ($i = 0, $len = count($matches[1]); $i < $len; $i++) {
                if (array_key_exists($i, $arguments)) {
                    $url = preg_replace('/' . $matches[0][$i] . '/', $arguments[$i], $url);
                }
            }
        }

        return $url;
    }

    /**
     * Removes unecessary slashed in the url
     *
     * @param string $url
     * @return string
     */
    public static function cleanUrl($url) {
        return preg_replace('/(\/{1,})/','/', $url);
    }
}
