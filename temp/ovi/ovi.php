<?php

// use this
// https://packagist.org/packages/voku/simple_html_dom


require 'simple_html_dom.php';

#      Quizoo automation
// define the absolute path where the cookie file should be stored by curl
define('PARSER_DIR', './');
include_once('CURLer.php');

$CURLer = new CURLer();
$CURLer->setOption(CURLOPT_RETURNTRANSFER, true);
$CURLer->setOption(CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; Acoo Browser; .NET CLR 1.1.4322; .NET CLR 2.0.50727)');
$CURLer->setOption(CURLOPT_FOLLOWLOCATION, 1);
$CURLer->setOption(CURLOPT_TIMEOUT, 1000);
//$CURLer->setOption(CURLOPT_VERBOSE, true);
$CURLer->setOption(CURLOPT_COOKIEFILE, "C:\Users\Peter\Csaba\PHP\ovi\cookie.jar");
$CURLer->setOption(CURLOPT_COOKIEJAR, "C:\Users\Peter\Csaba\PHP\ovi\cookie.jar");

// delete old cookies
$fp = fopen("C:\Users\Peter\Csaba\PHP\ovi\cookie.jar", 'w');  // Sets the file size to zero bytes
fclose($fp);

# Auth token
$headers = array(
    "User-Agent: Mozilla/5.0 (Windows NT 5.1; U; en; rv:1.8.1) Gecko/20061208 Firefox/5.0 Opera 11.1" . rand(1, 10000),
    "Accept: application/json, text/javascript, */*; q=0.01",
    "Accept-Language: en-US,en;q=0.5",
    // "Accept-Encoding: gzip, deflate",
    "Connection: close",
    "Referer: https://www.qkenhanced.com.au/external/Star/SignIn?wa=wsignin1.0&wtrealm=https://www.qkenhanced.com.au/webui/",
    "Content-Type: application/x-www-form-urlencoded; charset=UTF-8"
);
$CURLer->setOption(CURLOPT_HTTPHEADER, $headers);

# start url. we need to start from somewhere right?
$url = 'https://www.qkenhanced.com.au/external/Authentication/SignIn?ReturnUrl=/external/';
$CURLer->execute($url);
$content = $CURLer->getContent();

// get the Token
preg_match('/name="__RequestVerificationToken" type="hidden" value="(.*?)"/', $content, $match);
$auth_token = $match['1'];

$params = [
    'Password' => '*****************',
    'UserName' => '*******************',
    '__RequestVerificationToken' => $auth_token
];
// log in
$CURLer->executePost($url, $params);

// get dashboard
$CURLer->execute('https://www.qkenhanced.com.au/external/Navigation/Enhanced');
$content = $CURLer->getContent();
preg_match('/wresult" value="(.*?)"/', $content, $match);
$wresult = html_entity_decode($match['1']);

$params = [
    'wa' => 'wsignin1.0',
    'wresult' => $wresult
];
$CURLer->executePost('https://www.qkenhanced.com.au/webui/', $params);

// get dashboard
$CURLer->execute('https://www.qkenhanced.com.au/webui/ProgramJournal/RedirectToPJ?ChildId=13715106&RoomId=3676407');
$content = $CURLer->getContent();

// get just the main content
$html = str_get_html($content);
$content = $html->find('div[id=mainInner]', 0)->outertext;
$content = str_replace('Adel', '<strong style="font-size:20px">Adel</strong>', $content);
$content = preg_replace('|<img class="image-frame" src="/webui/Files/Room/small/(.*?)">|', '<a href="https://www.qkenhanced.com.au/webui/Files/Room/large/$1"><img class="image-frame" src="https://www.qkenhanced.com.au/webui/Files/Room/small/$1"></a>', $content);

print $content;


?>