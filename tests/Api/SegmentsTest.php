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
