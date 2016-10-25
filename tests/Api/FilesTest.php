<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class FilesTest extends MauticApiTestCase
{
    // public function testGet()
    // {
    //     $apiContext = $this->getContext('files');
    //     $response     = $apiContext->get(1);

    //     $message = isset($response['error']) ? $response['error']['message'] : '';
    //     $this->assertFalse(isset($response['error']), $message);
    // }

    public function testGetList()
    {
        $apiContext = $this->getContext('files');
        $response     = $apiContext->getList();

        $this->assertErrors($response);
    }

    public function testGetListAssetFiles()
    {
        $apiContext = $this->getContext('files');
        $apiContext->setFolder('assets');
        $response     = $apiContext->getList();

        $this->assertErrors($response);
    }

     public function testCreateAndDelete()
     {
         $apiContext = $this->getContext('files');
         $testFile   = dirname(__DIR__).'/'.'mauticlogo.png';

         $this->assertTrue(file_exists($testFile), 'A file for test at '.$testFile.' does not exist.');

         $file = $apiContext->create(
             array(
                 'file'  => $testFile
             )
         );

         $message = isset($file['error']) ? $file['error']['message'] : '';
         $this->assertFalse(isset($file['error']), $message);

         //now delete the file
         $response = $apiContext->delete($file['file']);

         $message = isset($response['error']) ? $response['error']['message'] : '';
         $this->assertFalse(isset($response['error']), $message);
     }
}
