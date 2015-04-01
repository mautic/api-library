<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic;

use Mautic\Auth\AuthInterface;
use Mautic\Exception\ContextNotFoundException;

class MauticApi
{

    /**
     * Get an API context object
     *
     * @param string        $apiContext     API context (leads, forms, etc)
     * @param AuthInterface $auth           API Auth object
     * @param string        $baseUrl        Base URL for API endpoints
     */
    static function getContext($apiContext, AuthInterface $auth, $baseUrl = '')
    {
        $apiContext = ucfirst($apiContext);

        static $contexts = array();

        if (!isset($context[$apiContext])) {
            $class = 'Mautic\\Api\\' . $apiContext;
            if (class_exists($class)) {
                $contexts[$apiContext] = new $class($auth, $baseUrl);
            } else {
                throw new ContextNotFoundException("A context of '$apiContext' was not found.");
            }
        }

        return $contexts[$apiContext];
    }
}

include 'AutoLoader.php';
