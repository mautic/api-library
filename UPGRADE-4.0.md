# Dependencies
* PHP 8.0 is the now the minimum
* We are decoupled from any HTTP messaging client with the help of [PSR-18 HTTP Client](https://www.php-fig.org/psr/psr-18/). This requires an extra package providing [psr/http-client-implementation](https://packagist.org/providers/psr/http-client-implementation). To use Guzzle 7, for example, simply require `guzzlehttp/guzzle` in your project. If you do not have an HTTP client installed, `php-http/discovery` will install one if you allow the Composer plugin.

# Api
* \Mautic\Api\Api::getLogger now returns void as the LoggerAwareInterface dictates
* \Mautic\Api\Api::getResponseInfo and \Mautic\Response::getInfo have been removed, all Auth classes now have a getResponse method to get the PSR-7 response message

# HTTP
## Timeout
The setCurlTimeout-method (`$auth->setCurlTimeout(10);`) has been removed. If you like to set the timeout, you should configure your HTTP client to do so. For example, with Guzzle 7, you can do this:

```php
$httpClient = new \GuzzleHttp\Client([
    'timeout'  => 10,
]);
$settings = [
    // ...
];

$initAuth = new \Mautic\Auth\ApiAuth($httpClient);
$auth = $initAuth->newAuth($settings);
```
