<?php

/**
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 *
 * @see        http://mautic.org
 *
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class ReportsTest extends MauticApiTestCase
{
    public function setUp(): void
    {
        $this->api = $this->getContext('reports');
    }

    public function testGet()
    {
        $response = $this->api->get(1);

        $this->assertErrors($response);
        $this->assertGreaterThanOrEqual(0, $response['totalResults']);
        $this->assertGreaterThanOrEqual(0, count($response['dataColumns']));
        $this->assertSame(0, $response['limit']);
        $this->assertSame(1, $response['page']);
    }

    public function testGetCustom()
    {
        $limit    = 5;
        $page     = 2;
        $dateFrom = new \DateTime('1 year ago', new \DateTimeZone('UTC'));
        $dateTo   = new \DateTime('now', new \DateTimeZone('UTC'));
        $response = $this->api->get(1, $limit, $page, $dateFrom, $dateTo);

        $this->assertErrors($response);
        $this->assertGreaterThanOrEqual(0, $response['totalResults']);
        $this->assertGreaterThanOrEqual(0, count($response['dataColumns']));
        $this->assertSame($limit, $response['limit']);
        $this->assertSame($page, $response['page']);
        // Mautic will modify the times slightly so check only for date.
        $this->assertSame($dateFrom->format('Y-m-d'), (new \DateTime($response['dateFrom']))->format('Y-m-d'), 'DateFrom does not match');
        $this->assertSame($dateTo->format('Y-m-d'), (new \DateTime($response['dateTo']))->format('Y-m-d'), 'DateTo does not match');
    }

    public function testGetList()
    {
        $this->standardTestGetList();
    }
}
