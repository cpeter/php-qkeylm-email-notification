<?php

namespace Cpeter\PhpQkeylmEmailNotification\Qkeylm;

use Cpeter\PhpQkeylmEmailNotification\Exception\EmptyUrlException;
use GuzzleHttp\Exception\RequestException;

class QkeylmApi
{

    private $parsers = array();
    
    private $options = array();
    
    public function __construct($options)
    {
        $this->options = $options;
        // http client
        $this->client = new \GuzzleHttp\Client(
            [
                'cookies' => true,
                'headers' => [
                    'Referer'   => $this->options['host']
                ]
            ]
        );
    }
    
    public function login()
    {
        $url = $this->options['host'].$this->options['page_token'];
        if (empty($url)) {
            throw new EmptyUrlException("URL must be set. We can not parse empty url.");
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

        // get the Auth token 
        $auth_token = $this->getAuthToken($body);
        $post_data = $this->getPostData($auth_token, $this->options['login'], $this->options['password']);
        
        try {
            $res = $client->request('POST', $url, $post_data);
        } catch(RequestException $e){
            $status_code = $e->getCode();
            throw new EmptyUrlException("URL '$url'' returned status code: $status_code. Was expecting 200.");
        }

        $status_code = $res->getStatusCode();
        if ($res->getStatusCode() != 200){
            throw new EmptyUrlException("URL '$url'' returned status code: $status_code. Was expecting 200.");
        }

        $body = $res->getBody();
        echo $body;
        
//
//        // loop through all parsers and try to get the cms value. 
//        // each CMS should have only one parser so once one parser has commited to do the job don't try the other
//        // parsers, they should not match
//        $version_found = false;
//        foreach ($this->parsers as $parser) {
//            // can the parser do anything with this content?
//            if ($parser->isParser($option['parser'])) {
//                $version_found = $parser->parse($body, $option);
//                break;
//            }
//        }
//
//        return $version_found;
    }
    
    private function getAuthToken($body){
        // get the Auth token 
        preg_match('/name="__RequestVerificationToken" type="hidden" value="(.*?)"/', $body, $match);
        return isset($match['1']) ? $match['1'] : '';
    }
    
    private function getPostData($auth_token, $login, $password){
        return [
            'form_params' => [
                'Password' => $login,
                'UserName' => $password,
                '__RequestVerificationToken' => $auth_token
            ],
            'debug' => true,
            'verify' => false
        ];
    }
    
}
