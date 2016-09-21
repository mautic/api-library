<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class PagesTest extends MauticApiTestCase
{
    protected $testPayload = array(
        'title' => 'test'
    );

    public function testGet()
    {
        $apiContext = $this->getContext('pages');
        $response   = $apiContext->get(1);
        $this->assertErrors($response);
    }

    public function testGetList()
    {
        $apiContext = $this->getContext('pages');
        $response   = $apiContext->getList();
        $this->assertErrors($response);
    }


    public function testCreateAndDelete()
    {
        $pageApi  = $this->getContext('pages');
        $response = $pageApi->create($this->testPayload);
        $this->assertErrors($response);

        //now delete the page
        $response = $pageApi->delete($response['page']['id']);
        $this->assertErrors($response);
    }

    public function testEditPatch()
    {
        $pageApi  = $this->getContext('pages');
        $response = $pageApi->edit(10000, $this->testPayload);

        //there should be an error as the page shouldn't exist
        $this->assertTrue(isset($response['error']), $response['error']['message']);

        $response = $pageApi->create($this->testPayload);
        $this->assertErrors($response);

        $response = $pageApi->edit(
            $response['page']['id'],
            array(
                'title' => 'test2'
            )
        );

        $this->assertErrors($response);

        //now delete the page
        $response = $pageApi->delete($response['page']['id']);
        $this->assertErrors($response);
    }

    public function testEditPut()
    {
        $pageApi  = $this->getContext('pages');
        $response = $pageApi->edit(10000, $this->testPayload, true);
        $this->assertErrors($response);

        //now delete the page
        $response = $pageApi->delete($response['page']['id']);
        $this->assertErrors($response);
    }
}
