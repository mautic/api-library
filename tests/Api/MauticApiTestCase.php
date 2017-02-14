<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

use Mautic\MauticApi;
use Mautic\Auth\ApiAuth;

abstract class MauticApiTestCase extends \PHPUnit_Framework_TestCase
{
    protected $config = null;
    protected $skipPayloadAssertion = array();

    protected function getAuth()
    {
        $this->config = include __DIR__.'/../local.config.php';
        $authMethod   = isset($this->config['AuthMethod']) ? $this->config['AuthMethod'] : 'OAuth';

        $apiAuth = new ApiAuth();
        $auth = $apiAuth->newAuth($this->config, $authMethod );

        $this->assertTrue($auth->isAuthorized(), 'Authorization failed. Check credentials in local.config.php.');

        return array($auth, $this->config['apiUrl']);
    }

    protected function getContext($context)
    {
        list($auth, $apiUrl) = $this->getAuth();

        $api = new MauticApi();
        return $api->newApi($context, $auth, $apiUrl);
    }

    protected function assertErrors($response)
    {
        $message = isset($response['error']) ? $response['error']['message'] : '';
        $this->assertFalse(isset($response['error']), $message);
    }

    protected function assertSuccess($response)
    {
        $this->assertFalse(empty($response['success']), 'Response does not contain success => true');
    }

    protected function assertPayload($response, array $payload = array())
    {
        $this->assertErrors($response);

        $this->assertFalse(empty($response[$this->api->itemName()]['id']), 'The '.$this->api->itemName().' id is empty.');

        if (empty($payload)) {
            $payload = $this->testPayload;
        }

        foreach ($payload as $itemProp => $itemVal) {
            if (in_array($itemProp, $this->skipPayloadAssertion)) continue;
            $this->assertTrue(array_key_exists($itemProp, $response[$this->api->itemName()]), 'The ["'.$this->api->itemName().'" => "'.$itemProp.'"] doesn\'t exist in the response.');
            $this->assertSame($response[$this->api->itemName()][$itemProp], $itemVal);
        }
    }

    protected function standardTestGetListOfSpecificIds()
    {
        // Create some items first
        $itemIds = array();
        for ($i = 0; $i <= 2; $i++) {
            $response = $this->api->create($this->testPayload);
            $this->assertErrors($response);
            $itemIds[] = $response[$this->api->itemName()]['id'];
        }

        $search   = 'ids:'.implode(',', $itemIds);
        $response = $this->api->getList($search);
        $this->assertErrors($response);
        $this->assertEquals(count($itemIds), $response['total']);

        foreach ($response[$this->api->listName()] as $item) {
            $this->assertTrue(in_array($item['id'], $itemIds));
            $this->api->delete($item['id']);
            $this->assertErrors($response);
        }
    }

    protected function standardTestGetList()
    {
        $response = $this->api->getList();
        $this->assertErrors($response);
        $this->assertTrue(isset($response['total']));
        $this->assertTrue(isset($response[$this->api->listName()]));
    }

    protected function standardTestCreateGetAndDelete(array $payload = null)
    {
        if (empty($payload)) {
            $payload = $this->testPayload;
        }

        // Create item
        $response = $this->api->create($payload);
        $this->assertPayload($response, $payload);

        // GET item
        $response = $this->api->get($response[$this->api->itemName()]['id']);
        $this->assertPayload($response, $payload);
        
        // Delete item
        $response = $this->api->delete($response[$this->api->itemName()]['id']);
        $this->assertErrors($response);
    }

    public function standardTestEditPatch(array $editTo)
    {
        $response = $this->api->edit(10000, $this->testPayload);

        //there should be an error as the item shouldn't exist
        $this->assertTrue(isset($response['error']), $response['error']['message']);

        $response = $this->api->create($this->testPayload);
        $this->assertPayload($response);

        $response = $this->api->edit($response[$this->api->itemName()]['id'], $editTo);
        $this->assertPayload($response, $editTo);

        $response = $this->api->delete($response[$this->api->itemName()]['id']);
        $this->assertErrors($response);
    }

    public function standardTestEditPut()
    {
        $response = $this->api->edit(10000, $this->testPayload, true);
        $this->assertPayload($response);

        //now delete the category
        $response = $this->api->delete($response[$this->api->itemName()]['id']);
        $this->assertErrors($response);
    }
}
