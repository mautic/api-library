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
 * Users Context
 */
class Users extends Api
{

    /**
     * {@inheritdoc}
     */
    protected $endpoint = 'users';

    /**
     * Get your (API) user
     *
     * @return array|mixed
     */
    public function getSelf()
    {
        return $this->makeRequest($this->endpoint.'/self');
    }

    /**
     * Get list of permissions for a user
     *
     * @param  int $id
     *
     * @return array|mixed
     */
    public function checkPermission($id)
    {
        return $this->makeRequest($this->endpoint.'/'.$id.'/permissioncheck');
    }

    /**
     * Get list of available roles
     *
     * @return array|mixed
     */
    public function getListRoles()
    {
        return $this->makeRequest($this->endpoint.'/self');
    }
}