<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class LeadsTest extends ContactsTest
{
    public function setUp()
    {
        $this->api = $this->getContext('leads');
        $this->testPayload = array(
            'firstname' => 'test',
            'lastname'  => 'test',
            'points'    => 3,
            'tags'      => array(
                'APItag1',
                'APItag2',
            )
        );
    }

    // Use the method from ContactsTest to test the 'leads' endpoint for BC
}
