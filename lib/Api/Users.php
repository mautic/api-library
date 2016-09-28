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
class Users extends Api
{

    /**
     * {@inheritdoc}
     */
    protected $endpoint = 'users';

    /**
     * Get a list of users
     *
     * @return array|mixed
     */
    public function getUsers()
    {
        return $this->makeRequest('users');
    }

    
    /**
     * Get a current user
     *
     * @return array|mixed
     */
    public function getSelfUser()
    {
    	return $this->makeRequest('users/self');
    }   
    
    /**
     * Get a user
     *
     * @return array|mixed
     */
    public function getUser($id)
    {
    	return $this->makeRequest('users/'.$id);
    } 
    
    /**
     * Create new user
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
    public function addUser($firstName,$lastName,$email,$password,$role)
    {
		$parameters = array (
				'username' => $email,
				'firstName' => $firstName,
				'lastName' => $lastName,
				'email' => $email,
				'plainPassword' => $password,
				'role' => $role,
				'isPublished' => true 
		);
    
    	$args = array('firstName', 'lastName', 'email', 'isPublished','role','username','plainPassword');
    
    	foreach ($args as $arg) {
    		if (!empty($$arg)) {
    			$parameters[$arg] = $$arg;
    		}
    	}
    
    	return $this->makeRequest('users/add', $parameters, 'POST');
    }
    
}
