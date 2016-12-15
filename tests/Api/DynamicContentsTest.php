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
        'name' => 'test',
        'content' => 'test'
    );

    protected $context = 'dynamicContents';

    protected $itemName = 'dynamicContent';

    public function testGetList()
    {
        $dynamiccontentApi = $this->getContext($this->context);
        $response          = $dynamiccontentApi->getList();
        $this->assertErrors($response);
    }

    public function testGetListOfSpecificIds()
    {
        $this->standardTestGetListOfSpecificIds();
    }

    public function testCreateGetAndDelete()
    {
        $apiContext = $this->getContext($this->context);

        // Test Create
        $response = $apiContext->create($this->testPayload);
        $this->assertPayload($response);

        // Test Get
        $response = $apiContext->get($response[$this->itemName]['id']);
        $this->assertPayload($response);

        // Test Delete
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }

    public function testEditPatch()
    {
        $dynamiccontentApi = $this->getContext($this->context);
        $response          = $dynamiccontentApi->edit(10000, $this->testPayload);

        //there should be an error as the dynamiccontent shouldn't exist
        $this->assertTrue(isset($response['error']), $response['error']['message']);

        $response = $dynamiccontentApi->create($this->testPayload);
        $this->assertErrors($response);

        $response = $dynamiccontentApi->edit(
            $response[$this->itemName]['id'],
            array(
                'name' => 'test2'
            )
        );

        $this->assertErrors($response);

        //now delete the dynamiccontent
        $response = $dynamiccontentApi->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }

    public function testEditPut()
    {
        $dynamiccontentApi = $this->getContext($this->context);
        $response          = $dynamiccontentApi->edit(10000, $this->testPayload, true);
        $this->assertPayload($response);

        //now delete the dynamiccontent
        $response = $dynamiccontentApi->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }
}
