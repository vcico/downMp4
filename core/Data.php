<?php

namespace app\core;

use app\exception\DownloadException;


/**
 * 数据处理类
 */
class Data
{
	
	private static $instance;
	
	private function __construct(){}
	
	public static function getInstance()
	{
		if(!self::$instance)
			self::$instance = new self();
		return self::$instance;
	}
	
	public function convert($data,$argv=[]){
//		$data = mb_convert_encoding($data, 'GBK','utf-8');
//		echo '===========';
//		echo $data;
//		echo '===========';
		return  $data;
		// return mb_convert_encoding($data, 'GBK', 'GBK,UTF-8,ASCII');
	}

	/**
	 * @param string $url 第一个参数是 Model 类的属性值
	 * 下载文件	 * @param array $argv model类 FieldHandle 属性配置值 【弹出首个(处理方法) 】
	 * ================================================
	 * 其他选项方法 格式也要保持一致
	 * ================================================
	 */
	public function download($url,$argv=[])
	{
		$url =  urldecode($url);
		
		
		$filename = md5($url);
		$tempfile = self::getTempPath().$filename;
		$i = 0;
		$fileinfo = $this->getInfo($url);
		foreach($this->getSlice($fileinfo['length']) as $key => $range)
		{
			$i++;
			for($Retry=0;$Retry<3;$Retry++){			// 每个分片 尝试4次下载 循环3次的try 和最后一个的 catch
				try{
					$this->_download($url,$tempfile.'-'.$key,implode('-',$range));
					break;
				}catch(DownloadException $e)
				{
					if($Retry == 2)  // 最后一次尝试后 抛出异常
					{
						$this->_download($url,$tempfile.'-'.$key,implode('-',$range));
					}else
						continue;
				}
			}
		}
		$typeArr = explode('/',$fileinfo['type']);
		$file = self::getFilePath().'/'.$filename.'.'.end($typeArr);
		$this->temp_merge($tempfile,$i,$file);
		return $file;
	}
	
	/**
	 * 合并缓存文件
	 */
	private function temp_merge($temp,$count,$filepath)
	{
		for($i=0;$i < $count;$i++)
		{
			file_put_contents($filepath,file_get_contents($temp.'-'.$i),FILE_APPEND) && unlink($temp.'-'.$i);
		}
	}
	
	private function _download($url,$file,$range)
	{
		echo "start  range[$range] $url ---- $file \n";
		$h_curl = curl_init();
		curl_setopt($h_curl, CURLOPT_HEADER, 0);
		curl_setopt($h_curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($h_curl, CURLOPT_TIMEOUT, 60);
		curl_setopt($h_curl, CURLOPT_URL, $url);
		curl_setopt($h_curl, CURLOPT_RETURNTRANSFER, 1); 
		// curl_setopt($h_curl, CURLOPT_FILE, $file);
		curl_setopt($h_curl, CURLOPT_SSL_VERIFYPEER, false); // 阻止对证书的合法性的检查
		curl_setopt($h_curl, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
		curl_setopt($h_curl, CURLOPT_RANGE, $range);
		$content = curl_exec($h_curl);
		file_put_contents($file,$content);
		$error = curl_error($h_curl);
		if(!empty($error)){
			throw new DownloadException($error);
		}
		curl_close($h_curl);
	}
	
	
	/**
	 * 获取文件信息
	 * @param string $url 下载文件的URL
	 * @return array [ 'length' => 文件byte大小,'type' =>'文件类型']
	 */
	private function getInfo($url)  // getLength
	{
		echo "\n\n";
		print_r($url);
		echo "\n\n";
		$headersInfo = @get_headers($url);
		if(!$headersInfo)
		{
			throw new DownloadException('get fileinfo failure');
		}
		$info = implode("\n", $headersInfo);
		$data = [];
		if(!preg_match("/Content-Length:(.*)/i", $info, $match)){
			throw new DownloadException('get file length failure');
		}
		$data['length'] = $match[1];
		$data['type'] = preg_match("/Content-Type:(.*)/i", $info, $match) ? $match[1] : '';
		// Content-Type: video/mp4
		return $data;
	}
	
	/**
	 * 获取分片 http头信息Range
	 */
	private function getSlice($length)
	{
		$max = 10485760; // 10M 1048576; // 每片最大 1 M
		$surplus = $length;
		$start = 0;
		$end = $max;
		while($surplus > 0)
		{
			if($surplus > $max){
				yield [$start,$end];
				$start = $end+1;
				$end += $max;
			}else{
				yield [$start,$length];
			}
			$surplus -= $max;
		}
	}
	
	/**
	 * 获取文件路径
	 */
	private static function getFilePath()
	{
		$path = 'download/'.date('Y-m-d');
		if(!is_dir($path))
		{
			mkdir($path,0777,true);
		}
		return $path;
	}
	
	/**
	 * 清空缓存文件夹
	 */
	public static function cleanTemp()
	{
		$path = self::getTempPath();
		$temps = scandir($path);
		foreach($temps as $key => $temp)
		{
			if($temp != '.' and $temp != '..')
			{
				unlink($path.$temp);
			}
		}
	}
	
	/**
	 * 获取缓存路径
	 */
	private static function getTempPath()
	{
		$temp_path = 'runtime/temp/';
		if(!is_dir($temp_path))
		{
			mkdir($temp_path,0777,true);
		}
		return $temp_path;
	}
	

	
}