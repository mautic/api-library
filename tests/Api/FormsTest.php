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
                'formType' => 'standalone',
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
        $formApi  = $this->getContext('forms');
        $response = $formApi->edit(
            10000,
            array(
                'name' => 'test',
                'formType' => 'standalone',
                'description' => 'API test'
            )
        );

        //there should be an error as the form shouldn't exist
        $this->assertTrue(isset($response['error']), $response['error']['message']);

        $response = $formApi->create(
            array(
                'name' => 'test',
                'formType' => 'standalone',
                'description' => 'API test',
                'fields' => array(
                    array(
                        'label' => 'field name',
                        'type' => 'text'
                    )
                ),
                'actions' => array(
                    array(
                        'name' => 'action name',
                        'description' => 'field desc',
                        'type' => 'lead.pointschange',
                        'properties' => array(
                            'operator' => 'plus',
                            'points' => 2
                        )
                    )
                )
            )
        );

        $message = isset($response['error']) ? $response['error']['message'] : '';
        $this->assertFalse(isset($response['error']), $message);

        $response = $formApi->edit(
            $response['form']['id'],
            array(
                'name' => 'test2',
                'formType' => 'standalone',
                'fields' => array(
                    array(
                        'id' => 73,
                        'label' => 'field name',
                        'type' => 'text'
                    )
                ),
                'actions' => array(
                    array(
                        'name' => 'action name',
                        'description' => 'field desc',
                        'type' => 'lead.pointschange',
                        'properties' => array(
                            'operator' => 'plus',
                            'points' => 2
                        )
                    )
                )
            )
        );

        $message = isset($response['error']) ? $response['error']['message'] : '';
        $this->assertFalse(isset($response['error']), $message);

        //now delete the form
        $result = $formApi->delete($response['form']['id']);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testEditPut()
    {
        $formApi  = $this->getContext('forms');
        $response = $formApi->edit(
            10000,
            array(
                'name' => 'test 2',
                'description' => 'API test',
                'formType' => 'standalone',
                'fields' => array(
                    array(
                        'label' => 'field name 2',
                        'type' => 'text'
                    )
                ),
                'actions' => array(
                    array(
                        'name' => 'action name 2',
                        'description' => 'field desc',
                        'type' => 'lead.pointschange',
                        'properties' => array(
                            'operator' => 'plus',
                            'points' => 2
                        )
                    )
                )
            ),
            true
        );

        $message = isset($response['error']) ? $response['error']['message'] : '';
        $this->assertFalse(isset($response['error']), $message);

        //now delete the form
        $result = $formApi->delete($response['form']['id']);

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }
}
