<?php

namespace app\core;

/**
 * 去重
 */ 
class Repeat{
	
	/**
	 * redis 集合 键
     */
	const SET_KEY = 'url_repeat';
	
	/**
	 * redis 链接
	 */
	public $conn;
	
	public function __construct()
	{
		$this->conn = new \Redis();
		$this->conn->connect('localhost',6379);
	}
	
	/**
	 * 下载成功后 添加到 集合
	 */
	public function add($url)
	{
		$this->conn->sAdd(self::SET_KEY,md5($url));
	}
	
	/**
	 * url是否存在(已经下载过)
	 */ 
	public function exist($url)
	{
		return $this->conn->sismember(self::SET_KEY,md5($url));
	}
	
	
	public function __destruct()
	{
		unset($this->conn);
	}
	
}
