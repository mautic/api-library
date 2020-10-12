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

class PagesTest extends MauticApiTestCase
{
    public function setUp(): void
    {
        $this->api         = $this->getContext('pages');
        $this->testPayload = [
            'title'      => 'test',
            'template'   => 'blank',
            'customHtml' => '<!DOCTYPE html>
<html lang="en" dir="ltr">
    <head>
        <script>console.log(\'hi there\')</script>
    </head>
    <body>
        <div data-section-wrapper="1">
            <center>
                <table data-section="1" style="margin: 0 auto;border-collapse: collapse !important;width: 600px;" cellpadding="0" cellspacing="0" width="600" class="w320">
                    <tr>
                        <td style="font-size: 30px;text-align: center;font-family: \'Droid Sans\', \'Helvetica Neue\', \'Arial\', \'sans-serif\' !important;font-weight: 400;" data-slot-container="1">
                            <div data-slot="text">
                                Awesome Co
                            </div>
                        </td>
                    </tr>
                </table>
            </center>
        </div>
    </body>
</html>',
        ];
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
        $editTo = [
            'title' => 'test2',
        ];
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
