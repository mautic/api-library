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
 * Exception representing an incorrect parameter set for an OAuth token request
 */
class IncorrectParametersReturnedException extends \Exception
{
    /**
     * {@inheritdoc}
     */
    public function __construct($message = 'Incorrect parameters returned.', $code = 500, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
