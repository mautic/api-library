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

class CommonTest extends \PHPUnit_Framework_TestCase
{
    protected function getAuth()
    {
        include __DIR__ . '/../../local.config.php';

        $auth = ApiAuth::initiate(array('accessToken' => $accessToken));

        $this->assertTrue($auth->isAuthorized(), 'Authorization failed. Check credentials in local.config.php.');

        return array($auth, $apiUrl);
    }

    protected function getContext($context)
    {
        list($auth, $apiUrl) = $this->getAuth();
        $api                 = MauticApi::getContext($context, $auth, $apiUrl);

        return $api;
    }
}