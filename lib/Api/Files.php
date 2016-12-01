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
 * Files Context
 */
class Files extends Api
{

    /**
     * {@inheritdoc}
     */
    protected $endpoint = 'files/images';

    /**
     * Changes the file folder to look at
     *
     * @param string $folder [images, assets]
     */
    public function setFolder($folder = 'assets')
    {
        $folder = str_replace('/', '.', $folder);
        $this->endpoint = 'files/'.$folder;
    }

    /**
     * {@inheritdoc}
     */
    public function edit($id, array $parameters, $createIfNotExists = false)
    {
        return $this->actionNotSupported('edit');
    }
}
