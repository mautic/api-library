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
 * Points Context.
 */
class PointGroups extends Api
{
    /**
     * {@inheritdoc}
     */
    protected $endpoint = 'points/groups';

    /**
     * {@inheritdoc}
     */
    protected $listName = 'pointGroups';

    /**
     * {@inheritdoc}
     */
    protected $itemName = 'pointGroup';

    /**
     * {@inheritdoc}
     */
    protected $searchCommands = [
        'ids',
        'is:published',
        'is:unpublished',
    ];
}
