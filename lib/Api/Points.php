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
    public function create(array $parameters)
    {
        return $this->actionNotSupported('create');
    }

    /**
     * {@inheritdoc}
     */
    public function edit($id, array $parameters, $createIfNotExists = false)
    {
        return $this->actionNotSupported('edit');
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        return $this->actionNotSupported('delete');
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
