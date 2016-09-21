<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class DynamicContentsTest extends MauticApiTestCase
{
    protected $testPayload = array(
        'name' => 'test'
    );

    public function testGet()
    {
        $dynamiccontentApi = $this->getContext('DynamicContents');
        $response          = $dynamiccontentApi->get(1);
        $this->assertErrors($response);
    }

    public function testGetList()
    {
        $dynamiccontentApi = $this->getContext('DynamicContents');
        $response          = $dynamiccontentApi->getList();
        $this->assertErrors($response);
    }

    public function testCreateAndDelete()
    {
        $dynamiccontentApi = $this->getContext('DynamicContents');
        $response          = $dynamiccontentApi->create($this->testPayload);
        $this->assertErrors($response);

        //now delete the dynamiccontent
        $response = $dynamiccontentApi->delete($response['dynamicContent']['id']);
        $this->assertErrors($response);
    }

    public function testEditPatch()
    {
        $dynamiccontentApi = $this->getContext('DynamicContents');
        $response          = $dynamiccontentApi->edit(10000, $this->testPayload);

        //there should be an error as the dynamiccontent shouldn't exist
        $this->assertTrue(isset($response['error']), $response['error']['message']);

        $response = $dynamiccontentApi->create($this->testPayload);
        $this->assertErrors($response);

        $response = $dynamiccontentApi->edit(
            $response['dynamicContent']['id'],
            array(
                'name' => 'test2'
            )
        );

        $this->assertErrors($response);

        //now delete the dynamiccontent
        $response = $dynamiccontentApi->delete($response['dynamicContent']['id']);
        $this->assertErrors($response);
    }

    public function testEditPut()
    {
        $dynamiccontentApi = $this->getContext('DynamicContents');
        $response          = $dynamiccontentApi->edit(10000, $this->testPayload, true);
        $this->assertErrors($response);

        //now delete the dynamiccontent
        $response = $dynamiccontentApi->delete($response['dynamicContent']['id']);
        $this->assertErrors($response);
    }
}
