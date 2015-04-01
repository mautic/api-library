<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Exception;

/**
 * Exception representing an unsupported action
 */
class ActionNotSupportedException extends \Exception
{
    /**
     * {@inheritdoc}
     */
    public function __construct($message = 'Action is not supported at this time.', $code = 500, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
