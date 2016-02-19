<?php

namespace Cpeter\PhpQkeylmEmailNotification\Qkeylm;

use Cpeter\PhpQkeylmEmailNotification\Exception\EmptyUrlException;
use GuzzleHttp\Exception\RequestException;
use voku\helper\HtmlDomParser;
use Swift_Image;

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
        $res = $this->getUrl($url);

        // get the Auth token 
        $auth_token = $this->getAuthToken($res->getBody());
        $post_data = $this->getPostData($auth_token, $this->options['login'], $this->options['password']);

        $res = $this->postUrl($url, $post_data);
        $wsignin = $this->getWSignInData($res->getBody());
        $post_data = [
            'wa' => 'wsignin1.0',
            'wresult' => $wsignin
        ];

        $url = $this->options['host'].$this->options['page_wsingin'];
        $res = $this->postUrl($url, $post_data);
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
        $res = $this->getUrl($url);

        return $this->extractContent($res->getBody());
    }

    public function extractContent($body)
    {
        // get just the main content
        $html = HtmlDomParser::str_get_html($body);
        $main_content = $html->find('div[id=mainInner]', 0)->outertext;
        $main_content = str_replace($this->options['child_name'], '<strong style="font-size:20px">' . $this->options['child_name'] . '</strong>', $main_content);
        $main_content = str_replace('"/webui/', '"' . $this->options['host'] . '/webui/', $main_content);
        
        // get all images and download them
        preg_match_all('|<img class="image-frame" src="(' . $this->options['host'] . '/webui/Files/Room/small/.*?)">|', $main_content, $images);
        
        // process the images if there is any
        $content['images'] = [];
        if (isset($images[1])){
            foreach($images[1] as $image){
                $content['images'][$image]['small'] = $this->fetchImage($image);
                // generate large image name 
                $large_image = str_replace('small', 'large', $image);
                $content['images'][$image]['large'] = $this->fetchImage($large_image);
            }    
        }

        $main_content = $this->addStyles($main_content);
        $content['body'] = $main_content;

        return  $content;
    }

    /**
     * Since fetching images needs authenticated user we'll need to couple the image processing with this class
     * 
     * @param $body
     * @return string
     */
    private function fetchImage($url){
        
        if (function_exists('sys_get_temp_dir') && is_writable(sys_get_temp_dir()) && ($tmpFile = tempnam(sys_get_temp_dir(), 'img_'))) {
            /* We have opened a tmpfile */
        } else {
            throw new Exception('Unable to fetch the image to make it attachable, sys_temp_dir is not writable');
        }

        if (!$this->logged_in){
            $this->login();
        }

        $this->getUrl($url, ['save_to' => $tmpFile]);

        return $tmpFile;
    }
    
    private function postUrl($url, $post_data, $options = []){
        try {
            $options = array_merge($options, ['verify' => false, 'form_params' => $post_data]);
            $res = $this->client->request('POST', $url, $options);
        } catch(RequestException $e){
            $status_code = $e->getCode();
            throw new EmptyUrlException("URL '$url'' returned status code: $status_code. Was expecting 200.");
        }

        $status_code = $res->getStatusCode();
        if ($res->getStatusCode() != 200){
            throw new EmptyUrlException("URL '$url'' returned status code: $status_code. Was expecting 200.");
        }
        return $res;
    }
    
    private function getUrl($url, $options = []){
        if (empty($url)) {
            throw new EmptyUrlException("URL must be set. We can not parse empty url.");
        }

        // fetch url and get the version id
        try {
            $options = array_merge($options, ['verify' => false]);
            $res = $this->client->get($url, $options);
        } catch(RequestException $e){
            $status_code = $e->getCode();
            throw new EmptyUrlException("URL '$url'' returned status code: $status_code. Was expecting 200.");
        }

        $status_code = $res->getStatusCode();
        if ($res->getStatusCode() != 200){
            throw new EmptyUrlException("URL '$url'' returned status code: $status_code. Was expecting 200.");
        }
        
        return $res;
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
            'UserName' => $login,
            'Password' => $password,
            '__RequestVerificationToken' => $auth_token
        ];
    }

    private function getWSignInData($body)
    {
        // wsignin data
        preg_match('/wresult" value="(.*?)"/', $body, $match);
        return isset($match['1']) ?  html_entity_decode($match['1']) : '';
    }

    private function addStyles($html){

        // hacking some style to the body.
        $html .= "<style>
.gaurav_ratiocinative_main_pic_gallery ul  {
  	list-style-type: none;
    margin: 0;
    padding: 0;
};
li {
    float: left;
    margin-right: 10px;
}
li:last-child {
    float:none;
}
</style>";

        return $html;
    }
}
