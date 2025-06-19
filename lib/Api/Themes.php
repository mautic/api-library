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
 * Themes Context.
 */
class Themes extends Api
{
    protected $endpoint = 'themes';

    protected $listName = 'themes';

    protected $itemName = 'theme';

    protected $temporaryFilePath;

    public function edit($id, array $parameters, $createIfNotExists = false)
    {
        return $this->actionNotSupported('edit');
    }

    /**
     * @return array|mixed
     */
    public function create(array $parameters)
    {
        if (!isset($parameters['file'])) {
            throw new \InvalidArgumentException('theme zip file must be set in parameters');
        }

        return parent::create($parameters);
    }

    public function createBatch(array $parameters)
    {
        return $this->actionNotSupported('createBatch');
    }

    public function editBatch(array $parameters, $createIfNotExists = false)
    {
        return $this->actionNotSupported('editBatch');
    }

    public function deleteBatch(array $ids)
    {
        return $this->actionNotSupported('deleteBatch');
    }

    /**
     * @return null
     */
    public function getTemporaryFilepath()
    {
        return $this->temporaryFilePath ?: sys_get_temp_dir();
    }

    /**
     * @param null $temporaryFilePath
     */
    public function setTemporaryFilePath($temporaryFilePath)
    {
        $this->temporaryFilePath = $temporaryFilePath;
    }
}
