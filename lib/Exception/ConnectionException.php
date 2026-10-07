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
 * Exception thrown when the HTTP client cannot complete the request to the
 * Mautic API, e.g. a DNS resolution failure, a connection timeout or a TLS
 * handshake error.
 *
 * Wrapping the underlying PSR-18 client exception (which carries the cURL
 * error message and number) means the caller receives a meaningful message
 * instead of a generic "unexpected status code (0)" response.
 */
class ConnectionException extends AbstractApiException
{
    /**
     * {@inheritdoc}
     */
    public const DEFAULT_MESSAGE = 'Could not connect to the Mautic API.';
}
