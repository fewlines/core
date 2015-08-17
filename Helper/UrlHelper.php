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

                foreach (Router::getInstance()->getRouteUrlParts() as $part => $value) {
                    if (is_array($value)) {
                        continue;
                    }

                    if (array_key_exists($part, $parts)) {
                        $tmpParts[] = $parts[$part];
                    }
                    else {
                        $tmpParts[] = $value;
                    }
                }

                $parts = $tmpParts;
            }

            $parts = implode("/", $parts);
        }

        return Router::getInstance()->getBaseUrl() . ltrim($parts, "/");
    }
}
