<?php


namespace app\core;


use app\exception\ErrorException;
use app\exception\DbException;

class Db
{
	
	/**
	 * 数据模型
	 * @var BaseModel
	 */
	public $model;
	
	/**
	 * 表结构
	 * @var array
	 * @example ['91pron' => ['name','video','duration']]
	 */
	public static $tableSchema = [];
	
	/**
	 * Mysql类
	 * @var \MysqliDb
	 */
	private static $mysqldb;
	
	/**
	 * 数据处理类
	 * @var Data
	 */
	private static $data;
	
	public function __construct()
	{
		if(!self::$mysqldb)
			self::$mysqldb = new \MysqliDb ('localhost', 'root', 'root', 'short_video');
		if(!self::$data)
			self::$data = Data::getInstance();
	}
	
	/**
	 * 插入之前要做的事情 如 下载 验证数据长度、类型等
	 */
	public function beforeInsert(){}
	
	
	/**
	 * 获取表结构
	 */
	final public function getTableSchema()
	{
		/*
		select COLUMN_NAME from information_schema.columns
		where table_schema = 'ebook'  #表所在数据库
		and table_name = 'user' ; #你要查的表
		*/
		
		$table = $this->model::tableName();
		// echo "SELECT COLUMN_NAME FROM information_schema.columns where table_schema = 'short_video'   and table_name = '$table' ";
		// exit;
		if(!isset(self::$tableSchema[$table]))
		{
			self::$tableSchema[$table] = self::$mysqldb->rawQueryValue("SELECT COLUMN_NAME FROM information_schema.columns where table_schema = 'short_video'   and table_name = '$table' " );
		}
		return self::$tableSchema[$table];
	}
	
	private function _insert()
	{
		foreach($this->model::$FieldHandle as $attr => $argv)
		{
			$func = array_shift($argv);
			if(!method_exists(self::$data,$func))
				throw new ErrorException('数据处理类方法不存在: '.$func);
			try{
				$this->model->$attr = (self::$data)->$func($this->model->$attr,$argv);
			}catch(\app\exception\DownloadException $e)
			{
				self::$data::cleanTemp();
				echo '下载失败【',$this->model->$attr,'】';
			}
		}
		// return $this->model;
		$data = [];
		$fields = $this->getTableSchema();
		// var_dump($fields);
		// exit;
		foreach($fields as $key => $field)
		{
			$data[$field] = property_exists($this->model,$field)?(string)$this->model->$field:'';
		}
		$id = self::$mysqldb->insert ($this->model::tableName(), $data);
		if(!$id)
		{
			throw new DbException('数据插入失败');
		}
		return $id;
	}
	
	/**
	 * 插入数据
	 */
	public function insert(BaseModel $model)
	{
		$this->model = $model;
		return $this->_insert();
	}
}