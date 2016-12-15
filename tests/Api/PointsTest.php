<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class PointsTest extends MauticApiTestCase
{
    protected $testPayload = array(
        'name' => 'test',
        'delta' => 5,
        'type' => 'page.hit',
        'description' => 'created as a API test'
    );

    protected $context = 'points';

    protected $itemName = 'point';

    protected function assertPayload($response, array $payload = array()) {
        $this->assertErrors($response);
        $this->assertFalse(empty($response[$this->itemName]['id']), 'The point id is empty.');
        $this->assertSame($response[$this->itemName]['name'], $this->testPayload['name']);
    }

    public function testGetList()
    {
        $apiContext = $this->getContext($this->context);
        $response   = $apiContext->getList();
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
        $apiContext = $this->getContext($this->context);
        $response   = $apiContext->edit(10000, $this->testPayload);

        //there should be an error as the point shouldn't exist
        $this->assertTrue(isset($response['error']), $response['error']['message']);

        $response = $apiContext->create($this->testPayload);
        $this->assertErrors($response);

        $response = $apiContext->edit(
            $response[$this->itemName]['id'],
            array(
                'name' => 'test2',
            )
        );

        $this->assertErrors($response);
        // $this->assertTrue(empty($response[$this->itemName]['id']), 'The point id is empty.');
        $this->assertSame($response[$this->itemName]['name'], 'test2');

        //now delete the point
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }

    public function testEditPut()
    {
        $apiContext = $this->getContext($this->context);
        $response   = $apiContext->edit(10000, $this->testPayload, true);
        $this->assertPayload($response);

        //now delete the point
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }

    public function testGetPointActionTypes()
    {
        $apiContext = $this->getContext($this->context);
        $response   = $apiContext->getPointActionTypes();
        $this->assertErrors($response);
        $this->assertFalse(empty($response['pointActionTypes']), 'The pointActionTypes array is empty.');
    }
}
