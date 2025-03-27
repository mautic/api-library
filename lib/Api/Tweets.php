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
 * Tweets Context.
 */
class Tweets extends Api
{
    protected $endpoint = 'tweets';

    protected $listName = 'tweets';

    protected $itemName = 'tweet';

    protected $searchCommands = [];
}
