<?php

/*
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Api;

/*
 * Emails Context
 */
class Focus extends Api
{
    protected $endpoint = 'focus';

    protected $listName = 'focus';

    protected $itemName = 'focus';

    protected $searchCommands = [
        'ids',
        'is:published',
        'is:unpublished',
        'is:mine',
        'is:uncategorized',
        'category',
    ];
}
