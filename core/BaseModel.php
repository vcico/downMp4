<?php


namespace app\core;

// use app\exception\ModelException;


/**
 * 数据模型基类
 */ 
abstract class BaseModel
{
	/**
	 * 自动增长 插入时不需要赋值
	 * @var string
	 */
	// public static $autoIncrement = 'id';
	
	public function __construct($data)
	{
		foreach($data as $key => $val)
		{
			if(property_exists($this,$key))
				//throw new ModelException('');
				$this->$key = $val;
		}
	}
	
	/**
	 * 字段特殊处理程序
	 * @var array
	 * @example [ 'image' => [ 'download', '500*500' ]  ]
	 *          [ 字段(属性) => [处理方法(来自Data类) , 参数1,参数2....]
	 */
	public static $FieldHandle = [];  
	
	/**
	 * 获取数据库表名
	 * 如果未覆盖 则默认model类名称
	 */
	public static function tableName(){
		//echo __CLASS__,"\n";
		return  basename(strtolower(get_called_class()));
	}
	
	
	
}