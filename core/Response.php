<?php

namespace app\core;



class Response
{
	
	
	/**
	 * 爬取的页面URL
	 * @string
	 */
	public $url;
	
	/**
	 * 爬取的页面内容
	 * @var string
	 */
	public $content;
	
	
	/**
	 * 请求对象
	 * @var Request
	 */
	public $request;
	
	
	public function __construct($content)
	{
		$this->content = mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8');
	}
	
}
