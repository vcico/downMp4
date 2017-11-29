<?php

namespace app\models;



class CaoceeModel extends \app\core\BaseModel
{
	
	public static function tableName(){
		return 'caocee';
	}
	
	public static $FieldHandle = [
		'file' => ['download'],
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