<?php
/**
 * Created by PhpStorm.
 * User: werner
 * Date: 1/9/2017
 * Time: 10:01 AM
 */

namespace Mautic\Exception;


class AuthorizationRequiredException extends \Exception
{
    /**
     * @var string
     */
    private $authUrl;

    /**
     * AuthorizationRequiredException constructor.
     * @param string $authUrl
     * {@inheritdoc}
     */
    public function __construct($authUrl, $message = 'Authorization is required.', $code = 401, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->authUrl = $authUrl;
    }

    /**
     * @return string
     */
    public function getAuthUrl()
    {
        return $this->authUrl;
    }
}