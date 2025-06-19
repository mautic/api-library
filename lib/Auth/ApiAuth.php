<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\Auth;

use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientInterface;

/**
 * OAuth Client modified from https://code.google.com/p/simple-php-oauth/.
 */
class ApiAuth
{
    protected ClientInterface $client;

    public function __construct(?ClientInterface $client = null)
    {
        $this->client = $client ?: Psr18ClientDiscovery::find();
    }

    /**
     * Get an API Auth object.
     *
     * @param array  $parameters
     * @param string $authMethod
     *
     * @return AuthInterface
     *
     * @deprecated
     */
    public static function initiate($parameters = [], $authMethod = 'OAuth')
    {
        $object = new self();

        return $object->newAuth($parameters, $authMethod);
    }

    /**
     * Get an API Auth object.
     *
     * @param array  $parameters
     * @param string $authMethod
     *
     * @return AuthInterface
     */
    public function newAuth($parameters = [], $authMethod = 'OAuth')
    {
        $class      = 'Mautic\\Auth\\'.$authMethod;
        $authObject = new $class($this->client);

        $reflection = new \ReflectionMethod($class, 'setup');
        $pass       = [];

        foreach ($reflection->getParameters() as $param) {
            if (isset($parameters[$param->getName()])) {
                $pass[] = $parameters[$param->getName()];
            } else {
                $pass[] = null;
            }
        }

        $reflection->invokeArgs($authObject, $pass);

        return $authObject;
    }
}
