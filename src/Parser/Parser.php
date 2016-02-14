<?php

namespace Cpeter\PhpQkeylmEmailNotification\Parser;

use Cpeter\PhpQkeylmEmailNotification\Exception\EmptyUrlException;
use GuzzleHttp\Exception\RequestException;

class Parser
{

    private $parsers = array();
    
    public function __construct()
    {
        $this->registerPlugins();
    }
    
    protected function registerPlugins()
    {
        // get all the parsers
        $parsers = glob(__DIR__ . '/Plugins/*.php');
        foreach($parsers as $parser){
            require_once $parser;
            $class = basename($parser, '.php');
            $obj = __NAMESPACE__ . "\\Plugins\\$class";
            $p_obj = new $obj();
            $this->attach( $p_obj );
        }
        
    }
    
    //add observer
    public function attach(IParser $parser) {
        $this->parsers[] = $parser;
    }

    //remove observer
    public function detach(IParser $parser) {

        $key = array_search($parser,$this->parsers, true);
        if($key){
            unset($this->parsers[$key]);
        }
    }
    
    public function parse($cms, $cms_options)
    {
        $url = $cms_options['version_page'];
        if (empty($url)) {
            throw new EmptyUrlException("URL must be set for '$cms'. We can not parse empty url.");
        }
        
        // fetch url and get the version id
        $client = new \GuzzleHttp\Client();

        try {
            $res = $client->get($url, array('verify' => false));
        } catch(RequestException $e){
            $status_code = $e->getCode();
            throw new EmptyUrlException("URL '$url'' returned status code: $status_code. Was expecting 200.");
        }

        $status_code = $res->getStatusCode();
        if ($res->getStatusCode() != 200){
            throw new EmptyUrlException("URL '$url'' returned status code: $status_code. Was expecting 200.");
        }

        $body = $res->getBody();

        // loop through all parsers and try to get the cms value. 
        // each CMS should have only one parser so once one parser has commited to do the job don't try the other
        // parsers, they should not match
        $version_found = false;
        foreach ($this->parsers as $parser) {
            // can the parser do anything with this content?
            if ($parser->isParser($cms_options['parser'])) {
                $version_found = $parser->parse($body, $cms_options);
                break;
            }
        }

        return $version_found;
    }
}
