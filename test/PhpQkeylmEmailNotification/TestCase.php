<?php

namespace Cpeter\PhpQkeylmEmailNotification\Test;

use Mockery as m;
use PHPUnit_Framework_TestCase;

class TestCase extends PHPUnit_Framework_TestCase
{

	public function setUp()
	{
		date_default_timezone_set("Australia/Sydney");
		parent::setUp();
	}

	public function tearDown()
	{
		m::close();

		parent::tearDown();
	}
}
