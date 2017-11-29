<?php


namespace app\core;

use app\exception\ErrorException;
use app\core\Response;

abstract class BaseSpider
{
	
	public $start_url;
	
	public static $max_exit = 3;  // 最大异常次数  达到爬虫退出
	
	public static $current_exit = 0;
	
	/**
	 * 是否启用
	 */
	public static $enable = false;
	
	public function __construct()
	{
		if(!$this->start_url)
		{
			throw new ErrorException('请设置爬虫start_url');
		}
	}
	
	/**
	 * 获取 唯一 URL   不需要反正基类
	 * @example
	 * 		参: http://91porn.com/view_video.php?viewkey=38e0245256026a0d05f4&page=1&viewtype=basic&category=mr  
	 *  	值: http://91porn.com/view_video.php?viewkey=38e0245256026a0d05f4
	 *      page  viewtype 参数不确定   viewkey 作为唯一标示(不可缺 其他有没有都行)
	 */
	public static function getUniqueUrl($url){
		return $url;
	}
	
	
	/**
	 * start_url的数据处理程序
	 */
	abstract public function start(Response $response);
	
}