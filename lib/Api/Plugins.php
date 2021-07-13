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
 * Plugins Context
 */
class Plugins extends Api
{
    /**
     * {@inheritdoc}
     */
    protected $endpoint = 'plugins';

    /**
     * @return array
     */
    public function reload()
    {
        return $this->makeRequest($this->endpoint.'/reload');
    }

    /**
     * @param $integrationName
     *
     * @return array
     */
    public function getSettings($integrationName)
    {
        return $this->makeRequest($this->endpoint.'/settings/'.$integrationName);
    }

    /**
     * @param $package
     *
     * @return array
     */
    public function install($package)
    {
        @set_time_limit(9999);
        return $this->makeRequest($this->endpoint.'/install', ['package' => $package]);
    }

}
