<?php

namespace Cpeter\PhpQkeylmEmailNotification\Test;

use Mockery as m;
use PHPUnit_Framework_TestCase;

class TestCase extends PHPUnit_Framework_TestCase
{
	public function tearDown()
	{
		m::close();

		parent::tearDown();
	}
}
