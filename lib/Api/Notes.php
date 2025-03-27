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
 * Notes Context.
 */
class Notes extends Api
{
    protected $endpoint = 'notes';

    protected $listName = 'notes';

    protected $itemName = 'note';

    protected $searchCommands = [
        'ids',
    ];
}
