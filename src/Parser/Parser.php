<?php

namespace Cpeter\PhpQkeylmEmailNotification\Parser;

use Cpeter\PhpQkeylmEmailNotification\Exception\EmptyUrlException;
use Cpeter\PhpQkeylmEmailNotification\Qkeylm\QkeylmApi;
use GuzzleHttp\Exception\RequestException;

class Parser
{

    private $parsers = array();
    
    public function __construct()
    {
    }
    
    public function parse($options)
    {

        $qkeylm = new \Cpeter\PhpQkeylmEmailNotification\Qkeylm\QkeylmApi($options);
        $qkeylm->login();

    }
}
