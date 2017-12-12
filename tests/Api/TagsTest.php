<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class TagsTest extends MauticApiTestCase
{
    public function setUp()
    {
        $this->api = $this->getContext('tags');
        $this->testPayload = array(
            'tag' => 'API-tag',
        );
    }

    public function testGetList()
    {
        $this->standardTestGetList();
    }

    public function testGetListOfSpecificIds()
    {
        // Create some tags first
        $itemIds = array();
        for ($i = 0; $i <= 2; $i++) {
            $response = $this->api->create(array('tag' => 'api-test-tag'.$i));
            $this->assertErrors($response);
            $itemIds[] = $response[$this->api->itemName()]['id'];
        }

        $search = 'ids:'.implode(',', $itemIds);
        $response = $this->api->getList($search);
        $this->assertErrors($response);
        $this->assertEquals(count($itemIds), $response['total']);

        foreach ($response[$this->api->listName()] as $item) {
            $this->assertTrue(in_array($item['id'], $itemIds));
            $this->api->delete($item['id']);
            $this->assertErrors($response);
        }
    }

    public function testCreateGetAndDelete()
    {
        $this->standardTestCreateGetAndDelete();
    }

    public function testEditPatch()
    {
        $editTo = array(
            'tag' => 'API-tag-edit',
        );
        $this->standardTestEditPatch($editTo);
    }

    public function testEditPut()
    {
        $this->standardTestEditPut();
    }

    public function testBatchEndpoints()
    {
        $this->standardTestBatchEndpoints(
            array(
                array('tag' => 'api-test-tag1'),
                array('tag' => 'api-test-tag2'),
                array('tag' => 'api-test-tag3'),
            )
        );
    }
}
