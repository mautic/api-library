<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class InfoTest extends MauticApiTestCase
{
    protected $context = 'info';

    public function testGetVersion() {
        // Delete a contact from test
        $apiContext = $this->getContext($this->context);
        $response = $apiContext->getVersion();
        $this->assertErrors($response);
        $this->assertRegExp("/^(\d+\.)?(\d+\.)?(.+|\d+)$/", $response['version']);
    }

}
