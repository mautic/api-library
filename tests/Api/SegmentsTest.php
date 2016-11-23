<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class SegmentsTest extends MauticApiTestCase
{
    protected $testPayload = array(
        'name' => 'API test'
    );

    protected $context = 'segments';

    protected $itemName = 'list'; // this will be changed to 'segment' in Mautic 3

    public function testGetList()
    {
        $apiContext = $this->getContext($this->context);
        $response   = $apiContext->getList();
        $this->assertErrors($response);
    }

    public function testGetListMinimal()
    {
        $apiContext = $this->getContext($this->context);
        $response   = $apiContext->getList('', 0,  0, '', 'ASC', false, true);
        $this->assertErrors($response);
    }

    public function testCreateAndDelete()
    {
        $apiContext = $this->getContext($this->context);
        $response   = $apiContext->create($this->testPayload);
        $this->assertErrors($response);

        //now delete the segment
        $response = $apiContext->delete($response[$this->itemName]['id']); 
        $this->assertErrors($response);
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

        //there should be an error as the segment shouldn't exist
        $this->assertTrue(isset($response['error']), $response['error']['message']);

        $response = $apiContext->create($this->testPayload);
        $this->assertErrors($response);

        $response = $apiContext->edit(
            $response[$this->itemName]['id'],
            array(
                'name' => 'test2'
            )
        );

        $this->assertErrors($response);

        //now delete the segment
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }

    public function testEditPut()
    {
        $apiContext = $this->getContext($this->context);
        $response   = $apiContext->edit(10000, $this->testPayload, true);
        $this->assertErrors($response);

        //now delete the segment
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }

    public function testAddAndRemove()
    {
        $apiContext = $this->getContext($this->context);
        $response   = $apiContext->addContact(1, 1);
        $this->assertErrors($response);

        //now remove the lead from the segment
        $response = $apiContext->removeContact(1, 1);
        $this->assertErrors($response);
    }
}
