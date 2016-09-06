<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class CategoriesTest extends MauticApiTestCase
{
    public function testGet()
    {
        $categoryApi = $this->getContext('categories');
        $category    = $categoryApi->get(1);

        $message = isset($category['error']) ? $category['error']['message'] : '';
        $this->assertFalse(isset($category['error']), $message);
    }

    public function testGetList()
    {
        $categoryApi = $this->getContext('categories');
        $categories   = $categoryApi->getList();

        $message = isset($categories['error']) ? $categories['error']['message'] : '';
        $this->assertFalse(isset($categories['error']), $message);
    }

    public function testCreateAndDelete()
    {
        $categoryApi = $this->getContext('categories');
        $category    = $categoryApi->create(
            array(
                'title' => 'test',
                'bundle' => 'asset'
            )
        );

        $message = isset($category['error']) ? $category['error']['message'] : '';
        $this->assertFalse(isset($category['error']), $message);

        //now delete the category
        $result = $categoryApi->delete($category['category']['id']);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testEditPut()
    {
        $categoryApi = $this->getContext('categories');
        $category    = $categoryApi->edit(
            10000,
            array(
                'title' => 'test',
                'bundle' => 'asset'
            )
        );

        //there should be an error as the category shouldn't exist
        $this->assertTrue(isset($category['error']), $category['error']['message']);

        $category = $categoryApi->create(
            array(
                'title' => 'test',
                'bundle' => 'asset'
            )
        );

        $message = isset($category['error']) ? $category['error']['message'] : '';
        $this->assertFalse(isset($category['error']), $message);

        $category = $categoryApi->edit(
            $category['category']['id'],
            array(
                'title' => 'test2',
                'bundle' => 'asset'
            )
        );

        $message = isset($category['error']) ? $category['error']['message'] : '';
        $this->assertFalse(isset($category['error']), $message);
    }

    public function testEditPatch()
    {
        $categoryApi = $this->getContext('categories');
        $category    = $categoryApi->edit(
            10000,
            array(
                'title' => 'test',
                'bundle' => 'asset',
                // following cannot be null
                'isPublished' => 1
            ),
            true
        );

        $message = isset($category['error']) ? $category['error']['message'] : '';
        $this->assertFalse(isset($category['error']), $message);
    }
}
