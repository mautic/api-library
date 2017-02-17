<?php
/*
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Api;

/*
 * Emails Context
 */
class Focus extends Api
{

    /**
     * {@inheritdoc}
     */
    protected $endpoint = 'focus';

    /**
     * {@inheritdoc}
     */
    protected $listName = 'focusitems';

    /**
     * {@inheritdoc}
     */
    protected $itemName = 'focusitem';


    /**
     * @param $id
     * @return array|mixed
     */
    public function generateJs($id)
    {
        return $this->makeRequest($this->endpoint.'/'.$id.'/js', [] , 'POST');
    }

}
