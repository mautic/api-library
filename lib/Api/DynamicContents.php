<?php

/**
 * @copyright   2016 Mautic, NP. All rights reserved.
 * @author      Mautic
 *
 * @see        http://mautic.org
 *
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Api;

/**
 * Dynamiccontents Context.
 */
class DynamicContents extends Api
{
    protected $endpoint = 'dynamiccontents';

    protected $listName = 'dynamicContents';

    protected $itemName = 'dynamicContent';

    protected $searchCommands = [
        'ids',
        'is:published',
        'is:unpublished',
        'is:mine',
        'is:uncategorized',
        'category',
        'lang',
    ];
}
