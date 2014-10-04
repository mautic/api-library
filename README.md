# Using the OAuth Client

**cURL support is required.**

## OAuth 2.0 Example

OAuth2 should only be used when SSL (https) is available.  If you are on a shared host without SSL, then it is recommended that you use OAuth 1.0a.

	<?php
	
	include oauthclient.php
	
	$consumerKey    = 'public consumer key';
	$consumerSecret = 'private consumer secret';
	$callback       = 'http://url-back-to-this-script.com';
	
	$oauthObject = new \Mautic\API\Oauth($consumerKey, $consumerSecret, $callback);
	
    $oauthObject->setAuthorizeUrl($authUrl);
    $oauthObject->setAccessTokenUrl($accessTokenUrl);
	
	if ($oauthObject->validateAccessToken()) {
	    list($accessToken, $accessTokenSecret, $expires) = $oauthObject->getAccessToken();
	    
	    // You can now store access token or make requests
	    // NOTE: Calling getAccessToken() erases the token from $_SESSION and thus using makeRequest() will require
	    // reauthorization unless the access token, secret, and expiry (if applicable) is reset with
	    
	    $oauthObject->setAccessToken($accesstoken, $accessTokenSecret, $expires);
	    
	    // Now you can make the request using makeRequest($url, array $params, $method)
	    $response = $oauthObject->makeRequest('http://my-api-url.com', array('param1' => 'value1', 'param2' => 'value2'), 'POST');
	    
	    echo "<pre>" . print_r($response,true) . "</pre>";
	}

## OAuth 1.0a Example
For OAuth 1.0a, everything is the same as above with the exception of setting a request token URL.
	
	<?php
	
	//...
	
    $oauthObject->setRequestTokenUrl($requestTokenUrl);
    
    //...