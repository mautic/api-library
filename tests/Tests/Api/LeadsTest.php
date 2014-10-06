<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

/**
 * Leads Context
 *
 * @package Mautic\Api
 */
class Leads extends CommonTest
{
    public function testGet()
    {
        $leadApi = $this->getContext('leads');
        $lead    = $leadApi->get(1);

        $valid = empty($result['error']) || $result['error']['code'] == 404;
        $this->assertTrue($valid, $result['error']['message']);
    }

    public function testGetList()
    {
        $leadApi = $this->getContext('leads');
        $leads   = $leadApi->getList();

        $this->assertTrue(empty($leads['error']), $leads['error']['message']);
    }

    public function testGetFieldList()
    {
        $leadApi = $this->getContext('leads');
        $fields  = $leadApi->getFieldList();

        $this->assertTrue(empty($fields['error']), $fields['error']['message']);
    }

    public function testGetListList()
    {
        $leadApi = $this->getContext('leads');
        $lists   = $leadApi->getLists();

        $this->assertTrue(empty($lists['error']), $lists['error']['message']);
    }

    public function testGetNotes()
    {
        $leadApi = $this->getContext('leads');
        $leads   = $leadApi->getNotes(1);

        $this->assertTrue(empty($leads['error']), $leads['error']['message']);
    }

    public function testCreateAndDelete()
    {
        $leadApi = $this->getContext('leads');
        $lead    = $leadApi->create(array(
            'firstname' => 'test',
            'lastname'  => 'test'
        ));

        $this->assertTrue(empty($lead['error']), $lead['error']['message']);

        //now delete the lead
        $result = $leadApi->delete($lead['lead']['id']);
        $this->assertTrue(empty($result['error']), $result['error']['message']);
    }

    public function testEditPut()
    {
        $leadApi = $this->getContext('leads');
        $lead    = $leadApi->edit(10000, array(
            'firstname' => 'test',
            'lastname'  => 'test'
        ));

        //there should be an error as the lead shouldn't exist
        $this->assertTrue(isset($lead['error']), $lead['error']['message']);

        $lead    = $leadApi->create(array(
            'firstname' => 'test',
            'lastname'  => 'test'
        ));

        $this->assertTrue(empty($lead['error']), $lead['error']['message']);

        $lead    = $leadApi->edit($lead['lead']['id'], array(
            'firstname' => 'test2',
            'lastname'  => 'test2'
        ));
        $this->assertTrue(empty($lead['error']), $lead['error']['message']);
    }

    public function testEditPatch()
    {
        $leadApi = $this->getContext('leads');
        $lead    = $leadApi->edit(10000, array(
            'firstname' => 'test',
            'lastname'  => 'test'
        ), true);

        $this->assertTrue(empty($lead['error']), $lead['error']['message']);
    }
}