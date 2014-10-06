<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Exception;

class UnexpectedResponseFormatException extends \Exception
{

    public function __construct($message = 'The response returned is in an unexpected format.', $code = 500, \Exception $previous = null)
    {
        if (empty($message)) {
            $message = 'The response returned is in an unexpected format.';
        }

        parent::__construct($message, $code, $previous);
    }
}
?>