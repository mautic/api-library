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
 * Leads Context
 */
class Leads extends Api
{

    /**
     * {@inheritdoc}
     */
    protected $endpoint = 'leads';

    /**
     * Get a list of users available as lead owners
     *
     * @return array|mixed
     */
    public function getOwners()
    {
        return $this->makeRequest('leads/list/owners');
    }

    /**
     * Get a list of custom fields
     *
     * @return array|mixed
     */
    public function getFieldList()
    {
        return $this->makeRequest('leads/list/fields');
    }

    /**
     * Get a list of lead lists
     *
     * @return array|mixed
     */
    public function getLists()
    {
        return $this->makeRequest('leads/list/lists');
    }

    /**
     * Get a list of a lead's notes
     *
     * @param int    $id Lead ID
     * @param string $search
     * @param int    $start
     * @param int    $limit
     * @param string $orderBy
     * @param string $orderByDir
     * @param bool   $publishedOnly
     *
     * @return array|mixed
     */
    public function getLeadNotes($id, $search = '', $start = 0, $limit = 0, $orderBy = '', $orderByDir = 'ASC')
    {
        $parameters = array();

        $args = array('search', 'start', 'limit', 'orderBy', 'orderByDir');

        foreach ($args as $arg) {
            if (!empty($$arg)) {
                $parameters[$arg] = $$arg;
            }
        }

        return $this->makeRequest('leads/'.$id.'/notes', $parameters);
    }

    /**
     * Get a list of smart lists the lead is in
     *
     * @param $id
     */
    public function getLeadLists($id)
    {
        return $this->makeRequest('leads/'.$id.'/lists');
    }

    /**
     * Get a list of campaigns the lead is in
     *
     * @param $id
     */
    public function getLeadCampaigns($id)
    {
        return $this->makeRequest('leads/'.$id.'/campaigns');
    }
    
    /**
     * Change the number of points a lead
     * 
     * @param int $leadId
     * @param int $points
     * @param array $parameters 'eventname' and 'actionname'
     * @return mixed
     */
    public function setPoints($id, $points, array $parameters = array()) {
        return $this->makeRequest('leads/'.$id.'/setpoints/'.$points, $parameters, 'PATCH');
    }
    
    /**
     * Add points a lead
     *
     * @param int $leadId
     * @param int $points
     * @param array $parameters 'eventname' and 'actionname'
     * @return mixed
     */
    public function addPoints($id, $points, array $parameters = array()) {     
        return $this->makeRequest('leads/'.$id.'/addpoints/'.$points, $parameters, 'PATCH');
    }
    
    /**
     * Remove points a lead
     *
     * @param int $leadId
     * @param int $points
     * @param array $parameters 'eventname' and 'actionname'
     * @return mixed
     */
    public function subtractPoints($id, $points, array $parameters = array()) {
        return $this->makeRequest('leads/'.$id.'/subtractpoints/'.$points, $parameters, 'PATCH');
    }
}
