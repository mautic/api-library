[![codecov](https://codecov.io/gh/mautic/api-library/branch/master/graph/badge.svg)](https://codecov.io/gh/mautic/api-library) [![Latest Stable Version](https://poser.pugx.org/mautic/api-library/v)](//packagist.org/packages/mautic/api-library) [![Total Downloads](https://poser.pugx.org/mautic/api-library/downloads)](//packagist.org/packages/mautic/api-library) [![Latest Unstable Version](https://poser.pugx.org/mautic/api-library/v/unstable)](//packagist.org/packages/mautic/api-library) [![License](https://poser.pugx.org/mautic/api-library/license)](//packagist.org/packages/mautic/api-library)
<!-- ALL-CONTRIBUTORS-BADGE:START - Do not remove or modify this section -->
[![All Contributors](https://img.shields.io/badge/all_contributors-8-orange.svg?style=flat-square)](#contributors-)
<!-- ALL-CONTRIBUTORS-BADGE:END -->

# Using the Mautic API Library

## Requirements
* PHP 8.0 or newer

## Installing the API Library
You can install the API Library with the following command:

```bash
composer require mautic/api-library
```

N.B. Make sure you have installed a PSR-18 HTTP Client before you install this package or install one at the same time e.g. `composer require mautic/api-library guzzlehttp/guzzle:^7.3`.

### HTTP Client

We are decoupled from any HTTP messaging client with the help of [PSR-18 HTTP Client](https://www.php-fig.org/psr/psr-18/). This requires an extra package providing [psr/http-client-implementation](https://packagist.org/providers/psr/http-client-implementation). To use Guzzle 7, for example, simply require `guzzlehttp/guzzle`:

``` bash
composer require guzzlehttp/guzzle:^7.3
```

The installed HTTP Client is auto-discovered using [php-http/discovery](https://packagist.org/providers/php-http/discovery), but you can also provide your own HTTP Client if you like.

```php
<?php

// Bootup the Composer autoloader
include __DIR__ . '/vendor/autoload.php';  

use GuzzleHttp\Client;
use Mautic\Auth\ApiAuth;

// Initiate an HTTP Client
$httpClient = new Client([
    'timeout'  => 10,
]);

// Initiate the auth object
$initAuth = new ApiAuth($httpClient);
$auth     = $initAuth->newAuth($settings);
// etc.
```

## Mautic Setup
The API must be enabled in Mautic. Within Mautic, go to the Configuration page (located in the Settings menu) and under API Settings enable
Mautic's API. If you intend to use Basic Authentication, ensure you enable it. You can also choose which OAuth protocol to use here.  After saving the configuration, go to the API Credentials page (located in the Settings menu) and create a new client.  Enter the callback/redirect URI that the request will be sent from.  Click Apply, then copy the Client ID and Client Secret to the application that will be using the API.

## Authorization

### Obtaining an access token
The first step is to obtain authorization.  Mautic supports OAuth 1.0a and OAuth 2, however it is up to the administrator
to decide which is enabled.  Thus it is best to have a configuration option within your project for the administrator
to choose what method should be used by your code.

```php
<?php

// Bootup the Composer autoloader
include __DIR__ . '/vendor/autoload.php';  

use Mautic\Auth\ApiAuth;

session_start();

$publicKey = '';
$secretKey = '';
$callback  = '';

// ApiAuth->newAuth() will accept an array of Auth settings
$settings = [
    'baseUrl'          => '',       // Base URL of the Mautic instance
    'version'          => 'OAuth2', // Version of the OAuth can be OAuth2 or OAuth1a. OAuth2 is the default value.
    'clientKey'        => '',       // Client/Consumer key from Mautic
    'clientSecret'     => '',       // Client/Consumer secret key from Mautic
    'callback'         => '',       // Redirect URI/Callback URI for this script
];

/*
// If you already have the access token, et al, pass them in as well to prevent the need for reauthorization
$settings['accessToken']        = $accessToken;
$settings['accessTokenSecret']  = $accessTokenSecret; //for OAuth1.0a
$settings['accessTokenExpires'] = $accessTokenExpires; //UNIX timestamp
$settings['refreshToken']       = $refreshToken;
*/

// Initiate the auth object
$initAuth = new ApiAuth();
$auth     = $initAuth->newAuth($settings);

// Initiate process for obtaining an access token; this will redirect the user to the $authorizationUrl and/or
// set the access_tokens when the user is redirected back after granting authorization

// If the access token is expired, and a refresh token is set above, then a new access token will be requested

try {
    if ($auth->validateAccessToken()) {

        // Obtain the access token returned; call accessTokenUpdated() to catch if the token was updated via a
        // refresh token

        // $accessTokenData will have the following keys:
        // For OAuth1.0a: access_token, access_token_secret, expires
        // For OAuth2: access_token, expires, token_type, refresh_token

        if ($auth->accessTokenUpdated()) {
            $accessTokenData = $auth->getAccessTokenData();

            //store access token data however you want
        }
    }
} catch (Exception $e) {
    // Do Error handling
}
```

### Using Two-Legged Authentication (Oauth2 Client Credentials) Instead
The above method uses authorization code flow for Oauth2. Client Credentials is the preferred method of
authentication when the use-case is application to application, where any actions
are triggered by the application itself and not a user taking an action (e.g. cleanup during cron).

```php
<?php

// Bootup the Composer autoloader
include __DIR__ . '/vendor/autoload.php';

use Mautic\Auth\ApiAuth;

session_start();

$publicKey = '';
$secretKey = '';
$callback  = '';

// ApiAuth->newAuth() will accept an array of Auth settings
$settings = [
    'AuthMethod'   => 'TwoLeggedOAuth2',
    'clientKey'    => '',
    'clientSecret' => '',
    'baseUrl'      => '',
];

/*
// If you already have the access token, et al, pass them in as well to prevent the need for reauthorization
$settings['accessToken']        = $accessToken;
$settings['accessTokenExpires'] = $accessTokenExpires; //UNIX timestamp
*/

// Initiate the auth object
$initAuth = new ApiAuth();
$auth     = $initAuth->newAuth($settings, $settings['AuthMethod']);

if (!$auth->isAuthorized()) {
    $auth->requestAccessToken();
    // $accessTokenData will have the following keys:
    // access_token, expires, token_type
    $accessTokenData = $auth->getAccessTokenData();

    //store access token data however you want
}

// Nothing else to do ... It's ready to use.
// Just pass the auth object to the API context you are creating.
```

### Using Basic Authentication Instead
Instead of messing around with OAuth, you may simply elect to use BasicAuth instead.

Here is the BasicAuth version of the code above.

```php
<?php

// Bootup the Composer autoloader
include __DIR__ . '/vendor/autoload.php';  

use Mautic\Auth\ApiAuth;

session_start();

// ApiAuth->newAuth() will accept an array of Auth settings
$settings = [
    'userName'   => '',             // Create a new user       
    'password'   => '',             // Make it a secure password
];

// Initiate the auth object specifying to use BasicAuth
$initAuth = new ApiAuth();
$auth     = $initAuth->newAuth($settings, 'BasicAuth');

// Nothing else to do ... It's ready to use.
// Just pass the auth object to the API context you are creating.
```

**Note:** If the credentials are incorrect an error response will be returned.

```php
 [
    'errors' => [
        [
            'code'    => 403,
            'message' => 'access_denied: OAuth2 authentication required',
            'type'    => 'access_denied',
        ],
    ],
 ];

```

## API Requests
Now that you have an access token and the auth object, you can make API requests.  The API is broken down into contexts.

### Get a context object

```php
<?php

use Mautic\MauticApi;

// Create an api context by passing in the desired context (Contacts, Forms, Pages, etc), the $auth object from above
// and the base URL to the Mautic server (i.e. http://my-mautic-server.com/api/)

$api        = new MauticApi();
$contactApi = $api->newApi('contacts', $auth, $apiUrl);
```

Supported contexts are currently:

See the [developer documentation](https://developer.mautic.org).

### Retrieving items
All of the above contexts support the following functions for retrieving items:

```php
<?php

$response = $contactApi->get($id);
$contact  = $response[$contactApi->itemName()];

// getList accepts optional parameters for filtering, limiting, and ordering
$response      = $contactApi->getList($filter, $start, $limit, $orderBy, $orderByDir);
$totalContacts = $response['total'];
$contact       = $response[$contactApi->listName()];
```

### Creating an item

```php
<?php

$fields = $contactApi->getFieldList();

$data = array();

foreach ($fields as $field) {
    $data[$field['alias']] = $_POST[$field['alias']];
}

// Set the IP address the contact originated from if it is different than that of the server making the request
$data['ipAddress'] = $ipAddress;

// Create the contact
$response = $contactApi->create($data);
$contact  = $response[$contactApi->itemName()];
```

### Editing an item


```php
<?php

$updatedData = [
    'firstname' => 'Updated Name'
];

$response = $contactApi->edit($contactId, $updatedData);
$contact  = $response[$contactApi->itemName()];

// If you want to create a new contact in the case that $contactId no longer exists
// $response will be populated with the new contact item
$response = $contactApi->edit($contactId, $updatedData, true);
$contact  = $response[$contactApi->itemName()];
```

### Deleting an item

```php
<?php

$response = $contactApi->delete($contactId);
$contact  = $response[$contactApi->itemName()];
```

### Error handling

```php
<?php

// $response returned by an API call should be checked for errors
$response = $contactApi->delete($contactId);

if (isset($response['errors'])) {
    foreach ($response['errors'] as $error) {
        echo $error['code'] . ": " . $error['message'];
    }
}
```

## Contributing

### Setting up your environment (automatically)
In order to get started quickly, we recommend that you use [DDEV](https://ddev.readthedocs.io/en/stable/) which sets things up automatically for you. It clones [https://github.com/mautic/mautic](mautic/mautic), sets up a local instance for you, and connects the API library tests to that instance.

To get started, run `ddev start`! Our first-run experience will guide you through the setup.

### Setting up your environment (manually)
If you want to set up your local environment manually, ensure that you copy `/tests/local.config.php.dist` to `/tests/local.config.php`, and fill in the required settings. We recommend using the Basic Authentication method to get up and running quickly.

### Unit tests

Configure the unit tests config before running the unit tests. The tests fire real API requests to a Mautic instance. 

1. Ensure you have set up your local environment using the steps above.
2. Run `composer test` to run the tests.

Modify this command to run a specific test: `composer test -- --filter testCreateGetAndDelete tests/Api/NotesTest.php`

Modify this command to run all tests in one class: `composer test -- --filter test tests/Api/NotesTest.php`

## Contributors ✨

Thanks goes to these wonderful people ([emoji key](https://allcontributors.org/docs/en/emoji-key)):

<!-- ALL-CONTRIBUTORS-LIST:START - Do not remove or modify this section -->
<!-- prettier-ignore-start -->
<!-- markdownlint-disable -->
<table>
  <tbody>
    <tr>
      <td align="center" valign="top" width="14.28%"><a href="https://webmecanik.com"><img src="https://avatars.githubusercontent.com/u/462477?v=4?s=100" width="100px;" alt="Zdeno Kuzmany"/><br /><sub><b>Zdeno Kuzmany</b></sub></a><br /><a href="https://github.com/mautic/api-library/commits?author=kuzmany" title="Code">💻</a></td>
      <td align="center" valign="top" width="14.28%"><a href="https://github.com/dlopez-akalam"><img src="https://avatars.githubusercontent.com/u/6641589?v=4?s=100" width="100px;" alt="dlopez-akalam"/><br /><sub><b>dlopez-akalam</b></sub></a><br /><a href="https://github.com/mautic/api-library/commits?author=dlopez-akalam" title="Code">💻</a></td>
      <td align="center" valign="top" width="14.28%"><a href="https://github.com/mollux"><img src="https://avatars.githubusercontent.com/u/3983285?v=4?s=100" width="100px;" alt="mollux"/><br /><sub><b>mollux</b></sub></a><br /><a href="https://github.com/mautic/api-library/commits?author=mollux" title="Code">💻</a></td>
      <td align="center" valign="top" width="14.28%"><a href="https://github.com/LadySolveig"><img src="https://avatars.githubusercontent.com/u/64533137?v=4?s=100" width="100px;" alt="Martina  Scholz"/><br /><sub><b>Martina  Scholz</b></sub></a><br /><a href="https://github.com/mautic/api-library/commits?author=LadySolveig" title="Code">💻</a></td>
      <td align="center" valign="top" width="14.28%"><a href="http://johnlinhart.com"><img src="https://avatars.githubusercontent.com/u/1235442?v=4?s=100" width="100px;" alt="John Linhart"/><br /><sub><b>John Linhart</b></sub></a><br /><a href="https://github.com/mautic/api-library/pulls?q=is%3Apr+reviewed-by%3Aescopecz" title="Reviewed Pull Requests">👀</a></td>
      <td align="center" valign="top" width="14.28%"><a href="https://github.com/Rocksheep"><img src="https://avatars.githubusercontent.com/u/1311371?v=4?s=100" width="100px;" alt="Marinus van Velzen"/><br /><sub><b>Marinus van Velzen</b></sub></a><br /><a href="https://github.com/mautic/api-library/commits?author=Rocksheep" title="Code">💻</a></td>
      <td align="center" valign="top" width="14.28%"><a href="https://pierre.ammeloot.fr"><img src="https://avatars.githubusercontent.com/u/4603318?v=4?s=100" width="100px;" alt="Pierre Ammeloot"/><br /><sub><b>Pierre Ammeloot</b></sub></a><br /><a href="#userTesting-PierreAmmeloot" title="User Testing">📓</a></td>
    </tr>
    <tr>
      <td align="center" valign="top" width="14.28%"><a href="https://matbcvo.github.io"><img src="https://avatars.githubusercontent.com/u/1006437?v=4?s=100" width="100px;" alt="Martin Vooremäe"/><br /><sub><b>Martin Vooremäe</b></sub></a><br /><a href="https://github.com/mautic/api-library/commits?author=matbcvo" title="Code">💻</a> <a href="https://github.com/mautic/api-library/commits?author=matbcvo" title="Tests">⚠️</a></td>
    </tr>
  </tbody>
</table>

<!-- markdownlint-restore -->
<!-- prettier-ignore-end -->

<!-- ALL-CONTRIBUTORS-LIST:END -->

This project follows the [all-contributors](https://github.com/all-contributors/all-contributors) specification. Contributions of any kind welcome!
