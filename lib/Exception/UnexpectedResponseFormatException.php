<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Exception;

use Mautic\Response;

/**
 * Exception representing an unexpected HTTP response
 */
class UnexpectedResponseFormatException extends AbstractApiException
{
    /**
     * {@inheritdoc}
     */
    const DEFAULT_MESSAGE = 'The response returned is in an unexpected format.';

    /**
     * @var Response
     */
    private $response;

    /**
     * {@inheritdoc}
     */
    public function __construct(Response $response, $message = '', $code = 500, \Exception $previous = null)
    {
        $this->response = $response;

        if (empty($message)) {
            // Use message appropriate to the subclass with late binding
            $message = self::DEFAULT_MESSAGE;
        }

        $message .= "\n\nResponse: ";

        // Attach first 1000 characters of the body
        if (mb_strlen($response->getBody()) > 1000) {
            $message .= mb_substr($response->getBody(), 0, 1000).'...';
        } else {
            $message .= $response->getBody();
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}
