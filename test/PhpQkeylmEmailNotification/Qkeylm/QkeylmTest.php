<?php

namespace Cpeter\PhpQkeylmEmailNotification\Test\Qkeylm;

use Mockery as m;
use Cpeter\PhpQkeylmEmailNotification\Qkeylm\QkeylmApi;
use Cpeter\PhpQkeylmEmailNotification\Test\TestCase;

class QkeylmTest extends TestCase
{

//$mock = M::mock('Engineering');
//$mock->shouldReceive('disengageWarp')->once()->ordered();
//$mock->shouldReceive('divertPower')->with(0.40, 'sensors')->once()->ordered();
//$mock->shouldReceive('divertPower')->with(0.30, 'auxengines')->once()->ordered();
//$mock->shouldReceive('runDiagnosticLevel')->with(1)->once()->ordered();
    
    /**
     * Check config object
     * 
     * Get a private property
     * 
     * @covers Cpeter\PhpQkeylmEmailNotification\Qkeylm\QkeylmApi::__construct
     */
    public function testConfigObjAfterInit()
    {
        $qkeylm_api = new QkeylmApi(['host' => 'http://www.host.org']);
        
        // check private property
        $property = $this->getPrivateProperty( 'Cpeter\PhpQkeylmEmailNotification\Qkeylm\QkeylmApi', 'config' );
        $config = $property->getValue( $qkeylm_api );
    
        $this->assertTrue(is_array($config) && !empty($config));
        $this->assertTrue($config['host'] == 'http://www.host.org');
    }

    /**
     * Check default value works
     * 
     * Mock a method
     * 
     * @covers Cpeter\PhpQkeylmEmailNotification\Configuration\Configuration::get
     */
    public function testGetDailyJournal()
    {
        $mock = M::mock('QkeylmApi');
        $mock->
    }

    /**
     * Check authToken extraction
     * 
     * @covers Cpeter\PhpQkeylmEmailNotification\Configuration\Configuration::getAuthToken
     */
    public function testGetAuthToken()
    {
        $qkeylm_api = new QkeylmApi(['host' => 'http://www.host.org']);
        $token = $this->invokeMethod($qkeylm_api, 'getAuthToken', ['name="__RequestVerificationToken" type="hidden" value="MyToken1234"']);
        $this->assertTrue($token == 'MyToken1234');
    }
    
    /**
     * getPrivateProperty
     *
     * @param 	string $className
     * @param 	string $propertyName
     * @return	ReflectionProperty
     */
    public function getPrivateProperty( $className, $propertyName ) {
        $reflector = new \ReflectionClass( $className );
        $property = $reflector->getProperty( $propertyName );
        $property->setAccessible( true );

        return $property;
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    public function tearDown()
    {
        M::close();
    }
    
}
