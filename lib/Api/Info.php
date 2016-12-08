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
 * Forms Context
 */
class Info extends Api
{

    /**
     * {@inheritdoc}
     */
    protected $endpoint = 'info';

    /**
     * Get the current Mautic version number
     *
     * @return array|mixed
     */
    public function getVersion()
    {
        return $this->makeRequest($this->endpoint.'/version');
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        return $this->actionNotSupported('get');
    }

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
}
