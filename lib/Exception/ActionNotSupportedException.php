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
 * Exception representing an unsupported action.
 */
class ActionNotSupportedException extends AbstractApiException
{
    /**
     * {@inheritdoc}
     */
    public const DEFAULT_MESSAGE = 'Action is not supported at this time.';
}
