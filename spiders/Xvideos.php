<?php



namespace app\spiders;

use app\core\Response;
use XPathSelector\Selector;
use app\core\Request;
use app\models\XvideosModel;
use app\core\Crawl;
use app\core\Category;


class Xvideos extends \app\core\BaseSpider
{
	
	
	public $start_url = [
		// 'http://www.xvideos.com/porn/chinese/',
		// 'http://www.xvideos.com/porn/japanese',
		// 'http://www.xvideos.com/porn/korean',
		// 'http://www.xvideos.com/',
		'http://www.xvideos.com/?k=China' => Category::CHINA,
		// 'http://www.xvideos.com/?k=japan&sort=relevance&datef=all&durf=1-3min&typef=straight' => Category::JAPAN,
		// 'http://www.xvideos.com/?k=china&sort=relevance&datef=all&durf=1-3min&typef=straight' => Category::CHINA,
		// 'http://www.xvideos.com/?k=USA&sort=relevance&datef=all&durf=1-3min&typef=straight' => Category::USA,
		// 'http://www.xvideos.com/?k=korean&sort=relevance&datef=all&durf=1-3min&typef=straight' => Category::KOREAN,
		// 'http://www.xvideos.com/?k=outdoor&sort=relevance&datef=all&durf=1-3min&typef=straight' => Category::OUTDOOR,
		
	];
	public $config = [
		
	];
	
	public static $enable = TRUE;
	
	public $video_Clarity = "/html5player.setVideoUrlLow\('(http[s]{0,1}:\/\/[^']*)'\)/";
	
	// html5player.setVideoUrlHigh();  // 720p
	
	
	 public function start(Response $response){
		$xs = Selector::loadHTML($response->content);
		$nodeList = $xs->findAll("//div[@id='content']//div[contains(@id,'video_')]");
		
		foreach($nodeList->getIterator() as $iter){
                    $url =  $iter->findOneOrNull("//div[@class='thumb']/a/@href");
                    $name = $iter->findOneOrNull("//p[1]/a/@title");
                    $duration = $iter->findOneOrNull("//p[@class='metadata']//span[@class='duration']/text()");
                    if($url)
                    {
                        $url =  self::getFullUrl($url->extract(),$response->url);
                        if(!Crawl::$repeat->exist($url)){  // 判断是否已采集
                            Crawl::$repeat->add($url);  	// 加入去重库
                            yield new Request($url, 'detail', '', ['name'=>$name?utf8_decode($name->extract()):'','duration' => $duration?$duration->extract():'']);
                        }
                    }
		}
		$nextPage = $xs->findOneOrNull("//div[contains(@class,'pagination')][1]//li/a[@class='active']/../following-sibling::li[1]/a/@href");
		if($nextPage)
			yield new Request(self::getFullUrl($nextPage->extract(),$response->url), 'start');
		else
			echo  "####### extract next page failure [".$response->url."] \n";
	}
	
	public function detail(Response $response)
	{
		// $xs = Selector::loadHTML($response->content);
		if(preg_match($this->video_Clarity, $response->content, $matches))
		{
			$data = $response->request->_Data;
			$data['video'] = $data['file'] = $matches[1];
			// print_r($data);
			return new XvideosModel($data);
		}else{
			throw new \app\exception\ParseException('parse and extract MP4 failure');
		}
	}
	
	
	public static function getFullUrl($url,$currentUrl)
	{
		$urlinfo = parse_url($currentUrl);
		return $urlinfo['scheme'].'://'.$urlinfo['host'].$url;
	}
	
}