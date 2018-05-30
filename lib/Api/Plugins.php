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
     * {@inheritdoc}
     */
    protected $listName = 'plugins';

    /**
     * {@inheritdoc}
     */
    protected $itemName = 'plugin';

    /**
     * Get settings of a plugin
     *
     * @return array|mixed
     */
    public function getPluginSettings($integrationName)
    {
        return $this->makeRequest($this->endpoint.'/settings/'.$integrationName);
    }
}
