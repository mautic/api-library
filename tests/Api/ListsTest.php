<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class ListsTest extends MauticApiTestCase
{
    public function testAddAndRemove()
    {
        $listApi = $this->getContext('lists');
        $response  = $listApi->addLead(1, 1);
        $this->assertErrors($response);

        //now remove the lead from the segment
        $response = $listApi->removeLead(1, 1);
        $this->assertErrors($response);
    }
}
