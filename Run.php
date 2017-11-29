<?php

use app\core\Crawl;

// use app\core\Http;
use app\core\Request;
use app\core\Response;
// use app\spiders\Caocee;
// use app\spiders\Pron91;
// use app\core\Repeat;
use app\core\Data;
// use app\models\Pron91 as Pron91Model;

/**
 * 去重
 * 停止条件
 * 异常处理
 * 代理
 */
//$rs = mb_convert_encoding($rs, 'utf-8', 'GBK,UTF-8,ASCII');
//echo mb_convert_encoding($rs, 'GBK','utf-8');
require "vendor/autoload.php";

// $repeat = new Repeat();  // URL重复过滤类 需要初始化(redis)

libxml_use_internal_errors(true);


$config = require "Config.php";

(new Crawl($config))->run();


// $db = new \app\core\Db ();
// $content = file_get_contents('detail.html');
// $spider = new app\spiders\Xvideos();
// $res = new Response($content);
// $res->url = 'http://91porn.com/video.php';

// $res->request = new Request($spider->start_url[0],'detail','xvideos',['name' => '青春小嫩模完美约会']);
// $result = $spider->detail($res);
// echo mb_convert_encoding($res->request->_Data['name'], 'GBK','utf-8');
 // $db->insert ($result);


// Data::cleanTemp();

// $data = [
	// 'name' =>  'it is sex',
	// 'video' => 'http://g.t4k.space//mp43/243858.mp4?st=ydXvsAi-ol0YEnQFHFAOAg&e=1511479473',
	// 'duration' => '11:04',
	// 'file' => 'http://g.t4k.space//mp43/243858.mp4?st=ydXvsAi-ol0YEnQFHFAOAg&e=1511479473'
// ];
// $db = new \app\core\Db();
// echo $db->insert(new Pron91Model($data));



// $repeat->add( Pron91::getUniqueUrl('http://91porn.com/view_video.php?viewkey=e5755c5fa02f9dea8c92'));
// $repeat->add( Pron91::getUniqueUrl('http://91porn.com/view_video.php?viewkey=38e0245256026a0d05f4&page=1&viewtype=basic&category=mr'));
// $repeat->add( Pron91::getUniqueUrl('http://91porn.com/view_video.php?viewkey=8929f257f85318c0d2ee&page=2&viewtype=basic&category=mr'));
// $repeat->add( Pron91::getUniqueUrl('http://91porn.com/view_video.php?viewkey=7589fa25f755efdc8497&page=2&viewtype=basic&category=mr'));
// $repeat->add( Pron91::getUniqueUrl('http://91porn.com/view_video.php?viewkey=cf125e4f77f45425ccd2&page=2&viewtype=basic&category=mr'));
// $repeat->add( Pron91::getUniqueUrl('http://91porn.com/view_video.php?viewkey=e9aaeaa61f5f08b2ab3c&page=2&viewtype=basic&category=mr'));
// var_dump( $repeat->exist(Pron91::getUniqueUrl('http://91porn.com/view_video.php?viewkey=e9aaeaa61f5f08b2ab3c&page=5&viewtype=basic&category=mr')) );

// $http = new Http();
// $spider = new app\spiders\Xvideos();

// $content = file_get_contents('https://www.xvideos.com/video28989051/6jia9.com_chinese_homemade_groupsex_party');
// file_put_contents('detail.html', $content);
// var_dump(preg_match("/html5player.setVideoUrlLow\('(https:\/\/[^']+)'\)/", $content, $matches));
// print_r($matches);
// exit;

// $content = file_get_contents('detail.html');
 // $video_Clarity = "/html5player.setVideoUrlLow\('(http[s]{0,1}:\/\/[^']*)'\)/";
// var_dump(preg_match($video_Clarity, $content, $matches));
// print_r($matches);

// $res = new Response($content);
// $res->url = 'http://91porn.com/video.php';
// $res->request = new Request($spider->start_url[0],'start','xvideos',['name'=>'sex sex']);
// $result = $spider->detail($res);

// print_r($result);

// if($result instanceof  \app\core\BaseModel)
	// print_r($result);


