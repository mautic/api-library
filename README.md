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

$publicKey = ''; 
$secretKey = ''; 
$callback  = ''; 

// ApiAuth::initiate will accept an array of OAuth settings
$settings = array(
    'baseUrl'          => '',       // Base URL of the Mautic instance
    'version'          => 'OAuth2'  // Version of the OAuth can be OAuth2 or OAuth1a. OAuth2 is the default value.
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
$auth = ApiAuth::initiate($settings);

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
Note that currently only the Lead context allows creating, editing and deleting items.  The others are read only.

### Get a context object

```php
<?php

use Mautic\MauticApi;

// Create an api context by passing in the desired context (Leads, Forms, Pages, etc), the $auth object from above
// and the base URL to the Mautic server (i.e. http://my-mautic-server.com/api/)

$leadApi = MauticApi::getContext("leads", $auth, $apiUrl);
```

Supported contexts are currently:

* Assets - read only
* Campaigns - read only
* Emails - read only
* Forms - read only
* Leads - read and write
* Lists - read and write
* Pages - read only
* Points - read only
* PointTriggers - read only
* Reports - read only

### Retrieving items
All of the above contexts support the following functions for retrieving items:

```php
<?php

$lead = $leadApi->get($id);

// getList accepts optional parameters for filtering, limiting, and ordering
$leads = $leadApi->getList($filter, $start, $limit, $orderBy, $orderByDir);
```

### Creating an item
Currently, only Leads support this

```php
<?php

$fields = $leadApi->getFieldList();

$data = array();

foreach ($fields as $f) {
    $data[$f['alias']] = $_POST[$f['alias']];
}

// Set the IP address the lead originated from if it is different than that of the server making the request
$data['ipAddress'] = $ipAddress;
 
// Create the lead 
$lead = $leadApi->create($data);
```
    
### Editing an item
Currently, only Leads support this

```php
<?php

$updatedData = array(
    'firstname' => 'Updated Name'
);

$result = $leadApi->edit($leadId, $updatedData);

// If you want to create a new lead in the case that $leadId no longer exists
// $result will be populated with the new lead item
$result = $leadApi->edit($leadId, $updatedData, true);
```
    
### Deleting an item
Currently, only Leads support this

```php
<?php

$result = $leadApi->delete($leadId);
```

### Error handling

```php
<?php

// $result returned by an API call should be checked for errors
$result = $leadApi->delete($leadId);

if (isset($result['error'])) {
    echo $result['error']['code'] . ": " . $result['error']['message'];
} else {
    // do whatever with the info
}
```
