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
