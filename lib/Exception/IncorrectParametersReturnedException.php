<?php
/**
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 *
 * @see        http://mautic.org
 *
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Exception;

/**
 * Exception representing an incorrect parameter set for an OAuth token request.
 */
class IncorrectParametersReturnedException extends AbstractApiException
{
    /**
     * {@inheritdoc}
     */
    public const DEFAULT_MESSAGE = 'Incorrect parameters returned.';
}
