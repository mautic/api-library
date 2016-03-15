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
 *
 * This class is deprecated and will be removed in future versions! Use Contacts instead!
 */
class Leads extends Contacts
{
    /**
     * Get a list of lead segments
     *
     * @return array|mixed
     */
    public function getLists()
    {
        return $this->makeRequest('contacts/list/segments');
    }

    /**
     * Get a list of a lead's notes
     *
     * @param int    $id Contact ID
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

        return $this->makeRequest('contacts/'.$id.'/notes', $parameters);
    }

    /**
     * Get a segment of smart segments the lead is in
     *
     * @param $id
     */
    public function getLeadLists($id)
    {
        return $this->makeRequest('contacts/'.$id.'/segments');
    }

    /**
     * Get a segment of campaigns the lead is in
     *
     * @param $id
     */
    public function getLeadCampaigns($id)
    {
        return $this->makeRequest('contacts/'.$id.'/campaigns');
    }
    
    /**
     * Change the number of points a lead
     * 
     * @param int $leadId
     * @param int $points
     * @return mixed
     */
    public function setPointsToLead($leadId, $points) {
    	return $this->makeRequest('leads/'.$leadId.'/setpoints/'.$points);
    }
    
    /**
     * Add points a lead
     *
     * @param int $leadId
     * @param int $points
     * @return mixed
     */
    public function addPointsToLead($leadId, $points) {
    	return $this->makeRequest('leads/'.$leadId.'/addpoints/'.$points);
    }
    
    /**
     * Remove points a lead
     *
     * @param int $leadId
     * @param int $points
     * @return mixed
     */
    public function removePointsToLead($leadId, $points) {
    	return $this->makeRequest('leads/'.$leadId.'/removepoints/'.$points);
    }
    
}
