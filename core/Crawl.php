<?php

namespace app\core;

use app\exception\ConfigException;
use app\exception\ErrorException;
use Generator;
use app\core\Category;

/**
 * 爬虫核心类 
 */
class Crawl
{
	
	/**
	 * 全局配置
	 */
	public static $config;

	/**
	 * 站点爬虫
	 * @var 未定
	 */ 
	public static $spiders = [];
	
	/**
	 * 请求队列
	 * @var array
	 */
	public $queue = [];
	
	/**
	 * http请求对象
	 * @var Http
	 */
	public $http;
	
	/**
	 * 数据入库处理类
	 * @var Db
	 */
	public $db;
	
	/**
	 * 重复过滤类
	 */
	public static $repeat;
	
	public function __construct($config)
	{
		self::$config = $config;
		$this->initSpiders();
		if(count($this->queue) < 1)
		{
			echo '未找到启用的站点爬虫',"\n";
			exit;
		}
		$this->http = new Http();
		$this->db = new Db();
		self::$repeat = new Repeat();
	}
	
	/**
	 * 检查必要的配置
	 */
	public function checkConfig()
	{
		$configError = [
			'spider_path' => '找不到站点爬虫路径',
			'spider_space'=>'找不到站点爬虫命名空间',
			'spider' => '缺少爬虫配置'
		];
		foreach($configError as $key => $val){
			if(!isset(self::$config[$key]))
				throw new ConfigException('配置出错: '.$val);
		}
	}
	
	/**
	 *  初始化请求队列
	 * 两种 start_url 有分类和无分类
		 ['http://91porn.com/video.php' ...]
		 ['https://vid.caocee.com/videos?c=1' => '网友自拍' ...]
	 */
	public function initRequestQueue($spider_name)
	{
		foreach((self::$spiders[$spider_name])->start_url as $key => $val)
		{
			if(is_numeric($key)){
				array_push($this->queue,new Request($val,'start',$spider_name,['cate' => Category::OTHER]));
			}else{
				array_push($this->queue,new Request($key,'start',$spider_name,['cate' => $val]));
			}
		}
	}
	
	/**
	 * 初始化爬虫站点
	 */
	public function initSpiders()
	{
		$this->checkConfig();
		$spiders = scandir(self::$config['spider_path']);
		foreach($spiders as $key => $spider)
		{
			if($spider != '.' && $spider != '..'){
				$spider_name = basename($spider,'.php');
				$classname =  self::$config['spider_space'].$spider_name;
				if($classname::$enable){
					$spider_instance = new $classname();
					$spider_instance->config = array_merge(self::$config['spider'],$spider_instance->config);  // 站点个性化配置
					self::$spiders[$spider_name] = $spider_instance;
					$this->initRequestQueue($spider_name);
				}
			}
		}
	}
	
	
	public function _run()
	{
		$request = array_shift($this->queue);
		// echo 'get Request',"\n";
		// print_r($request);
		
		$response = $this->http->getResponse($request);
		if($response === false)  // 获取不到页面
			return false;
		if(!isset(self::$spiders[$request->spider])) // 站点爬虫已经删除
		{
			echo '[',$request->spider,']ignore :web spider is not exist ',"\n";
			return false;
		}
		$spider = self::$spiders[$request->spider];
		$method = $request->method;
		if(!method_exists($spider,$method)){
			throw new ErrorException('爬虫方法不存在: '.$method);
		}
		// file_put_contents('result.html',$response->content);
		
		try{
			$result = $spider->$method($response);  // 页面采集回来的后处理程序
			if($result instanceof Generator){    // 爬虫结果处理后返回的若是 生成器  生成的是Request
				foreach($result as $key => $newRequest)
				{
					echo 'new Request add to queue :',$newRequest->url,"\n";
					if($newRequest instanceof Request){ 
						$newRequest->spider = $request->spider;
						$newRequest->appendData($request->_Data);
						// echo 'New Request',"\n";
						// print_r($newRequest);
						array_push($this->queue,$newRequest);   // 产生的Request 放入队列
					}
				}
			}elseif($result instanceof BaseModel){  	// 得到最终数据
				echo 'OO get Model [',$request->url,']',"\n";
				try{
					$id = $this->db->insert($result);
					echo 'Success - add data to db : [',$id,'] ',$response->url,"\n";
				}catch(\app\exception\DbException $e)
				{
					echo $e->getMessage(),$response->url,"\n";
				}
			}			
		}catch(\app\exception\ParseException $e)
		{
			echo 'parse and extract Important data failute',"\n";
			if($e->getCode() == 404){
				echo '[',$request->spider,'] quit the web quit .  this all now spiders ',"\n";
				unset(self::$spiders[$request->spider]);
				// print_r(self::$spiders);
			}
		}
		
		unset($response);
		unset($request);
	}
	
	public function run()
	{
		echo '-------- init spiders ------------',"\n";
		print_r(self::$spiders);
		// print_r($this->queue);
		
		while(count($this->queue) > 0)
		{
			$this->_run();
		}
		
		
	}
	
}