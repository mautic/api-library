<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Auth;

interface AuthInterface
{
    /**
     * Make a request to server using the supported auth method
     *
     * @param string $url
     * @param array  $parameters
     * @param string $method
     * @param array  $settings
     *
     * @return array
     */
    public function makeRequest($url, array $parameters = array(), $method = 'GET', array $settings = array());

    /**
     * Check if current authorization is still valid
     *
     * @return bool
     */
    public function isAuthorized();

    public function getResponseInfo();

    public function getResponseHeaders();

}
