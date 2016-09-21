<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class ReportsTest extends MauticApiTestCase
{
    public function testGet()
    {
        $apiContext = $this->getContext('reports');
        $response   = $apiContext->get(1);
        $this->assertErrors($response);
    }

    public function testGetList()
    {
        $apiContext = $this->getContext('reports');
        $response   = $apiContext->getList();
        $this->assertErrors($response);
    }
}
