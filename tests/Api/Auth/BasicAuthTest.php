<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

namespace Mautic\Tests\Api\Auth;

use Mautic\MauticApi;
use Mautic\Auth\ApiAuth;
use Mautic\Tests\Api\MauticApiTestCase;

class BasicAuthTest extends MauticApiTestCase
{
    protected $configFile = '';

    public function setUp()
    {
        $this->configFile = __DIR__.'/../../local.config.php';
    }

    /**
    * Verify that the error handling in setup method is working
    * - No Username
    *
    * @expectedException Mautic\Exception\RequiredParameterMissingException
    * @expectedExceptionCode 500
    */
    public function testParameterExceptionErrorNoUserName()
    {
        // This should throw an error becuse the userName is missing
        $auth = (new ApiAuth())->newAuth(['password'=>'********'], 'BasicAuth' );
    }

    /**
    * Verify that the error handling in setup method is working
    * - No Password
    *
    * @expectedException Mautic\Exception\RequiredParameterMissingException
    * @expectedExceptionCode 500
    */
    public function testParameterExceptionErrorNoPassword()
    {
        // This should throw an error becuse the password is missing
        $auth = (new ApiAuth())->newAuth(['userName'=>'anyolduser'], 'BasicAuth' );
    }

    /**
    * Verify that the error handling in setup method is working
    * - Empty Username
    *
    * @expectedException Mautic\Exception\RequiredParameterMissingException
    * @expectedExceptionCode 500
    */
    public function testParameterExceptionErrorEmptyUserName()
    {
        // This should throw an error becuse the userName is empty - test blanks
        $auth = (new ApiAuth())->newAuth(['userName'=>' ','password'=>'********'], 'BasicAuth' );
    }

    /**
    * Verify that the error handling in setup method is working
    * - Empty password
    *
    * @expectedException Mautic\Exception\RequiredParameterMissingException
    * @expectedExceptionCode 500
    */
    public function testParameterExceptionErrorEmptyPassword()
    {
        // This should throw an error because the password is empty - test blanks
        $auth = (new ApiAuth())->newAuth(['userName'=>'admin','password'=>' '], 'BasicAuth' );
    }

    /**
    * Ensure the Config has the correct settings
    */
    public function testConfigReady()
    {
        $this->assertTrue(file_exists($this->configFile), 'Cannot find local.config.php!');

        // get local config
        $config = include $this->configFile;

        // Make sure the parameters exist and are set
        //
        // AuthMethod - set to 'BasicAuth'
        $toTest = 'AuthMethod';
        $this->assertTrue(isset($config[$toTest]), $toTest .' Check failed. Check '. $toTest .' in local.config.php.');
        $this->assertEquals('BasicAuth', $config[$toTest], 'Expected value for '. $toTest .' not found. Check '. $toTest .' in local.config.php.');
        //
        // userName & password - ! empty
        $toCheck = array('userName','password');
        foreach($toCheck as $toTest) {
            $this->assertTrue(isset($config[$toTest]), $toTest .' Check failed. Check '. $toTest .' in local.config.php.');
            $this->assertTrue(!empty($config[$toTest]), $toTest .' must contain a value. Check '. $toTest .' in local.config.php.');
        }
    }

    /**
    * @depends testConfigReady
    */
    public function testPublicInterface()
    {
        $config = include $this->configFile;
        $auth   = (new ApiAuth())->newAuth($config, 'BasicAuth' );

        $this->assertTrue($auth->isAuthorized(), 'Authorization failed. Check credentials in local.config.php.');
        $this->assertTrue($auth->validateAccessToken(), 'validateAccessToken test failed. Check credentials in local.config.php.');
        $this->assertTrue(is_array($auth->getAccessTokenData()), 'getAccessTokenData test failed. Check credentials in local.config.php.');
    }

    /**
    * @depends testConfigReady
    */
    public function testGetList()
    {
        $this->api = $this->getContext('contactFields');
        $this->standardTestGetList();
    }
}
