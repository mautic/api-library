<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class CategoriesTest extends MauticApiTestCase
{
    protected $testPayload = array(
        'title' => 'test',
        'bundle' => 'asset'
    );

    protected $context = 'categories';

    protected $itemName = 'category';

    public function testGetList()
    {
        $categoryApi = $this->getContext($this->context);
        $response    = $categoryApi->getList();
        $this->assertErrors($response);
    }

    public function testGetListOfSpecificIds()
    {
        $this->standardTestGetListOfSpecificIds();
    }

    public function testCreateGetAndDelete()
    {
        $apiContext  = $this->getContext($this->context);

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
        $categoryApi = $this->getContext($this->context);
        $response    = $categoryApi->edit(10000, $this->testPayload);

        //there should be an error as the category shouldn't exist
        $this->assertTrue(isset($response['error']), $response['error']['message']);

        $response = $categoryApi->create($this->testPayload);
        $this->assertErrors($response);

        $response = $categoryApi->edit(
            $response[$this->itemName]['id'],
            array(
                'title' => 'test2',
                'bundle' => 'asset'
            )
        );

        $this->assertErrors($response);

        //now delete the category
        $response = $categoryApi->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }

    public function testEditPut()
    {
        $categoryApi = $this->getContext($this->context);
        $response    = $categoryApi->edit(10000, $this->testPayload, true);
        $this->assertErrors($response);

        //now delete the category
        $response = $categoryApi->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }
}
