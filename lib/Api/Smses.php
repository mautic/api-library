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
 * Smses Context.
 */
class Smses extends Api
{
    /**
     * {@inheritdoc}
     */
    protected $endpoint = 'smses';

    /**
     * {@inheritdoc}
     */
    protected $listName = 'smses';

    /**
     * {@inheritdoc}
     */
    protected $itemName = 'sms';

    /**
     * {@inheritdoc}
     */
    protected $searchCommands = [
        'ids',
        'is:published',
        'is:unpublished',
        'is:mine',
        'is:uncategorized',
        'category',
        'lang',
    ];

    /**
     * Send sms to a specific contact.
     *
     * @param int $id
     * @param int $contactId
     *
     * @return array|mixed
     */
    public function sendToContact($id, $contactId)
    {
        return $this->makeRequest($this->endpoint.'/'.$id.'/contact/'.$contactId.'/send');
    }
}
