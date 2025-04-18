<?php

/**
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 *
 * @see        http://mautic.org
 *
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Api;

/**
 * Users Context.
 */
class Users extends Api
{
    protected $endpoint = 'users';

    protected $listName = 'users';

    protected $itemName = 'user';

    protected $searchCommands = [
        'ids',
        'is:admin',
        'is:active',
        'is:inactive',
        'email',
        'role',
        'username',
        'name',
        'position',
    ];

    /**
     * Get your (API) user.
     *
     * @return array|mixed
     */
    public function getSelf()
    {
        return $this->makeRequest($this->endpoint.'/self');
    }

    /**
     * Get list of permissions for a user.
     *
     * @param int          $id
     * @param string|array $permissions
     *
     * @return array|mixed
     */
    public function checkPermission($id, $permissions)
    {
        return $this->makeRequest($this->endpoint.'/'.$id.'/permissioncheck', ['permissions' => $permissions], 'POST');
    }
}
