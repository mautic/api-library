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
        'name' => 'test'
    );

    public function testGet()
    {
        $segmentApi = $this->getContext('segments');
        $response   = $segmentApi->get(1);
        $this->assertErrors($response);
    }

    public function testGetList()
    {
        $segmentApi = $this->getContext('segments');
        $response   = $segmentApi->getList();
        $this->assertErrors($response);
    }

    public function testGetListMinimal()
    {
        $segmentApi = $this->getContext('segments');
        $response   = $segmentApi->getList('', 0,  0, '', 'ASC', false, true);
        $this->assertErrors($response);
    }

    public function testCreateAndDelete()
    {
        $segmentApi = $this->getContext('segments');
        $response   = $segmentApi->create($this->testPayload);
        $this->assertErrors($response);

        //now delete the segment
        $response = $segmentApi->delete($response['list']['id']); // 'list' will be changed to 'segment' in Mautic 3
        $this->assertErrors($response);
    }

    public function testEditPatch()
    {
        $segmentApi = $this->getContext('segments');
        $response   = $segmentApi->edit(10000, $this->testPayload);

        //there should be an error as the segment shouldn't exist
        $this->assertTrue(isset($response['error']), $response['error']['message']);

        $response = $segmentApi->create($this->testPayload);
        $this->assertErrors($response);

        $response = $segmentApi->edit(
            $response['list']['id'], // 'list' will be changed to 'segment' in Mautic 3
            array(
                'name' => 'test2'
            )
        );

        $this->assertErrors($response);

        //now delete the segment
        $response = $segmentApi->delete($response['list']['id']); // 'list' will be changed to 'segment' in Mautic 3
        $this->assertErrors($response);
    }

    public function testEditPut()
    {
        $segmentApi = $this->getContext('segments');
        $response   = $segmentApi->edit(10000, $this->testPayload, true);
        $this->assertErrors($response);

        //now delete the segment
        $response = $segmentApi->delete($response['list']['id']); // 'list' will be changed to 'segment' in Mautic 3
        $this->assertErrors($response);
    }

    public function testAddAndRemove()
    {
        $segmentApi = $this->getContext('segments');
        $response   = $segmentApi->addContact(1, 1);
        $this->assertErrors($response);

        //now remove the lead from the segment
        $response = $segmentApi->removeContact(1, 1);
        $this->assertErrors($response);
    }
}
