<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

class FocusTest extends MauticApiTestCase
{
    public function setUp()
    {
        $this->api = $this->getContext('focus');
        $this->testPayload = array(
            'name' => 'test',
            'type' => 'notice',
            'website' => 'http://',
            'style' => 'bar',
            'properties' => array(
                array(
                    'bar' => array(
                        array(
                            'allow_hide' => 1,
                            'sticky' => 1,
                            'size' => 'large',
                            'placement' => 'top',
                        ),
                    ),
                    'modal' => array(
                        array(
                            'placement' => 'top',
                        ),
                    ),
                    'notification' => array(
                        array(
                            'placement' => 'top_left',
                        ),
                    ),
                    'page' => array(),
                    'animate' => 1,
                    'link_activation' => 1,
                    'colors' => array(
                        array(
                            'primary' => '27184e',
                            'text' => '',
                            'button' => '',
                            'button_text' => '',
                        ),
                    ),
                    'content' => array(
                        array(
                            'headline' => '27184e',
                            'tagline' => '',
                            'link_text' => '',
                            'link_url' => '',
                            'link_new_window' => '',
                            'font' => 'Arial, Helvetica, sans-serif',
                        ),
                    ),
                    'when' => 'immediately',
                    'timeout' => '',
                    'frequency' => 'everypage',
                    'stop_after_conversion' => 1,
                ),
                'form' => '',
                'htmlMode' => '1',
                'html' => '<div><strong style="color:red">html mode enabled</strong></div>',
                'css' => '.mf-bar-collapser {border-radius: 0 !important}',
            ),
        );
    }

    public function testGetList()
    {
        $this->standardTestGetList();
    }

    public function testGetListOfSpecificIds()
    {
        $this->standardTestGetListOfSpecificIds();
    }

    public function testCreateGetAndDelete()
    {
        $this->standardTestCreateGetAndDelete();
    }

    public function testEditPatch()
    {
        $editTo = array(
            'name' => 'test2',
        );
        $this->standardTestEditPatch($editTo);
    }

    public function testEditPut()
    {
        $this->standardTestEditPut();
    }

    public function testBatchEndpoints()
    {
        $this->standardTestBatchEndpoints();
    }
}
