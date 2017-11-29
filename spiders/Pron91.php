<?php


namespace app\spiders;

use app\core\Response;
use XPathSelector\Selector;
use app\core\Request;
use app\models\Pron91 as Pron91Model;

class Pron91  extends \app\core\BaseSpider
{
	public $start_url = ['http://91porn.com/video.php'];
	
	public $config = [
		
	];
	
	public static $enable = false;
	
	/**
	 * 限量
	 */
	// public static $limited = 10;
	
	/**
	 * 当前数量
	 */
	// public static $currentNO = 0;
	
	/**
	 * 获取 唯一 URL 
	 * @example
	 * 		http://91porn.com/view_video.php?viewkey=38e0245256026a0d05f4&page=1&viewtype=basic&category=mr  
	 *  	http://91porn.com/view_video.php?viewkey=38e0245256026a0d05f4
	 */
	public static function getUniqueUrl($url)  // 放到基类
	{
		$arr = parse_url($url);
		parse_str($arr['query'],$parr);
		return $arr['scheme'].'://'.$arr['host'].$arr['path'].'?viewkey='.$parr['viewkey'];
	}
	
	public function start(Response $response){
		$xs = Selector::loadHTML($response->content);
		$nodeList = $xs->findAll("//div[@id='videobox']//div[@class='listchannel']");
		foreach($nodeList->getIterator() as $iter){
			$url =  $iter->findOneOrNull("//div[contains(@class, 'imagechanne')]/a/@href");
			$name = $iter->findOneOrNull("//div[contains(@class, 'imagechanne')]/a/img/@title");
			if($url)
			{
				$url = self::getUniqueUrl( $url->extract());
				if(!Crawl::$repeat->exist($url)){  // 判断是否已采集
					Crawl::$repeat->add($url);  	// 加入去重库
					yield new Request($url, 'detail', '', ['name'=>$name?$name->extract():'']);
				}
			}
		}
		$currentPage = $xs->findOneOrNull("//div[@id='paging']//span[@class='pagingnav']");
		try{
			$nextPage = $currentPage->node->nextSibling->getAttribute('href');
			yield new Request(self::createNextPage($nextPage,$response->url), 'start');
		} catch (\Exception $e){
			echo  "####### extract next page failure [".$currentPage->extract()."] \n";
		}
	}
        
        
	public function detail(Response $response)
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
        
	public static function createNextPage($pageNo,$url)
	{
		$urls = explode('?', $url);
		return $urls[0].$pageNo;
	}
	
	
	
	
}