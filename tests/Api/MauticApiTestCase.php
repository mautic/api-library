<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

use Mautic\Auth\ApiAuth;
use Mautic\MauticApi;

abstract class MauticApiTestCase extends \PHPUnit_Framework_TestCase
{
    protected $config = null;
    protected $skipPayloadAssertion = array();

    protected function getAuth()
    {
        $this->config = include __DIR__.'/../local.config.php';

        $auth = ApiAuth::initiate($this->config);

        $this->assertTrue($auth->isAuthorized(), 'Authorization failed. Check credentials in local.config.php.');

        return array($auth, $this->config['apiUrl']);
    }

    protected function getContext($context)
    {
        list($auth, $apiUrl) = $this->getAuth();

        return MauticApi::getContext($context, $auth, $apiUrl);
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

        $this->assertFalse(empty($response[$this->itemName]['id']), 'The '.$this->itemName.' id is empty.');

        if (empty($payload)) {
            $payload = $this->testPayload;
        }

        foreach ($payload as $itemProp => $itemVal) {
            if (in_array($itemProp, $this->skipPayloadAssertion)) continue;
            $this->assertTrue(array_key_exists($itemProp, $response[$this->itemName]), 'The ["'.$this->itemName.'" => "'.$itemProp.'"] doesn\'t exist in the response.');
            $this->assertSame($response[$this->itemName][$itemProp], $itemVal);
        }
    }

    protected function standardTestGetListOfSpecificIds()
    {
        $apiContext = $this->getContext($this->context);

        // Create some items first
        $itemIds = array();
        $response = $apiContext->create($this->testPayload);
        $this->assertErrors($response);
        $itemIds[] = $response[$this->itemName]['id'];
        $response = $apiContext->create($this->testPayload);
        $this->assertErrors($response);
        $itemIds[] = $response[$this->itemName]['id'];

        $search = 'ids:'.implode(',', $itemIds);

        $apiContext = $this->getContext($this->context);
        $response = $apiContext->getList($search);
        $this->assertErrors($response);
        $this->assertEquals(count($itemIds), $response['total']);

        foreach ($response[$this->context] as $item) {
            $this->assertTrue(in_array($item['id'], $itemIds));
            $apiContext->delete($item['id']);
            $this->assertErrors($response);
        }
    }
}
