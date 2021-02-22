[![codecov](https://codecov.io/gh/mautic/api-library/branch/master/graph/badge.svg)](https://codecov.io/gh/mautic/api-library) [![Latest Stable Version](https://poser.pugx.org/mautic/api-library/v)](//packagist.org/packages/mautic/api-library) [![Total Downloads](https://poser.pugx.org/mautic/api-library/downloads)](//packagist.org/packages/mautic/api-library) [![Latest Unstable Version](https://poser.pugx.org/mautic/api-library/v/unstable)](//packagist.org/packages/mautic/api-library) [![License](https://poser.pugx.org/mautic/api-library/license)](//packagist.org/packages/mautic/api-library)

# Using the Mautic API Library

## Requirements
* PHP 7.2 or newer
* cURL support

## Installing the API Library
You can install the API Library with the following command:

```bash
composer require mautic/api-library
```

## Mautic Setup
The API must be enabled in Mautic. Within Mautic, go to the Configuration page (located in the Settings menu) and under API Settings enable
Mautic's API. If you intend on using Basic Authentication, ensure you enable it. You can also choose which OAuth protocol to use here.  After saving the configuration, go to the API Credentials page
(located in the Settings menu) and create a new client.  Enter the callback/redirect URI that the request will be sent from.  Click Apply
then copy the Client ID and Client Secret to the application that will be using the API.

## Authorization

### Obtaining an access token
The first step is to obtain authorization.  Mautic supports OAuth 1.0a and OAuth 2 however it is up to the administrator
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
**Note:** You can also specify a CURLOPT_TIMEOUT in the request (default is set to wait indefinitely):
```php
$initAuth = new ApiAuth();
$auth     = $initAuth->newAuth($settings, 'BasicAuth');
$timeout  = 10;

$auth->setCurlTimeout($timeout);
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
