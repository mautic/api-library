<?php
/**
 * @package     Mautic
 * @copyright   2016 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Api;

/**
 * CampaignEvents Context
 */
class CampaignEvents extends Api
{

    /**
     * {@inheritdoc}
     */
    protected $endpoint = 'campaigns/events';

    /**
     * {@inheritdoc}
     */
    protected $listName = 'events';

    /**
     * {@inheritdoc}
     */
    protected $itemName = 'event';
}
