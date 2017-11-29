<?php


namespace app\models;



/**
 * 入库的数据模型
 */
class XvideosModel extends \app\core\BaseModel
{
	public static function tableName(){
		return 'xvideos';
	}
	public static $FieldHandle = [
		'file' => ['download'],
		'name' => ['convert'],
	];
	
	/**
	 * 视频分类
	 */
	public $cate;
	
	/**
	 *  视频名称
	 */
	public $name;
	
	/**
	 * 视频地址
	 */
	public $video;
	
	/**
	 * 时长
	 */
	public $duration;
	
	/**
	 * 文件
	 */
	public $file;
	
	
}