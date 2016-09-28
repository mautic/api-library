<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class UsersTest extends MauticApiTestCase
{
    public function testGetUser()
    {
        $usersApi = $this->getContext('users');
        $result     = $usersApi->agetUser(1);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }
}
