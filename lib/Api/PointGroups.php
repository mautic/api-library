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
 * Points Context.
 */
class PointGroups extends Api
{
    protected $endpoint = 'points/groups';

    protected $listName = 'pointGroups';

    protected $itemName = 'pointGroup';

    protected $searchCommands = [
        'ids',
        'is:published',
        'is:unpublished',
    ];
}
