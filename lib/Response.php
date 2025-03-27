<?php

/**
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 *
 * @see        http://mautic.org
 *
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic;

use Mautic\Exception\UnexpectedResponseFormatException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class helping with API responses.
 */
class Response
{
    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var string
     */
    private $body;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
        $this->body     = (string) $this->response->getBody();
        $this->validate();
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->response->getStatusCode();
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->response->getHeaders();
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return array
     */
    public function getDecodedBody()
    {
        try {
            $parsed = $this->decodeFromJson();
        } catch (UnexpectedResponseFormatException $e) {
            $parsed = $this->decodeFromUrlParams();
        }

        return $parsed;
    }

    /**
     * @return array
     *
     * @throws UnexpectedResponseFormatException
     */
    public function decodeFromJson()
    {
        $parsed = json_decode($this->body, true);

        if (is_null($parsed)) {
            throw new UnexpectedResponseFormatException($this);
        }

        return $parsed;
    }

    /**
     * @return array
     *
     * @throws UnexpectedResponseFormatException
     */
    public function decodeFromUrlParams()
    {
        if (false !== strpos($this->body, '=')) {
            parse_str($this->body, $parsed);
        }

        if (empty($parsed)) {
            throw new UnexpectedResponseFormatException($this);
        }

        return $parsed;
    }

    /**
     * @return bool
     */
    public function isZip()
    {
        return $this->response->hasHeader('Content-Type') && 'application/zip' === $this->response->getHeader('Content-Type')[0];
    }

    /**
     * @return bool
     */
    public function isHtml()
    {
        return '<' === substr(trim($this->body), 0, 1);
    }

    /**
     * @param string $path
     *
     * @return array
     */
    public function saveToFile($path)
    {
        if (!file_exists($path)) {
            if (!@mkdir($path) && !is_dir($path)) {
                throw new \Exception('Cannot create directory '.$path);
            }
        }
        $file = tempnam($path, 'mautic_api_');

        if (!is_writable($file)) {
            throw new \Exception($file.' is not writable');
        }

        if (!$handle = fopen($file, 'w')) {
            throw new \Exception('Cannot open file '.$file);
        }

        if (false === fwrite($handle, $this->body)) {
            throw new \Exception('Cannot write into file '.$file);
        }

        fclose($handle);

        return [
            'file' => $file,
        ];
    }

    /**
     * @throws UnexpectedResponseFormatException
     */
    private function validate()
    {
        if (!in_array($this->response->getStatusCode(), [200, 201])) {
            $message = 'The response has unexpected status code ('.$this->response->getStatusCode().').';
            throw new UnexpectedResponseFormatException($this, $message, $this->response->getStatusCode());
        }

        if ($this->isHtml()) {
            throw new UnexpectedResponseFormatException($this);
        }
    }
}
