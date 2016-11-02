<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class StatsTest extends MauticApiTestCase
{
    protected $context = 'stats';

    protected function assertPayload($response, array $payload = array())
    {
        $this->assertErrors($response);
        $this->assertTrue(isset($response[$this->context]), 'The '.$this->context.' array does not exist.');
    }

    protected function assertGreaterThanIdsInArray($response)
    {
        $previous = 0;

        foreach ($response[$this->context] as $row) {
            $id = (int) $row['id'];
            $this->assertGreaterThan($previous, $id);
            $previous = $id;
        }
    }

    protected function assertLessThanIdsInArray($response)
    {
        $previous = 999999999999999;

        foreach ($response[$this->context] as $row) {
            $id = (int) $row['id'];
            $this->assertLessThan($previous, $id);
            $previous = $id;
        }
    }

    public function testGetSimple()
    {
        $apiContext = $this->getContext($this->context);
        $response   = $apiContext->get('asset_downloads');
        $this->assertPayload($response);
    }

    public function testGetStartLimit()
    {
        $apiContext = $this->getContext($this->context);
        $response   = $apiContext->get('asset_downloads', 1, 2);
        $this->assertPayload($response);
        $this->assertTrue((count($response[$this->context])) <= 2);
    }

    public function testGetOrderSimple()
    {
        $apiContext = $this->getContext($this->context);
        $orderWithoutDir = array(
            array(
                'col' => 'date_download'
            )
        );

        $response = $apiContext->get('asset_downloads', 0, 10, $orderWithoutDir);
        $this->assertPayload($response);
        $this->assertGreaterThanIdsInArray($response);
    }

    public function testGetOrderAsc()
    {
        $apiContext = $this->getContext($this->context);
        $orderWithDir = array(
            array(
                'col' => 'id',
                'dir' => 'asc'
            )
        );

        $response = $apiContext->get('asset_downloads', 0, 10, $orderWithDir);
        $this->assertPayload($response);
        $this->assertGreaterThanIdsInArray($response);
    }

    public function testGetOrderDesc()
    {
        $apiContext = $this->getContext($this->context);
        $orderWithDirDesc = array(
            array(
                'col' => 'id',
                'dir' => 'DESC'
            )
        );

        $response = $apiContext->get('asset_downloads', 0, 10, $orderWithDirDesc);
        $this->assertPayload($response);
        $this->assertLessThanIdsInArray($response);
    }

    public function testGetWhereEqual()
    {
        $apiContext = $this->getContext($this->context);
        $where = array(
            array(
                'col' => 'id',
                'expr' => 'eq',
                'val' => 3,
            )
        );
        $response = $apiContext->get('asset_downloads', 0, 2, array(), $where);
        $this->assertPayload($response);
        $this->assertTrue((count($response[$this->context])) <= 1);

        // The record might not exist in the database, but in case it does...
        if ((count($response[$this->context])) === 1) {
            $this->assertSame((int) $response[$this->context][0]['id'], $where[0]['val']);
        }
    }

    public function testGetWhereGreaterThan()
    {
        $apiContext = $this->getContext($this->context);
        $where = array(
            array(
                'col' => 'id',
                'expr' => 'gt',
                'val' => 3,
            )
        );
        $response = $apiContext->get('asset_downloads', 0, 2, array(), $where);
        $this->assertPayload($response);

        // The record might not exist in the database, but in case it does...
        if ((count($response[$this->context])) > 0) {
            $this->assertSame((int) $response[$this->context][0]['id'], ($where[0]['val'] + 1));
        }
    }
}
