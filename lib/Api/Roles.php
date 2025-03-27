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
 * Roles Context.
 */
class Roles extends Api
{
    protected $endpoint = 'roles';

    protected $listName = 'roles';

    protected $itemName = 'role';

    protected $searchCommands = [
        'ids',
        'is:admin',
        'name',
    ];
}
