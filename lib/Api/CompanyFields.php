<?php

/**
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 *
 * @see        http://mautic.org
 *
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Api;

/**
 * CompanyFields Context.
 */
class CompanyFields extends Api
{
    protected $endpoint = 'fields/company';

    protected $listName = 'fields';

    protected $itemName = 'field';
}
