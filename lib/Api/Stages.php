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
 * Stages Context
 */
class Stages extends Api
{

    /**
     * {@inheritdoc}
     */
    protected $endpoint = 'stages';

    /**
     * Add a contact to the stage
     *
     * @param int $id        Stage ID
     * @param int $contactId Contact ID
     *
     * @return array|mixed
     */
    public function addContact($id, $contactId)
    {
        return $this->makeRequest($this->endpoint.'/'.$id.'/contact/add/'.$contactId, array(), 'POST');
    }

    /**
     * Remove a contact from the stage
     *
     * @param int $id        Stage ID
     * @param int $contactId Contact ID
     *
     * @return array|mixed
     */
    public function removeContact($id, $contactId)
    {
        return $this->makeRequest($this->endpoint.'/'.$id.'/contact/remove/'.$contactId, array(), 'POST');
    }
}
