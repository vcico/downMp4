<?php


namespace app\spiders;


use app\core\Response;
use XPathSelector\Selector;
use app\core\Request;
use app\models\CaoceeModel;
use app\core\Crawl;
use app\core\Category;

class Caocee extends \app\core\BaseSpider
{
	
	public $start_url = [
		'https://vid.caocee.com/videos?c=1' => Category::SELFIE,
		'https://vid.caocee.com/videos?c=5' => Category::OTHER,
		'https://vid.caocee.com/videos?c=10' => Category::OTHER,
		'https://vid.caocee.com/videos?c=11' =>  Category::USA,
		'https://vid.caocee.com/videos?c=17' => Category::OTHER,
		'https://vid.caocee.com/videos?c=19' => Category::OUTDOOR,
		'https://vid.caocee.com/videos?c=20' => Category::SECRETLY,
		'https://vid.caocee.com/videos?c=27' => Category::OTHER,
		'https://vid.caocee.com/videos?c=26' => Category::OTHER,
	];
	
	public static $enable = false;
	
	public $config = [
		
	];
	
	public function start(Response $response){
		
		$xs = Selector::loadHTML($response->content);
		$nodeList = $xs->findAll("//div[@id='content']//div[@class='video_box']");
		foreach($nodeList->getIterator() as $iter){
			$duration =  $iter->findOneOrNull("//div[@class='box_left']");
			$url =  $iter->findOneOrNull("//a/@href");
			$name = $iter->findOneOrNull("//a/span");
			if($url)
			{
				$url = self::getCompleteUrl( $url->extract(),$response->url);
				if(!Crawl::$repeat->exist($url)){  // 判断是否已采集
					Crawl::$repeat->add($url);  	// 加入去重库
					yield new Request(
						$url,
						'detail', 
						'', 
						[
							'name'=>$name?$name->extract():'',
							'duration'=>$duration?$duration->extract():''
						]
					);
				}
			}
		}
		$nextPage = $xs->findOneOrNull("//div[@id='content']//div[@class='pagination']//li[last()]/a[@class='prevnext']/@href");
		try{
			yield new Request($nextPage->extract(), 'start');
		} catch (\Exception $e){
			echo  "####### get next page failure [".$currentPage->extract()."] \n";
		}
	}
	
	public function detail(Response $response)
	{
		$xs = Selector::loadHTML($response->content);
		$videoDM = $xs->findOneOrNull("//div[@id='player']/script[2]");
		if($videoDM && preg_match('/file: "([^"]+)",/',$videoDM->extract(),$match)){
			$video = $match[1];
			self::$current_exit = 0;
			return new CaoceeModel(array_merge($response->request->_Data,['video'=>$video,'file'=>$video]));
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
	

	/**
	 * 获取完整的详情页URL
	 * @param string $url 需要补全的URL
	 * @param string $currentUrl 当前的URL
	 * @return string
	 */ 
	public static function getCompleteUrl($url,$currentUrl){
		$urlinfo = parse_url($currentUrl);
		// if(substr($url,0,1)=='/'){
		return $urlinfo['scheme'].'://'.$urlinfo['host'].$url;
		// }else{			
		// }
	}
	
}