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
 * Webhooks Context.
 */
class Webhooks extends Api
{
    protected $endpoint = 'hooks';

    protected $listName = 'hooks';

    protected $itemName = 'hook';

    protected $searchCommands = [
        'ids',
        'is:published',
        'is:unpublished',
        'is:mine',
        'is:uncategorized',
        'category',
    ];

    /**
     * Get list of available webhook triggers.
     *
     * @return array|mixed
     */
    public function getTriggers()
    {
        return $this->makeRequest($this->endpoint.'/triggers');
    }
}
