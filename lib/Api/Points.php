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
class Points extends Api
{
    protected $endpoint = 'points';

    protected $listName = 'points';

    protected $itemName = 'point';

    protected $searchCommands = [
        'ids',
    ];

    /**
     * Get list of available action types.
     *
     * @return array|mixed
     */
    public function getPointActionTypes()
    {
        return $this->makeRequest($this->endpoint.'/actions/types');
    }
}
