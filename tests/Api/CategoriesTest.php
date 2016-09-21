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

    public function testGet()
    {
        $categoryApi = $this->getContext('categories');
        $response    = $categoryApi->get(1);
        $this->assertErrors($response);
    }

    public function testGetList()
    {
        $categoryApi = $this->getContext('categories');
        $response    = $categoryApi->getList();
        $this->assertErrors($response);
    }

    public function testCreateAndDelete()
    {
        $categoryApi = $this->getContext('categories');
        $response    = $categoryApi->create($this->testPayload);
        $this->assertErrors($response);

        //now delete the category
        $response = $categoryApi->delete($response['category']['id']);
        $this->assertErrors($response);
    }

    public function testEditPatch()
    {
        $categoryApi = $this->getContext('categories');
        $response    = $categoryApi->edit(10000, $this->testPayload);

        //there should be an error as the category shouldn't exist
        $this->assertTrue(isset($response['error']), $response['error']['message']);

        $response = $categoryApi->create($this->testPayload);
        $this->assertErrors($response);

        $response = $categoryApi->edit(
            $response['category']['id'],
            array(
                'title' => 'test2',
                'bundle' => 'asset'
            )
        );

        $this->assertErrors($response);

        //now delete the category
        $response = $categoryApi->delete($response['category']['id']);
        $this->assertErrors($response);
    }

    public function testEditPut()
    {
        $categoryApi = $this->getContext('categories');
        $response    = $categoryApi->edit(10000, $this->testPayload, true);
        $this->assertErrors($response);

        //now delete the category
        $response = $categoryApi->delete($response['category']['id']);
        $this->assertErrors($response);
    }
}
