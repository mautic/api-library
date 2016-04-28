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
 * Data Context
 */
class Data extends Api
{

    /**
     * {@inheritdoc}
     */
    protected $endpoint = 'data';

    /**
     * Get a single item
     *
     * @param int   $id
     * @param array $options
     *
     * @return array|mixed
     */
    public function get($id, $options)
    {
        return $this->makeRequest("{$this->endpoint}/$id", $options);
    }
}
