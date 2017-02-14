<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Api;

/**
 * Companies Context
 */
class Companies extends Api
{

    /**
     * {@inheritdoc}
     */
    protected $endpoint = 'companies';

    /**
     * {@inheritdoc}
     */
    protected $listName = 'companies';

    /**
     * {@inheritdoc}
     */
    protected $itemName = 'company';

    /**
     * {@inheritdoc}
     */
    protected $searchCommands = array(
        'ids',
        'is:mine',
    );

    /**
     * Add a contact to the company
     *
     * @param int $id        Company ID
     * @param int $contactId Contact ID
     *
     * @return array|mixed
     */
    public function addContact($id, $contactId)
    {
        return $this->makeRequest($this->endpoint.'/'.$id.'/contact/add/'.$contactId, array(), 'POST');
    }

    /**
     * Remove a contact from the company
     *
     * @param int $id        Company ID
     * @param int $contactId Contact ID
     *
     * @return array|mixed
     */
    public function removeContact($id, $contactId)
    {
        return $this->makeRequest($this->endpoint.'/'.$id.'/contact/remove/'.$contactId, array(), 'POST');
    }
}
