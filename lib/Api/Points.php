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
 * Points Context
 */
class Points extends Api
{

    /**
     * {@inheritdoc}
     */
    protected $endpoint = 'points';

    /**
     * Get list of available action types
     *
     * @return array|mixed
     */
    public function getPointActionTypes()
    {
        return $this->makeRequest($this->endpoint.'/actions/types');
    }

    /**
     * only point rules "mautic.api_call" ca be applied throught this method
     *
     * @param [int] $pointsId
     * @param [int] $leadId
     * @param [array] $parameters No specific parameters configured
     *
     * @return []           []
     */
    public function applyRule($pointsId, $leadId, array $parameters = array()) {
    	return $this->makeRequest('points/'.$pointsId.'/lead/'.$leadId, $parameters, 'PATCH');
    }
}
