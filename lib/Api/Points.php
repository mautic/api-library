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
     * {@inheritdoc}
     */
    protected $listName = 'points';

    /**
     * {@inheritdoc}
     */
    protected $itemName = 'point';

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
     * [apply_rule description]
     * @param  [int] $PointsId [id des regles de points]
     * @param  [int] $LeadId   [id du lead]
     * @return []           []
     */
    public function apply_rule($PointsId, $LeadId) {
    	return $this->makeRequest('points/'.$PointsId.'/lead/'.$LeadId);
    }
}
