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

class AssetsTest extends MauticApiTestCase
{
    protected $skipPayloadAssertion = ['file'];

    protected $mediaFolder = 'media';

    public function setUp(): void
    {
        $this->api         = $this->getContext('assets');
        $this->testPayload = [
            'title'           => 'Mautic Logo sent as a API request',
            'storageLocation' => 'remote',
            'file'            => 'https://www.mautic.org/media/logos/logo/Mautic_Logo_DB.pdf',
        ];

        if ('4' == $this->mauticVersion) {
            $this->mediaFolder = 'assets';
        }
    }

    public function testGetList()
    {
        $this->standardTestGetList();
    }

    public function testGetListOfSpecificIds()
    {
        $this->standardTestGetListOfSpecificIds();
    }

    public function testCreateWithLocalFileGetAndDelete()
    {
        // Upload a testing file
        $apiFiles = $this->getContext('files');
        $apiFiles->setFolder('assets');
        $fileRequest = [
            'file' => dirname(__DIR__).'/mauticlogo.png',
        ];
        $response = $apiFiles->create($fileRequest);
        $this->assertErrors($response);
        $file = $response['file'];

        // Build local file payload
        $testPayload                    = $this->testPayload;
        $testPayload['storageLocation'] = 'local';
        $testPayload['file']            = $file['name'];

        // Create Asset
        $response = $this->api->create($testPayload);
        $this->assertPayload($response, $testPayload);

        $response = $this->api->get($response[$this->api->itemName()]['id']);
        $this->assertPayload($response, $testPayload);

        // Delete Asset
        $response = $this->api->delete($response[$this->api->itemName()]['id']);
        $this->assertErrors($response);
    }

    public function testCreateWithRemoteFileGetAndDelete()
    {
        $this->standardTestCreateGetAndDelete();
    }

    public function testBatchEndpoints()
    {
        $this->standardTestBatchEndpoints();
    }

    public function testEditPut()
    {
        $this->standardTestEditPut();
    }
}
