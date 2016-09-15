<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class FormsTest extends MauticApiTestCase
{
    public function testGet()
    {
        $apiContext = $this->getContext('forms');
        $result     = $apiContext->get(1);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testGetList()
    {
        $apiContext = $this->getContext('forms');
        $result     = $apiContext->getList();

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testCreateAndDelete()
    {
        $formApi = $this->getContext('forms');
        $form    = $formApi->create(
            array(
                'name' => 'test',
                'description' => 'API test'
            )
        );

        $message = isset($form['error']) ? $form['error']['message'] : '';
        $this->assertFalse(isset($form['error']), $message);

        //now delete the form
        $result = $formApi->delete($form['form']['id']);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testEditPatch()
    {
        $formApi = $this->getContext('forms');
        $form    = $formApi->edit(
            10000,
            array(
                'name' => 'test',
                'description' => 'API test'
            )
        );

        //there should be an error as the form shouldn't exist
        $this->assertTrue(isset($form['error']), $form['error']['message']);

        $form = $formApi->create(
            array(
                'name' => 'test',
                'description' => 'API test'
            )
        );

        $message = isset($form['error']) ? $form['error']['message'] : '';
        $this->assertFalse(isset($form['error']), $message);

        $form = $formApi->edit(
            $form['form']['id'],
            array(
                'name' => 'test2'
            )
        );

        $message = isset($form['error']) ? $form['error']['message'] : '';
        $this->assertFalse(isset($form['error']), $message);

        //now delete the form
        $result = $formApi->delete($form['form']['id']);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testEditPut()
    {
        $formApi = $this->getContext('forms');
        $form    = $formApi->edit(
            10000,
            array(
                'name' => 'test',
                'description' => 'API test'
            ),
            true
        );

        $message = isset($form['error']) ? $form['error']['message'] : '';
        $this->assertFalse(isset($form['error']), $message);

        //now delete the form
        $result = $formApi->delete($form['form']['id']);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }
}
