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
    //     $result     = $apiContext->get(1);

    //     $message = isset($result['error']) ? $result['error']['message'] : '';
    //     $this->assertFalse(isset($result['error']), $message);
    // }

    public function testGetList()
    {
        $apiContext = $this->getContext('files');
        $result     = $apiContext->getList();

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

    public function testGetListAssetFiles()
    {
        $apiContext = $this->getContext('files');
        $apiContext->setFolder('assets');
        $result     = $apiContext->getList();

        $message = isset($result['error']) ? $result['error']['message'] : '';
        $this->assertFalse(isset($result['error']), $message);
    }

//     public function testCreateAndDelete()
//     {
//         $apiContext = $this->getContext('files');
//         $testFile   = dirname(__DIR__).'/'.'mauticlogo.png';

//         $this->assertTrue(file_exists($testFile), 'A file for test at '.$testFile.' does not exist.');

//         $file = $apiContext->create(
//             array(
//                 'file'  => $testFile
//             )
//         );
// echo "<pre>";var_dump($file);die("</pre>");

//         $message = isset($file['error']) ? $file['error']['message'] : '';
//         $this->assertFalse(isset($file['error']), $message);

//         //now delete the file
//         $result = $apiContext->delete($file['file']['id']);

//         $message = isset($result['error']) ? $result['error']['message'] : '';
//         $this->assertFalse(isset($result['error']), $message);
//     }
}
