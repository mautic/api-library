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
    public function testGet()
    {
        $apiContext = $this->getContext('pages');
        $result     = $apiContext->get(1);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testGetList()
    {
        $apiContext = $this->getContext('pages');
        $result     = $apiContext->getList();

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }


    public function testCreateAndDelete()
    {
        $pageApi = $this->getContext('pages');
        $page    = $pageApi->create(
            array(
                'title' => 'test'
            )
        );

        $message = isset($page['error']) ? $page['error']['message'] : '';
        $this->assertFalse(isset($page['error']), $message);

        //now delete the page
        $result = $pageApi->delete($page['page']['id']);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testEditPatch()
    {
        $pageApi = $this->getContext('pages');
        $page    = $pageApi->edit(
            10000,
            array(
                'title' => 'test'
            )
        );

        //there should be an error as the page shouldn't exist
        $this->assertTrue(isset($page['error']), $page['error']['message']);

        $page = $pageApi->create(
            array(
                'title' => 'test'
            )
        );

        $message = isset($page['error']) ? $page['error']['message'] : '';
        $this->assertFalse(isset($page['error']), $message);

        $page = $pageApi->edit(
            $page['page']['id'],
            array(
                'title' => 'test2'
            )
        );

        $message = isset($page['error']) ? $page['error']['message'] : '';
        $this->assertFalse(isset($page['error']), $message);

        //now delete the page
        $result = $pageApi->delete($page['page']['id']);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testEditPut()
    {
        $pageApi = $this->getContext('pages');
        $page    = $pageApi->edit(
            10000,
            array(
                'title' => 'test',
                // following cannot be null
                'isPublished' => 1
            ),
            true
        );

        $message = isset($page['error']) ? $page['error']['message'] : '';
        $this->assertFalse(isset($page['error']), $message);

        //now delete the page
        $result = $pageApi->delete($page['page']['id']);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }
}
