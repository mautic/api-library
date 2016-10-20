<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class CompaniesTest extends MauticApiTestCase
{
    /**
     * Payload of example form to test the endpoints with
     *
     * @var array
     */
    protected $testPayload = array(
        'companyname' => 'test',
        'companyemail' => 'test@company.com',
        'companycity' => 'Raleigh',
    );

    protected $context = 'companies';

    protected $itemName = 'company';

    protected function assertPayload($response)
    {
        $this->assertErrors($response);

        $this->assertFalse(empty($response[$this->itemName]['id']), 'The '.$this->itemName.' id is empty.');
        $this->assertFalse(empty($response[$this->itemName]['fields']['all']), 'The '.$this->itemName.' fields are missing.');

        foreach ($this->testPayload as $itemProp => $itemVal) {
            $this->assertTrue(isset($response[$this->itemName]['fields']['all'][$itemProp]), 'The ["'.$this->itemName.'" => "'.$itemProp.'"] doesn\'t exist in the response.');
            $this->assertSame($response[$this->itemName]['fields']['all'][$itemProp], $itemVal);
        }
    }

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
                'companyname' => 'test2',
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
}
