<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class UsersTest extends MauticApiTestCase
{
    /**
     * Payload of example form to test the endpoints with
     *
     * @var array
     */
    protected $testPayload = array(
        'username' => 'apitest',
        'firstName' => 'API',
        'lastName' => 'Test',
        'email' => 'apitest@email.com',
        'plainPassword' => array(
            'password' => 'topSecret007',
            'confirm' => 'topSecret007',
        ),
        'role' => 1,
    );

    protected $skipPayloadAssertion = array('plainPassword', 'role');

    protected $context = 'users';

    protected $itemName = 'user';

    public function testGetList()
    {
        $apiContext = $this->getContext($this->context);
        $response   = $apiContext->getList();
        $this->assertErrors($response);
    }

    public function testCreateGetAndDelete()
    {
        $apiContext  = $this->getContext($this->context);

        // Test Create
        $response = $apiContext->create($this->testPayload);
        $this->assertPayload($response);

        // Test Get
        $response = $apiContext->get($response[$this->itemName]['id']);
        $this->assertPayload($response);

        // Test Delete
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }

    public function testEditPatch()
    {
        $apiContext = $this->getContext($this->context);
        $response   = $apiContext->edit(10000, $this->testPayload);

        //there should be an error as the form shouldn't exist
        $this->assertTrue(isset($response['error']), $response['error']['message']);

        $response = $apiContext->create($this->testPayload);

        $this->assertErrors($response);

        $response = $apiContext->edit(
            $response[$this->itemName]['id'],
            array(
                'lastName' => 'test2',
            )
        );

        $this->assertErrors($response);

        //now delete the form
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }

    public function testEditPut()
    {
        $apiContext = $this->getContext($this->context);
        $response   = $apiContext->edit(10000, $this->testPayload, true);
        $this->assertPayload($response);

        //now delete the form
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }

    public function testGetSelf()
    {
        $apiContext = $this->getContext($this->context);
        $response   = $apiContext->getSelf();
        $this->assertErrors($response);
    }

    public function testGetSelfPermissionsString()
    {
        $apiContext = $this->getContext($this->context);
        $response   = $apiContext->getSelf();
        $this->assertErrors($response);

        $permission = 'user:users:create';
        $response   = $apiContext->checkPermission($response['id'], $permission);
        $this->assertErrors($response);
        $this->assertTrue(isset($response[$permission]));
    }

    public function testGetSelfPermissionsArray()
    {
        $apiContext = $this->getContext($this->context);
        $response   = $apiContext->getSelf();
        $this->assertErrors($response);

        $permission = array('user:users:create', 'user:users:edit');
        $response   = $apiContext->checkPermission($response['id'], $permission);
        $this->assertErrors($response);
        foreach ($permission as $p) {
            $this->assertTrue(isset($response[$p]));
        }
    }
}
