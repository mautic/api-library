<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

use Mautic\Auth\ApiAuth;
use Mautic\MauticApi;

abstract class MauticApiTestCase extends \PHPUnit_Framework_TestCase
{
    protected function getAuth()
    {
        $parameters = include __DIR__.'/../local.config.php';

        $auth = ApiAuth::initiate($parameters);

        $this->assertTrue($auth->isAuthorized(), 'Authorization failed. Check credentials in local.config.php.');

        return array($auth, $parameters['apiUrl']);
    }

    protected function getContext($context)
    {
        list($auth, $apiUrl) = $this->getAuth();

        return MauticApi::getContext($context, $auth, $apiUrl);
    }
}
