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
 * ContactFields Context.
 */
class ContactFields extends Api
{
    protected $endpoint = 'fields/contact';

    protected $listName = 'fields';

    protected $itemName = 'field';
}
