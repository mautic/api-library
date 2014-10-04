<?php
session_name("oauthtester");
session_start();

require 'kint/Kint.class.php';

$auth = (isset($_POST['auth'])) ? $_POST['auth'] : @$_SESSION['auth'];
if (empty($auth)) {
    $auth = 'OAuth1a';
}

//OAuth1a
$consumerKey     = (isset($_POST['OAuth1a']['consumerKey'])) ? $_POST['OAuth1a']['consumerKey'] : @$_SESSION['settings']['OAuth1a']['consumerKey'];
$consumerSecret  = (isset($_POST['OAuth1a']['consumerSecret'])) ? $_POST['OAuth1a']['consumerSecret'] : @$_SESSION['settings']['OAuth1a']['consumerSecret'];
$callback        = (isset($_POST['OAuth1a']['callback'])) ? $_POST['OAuth1a']['callback'] : @$_SESSION['settings']['OAuth1a']['callback'];
$authUrl         = (isset($_POST['OAuth1a']['authUrl'])) ? $_POST['OAuth1a']['authUrl'] : @$_SESSION['settings']['OAuth1a']['authUrl'];
$accessTokenUrl  = (isset($_POST['OAuth1a']['accessTokenUrl'])) ? $_POST['OAuth1a']['accessTokenUrl'] : @$_SESSION['settings']['OAuth1a']['accessTokenUrl'];
$requestTokenUrl = (isset($_POST['OAuth1a']['requestTokenUrl'])) ? $_POST['OAuth1a']['requestTokenUrl'] : @$_SESSION['settings']['OAuth1a']['requestTokenUrl'];

//OAuth2
$clientKey    = (isset($_POST['OAuth2']['clientKey'])) ? $_POST['OAuth2']['clientKey'] : @$_SESSION['settings']['OAuth2']['clientKey'];
$clientSecret = (isset($_POST['OAuth2']['clientSecret'])) ? $_POST['OAuth2']['clientSecret'] : @$_SESSION['settings']['OAuth2']['clientSecret'];
$redirectUri  = (isset($_POST['OAuth2']['redirectUri'])) ? $_POST['OAuth2']['redirectUri'] : @$_SESSION['settings']['OAuth2']['redirectUri'];
$authUrl2     = (isset($_POST['OAuth2']['authUrl2'])) ? $_POST['OAuth2']['authUrl2'] : @$_SESSION['settings']['OAuth2']['authUrl2'];
$tokenUrl     = (isset($_POST['OAuth2']['tokenUrl'])) ? $_POST['OAuth2']['tokenUrl'] : @$_SESSION['settings']['OAuth2']['tokenUrl'];
$scope        = (isset($_POST['OAuth2']['scope'])) ? $_POST['OAuth2']['scope'] : @$_SESSION['settings']['OAuth2']['scope'];
$state        = (isset($_POST['OAuth2']['state'])) ? $_POST['OAuth2']['state'] : @$_SESSION['settings']['OAuth2']['state'];

//API Calls
$apiurl = (isset($_POST['apiurl'])) ? $_POST['apiurl'] : @$_SESSION['apiurl'];
if (!empty($apiurl) && substr($apiurl, -1) !== '/') {
    $apiurl .= '/';
}
$apiendpoint = (isset($_POST['apiendpoint'])) ? $_POST['apiendpoint'] : @$_SESSION['apiendpoint'];
$method      = (isset($_POST['method'])) ? $_POST['method'] : @$_SESSION['method'];
if (empty($method)) {
    $method = "GET";
}
$responsetype = (isset($_POST['responsetype'])) ? $_POST['responsetype'] : @$_SESSION['responsetype'];
if (empty($responsetype)) {
    $responsetype = '.json';
}

$parameters = (isset($_POST['parameters'])) ? $_POST['parameters'] : @$_SESSION['parameters'];
if (!is_array($parameters)) {
    $parameters = array();
}

//clean up the parameters
$cleanParams = array();
foreach ($parameters as $k => $p) {
    if (!empty($p['key'])) {
        $cleanParams[] = $p;
    }
}
$parameters     = $cleanParams;
$parameterCount = count($parameters);

if (isset($_SESSION['redirect'])) {
    $output = $_SESSION['lastOutput'];
    unset($_SESSION['redirect']);
} else {
    $output = "";

    //should the tokens be reset
    if (!empty($_POST[$auth]['reset'])) {
        unset($_SESSION[$auth]);
    }

    if ($auth == 'OAuth1a') {
        $valid = (isset($_GET['oauth_verifier']) || !empty($_POST)) && !empty($consumerKey) && !empty($consumerSecret) && !empty($callback) && !empty($authUrl) && !empty($accessTokenUrl);
    } elseif ($auth == 'OAuth2') {
        $valid = (isset($_GET['code']) || !empty($_POST)) && !empty($clientKey) && !empty($clientSecret) && !empty($redirectUri) && !empty($authUrl2) && !empty($tokenUrl);
    }

    if ($valid) {
        $_SESSION['settings']['OAuth1a']['consumerKey']     = $consumerKey;
        $_SESSION['settings']['OAuth1a']['consumerSecret']  = $consumerSecret;
        $_SESSION['settings']['OAuth1a']['callback']        = $callback;
        $_SESSION['settings']['OAuth1a']['authUrl']         = $authUrl;
        $_SESSION['settings']['OAuth1a']['accessTokenUrl']  = $accessTokenUrl;
        $_SESSION['settings']['OAuth1a']['requestTokenUrl'] = $requestTokenUrl;

        $_SESSION['settings']['OAuth2']['clientKey']    = $clientKey;
        $_SESSION['settings']['OAuth2']['clientSecret'] = $clientSecret;
        $_SESSION['settings']['OAuth2']['redirectUri']  = $redirectUri;
        $_SESSION['settings']['OAuth2']['authUrl2']     = $authUrl2;
        $_SESSION['settings']['OAuth2']['tokenUrl']     = $tokenUrl;
        $_SESSION['settings']['OAuth2']['scope']        = $scope;
        $_SESSION['settings']['OAuth2']['state']        = $state;

        $_SESSION['auth']         = $auth;
        $_SESSION['apiurl']       = $apiurl;
        $_SESSION['apiendpoint']  = $apiendpoint;
        $_SESSION['responsetype'] = $responsetype;
        $_SESSION['method']       = $method;
        $_SESSION['parameters']   = $parameters;

        require '../oauthclient.php';

        if ($auth != 'None') {
            if ($auth == 'OAuth1a') {
                $oauthObject = new \Mautic\API\Oauth($consumerKey, $consumerSecret, $callback, $auth);
                $oauthObject->setRequestTokenUrl($requestTokenUrl);
                $oauthObject->setAuthorizeUrl($authUrl);
                $oauthObject->setAccessTokenUrl($accessTokenUrl);
            } else {
                $oauthObject = new \Mautic\API\Oauth($clientKey, $clientSecret, $redirectUri, $auth);
                $oauthObject->setAuthorizeUrl($authUrl2);
                $oauthObject->setAccessTokenUrl($tokenUrl);
                $oauthObject->setScope($scope);
            }

            $oauthObject->enableDebugMode();

            try {
                if ($oauthObject->validateAccessToken()) {
                    if (!empty($_POST['apiurl'])) {
                        $url = $apiurl . $apiendpoint . $responsetype;

                        $postParams = array();
                        foreach ($parameters as $k => $v) {
                            $postParams[$v['key']] = $v['value'];
                        }
                        parse_str(http_build_query($postParams), $postParams);

                        if ($responsetype == '/') {
                            $returnType = 'json';
                        } else {
                            $returnType = substr($responsetype, 1);
                        }

                        $response = $oauthObject->makeRequest($url, $method, $postParams, $returnType);
                    }
                }

                if (!empty($response)) {
                    if (is_array($response)) {
                        $output .= "<p>Response: " . @Kint::dump($response) . "</p>";
                    } else {
                        $output .= "<p>Response: <br />$response</p>";
                    }
                }
            } catch (Exception $e) {
                $output = '<div class="text-danger">' . $e->getMessage() . '</div>';
            }
        }

        $output .= @Kint::dump($_SESSION[$auth]);
        $output .= @Kint::dump($oauthObject);

        if (!empty($_POST)) {
            //save to session, refresh
            $_SESSION['redirect']   = 1;
            $_SESSION['lastOutput'] = $output;
            header('Location: index.php');
            exit;
        }
    } else {
        $output = $_SESSION['lastOutput'];
    }
    session_write_close();
}
?>
<html>
<head>
    <title>API Tester</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="bootstrap/js/jquery-1.11.1.min.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function () {
            $('.method-dropdown li').click(function (e) {
                e.preventDefault();
                var selected = $(this).text();
                $('.method').val(selected);
                $('.method_label').html(selected);
            });

            $('.auth-dropdown li').click(function (e) {
                e.preventDefault();
                var selected = $(this).text();
                $('.auth').val(selected);
                $('.auth_label').html(selected);
            });

            $('.responsetype-dropdown li').click(function (e) {
                e.preventDefault();
                var selected = $(this).text();
                $('.responsetype').val(selected);
                $('.responsetype_label').html(selected);
            });
        });

        function addParameter() {
            var parameterCount = $('#parameterCount').val();

            var template = $('#template div.row').clone();
            $(template).find('.parameter-key').each(function () {
                $(this).attr('name', 'parameters[' + parameterCount + '][key]');
            });

            $(template).find('.parameter-value').each(function () {
                $(this).attr('name', 'parameters[' + parameterCount + '][value]');
            });

            $(template).appendTo("#parameters");

            parameterCount = parseInt(parameterCount) + 1;
            $('#parameterCount').val(parameterCount);
        }
    </script>

    <style>
        .custom {
            -webkit-border-radius: 0px !important;
            -moz-border-radius: 0px !important;
            border-radius: 0px !important;
        }
        .custom-right {
            -webkit-border-radius: 4px 0px 0px 4px !important;
            -moz-border-radius: 4px 0px 0px 4px !important;
            border-radius: 4px 0px 0px 4px !important;
            width: 100%;
        }
        .parameter-row {
            margin-bottom: 3px;
        }
    </style>
</head>

<body>
<div class="container">
<h1>API Tester</h1>

<div class="well">
    <form action="index.php" method="POST">

        <div class="row">
            <div class="col-lg-5" style="padding-right: 0px !important;">
                <input class="form-control custom-right" type="text" value="<?php echo $apiurl; ?>" placeholder="URL" name="apiurl">
            </div>
            <div class="col-lg-7" style="padding-left: 0px !important;">
                <div class="input-group">
                    <input class="form-control custom" type="text" value="<?php echo $apiendpoint; ?>" placeholder="API Endpoint" name="apiendpoint">
                    <div class="input-group-btn fix-my-width">
                        <button type="button" class="btn btn-default dropdown-toggle custom" data-toggle="dropdown">
                            <span class="responsetype_label"><?php echo $responsetype; ?></span> <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu responsetype-dropdown">
                            <li><a href="#">/</a></li>
                            <li><a href="#">.json</a></li>
                            <li><a href="#">.xml</a></li>
                        </ul>
                        <input type="hidden" name="responsetype" class="responsetype" value="<?php echo $responsetype; ?>">
                        <button class="btn btn-primary custom" type="submit">Submit</button>
                    </div>

                    <div class="input-group-btn">
                        <button type="button" class="btn btn-default dropdown-toggle custom" data-toggle="dropdown">
                            <span class="auth_label"><?php echo $auth; ?></span> <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu auth-dropdown">
                            <li><a href="#">None</a></li>
                            <li><a href="#">OAuth1a</a></li>
                            <li><a href="#">OAuth2</a></li>
                        </ul>
                        <input type="hidden" name="auth" class="auth" value="<?php echo $auth; ?>">
                    </div>

                    <div class="input-group-btn">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                            <span class="method_label"><?php echo $method; ?></span> <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu method-dropdown">
                            <li><a href="#">GET</a></li>
                            <li><a href="#">POST</a></li>
                            <li><a href="#">PUT</a></li>
                            <li><a href="#">PATCH</a></li>
                            <li><a href="#">DELETE</a></li>
                        </ul>
                        <input type="hidden" name="method" class="method" value="<?php echo $method; ?>">
                    </div>
                </div>
            </div>
        </div>

        <div id="parameters" style="margin-top: 25px;">
            <div class="row" style="margin-bottom: 10px;">
                <div class="col-lg-12">
                    <h3>
                        <span>Parameters</span>
                        <button type="button" class="btn btn-success btn-sm" onclick="addParameter();">
                            <i class="glyphicon glyphicon-plus"></i>
                        </button>
                    </h3>


                    <h6>Use brackets [ ] for array keys i.e. foo[] or foo[bar]</h6>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3"><strong>Key</strong></div>
                <div class="col-lg-1"></div>
                <div class="col-lg-7"><strong>Value</strong></div>
                <div class="col-lg-1"></div>
            </div>
            <?php foreach ($parameters as $k => $param): ?>
                <div class="row parameter-row">
                    <div class="col-lg-3">
                        <input type="text" class="form-control parameter-key" name="parameters[<?php echo $k; ?>][key]" value="<?php echo $param['key']; ?>" />
                    </div>
                    <div class="col-lg-1">=</div>
                    <div class="col-lg-7">
                        <input type="text" class="form-control parameter-value" name="parameters[<?php echo $k; ?>][value]" value="<?php echo $param['value']; ?>" />
                    </div>
                    <div class="col-lg-1">
                        <button type="button" class="btn btn-danger" onclick="$(this).closest('.parameter-row').remove();">
                            <i class="glyphicon glyphicon-trash"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="row parameter-row">
                <div class="col-lg-3">
                    <input type="text" class="form-control parameter-key" name="parameters[<?php echo $parameterCount; ?>][key]" />
                </div>
                <div class="col-lg-1">=</div>
                <div class="col-lg-7">
                    <input type="text" class="form-control parameter-value" name="parameters[<?php echo $parameterCount; ?>][value]" />
                </div>
                <div class="col-lg-1">
                    <button type="button" class="btn btn-danger" onclick="$(this).closest('.parameter-row').remove();">
                        <i class="glyphicon glyphicon-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </form>
    <input type="hidden" id="parameterCount" value="<?php echo $parameterCount + 1; ?>" />
</div>

<ul class="nav nav-tabs" role="tablist">
    <li class="active" id="responseTab"><a href="#response" role="tab" data-toggle="tab">Response</a></li>
    <li id="oauth1aTab"><a href="#oauth1a" role="tab" data-toggle="tab">OAuth1.0a</a></li>
    <li id="oauth2Tab"><a href="#oauth2" role="tab" data-toggle="tab">OAuth2</a></li>
</ul>

<!-- Tab panes -->
<div class="tab-content container">
    <div id="response" class="tab-pane active">
        <?php echo $output; ?>
    </div>

    <div id="oauth1a" class="tab-pane">
        <form method="post" action="index.php">
            <?php $hasError = false; ?>
            <input type="hidden" name="auth" value="OAuth1a" />
            <?php $label = (empty($consumerKey)) ? 'has-error' : 'has-success'; ?>
            <?php $hasError = ($label == 'has-error') ? true : $hasError; ?>
            <div class="form-group <?php echo $label; ?>">
                <label class="control-label" for="consumerKey">Consumer Key</label>
                <input type="text" class="form-control" id="consumerKey" name="OAuth1a[consumerKey]" value="<?php echo $consumerKey; ?>" />
            </div>

            <?php $label = (empty($consumerSecret)) ? 'has-error' : 'has-success'; ?>
            <?php $hasError = ($label == 'has-error') ? true : $hasError; ?>
            <div class="form-group <?php echo $label; ?>">
                <label class="control-label" for="consumerSecret">Consumer Secret</label>
                <input type="text" class="form-control" id="consumerSecret" name="OAuth1a[consumerSecret]" value="<?php echo $consumerSecret; ?>" />
            </div>

            <?php $label = (empty($callback)) ? 'has-error' : 'has-success'; ?>
            <?php $hasError = ($label == 'has-error') ? true : $hasError; ?>
            <div class="form-group <?php echo $label; ?>">
                <label class="control-label" for="callback">Callback URL</label>
                <input type="text" class="form-control" id="callback" name="OAuth1a[callback]" value="<?php echo $callback; ?>" />
            </div>

            <?php $label = (empty($requestTokenUrl)) ? 'has-error' : 'has-success'; ?>
            <?php $hasError = ($label == 'has-error') ? true : $hasError; ?>
            <div class="form-group <?php echo $label; ?>">
                <label class="control-label" for="requestTokenUrl">Request Token URL</label>
                <input type="text" class="form-control" id="requestTokenUrl" name="OAuth1a[requestTokenUrl]" value="<?php echo $requestTokenUrl; ?>" />
            </div>

            <?php $label = (empty($authUrl)) ? 'has-error' : 'has-success'; ?>
            <?php $hasError = ($label == 'has-error') ? true : $hasError; ?>
            <div class="form-group <?php echo $label; ?>">
                <label class="control-label" for="authUrl">Authorization URL</label>
                <input type="text" class="form-control" id="authUrl" name="OAuth1a[authUrl]" value="<?php echo $authUrl; ?>" />
            </div>

            <?php $label = (empty($accessTokenUrl)) ? 'has-error' : 'has-success'; ?>
            <?php $hasError = ($label == 'has-error') ? true : $hasError; ?>
            <div class="form-group <?php echo $label; ?>">
                <label class="control-label" for="accessTokenUrl">Access Token URL</label>
                <input type="text" class="form-control" id="accessTokenUrl" name="OAuth1a[accessTokenUrl]" value="<?php echo $accessTokenUrl; ?>" />
            </div>

            <?php if (!empty($_SESSION['OAuth1']['access_token'])): ?>
                <input type="submit" name="OAuth1[reset]" class="btn btn-danger" value="Reauthorize" />
            <?php else: ?>
                <input type="submit" name="OAuth1[go]" value="Authorize" class="btn btn-primary" />
            <?php endif; ?>
        </form>

        <?php if ($hasError): ?>
            <script>
                $('#oauth1aTab a').addClass('text-danger');
            </script>
        <?php endif; ?>
    </div>

    <div id="oauth2" class="tab-pane">
        <form method="post" action="index.php">
            <?php $hasError = false; ?>
            <input type="hidden" name="auth" value="OAuth2" />

            <?php $label = (empty($clientKey)) ? 'has-error' : 'has-success'; ?>
            <?php $hasError = ($label == 'has-error') ? true : $hasError; ?>
            <div class="form-group <?php echo $label; ?>">
                <label class="control-label" for="clientKey">Client Key</label>
                <input type="text" class="form-control" id="clientKey" name="OAuth2[clientKey]" value="<?php echo $clientKey; ?>" />
            </div>

            <?php $label = (empty($clientSecret)) ? 'has-error' : 'has-success'; ?>
            <?php $hasError = ($label == 'has-error') ? true : $hasError; ?>
            <div class="form-group <?php echo $label; ?>">
                <label class="control-label" for="clientSecret">Client Secret</label>
                <input type="text" class="form-control" id="clientSecret" name="OAuth2[clientSecret]" value="<?php echo $clientSecret; ?>" />
            </div>

            <?php $label = (empty($redirectUri)) ? 'has-error' : 'has-success'; ?>
            <?php $hasError = ($label == 'has-error') ? true : $hasError; ?>
            <div class="form-group <?php echo $label; ?>">
                <label class="control-label" for="redirectUri">Redirect URI</label>
                <input type="text" class="form-control" id="redirectUri" name="OAuth2[redirectUri]" value="<?php echo $redirectUri; ?>" />
            </div>

            <?php $label = (empty($authUrl2)) ? 'has-error' : 'has-success'; ?>
            <?php $hasError = ($label == 'has-error') ? true : $hasError; ?>
            <div class="form-group <?php echo $label; ?>">
                <label class="control-label" for="authUrl2">Authorization URL</label>
                <input type="text" class="form-control" id="authUrl2" name="OAuth2[authUrl2]" value="<?php echo $authUrl2; ?>" />
            </div>

            <?php $label = (empty($tokenUrl)) ? 'has-error' : 'has-success'; ?>
            <?php $hasError = ($label == 'has-error') ? true : $hasError; ?>
            <div class="form-group <?php echo $label; ?>">
                <label class="control-label" for="tokenUrl">Token URL</label>
                <input type="text" class="form-control" id="tokenUrl" name="OAuth2[tokenUrl]" value="<?php echo $tokenUrl; ?>" />
            </div>

            <div class="form-group">
                <label class="control-label" for="scope">Scope</label>
                <input type="text" class="form-control" id="scope" name="OAuth2[scope]" value="<?php echo $scope; ?>" />
            </div>

            <div class="form-group">
                <label class="control-label" for="state">State</label>
                <input type="text" class="form-control" id="state" name="OAuth2[state]" value="<?php echo $state; ?>" />
            </div>

            <?php if (!empty($_SESSION['OAuth2']['access_token'])): ?>
                <input type="submit" name="OAuth2[reset]" class="btn btn-danger" value="Reauthorize" />
            <?php else: ?>
                <input type="submit" name="OAuth2[go]" value="Authorize" class="btn btn-primary" />
            <?php endif; ?>
        </form>
        <?php if ($hasError): ?>
            <script>
                $('#oauth2Tab a').addClass('text-danger');
            </script>
        <?php endif; ?>
    </div>
</div>
</div>
<div id="template" class="hide">
    <div class="row parameter-row">
        <div class="col-lg-3">
            <input type="text" class="form-control parameter-key" />
        </div>
        <div class="col-lg-1">=</div>
        <div class="col-lg-7">
            <input type="text" class="form-control parameter-value" />
        </div>
        <div class="col-lg-1">
            <button type="button" class="btn btn-danger" onclick="$(this).closest('.parameter-row').remove();">
                <i class="glyphicon glyphicon-trash"></i>
            </button>
        </div>
    </div>
</div>
</body>
</html>
