<?php

namespace Cpeter\PhpQkeylmEmailNotification\Qkeylm;

use Cpeter\PhpQkeylmEmailNotification\Exception\EmptyUrlException;
use GuzzleHttp\Exception\RequestException;
use voku\helper\HtmlDomParser;

class QkeylmApi
{

    private $options = array();

    private $logged_in;
    
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
        try {
            $res = $this->client->get($url, ['verify' => false]);
        } catch(RequestException $e){
            $status_code = $e->getCode();
            throw new EmptyUrlException("URL '$url'' returned status code: $status_code. Was expecting 200.");
        }

        $status_code = $res->getStatusCode();
        if ($res->getStatusCode() != 200){
            throw new EmptyUrlException("URL '$url'' returned status code: $status_code. Was expecting 200.");
        }
        
        // get the Auth token 
        $auth_token = $this->getAuthToken($res->getBody());
        $post_data = $this->getPostData($auth_token, $this->options['login'], $this->options['password']);

        try {
            $res = $this->client->request('POST', $url, $post_data);
        } catch(RequestException $e){
            $status_code = $e->getCode();
            throw new EmptyUrlException("URL '$url'' returned status code: $status_code. Was expecting 200.");
        }

        $status_code = $res->getStatusCode();
        if ($res->getStatusCode() != 200){
            throw new EmptyUrlException("URL '$url'' returned status code: $status_code. Was expecting 200.");
        }
        
        $wsignin = $this->getWSignInData($res->getBody());
        $post_data = [
            'form_params' => [
                    'wa' => 'wsignin1.0',
                    'wresult' => $wsignin
            ],
            'verify' => false
        ];

        $url = $this->options['host'].$this->options['page_wsingin'];
        try {
            $res = $this->client->request('POST', $url, $post_data);
        } catch(RequestException $e){
            $status_code = $e->getCode();
            throw new EmptyUrlException("URL '$url'' returned status code: $status_code. Was expecting 200.");
        }

        $status_code = $res->getStatusCode();
        if ($res->getStatusCode() != 200){
            throw new EmptyUrlException("URL '$url'' returned status code: $status_code. Was expecting 200.");
        }

        $body = $res->getBody();
        $this->logged_in = strpos($body, "/webui/Account/Logout") !== false;

        return $this->logged_in;
    }

    /**
     * Add option for data?
     */
    public function getDailyJournal()
    {
        if (!$this->logged_in){
            $this->login();
        }

        $url = $this->options['host'].$this->options['page_journal'];
        if (empty($url)) {
            throw new EmptyUrlException("URL must be set. We can not parse empty url.");
        }

        // fetch url and get the version id
        try {
            $res = $this->client->get($url, ['verify' => false]);
        } catch(RequestException $e){
            $status_code = $e->getCode();
            throw new EmptyUrlException("URL '$url'' returned status code: $status_code. Was expecting 200.");
        }

        $status_code = $res->getStatusCode();
        if ($res->getStatusCode() != 200){
            throw new EmptyUrlException("URL '$url'' returned status code: $status_code. Was expecting 200.");
        }

        $body = $res->getBody();
        $content = $this->extractContent($body);

        return  $content;
    }

    public function extractContent($body)
    {
        // get just the main content
        $html = HtmlDomParser::str_get_html($body);
        $content = $html->find('div[id=mainInner]', 0)->outertext;
        $content = str_replace($this->options['child_name'], '<strong style="font-size:20px">' . $this->options['child_name'] . '</strong>', $content);
        $content = str_replace('"/webui/', '"' . $this->options['host'] . '/webui/', $content);

        // @todo
        // images can not be viewed because it will require user login
        // download images and attach to the email as small and large one, then delete the file

        $content = preg_replace(
            '|<img class="image-frame" src="' . $this->options['host'] . '/webui/Files/Room/small/(.*?)">|',
            '<a href="' . $this->options['host'] . '/webui/Files/Room/large/$1"><img class="image-frame" src="' . $this->options['host'] . '/webui/Files/Room/small/$1"></a>',
            $content);

        return ['body' => $content];
    }

    private function getAuthToken($body)
    {
        // get the Auth token 
        preg_match('/name="__RequestVerificationToken" type="hidden" value="(.*?)"/', $body, $match);
        return isset($match['1']) ? $match['1'] : '';
    }
    
    private function getPostData($auth_token, $login, $password)
    {
        return [
            'form_params' => [
                'UserName' => $login,
                'Password' => $password,
                '__RequestVerificationToken' => $auth_token
            ],
            'verify' => false
        ];
    }

    private function getWSignInData($body)
    {
        // wsignin data
        preg_match('/wresult" value="(.*?)"/', $body, $match);
        return isset($match['1']) ?  html_entity_decode($match['1']) : '';
    }
}
