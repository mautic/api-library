<?php

/**
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 *
 * @see        http://mautic.org
 *
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class PointsTest extends MauticApiTestCase
{
    public function setUp(): void
    {
        $this->api         = $this->getContext('points');
        $this->testPayload = [
            'name'        => 'test',
            'delta'       => 5,
            'type'        => 'page.hit',
            'description' => 'created as a API test',
        ];
    }

    public function testGetList()
    {
        $this->standardTestGetList();
    }

    public function testGetListOfSpecificIds()
    {
        $this->standardTestGetListOfSpecificIds();
    }

    public function testCreateGetAndDelete()
    {
        $this->standardTestCreateGetAndDelete();
    }

    public function testEditPatch()
    {
        $editTo = [
            'name' => 'test2',
        ];
        $this->standardTestEditPatch($editTo);
    }

    public function testEditPut()
    {
        $this->standardTestEditPut();
    }

    public function testGetPointActionTypes()
    {
        $response   = $this->api->getPointActionTypes();
        $this->assertErrors($response);
        $this->assertFalse(empty($response['pointActionTypes']), 'The pointActionTypes array is empty.');
    }

    public function testBatchEndpoints()
    {
        $this->standardTestBatchEndpoints();
    }
}
