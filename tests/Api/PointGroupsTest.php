<?php

declare(strict_types=1);

namespace Mautic\Tests\Api;

class PointGroupsTest extends MauticApiTestCase
{
    public function setUp(): void
    {
        $this->api         = $this->getContext('pointGroups');
        $this->testPayload = [
            'name'        => 'New Point Group',
            'description' => 'Description of the new point group',
        ];
    }

    public function testGetList(): void
    {
        $this->standardTestGetList();
    }

    public function testGetListOfSpecificIds(): void
    {
        $this->standardTestGetListOfSpecificIds();
    }

    public function testCreateGetAndDelete(): void
    {
        $this->standardTestCreateGetAndDelete();
    }

    public function testEditPatch(): void
    {
        $editTo = [
            'name' => 'Updated Point Group Name',
        ];
        $this->standardTestEditPatch($editTo);
    }

    public function testEditPut(): void
    {
        $this->standardTestEditPut();
    }

    public function testBatchEndpoints(): void
    {
        $this->standardTestBatchEndpoints();
    }
}
