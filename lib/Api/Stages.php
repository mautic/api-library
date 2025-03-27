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
 * Stages Context.
 */
class Stages extends Api
{
    protected $endpoint = 'stages';

    protected $listName = 'stages';

    protected $itemName = 'stage';

    protected $bcRegexEndpoints = [
        'stages/(.*?)/contact/(.*?)/add'    => 'stages/$1/contact/add/$2', // 2.6.0
        'stages/(.*?)/contact/(.*?)/remove' => 'stages/$1/contact/remove/$2', // 2.6.0
    ];

    protected $searchCommands = [
        'ids',
    ];

    /**
     * Add a contact to the stage.
     *
     * @param int $id        Stage ID
     * @param int $contactId Contact ID
     *
     * @return array|mixed
     */
    public function addContact($id, $contactId)
    {
        return $this->makeRequest($this->endpoint.'/'.$id.'/contact/'.$contactId.'/add', [], 'POST');
    }

    /**
     * Remove a contact from the stage.
     *
     * @param int $id        Stage ID
     * @param int $contactId Contact ID
     *
     * @return array|mixed
     */
    public function removeContact($id, $contactId)
    {
        return $this->makeRequest($this->endpoint.'/'.$id.'/contact/'.$contactId.'/remove', [], 'POST');
    }
}
