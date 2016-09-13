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
    public function testGet()
    {
        $dynamiccontentApi = $this->getContext('DynamicContents');
        $dynamiccontent    = $dynamiccontentApi->get(1);

        $message = isset($dynamiccontent['error']) ? $dynamiccontent['error']['message'] : '';
        $this->assertFalse(isset($dynamiccontent['error']), $message);
    }

    public function testGetList()
    {
        $dynamiccontentApi = $this->getContext('DynamicContents');
        $dynamiccontents   = $dynamiccontentApi->getList();

        $message = isset($dynamiccontents['error']) ? $dynamiccontents['error']['message'] : '';
        $this->assertFalse(isset($dynamiccontents['error']), $message);
    }

    public function testCreateAndDelete()
    {
        $dynamiccontentApi = $this->getContext('DynamicContents');
        $dynamiccontent    = $dynamiccontentApi->create(
            array(
                'name' => 'test'
            )
        );

        $message = isset($dynamiccontent['error']) ? $dynamiccontent['error']['message'] : '';
        $this->assertFalse(isset($dynamiccontent['error']), $message);

        //now delete the dynamiccontent
        $result = $dynamiccontentApi->delete($dynamiccontent['dynamicContent']['id']);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testEditPut()
    {
        $dynamiccontentApi = $this->getContext('DynamicContents');
        $dynamiccontent    = $dynamiccontentApi->edit(
            10000,
            array(
                'name' => 'test'
            )
        );

        //there should be an error as the dynamiccontent shouldn't exist
        $this->assertTrue(isset($dynamiccontent['error']), $dynamiccontent['error']['message']);

        $dynamiccontent = $dynamiccontentApi->create(
            array(
                'name' => 'test'
            )
        );

        $message = isset($dynamiccontent['error']) ? $dynamiccontent['error']['message'] : '';
        $this->assertFalse(isset($dynamiccontent['error']), $message);

        $dynamiccontent = $dynamiccontentApi->edit(
            $dynamiccontent['dynamicContent']['id'],
            array(
                'name' => 'test2'
            )
        );

        $message = isset($dynamiccontent['error']) ? $dynamiccontent['error']['message'] : '';
        $this->assertFalse(isset($dynamiccontent['error']), $message);

        //now delete the dynamiccontent
        $result = $dynamiccontentApi->delete($dynamiccontent['dynamicContent']['id']);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testEditPatch()
    {
        $dynamiccontentApi = $this->getContext('DynamicContents');
        $dynamiccontent    = $dynamiccontentApi->edit(
            10000,
            array(
                'name' => 'test',
                // following cannot be null
                'language' => 'en',
                'isPublished' => 1
            ),
            true
        );

        $message = isset($dynamiccontent['error']) ? $dynamiccontent['error']['message'] : '';
        $this->assertFalse(isset($dynamiccontent['error']), $message);

        //now delete the dynamiccontent
        $result = $dynamiccontentApi->delete($dynamiccontent['dynamicContent']['id']);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }
}
