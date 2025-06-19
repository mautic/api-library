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
 * Marketing Messages Context.
 */
class Messages extends Api
{
    protected $endpoint = 'messages';

    protected $listName = 'messages';

    protected $itemName = 'message';
}
