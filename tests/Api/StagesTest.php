<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class StagesTest extends MauticApiTestCase
{
    public function testGet()
    {
        $stageApi = $this->getContext('stages');
        $stage    = $stageApi->get(1);

        $message = isset($stage['error']) ? $stage['error']['message'] : '';
        $this->assertFalse(isset($stage['error']), $message);
    }

    public function testGetList()
    {
        $stageApi = $this->getContext('stages');
        $stages   = $stageApi->getList();

        $message = isset($stages['error']) ? $stages['error']['message'] : '';
        $this->assertFalse(isset($stages['error']), $message);
    }

    public function testCreateAndDelete()
    {
        $stageApi = $this->getContext('stages');
        $stage    = $stageApi->create(
            array(
                'name' => 'test'
            )
        );

        $message = isset($stage['error']) ? $stage['error']['message'] : '';
        $this->assertFalse(isset($stage['error']), $message);

        //now delete the stage
        $result = $stageApi->delete($stage['stage']['id']);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testEditPatch()
    {
        $stageApi = $this->getContext('stages');
        $stage    = $stageApi->edit(
            10000,
            array(
                'name' => 'test'
            )
        );

        //there should be an error as the stage shouldn't exist
        $this->assertTrue(isset($stage['error']), $stage['error']['message']);

        $stage = $stageApi->create(
            array(
                'name' => 'test'
            )
        );

        $message = isset($stage['error']) ? $stage['error']['message'] : '';
        $this->assertFalse(isset($stage['error']), $message);

        $stage = $stageApi->edit(
            $stage['stage']['id'],
            array(
                'name' => 'test2'
            )
        );

        $message = isset($stage['error']) ? $stage['error']['message'] : '';
        $this->assertFalse(isset($stage['error']), $message);

        //now delete the stage
        $result = $stageApi->delete($stage['stage']['id']);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testEditPut()
    {
        $stageApi = $this->getContext('stages');
        $stage    = $stageApi->edit(
            10000,
            array(
                'name' => 'test',
                // following cannot be null
                'isPublished' => 1
            ),
            true
        );

        $message = isset($stage['error']) ? $stage['error']['message'] : '';
        $this->assertFalse(isset($stage['error']), $message);

        //now delete the stage
        $result = $stageApi->delete($stage['stage']['id']);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testAddAndRemove()
    {
        $stageApi = $this->getContext('stages');
        $result   = $stageApi->addContact(1, 1);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);

        //now remove the lead from the stage
        $result = $stageApi->removeContact(1, 1);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }
}
