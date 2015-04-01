<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

/**
 * Points Context
 *
 * @package Mautic\Api
 */
class Points extends CommonTest
{

    public function testGet()
    {
        $apiContext = $this->getContext('points');
        $result     = $apiContext->get(1);

        $valid = empty($result['error']) || $result['error']['code'] == 404;
        $this->assertTrue($valid, $result['error']['message']);
    }

    public function testGetList()
    {
        $apiContext = $this->getContext('points');
        $result     = $apiContext->getList();

        $this->assertTrue(empty($result['error']), $result['error']['message']);
    }
}