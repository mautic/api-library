<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class EmailsTest extends MauticApiTestCase
{
    protected $context = 'emails';

    protected $itemName = 'email';

    protected $skipPayloadAssertion = array('lists');

    public function setUp() {
        $this->testPayload = array(
            'name' => 'test',
            'subject' => 'API test email',
            'customHtml' => '<h1>Hi there!</h1>',
            'emailType' => 'list',
            'dynamicContent' => array(
                array(
                    'tokenName' => 'test content name',
                    'content' => 'Some default <strong>content</strong>',
                    'filters' => array(
                        array(
                            'content' => 'Variation 1',
                            'filters' => array()
                        ),
                        array(
                            'content' => 'Variation 2',
                            'filters' => array(
                                array(
                                    'glue' => 'and',
                                    'field' => 'city',
                                    'object' => 'lead',
                                    'type' => 'text',
                                    'filter' => 'Prague',
                                    'display' => null,
                                    'operator' => '=',
                                ),
                                array(
                                    'glue' => 'and',
                                    'field' => 'email',
                                    'object' => 'lead',
                                    'type' => 'email',
                                    'filter' => 'Prague',
                                    'display' => null,
                                    'operator' => '!empty',
                                )
                            )
                        )
                    )
                ),
                array(
                    'tokenName' => 'test content name2',
                    'content' => 'Some default <strong>content2</strong>',
                    'filters' => array(
                        array(
                            'content' => 'Variation 3',
                            'filters' => array()
                        ),
                        array(
                            'content' => 'Variation 4',
                            'filters' => array(
                                array(
                                    'glue' => 'and',
                                    'field' => 'city',
                                    'object' => 'lead',
                                    'type' => 'text',
                                    'filter' => 'Raleigh',
                                    'display' => null,
                                    'operator' => '=',
                                )
                            )
                        )
                    )
                )
            )
        );
    }

    public function testGetList()
    {
        $apiContext = $this->getContext($this->context);
        $response = $apiContext->getList();
        $this->assertErrors($response);
    }

    public function testCreateGetAndDelete()
    {
        $apiContext = $this->getContext($this->context);
        $segmentApi = $this->getContext('segments');
        $response   = $segmentApi->create(array('name' => 'test'));
        $this->assertErrors($response);
        $segment    = $response['list'];

        // Add testing segment to the email
        $this->testPayload['lists'] = array($segment['id']);

        // Test Create
        $response = $apiContext->create($this->testPayload);
        $this->assertPayload($response);
        $this->assertSame($response[$this->itemName]['lists'][0]['id'], $segment['id']);
        $this->assertequals(count($response[$this->itemName]['lists']), 1);

        // Test Get
        $response = $apiContext->get($response[$this->itemName]['id']);
        $this->assertPayload($response);

        // Test Delete
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
        $response = $segmentApi->delete($segment['id']);
        $this->assertErrors($response);
    }

    public function testEditPatch()
    {
        $apiContext = $this->getContext($this->context);
        $response = $apiContext->edit(10000, $this->testPayload);

        //there should be an error as the email shouldn't exist
        $this->assertTrue(isset($response['error']), $response['error']['message']);
        
        // Unset the emailType, 'template' must be the default value
        unset($this->testPayload['emailType']);

        $response = $apiContext->create($this->testPayload);
        $this->assertErrors($response);
        $this->assertSame($response[$this->itemName]['emailType'], 'template');

        $response = $apiContext->edit(
            $response[$this->itemName]['id'],
            array(
                'name' => 'test2',
                'body' => 'test2'
            )
        );

        $this->assertErrors($response);

        //now delete the email
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
    }

    public function testEditPut()
    {
        $apiContext = $this->getContext($this->context);
        $segmentApi = $this->getContext('segments');
        $response   = $segmentApi->create(array('name' => 'test'));
        $this->assertErrors($response);
        $segment1   = $response['list'];

        // Add testing segment to the email
        $this->testPayload['lists'] = array($segment1['id']);

        $response = $apiContext->create($this->testPayload);
        $this->assertErrors($response);
        $email = $response['email'];

        $response   = $segmentApi->create(array('name' => 'test2'));
        $this->assertErrors($response);
        $segment2   = $response['list'];
        $email['lists'] = array($segment2['id']);
        
        $response = $apiContext->edit($email['id'], $email, true);
        $this->assertPayload($response);
        $this->assertSame($response[$this->itemName]['lists'][0]['id'], $segment2['id']);
        $this->assertequals(count($response[$this->itemName]['lists']), 1);

        //now delete the email
        $response = $apiContext->delete($response[$this->itemName]['id']);
        $this->assertErrors($response);
        $response = $segmentApi->delete($segment1['id']);
        $this->assertErrors($response);
        $response = $segmentApi->delete($segment2['id']);
        $this->assertErrors($response);
    }

    public function testSendToSegment()
    {
        $apiContext = $this->getContext($this->context);
        $segmentApi = $this->getContext('segments');
        $contactApi = $this->getContext('contacts');

        // Create a test segment
        $response   = $segmentApi->create(array('name' => 'test'));
        $this->assertErrors($response);
        $segment    = $response['list'];

        // Add testing segment to the email
        $this->testPayload['lists'] = array($segment['id']);
        $this->testPayload['subject'] .= ' - SendToSegment test';

        // Create a test email with the test segment
        $response   = $apiContext->create($this->testPayload);
        $this->assertErrors($response);
        $email      = $response['email'];
        
        // Create a test contact
        $response   = $contactApi->create(array('email' => $this->config['testEmail']));
        $this->assertErrors($response);
        $contact    = $response['contact'];

        // Add contact to the segment
        $segmentApi->addContact($segment['id'], $contact['id']);
        $this->assertErrors($response);

        // Finally send the email to the segment
        $response = $apiContext->send($email['id']);
        $this->assertErrors($response);
        $this->assertSuccess($response);
        $this->assertequals($response['sentCount'], 1);

        // Clean
        $response = $apiContext->delete($email['id']);
        $this->assertErrors($response);
        $response = $segmentApi->delete($segment['id']);
        $this->assertErrors($response);
        $response = $contactApi->delete($contact['id']);
        $this->assertErrors($response);
    }

    public function testSendToContact()
    {
        $apiContext = $this->getContext($this->context);
        $contactApi = $this->getContext('contacts');

        // Change the type to template so we don't have to create a list
        $this->testPayload['emailType'] = 'template';
        $this->testPayload['subject'] .= ' - SendToContact test';

        // Create a test email
        $response   = $apiContext->create($this->testPayload);
        $this->assertErrors($response);
        $email      = $response['email'];
        
        // Create a test contact
        $response   = $contactApi->create(array('email' => $this->config['testEmail']));
        $this->assertErrors($response);
        $contact    = $response['contact'];

        // Finally send the email to the contact
        $response = $apiContext->sendToContact($email['id'], $contact['id']);
        $this->assertErrors($response);
        $this->assertSuccess($response);

        // Clean
        $response = $apiContext->delete($email['id']);
        $this->assertErrors($response);
        $response = $contactApi->delete($contact['id']);
        $this->assertErrors($response);
    }
}
