<?php


namespace app\core;



/**
 * 请求类
 *
 */
class Request
{
	
	/**
	 * 站点爬虫的名称
	 * @var string
	 */
	public $spider;
	
	/**
	 * 结果的处理方法 (站点爬虫实例的方法)
	 * @var string
	 */
	public $method;
	
	/**
	 * 该类要爬去的网页URL
	 * @var string
	 */
	public $url;
	
	/**
	 * 携带的数据
	 * @var array
	 */
	public $_Data = [];
	
	/**
	 * 构造方法
	 * $spider默认为空是为了在处理方法返回时自动赋值(在处理完之后返回一个Reqeuest 构造时不用重复指定spider)
	 */
	public function __construct($url,$method,$spider='',$data=[])
	{
		$this->url = $url;
		$this->method = $method;
		$this->spider = $spider;
		$this->_Data = $data;
		// $this->appendData($data);
	}
	
	/**
	 * 增加携带的数据
	 */
	public function appendData($data)
	{
		$this->_Data = array_merge($this->_Data,$data);
	}
	
}