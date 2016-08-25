<?php

namespace Cpeter\PhpQkeylmEmailNotification\Test\Qkeylm;

use Mockery as m;
use Cpeter\PhpQkeylmEmailNotification\Qkeylm\QkeylmApi;
use Cpeter\PhpQkeylmEmailNotification\Test\TestCase;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

class QkeylmTest extends TestCase
{

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
     * Mock a method of the class and mock the http client handler
     * 
     * @covers Cpeter\PhpQkeylmEmailNotification\Qkeylm\QkeylmApi::getDailyJournal
     */
    public function testGetDailyJournal()
    {

        // Create a mock and queue a few responses.
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 0]),
            new Response(200, ['X-Foo' => 'Bar'],
                '<body>
                    <div>
                        <h1>
                            Friday 11 March 2016
                        </h1>
                        <div id="mainInner">
                            <img class="image-frame" src="http://www.host.org/webui/Files/Room/small/my/image.jpg">
                        </div>
                    </div>
                </body>
                '
            ),
            new Response(200, ['Content-Length' => 0]),
            new Response(200, ['Content-Length' => 0])
        ]);

        $handler = HandlerStack::create($mock);

        $mock = M::mock('Cpeter\PhpQkeylmEmailNotification\Qkeylm\QkeylmApi[login]', [
            [
                'host' => 'http://www.host.org',
                'page_journal' => '/journal_page',
                'page_journal_date' => '/journal_page_print',
                'child_name' => 'Adel',
                'handler' => $handler
            ]
        ]);
        $mock->shouldReceive('login')->andReturn(true);

        $journal =  $mock->getDailyJournal();
        $this->assertTrue(isset($journal['images']['http://www.host.org/webui/Files/Room/small/my/image.jpg']));
        $this->assertTrue(isset($journal['body']));
        $this->assertTrue($journal['date'] == '2016-03-11');

    }

    /**
     * Check authToken extraction
     * 
     * @covers Cpeter\PhpQkeylmEmailNotification\Qkeylm\QkeylmApi::getAuthToken
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
