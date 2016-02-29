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
     * @covers Cpeter\PhpQkeylmEmailNotification\Configuration\Configuration::get
     */
    public function testGet()
    {
//        $configuration = Configuration::defaults();
//        $config = $configuration->get('invalid', 'default');
//        $this->assertTrue($config == 'default');
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
}
