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
 * Companies Context.
 */
class Companies extends Api
{
    protected $endpoint = 'companies';

    protected $listName = 'companies';

    protected $itemName = 'company';

    /**
     * @var array
     */
    protected $bcRegexEndpoints = [
        'companies/(.*?)/contact/(.*?)/add'    => 'companies/$1/contact/add/$2', // 2.6.0
        'companies/(.*?)/contact/(.*?)/remove' => 'companies/$1/contact/remove/$2', // 2.6.0
    ];

    protected $searchCommands = [
        'ids',
        'is:mine',
    ];

    /**
     * Add a contact to the company.
     *
     * @param int $id        Company ID
     * @param int $contactId Contact ID
     *
     * @return array|mixed
     */
    public function addContact($id, $contactId)
    {
        return $this->makeRequest($this->endpoint.'/'.$id.'/contact/'.$contactId.'/add', [], 'POST');
    }

    /**
     * Remove a contact from the company.
     *
     * @param int $id        Company ID
     * @param int $contactId Contact ID
     *
     * @return array|mixed
     */
    public function removeContact($id, $contactId)
    {
        return $this->makeRequest($this->endpoint.'/'.$id.'/contact/'.$contactId.'/remove', [], 'POST');
    }
}
