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
 * Smses Context
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
    protected $searchCommands = array(
        'ids',
        'is:published',
        'is:unpublished',
        'is:mine',
        'is:uncategorized',
        'category',
        'lang',
    );

    /**
     * @var array
     */
    protected $bcRegexEndpoints = array(
        'smses/(.*?)/contact/(.*?)/send'    => 'smses/$1/contact/$2/send',
    );

    /**
     * Send a message to contact
     *
     * @param int    $id
     * @param int    $recipientId
     *
     * @return mixed
     */
    public function sendSMS($id, $recipientId) {
        return $this->makeRequest($this->endpoint.'/'. intval($id) .'/contact/' . intval($recipientId) . '/send');        
    }
}
