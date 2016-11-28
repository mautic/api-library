<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api;

use Mautic\Tests\Api;

class CompanyFieldTest extends ContactFieldsTest
{
    protected $context = 'companyFields';
    protected $prefix = 'Company ';

    // Methods inherited from ContactFieldsTest


    // Except following, ignore them
    public function testBooleanField()
    {
        return;
    }

    public function testDefaultFieldValue()
    {
        return;
    }
}
