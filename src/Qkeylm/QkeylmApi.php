<?php

namespace Cpeter\PhpQkeylmEmailNotification\Qkeylm;

use Cpeter\PhpQkeylmEmailNotification\Exception\EmptyUrlException;
use GuzzleHttp\Exception\RequestException;
use voku\helper\HtmlDomParser;

/**
 * Class QkeylmApi
 * 
 * Handle interactions with the QKeyLM website:
 * - login
 * - fetch journal page for the current day
 * - fetch images
 * - prepare html to be set to the client
 * 
 * @package Cpeter\PhpQkeylmEmailNotification\Qkeylm
 */
class QkeylmApi
{

    /**
     * Config values passed to the app
     *
     * @var array
     */
    private $config = array();

    /**
     * Track the login state
     *
     * @var bool
     */
    private $logged_in = false;

    /**
     * QkeylmApi constructor.
     *
     * @param array $config
     */
    public function __construct($config)
    {
        $this->config = $config;
        // http client
        $this->client = new \GuzzleHttp\Client(
            [
                'cookies' => true,
                'headers' => [
                    'Referer'   => $this->config['host']
                ]
            ]
        );
    }

    /**
     * Log in to the website.
     *
     * There are a couple of securify measures in place to get the user authetificated. Hence we need to
     * send the initial login request, then follow up with another post with the security challange.
     *
     * @return bool true|false True if login successfull
     * @throws EmptyUrlException
     */
    public function login()
    {
        $url = $this->config['host'].$this->config['page_token'];
        $res = $this->getUrl($url);

        // get the Auth token
        $auth_token = $this->getAuthToken($res->getBody());
        $post_data = $this->getPostData($auth_token, $this->config['login'], $this->config['password']);

        $res = $this->postUrl($url, $post_data);
        $wsignin = $this->getWSignInData($res->getBody());
        $post_data = [
            'wa' => 'wsignin1.0',
            'wresult' => $wsignin
        ];

        $url = $this->config['host'].$this->config['page_wsingin'];
        $res = $this->postUrl($url, $post_data);
        $body = $res->getBody();
        $this->logged_in = strpos($body, "/webui/Account/Logout") !== false;

        return $this->logged_in;
    }

    /**
     * Fetch the daily journal.
     *
     * @todo upon request this methos could be improved to accept a date the journal to be fetched from
     * @return mixed
     * @throws EmptyUrlException
     */
    public function getDailyJournal()
    {
        if (!$this->logged_in) {
            $this->login();
        }

        $url = $this->config['host'].$this->config['page_journal'];
        $res = $this->getUrl($url);

        return $this->extractContent($res->getBody());
    }

    /**
     * Parse the HTML page returned by the website and keep just the main body that we are interested in.
     * All embeded images are downloaded locally.
     *
     * @param string $body
     * @return array
     * @throws Exception
     */
    public function extractContent($body)
    {
        // get just the main content
        $html = HtmlDomParser::str_get_html($body);
        $main_content = $html->find('div[id=mainInner]', 0)->outertext;
        $main_content = $this->highlightChildName($this->config['child_name'], $main_content);
        $main_content = str_replace('"/webui/', '"' . $this->config['host'] . '/webui/', $main_content);
        
        // get all images and download them
        preg_match_all(
            '|<img class="image-frame" src="(' . $this->config['host'] . '/webui/Files/Room/small/.*?)">|',
            $main_content,
            $images
        );
        
        // process the images if there is any
        $content['images'] = [];
        if (isset($images[1])) {
            foreach ($images[1] as $image) {
                $content['images'][$image]['small'] = $this->fetchImage($image);
                // generate large image name
                $large_image = str_replace('small', 'large', $image);
                $content['images'][$image]['large'] = $this->fetchImage($large_image);
            }
        }

        // add some inline style to be used by the email client
        $main_content = $this->addStyles($main_content);
        $content['body'] = $main_content;

        // get the date the journal is from
        $date = $html->find('div[class=head-dailyjournal-txt]', 0)->innertext;
        // remove some extra content
        $date = preg_replace("|(\d{4}).*$|", "$1", $date);
        $content['date'] = date("Y-m-d", strtotime(trim($date)));

        return  $content;
    }

    /**
     * Fetch images using the authentificated session.
     *
     * Images are login protected and hence this process can not be decoupled form this class.
     * 
     * @param $body
     * @return string
     */
    private function fetchImage($url)
    {
        // fetching images require logged in state
        if (!$this->logged_in) {
            $this->login();
        }

        if (function_exists('sys_get_temp_dir') && is_writable(sys_get_temp_dir()) &&
            ($tmpFile = tempnam(sys_get_temp_dir(), 'img_'))) {
            /* We have opened a tmpfile */
        } else {
            throw new Exception('Unable to fetch the image to make it attachable, sys_temp_dir is not writable');
        }

        $this->getUrl($url, ['save_to' => $tmpFile]);

        return $tmpFile;
    }

    /**
     *  
     *
     * @param $url
     * @param $post_data
     * @param array $config
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws EmptyUrlException
     */
    private function postUrl($url, $post_data, $config = [])
    {
        try {
            $config = array_merge($config, ['verify' => false, 'form_params' => $post_data]);
            $res = $this->client->request('POST', $url, $config);
        } catch (RequestException $e) {
            $status_code = $e->getCode();
            throw new EmptyUrlException("URL '$url'' returned status code: $status_code. Was expecting 200.");
        }

        $status_code = $res->getStatusCode();
        if ($res->getStatusCode() != 200) {
            throw new EmptyUrlException("URL '$url'' returned status code: $status_code. Was expecting 200.");
        }
        return $res;
    }
    
    private function getUrl($url, $config = [])
    {
        if (empty($url)) {
            throw new EmptyUrlException("URL must be set. We can not parse empty url.");
        }

        // fetch url and get the version id
        try {
            $config = array_merge($config, ['verify' => false]);
            $res = $this->client->get($url, $config);
        } catch (RequestException $e) {
            $status_code = $e->getCode();
            throw new EmptyUrlException("URL '$url'' returned status code: $status_code. Was expecting 200.");
        }

        $status_code = $res->getStatusCode();
        if ($res->getStatusCode() != 200) {
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

    private function highlightChildName($child_names, $body)
    {
        if (!is_array($child_names)) {
            $child_names = [$child_names];
        }
        foreach ($child_names as $child_name) {
            $body = str_replace($child_name, '<strong style="font-size:20px">' . $child_name . '</strong>', $body);
        }

        return $body;
    }

    private function addStyles($html)
    {
        // hacking some style to the body.
        $html = str_replace('class="programjournal-smallimg"', 'style="float: left; margin-right: 10px"', $html);
        $html = str_replace(
            'class="gaurav_ratiocinative_main_pic_gallery-smallimg"',
            'style="float: none; clear: both"',
            $html
        );
        $html = str_replace('<ul>', '<ul style="list-style-type: none;  margin: 0; padding: 0;">', $html);
        return $html;
    }
}
