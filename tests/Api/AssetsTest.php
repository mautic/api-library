<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class AssetsTest extends MauticApiTestCase
{
    public function testGet()
    {
        $apiContext = $this->getContext('assets');
        $response     = $apiContext->get(1);

        $this->assertErrors($response);
    }

    public function testGetList()
    {
        $apiContext = $this->getContext('assets');
        $response     = $apiContext->getList();

        $this->assertErrors($response);
    }

//     public function testCreateAndDelete()
//     {
//         $apiContext = $this->getContext('assets');
//         $testFile   = dirname(__DIR__).'/'.'mauticlogo.png';

//         $this->assertTrue(file_exists($testFile), 'A file for test at '.$testFile.' does not exist.');

//         $asset = $apiContext->create(
//             array(
//                 'title' => 'Mautic Logo',
//                 'file'  => $testFile,
//                 'storageLocation' => 'local'
//             )
//         );

//         $message = isset($asset['error']) ? $asset['error']['message'] : '';
//         $this->assertFalse(isset($asset['error']), $message);
// echo "<pre>";var_dump($asset);die("</pre>");
//         //now delete the asset
//         $response = $apiContext->delete($asset['asset']['id']);

//         $message = isset($response['error']) ? $response['error']['message'] : '';
//         $this->assertFalse(isset($response['error']), $message);
//     }
}
