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
 * Contacts Context
 */
class Contacts extends Api
{

    /**
     * Contact unsubscribed themselves.
     */
    const UNSUBSCRIBED = 1;

    /**
     * Contact was unsubscribed due to an unsuccessful send.
     */
    const BOUNCED = 2;

    /**
     * Contact was manually unsubscribed by user.
     */
    const MANUAL = 3;

    /**
     * {@inheritdoc}
     */
    protected $endpoint = 'contacts';

    /**
     * {@inheritdoc}
     */
    protected $listName = 'contacts';

    /**
     * {@inheritdoc}
     */
    protected $itemName = 'contact';

    /**
     * Get a list of users available as contact owners
     *
     * @return array|mixed
     */
    public function getOwners()
    {
        return $this->makeRequest($this->endpoint.'/list/owners');
    }

    /**
     * Get a list of custom fields
     *
     * @return array|mixed
     */
    public function getFieldList()
    {
        return $this->makeRequest($this->endpoint.'/list/fields');
    }

    /**
     * Get a list of contact segments
     *
     * @return array|mixed
     */
    public function getSegments()
    {
        return $this->makeRequest($this->endpoint.'/list/segments');
    }

    /**
     * Get a list of a contact's engagement events
     *
     * @param int    $id Contact ID
     * @param string $search
     * @param array  $includeEvents
     * @param array  $excludeEvents
     * @param string $orderBy
     * @param string $orderByDir
     * @param int    $page
     *
     * @return array|mixed
     */
    public function getEvents($id, $search = '', array $includeEvents = array(), array $excludeEvents = array(), $orderBy = '', $orderByDir = 'ASC', $page = 1)
    {
        $parameters = array(
            'filters' => array(
                'search' => $search,
                'includeEvents' => $includeEvents,
                'excludeEvents' => $excludeEvents,
            ),
            'order' => array(
                $orderBy,
                $orderByDir,
            ),
            'page' => $page
        );

        return $this->makeRequest(
            $this->endpoint.'/'.$id.'/events',
            $parameters
        );
    }

    /**
     * Get a list of a contact's notes
     *
     * @param int    $id Contact ID
     * @param string $search
     * @param int    $start
     * @param int    $limit
     * @param string $orderBy
     * @param string $orderByDir
     *
     * @return array|mixed
     */
    public function getContactNotes($id, $search = '', $start = 0, $limit = 0, $orderBy = '', $orderByDir = 'ASC')
    {
        $parameters = array();

        $args = array('search', 'start', 'limit', 'orderBy', 'orderByDir');

        foreach ($args as $arg) {
            if (!empty($$arg)) {
                $parameters[$arg] = $$arg;
            }
        }

        return $this->makeRequest($this->endpoint.'/'.$id.'/notes', $parameters);
    }

    /**
     * Get a list of a contact's devices
     *
     * @param int    $id Contact ID
     * @param string $search
     * @param int    $start
     * @param int    $limit
     * @param string $orderBy
     * @param string $orderByDir
     *
     * @return array|mixed
     */
    public function getContactDevices($id, $search = '', $start = 0, $limit = 0, $orderBy = '', $orderByDir = 'ASC')
    {
        $parameters = array();

        $args = array('search', 'start', 'limit', 'orderBy', 'orderByDir');

        foreach ($args as $arg) {
            if (!empty($$arg)) {
                $parameters[$arg] = $$arg;
            }
        }

        return $this->makeRequest($this->endpoint.'/'.$id.'/devices', $parameters);
    }

    /**
     * Get a list of smart segments the contact is in
     *
     * @param $id
     *
     * @return array|mixed
     */
    public function getContactSegments($id)
    {
        return $this->makeRequest($this->endpoint.'/'.$id.'/segments');
    }

    /**
     * Get a list of companies the contact is in
     *
     * @param $id
     *
     * @return array|mixed
     */
    public function getContactCompanies($id)
    {
        return $this->makeRequest($this->endpoint.'/'.$id.'/companies');
    }

    /**
     * Get a list of campaigns the contact is in
     *
     * @param $id
     *
     * @return array|mixed
     */
    public function getContactCampaigns($id)
    {
        return $this->makeRequest($this->endpoint.'/'.$id.'/campaigns');
    }

    /**
     * Add the points to a contact
     *
     * @param int $id
     * @param int $points
     * @param array $parameters 'eventName' and 'actionName'
     *
     * @return mixed
     */
    public function addPoints($id, $points, array $parameters = array()) {
        return $this->makeRequest('contacts/'.$id.'/points/plus/'.$points, $parameters, 'POST');
    }

    /**
     * Subtract points from a contact
     *
     * @param int $id
     * @param int $points
     * @param array $parameters 'eventName' and 'actionName'
     *
     * @return mixed
     */
    public function subtractPoints($id, $points, array $parameters = array()) {
        return $this->makeRequest('contacts/'.$id.'/points/minus/'.$points, $parameters, 'POST');
    }

    /**
     * Adds Do Not Contact
     *
     * @param int    $id
     * @param string $channel
     *
     * @return mixed
     */
    public function addDnc($id, $channel = 'email', $reason = Contact::MANUAL, $channelId = null, $comments = 'via API') {

        return $this->makeRequest(
            'contacts/'.$id.'/dnc/add/'.$channel,
            array(
                'reason' => $reason,
                'channelId' => $channelId,
                'comments' => $comments,
            ),
            'POST'
        );
    }

    /**
     * Removes Do Not Contact
     *
     * @param int    $id
     * @param string $channel
     *
     * @return mixed
     */
    public function removeDnc($id, $channel) {
        return $this->makeRequest(
            'contacts/'.$id.'/dnc/remove/'.$channel,
            array(),
            'POST'
        );
    }
}
