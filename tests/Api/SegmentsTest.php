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
    public function testGet()
    {
        $segmentApi = $this->getContext('segments');
        $segment    = $segmentApi->get(1);

        $message = isset($segment['error']) ? $segment['error']['message'] : '';
        $this->assertFalse(isset($segment['error']), $message);
    }

    public function testGetList()
    {
        $segmentApi = $this->getContext('segments');
        $segments   = $segmentApi->getList();

        $message = isset($segments['error']) ? $segments['error']['message'] : '';
        $this->assertFalse(isset($segments['error']), $message);
    }

    public function testCreateAndDelete()
    {
        $segmentApi = $this->getContext('segments');
        $segment    = $segmentApi->create(
            array(
                'name' => 'test'
            )
        );

        $message = isset($segment['error']) ? $segment['error']['message'] : '';
        $this->assertFalse(isset($segment['error']), $message);

        //now delete the segment
        $result = $segmentApi->delete($segment['list']['id']); // 'list' will be changed to 'segment' in Mautic 3

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testEditPut()
    {
        $segmentApi = $this->getContext('segments');
        $segment    = $segmentApi->edit(
            10000,
            array(
                'name' => 'test'
            )
        );

        //there should be an error as the segment shouldn't exist
        $this->assertTrue(isset($segment['error']), $segment['error']['message']);

        $segment = $segmentApi->create(
            array(
                'name' => 'test'
            )
        );

        $message = isset($segment['error']) ? $segment['error']['message'] : '';
        $this->assertFalse(isset($segment['error']), $message);

        $segment = $segmentApi->edit(
            $segment['list']['id'], // 'list' will be changed to 'segment' in Mautic 3
            array(
                'name' => 'test2'
            )
        );

        $message = isset($segment['error']) ? $segment['error']['message'] : '';
        $this->assertFalse(isset($segment['error']), $message);
    }

    public function testEditPatch()
    {
        $segmentApi = $this->getContext('segments');
        $segment    = $segmentApi->edit(
            10000,
            array(
                'name' => 'test',
                // following cannot be null
                'isPublished' => 1,
                'isGlobal' => 1
            ),
            true
        );

        $message = isset($segment['error']) ? $segment['error']['message'] : '';
        $this->assertFalse(isset($segment['error']), $message);
    }

    public function testAddAndRemove()
    {
        $segmentApi = $this->getContext('segments');
        $result     = $segmentApi->addContact(1, 1);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);

        //now remove the lead from the segment
        $result = $segmentApi->removeContact(1, 1);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }
}
