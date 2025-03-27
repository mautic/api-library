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
 * Categories Context.
 */
class Categories extends Api
{
    protected $endpoint = 'categories';

    protected $listName = 'categories';

    protected $itemName = 'category';

    protected $searchCommands = [
        'ids',
        'is:published',
        'is:unpublished',
    ];
}
