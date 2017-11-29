<?php


namespace app\spiders;

use app\core\Response;
use XPathSelector\Selector;
use app\core\Request;
// use app\models\Pron91 as Pron91Model;

class Sex  extends \app\core\BaseSpider
{
	public $start_url = ['http://www.sex.com/videos/'];
	
	public $config = [
		
	];
	
	public static $enable = false;
	

	
	public function start(Response $response){
		$xs = Selector::loadHTML($response->content);
		$nodeList = $xs->findAll("//div[@id='masonry_container']/div[@class='masonry_box small_pin_box']");
		
		foreach($nodeList->getIterator() as $iter){
			$url =  $iter->findOneOrNull("//a/@href");
			$name = $iter->findOneOrNull("//div[contains(@class,'title')]/a/text()");
			if($url)
			{
				$url =  self::createNextPage($url->extract(),$response->url);
				if(!Crawl::$repeat->exist($url)){  // 判断是否已采集
					Crawl::$repeat->add($url);  	// 加入去重库
					yield new Request($url, 'detail', '', ['name'=>$name?$name->extract():'']);
				}
			}
		}
		$currentPage = $xs->findOneOrNull("//div[@class='pagination']//a[text()='Next']/@href");
		try{
			yield new Request(self::createNextPage($currentPage->extract(),$response->url), 'start');
		} catch (\Exception $e){
			echo  "####### extract next page failure [".$currentPage->extract()."] \n";
		}
	}
        
	// 获取iframe才是真正的播放页面
	public function getIframe(Response $response)
	{
		$data = [];
		$xs = Selector::loadHTML($response->content);
		$video = $xs->findOneOrNull("//div[@id='viewvideo']//source/@src");
		$duration = $xs->findOneOrNull("//div[@id='useraction']/div[@class='boxPart']");
		$matches = [];
		$data['duration'] =  $duration && preg_match('/(\d+\:\d+)/i', $duration->extract(), $matches) ? $matches[1] : '' ;  // 时长
		
		if($video){
			$data['video'] = $data['file'] = $video->extract();
			self::$current_exit = 0;
			return new Pron91Model(array_merge($response->request->_Data,$data));
		}else{
			self::$current_exit += 1;
			if(self::$current_exit >= self::$max_exit){
				echo '[',$response->request->spider,']: parse and extract video failure(',self::$current_exit,") quit the web spider \n";
				throw new \app\exception\ParseException('Parse and extract video failure:'.$response->url,404);
			}else{
				echo '[',$response->request->spider,']: parse and extract video failure(',self::$current_exit,") Try again later \n";
			}
			return false;
		}
		
	}
	
	// tiqu
	public function detail()
	{
		$xs = Selector::loadHTML($response->content);
		// $video = $xs->findOneOrNull("//div[@id='viewvideo']//source/@src");
		// $duration = $xs->findOneOrNull("//div[@id='useraction']/div[@class='boxPart']");
		
		// player.updateSrc([
                // {
                    // src: '/video/stream/687229',
                    // type: 'video/mp4',
                    // label: 'SD',
                    // res: 360
                // },
                                // {
                    // src: '/video/stream/687229/hd',
                    // type: 'video/mp4',
                    // label: 'HD',
                    // res: 720
                // }
                            // ]);
		
		//div[@id='container']//div[@class='image_frame']/iframe/@src   // 有些有iframe 有些没   iframe链接到别的网站去  网页结构不一致
	}
        
	public static function getMp4Url()
	{
		
		// http://videos1.sex.com/stream/2017/11/18/705097_hd.mp4
		// http://videos2.sex.com/stream/2017/10/26/686237.mp4
		// http://videos1.sex.com/stream/2017/10/26/686237.mp4
		
		    // {
				// src: '/video/stream/705097',
				// type: 'video/mp4',
				// label: 'SD',
				// res: 360
			// },
			// {
				// src: '/video/stream/705097/hd',
				// type: 'video/mp4',
				// label: 'HD',
				// res: 720
			// }
		
	}
		
	public static function createNextPage($url,$currentUrl)
	{
		$urlinfo = parse_url($currentUrl);
		return $urlinfo['scheme'].'://'.$urlinfo['host'].$url;
	}

	
}