# Using the Mautic API Library

## Requirements
* PHP 5.3.7 or newer
* cURL support

## Mautic Setup
The API must be enabled in Mautic. Within Mautic, go to the Configuration page (located in the Settings menu) and under API Settings enable
Mautic's API.  You can also choose which OAuth protocol to use here.  After saving the configuration, go to the API Credentials page
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

// ApiAuth::initiate will accept an array of OAuth settings
$settings = array(
    'baseUrl'          => '',       // Base URL of the Mautic instance
    'version'          => 'OAuth2', // Version of the OAuth can be OAuth2 or OAuth1a. OAuth2 is the default value.
    'clientKey'        => '',       // Client/Consumer key from Mautic
    'clientSecret'     => '',       // Client/Consumer secret key from Mautic
    'callback'         => ''        // Redirect URI/Callback URI for this script
);

/*
// If you already have the access token, et al, pass them in as well to prevent the need for reauthorization
$settings['accessToken']        = $accessToken;
$settings['accessTokenSecret']  = $accessTokenSecret; //for OAuth1.0a
$settings['accessTokenExpires'] = $accessTokenExpires; //UNIX timestamp
$settings['refreshToken']       = $refreshToken;
*/

// Initiate the auth object
$initAuth = new ApiAuth();
$auth = $initAuth->newAuth($settings);

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

## API Requests
Now that you have an access token and the auth object, you can make API requests.  The API is broken down into contexts.
Note that currently only the Contact context allows creating, editing and deleting items.  The others are read only.

### Get a context object

```php
<?php

use Mautic\MauticApi;

// Create an api context by passing in the desired context (Contacts, Forms, Pages, etc), the $auth object from above
// and the base URL to the Mautic server (i.e. http://my-mautic-server.com/api/)

$api = new MauticApi();
$contactApi = $api->newApi('contacts', $auth, $apiUrl);
```

Supported contexts are currently:

* Assets - read only
* Campaigns - read only
* Emails - read only
* Forms - read only
* Contacts - read and write
* Segments - read and write
* Pages - read only
* Points - read only
* PointTriggers - read only
* Reports - read only

### Retrieving items
All of the above contexts support the following functions for retrieving items:

```php
<?php

$contact = $contactApi->get($id);

// getList accepts optional parameters for filtering, limiting, and ordering
$contacts = $contactApi->getList($filter, $start, $limit, $orderBy, $orderByDir);
```

### Creating an item
Currently, only Contacts support this

```php
<?php

$fields = $contactApi->getFieldList();

$data = array();

foreach ($fields as $f) {
    $data[$f['alias']] = $_POST[$f['alias']];
}

// Set the IP address the contact originated from if it is different than that of the server making the request
$data['ipAddress'] = $ipAddress;
 
// Create the contact 
$contact = $contactApi->create($data);
```
    
### Editing an item
Currently, only Contacts support this

```php
<?php

$updatedData = array(
    'firstname' => 'Updated Name'
);

$result = $contactApi->edit($contactId, $updatedData);

// If you want to create a new contact in the case that $contactId no longer exists
// $result will be populated with the new contact item
$result = $contactApi->edit($contactId, $updatedData, true);
```
    
### Deleting an item
Currently, only Contacts support this

```php
<?php

$result = $contactApi->delete($contactId);
```

### Error handling

```php
<?php

// $result returned by an API call should be checked for errors
$result = $contactApi->delete($contactId);

if (isset($result['error'])) {
    echo $result['error']['code'] . ": " . $result['error']['message'];
} else {
    // do whatever with the info
}
```

## Unit tests

Configure the unit tests config before running the unit tests. The tests fire real API requests to a Mautic instance.

1. Copy `/tests/local.config.php.dist` to `/tests/local.config.php`.
2. Open the API tester in the browser like http://localhost/api-library/apitester/index.php
3. Fill in the URL of your Mautic instance.
4. Click Submit to store the URL to the session.
5. Fill in one of the OAuth credentials and authorize.
6. Open the $_SESSION array and copy the 'access_token' to the local.config.php file.
7. Then run `phpunit` to run the tests.

Modify this command to run a specific test: `phpunit --filter testCreateGetAndDelete NotesTest tests/Api/NotesTest.php`
