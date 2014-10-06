# Using the Mautic API Library

## Requirements
* PHP 5.3
* cURL support

## Authorization
### OAuth URLs
***OAuth 1.0a***

* Authorization URL: /oauth/v1/authorize
* Request Token URL: /oauth/v1/request_token
* Access Token URL:  /oauth/v1/access_token

***OAuth 2***

* Authorization URL: /oauth/v2/authorize
* Access Token URL:  /oauth/v2/token
    
### Obtaining an access token
The first step is to obtain authorization.  Mautic supports OAuth 1.0a and OAuth 2 however it is up to the administrator
to decide which is enabled.  Thus it is best to have a configuration option within your project for the administrator 
to choose what method should be used by your code.

    <?php
    // Include the MauticApi file which handles the API class autoloading
    include __DIR__ . '/lib/Mautic/MauticApi.php';  
    
    $publicKey = ''; // Client/Consumer key from Mautic
    $secretKey = ''; // Client/Consumer secret key from Mautic
    $callback  = ''; // Redirect URI/Callback URI for this script
    
    // ApiAuth::initiate will accept an array of OAuth settings
    $settings = array(
        'clientKey'        => $publicKey,
        'clientSecret'     => $secretKey,
        'callback'         => $callback,
        'accessTokenUrl'   => $accessTokenUrl,
        'authorizationUrl' => $authorizationUrl
    );
    
    // Pass requestTokenUrl to activate OAuth1
    // $settings['requestTokenUrl'] = $requestTokenUrl;
    
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
    
## API Requests
Now that you have an access token and the auth object, you can make API requests.  The API is broken down into contexts.
Note that currently only the Lead context allows creating, editing and deleting items.  The others are read only.

### Get a context object

    // Create an api context by passing in the desired context (Leads, Forms, Pages, etc), the $auth object from above
    // and the base URL to the Mautic server (i.e. http://my-mautic-server.com/api/)

    $leadApi = MauticApi::getContext("leads", $auth, $apiUrl);
    
Supported contexts are currently:

* Assets - read only
* Campaigns - read only
* Forms - read only
* Leads - read and write
* Pages - read only
* Points - read only
* PointTriggers - read only

### Retrieving items
All of the above contexts support the following functions for retrieving items:

    $lead = $leadApi->get($id);
    
    // getList accepts optional parameters for filtering, limiting, and ordering
    $leads = $leadApi->getList($filter, $start, $limit, $orderBy, $orderByDir);
    
### Creating an item
Currently, only Leads support this
    
    $fields = $leadApi->getFieldList();
    
    $data = array();
    
    foreach ($fields as $f) {
        $data[$f['alias']] = $_POST[$f['alias']];
    }
    
    // Set the IP address the lead originated from if it is different than that of the server making the request
    $data['ipAddress'] = $ipAddress;
     
    // Create the lead 
    $lead = $leadApi->create($data);
    
### Editing an item
Currently, only Leads support this

    $updatedData = array(
        'firstname' => 'Updated Name'
    );
    
    $result = $leadApi->edit($leadId, $updatedData);
    
    // If you want to create a new lead in the case that $leadId no longer exists
    // $result will be populated with the new lead item
    $result = $leadApi->edit($leadId, $updatedData, true);
    
### Deleting an item
Currently, only Leads support this
    
    $result = $leadApi->delete($leadId);
    
### Error handling

    // $result returned by an API call should be checked for errors
    $result = $leadApi->delete($leadId);
    
    if (isset($result['error'])) {
        echo $result['error']['code'] . ": " . $result['error']['message'];
    } else {
        // do whatever with the info
    }