<?php


namespace app\core;

use app\exception\HttpException;
use app\core\Response;

/**
 * 请求下载类
 */
class Http
{
	
	/**
	 *  站点爬虫对象
	 */
	//public $spider;
	
	/**
	 * 请求对象
	 * @var Request
	 */
	public $request;
	
	
	public static $cookiePath = 'runtime/cookies/{{HOST}}';
	
	
	public static function getCookiePath($url)
	{
		
		$path = preg_replace('/\{\{HOST\}\}/', self::getHost($url), self::$cookiePath);
		if(!is_dir($path)){
			mkdir($path,0777,true);
		}
		$file = $path.'/cookiefile.txt';
		if(!is_file($file)){
			$myfile = fopen($file, "w");
			fclose($myfile);
		}
		return  $file;
	}
	
	public static function getHost($url)
	{
		$tempu=parse_url($url);
		return $tempu['host'];
	}
	
	
	/**
	 *  // $cookies = curl_getinfo($curl, CURLINFO_COOKIELIST);
	 */
	private function _curl($url)
	{
		echo 'start download [',$url,']',"\n";
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url); 
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);  // 把结果作为字符串返回 默认输出返回true
		curl_setopt ($curl , CURLOPT_AUTOREFERER , true); // location重定向自动设置 Referer
		curl_setopt ($curl , CURLOPT_FOLLOWLOCATION , true); // 自动跟踪location重定向
		curl_setopt ($curl , CURLOPT_MAXREDIRS , 3); // 最大重定向次数
		curl_setopt ($curl , CURLOPT_CONNECTTIMEOUT , 3); // 超时秒数
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 阻止对证书的合法性的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
		curl_setopt($curl,CURLOPT_HEADER,0);
		curl_setopt($curl,CURLOPT_COOKIEFILE,self::getCookiePath($url));
		curl_setopt($curl,CURLOPT_COOKIEJAR,self::getCookiePath($url));
		curl_setopt($curl,CURLOPT_ENCODING,'gzip');  //  gzip 解压缩
		$headers = [
			'Accept-Encoding: gzip',
			'Accept-Language: zh-CN,zh;q=0.8,zh-TW;q=0.7,zh-HK;q=0.5,en-US;q=0.3,en;q=0.2',
			'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*\/*;q=0.8',
			'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:57.0) Gecko/20100101 Firefox/57.0',
		];
		curl_setopt ($curl , CURLOPT_HTTPHEADER , $headers); 
		$output = curl_exec($curl);
		$info = curl_getinfo($curl);  // 获取最后一次请求的信息
		if($output === false)
		{
			print_r(curl_error($curl));
			throw new HttpException('network anomaly' . $info['url'] . '('.curl_errno($curl).')['.$info['http_code'].']');
		}
		if($info['http_code'] >= 400)
		{
			throw new HttpException('Abnormal page' . $info['url'] .$info['http_code']);
		}
                
//                if(200 == $info['http_code'] ) {
//                    if (preg_match('#<meta[^>]*charset="?gb2312"[^>]*>#', $output)) {
//                      $output = iconv("gb2312","utf-8//IGNORE",$output);
//                      $output = preg_replace('#<meta[^>]*charset="?gb2312"[^>]*>#is', '<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">', $output);
//                    }
//                    if (!preg_match('#<meta charset="utf-8"[^>]*>#is', $output)) {
//                      $output = str_replace('<head>', '<head><meta http-equiv="Content-Type" content="text/html;charset=UTF-8">', $output);
//                    }
//                    if (preg_match('#<meta charset="utf-8"[^>]*>#is', $output)) {
//                        $output = str_replace('<head>', '<head><meta http-equiv="Content-Type" content="text/html;charset=UTF-8">', $output);
//                        $output = preg_replace('#<meta charset="utf-8"[^>]*>#is', '', $output);
////                        $output = preg_replace('#<meta charset="utf-8"[^>]*>#is', '<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">', $output);
//                    }
//                }
                
		curl_close($curl);
		return  $output;
	}
	
	public function _getResponse()
	{
		try{
			$content = $this->_curl($this->request->url);
			$response = new Response($content);
			$response->url = $this->request->url;
			$response->request = $this->request;
			return $response;
		}catch(HttpException $e) {
			echo '[',$e->getCode(),']',$e->getMessage(),"\n";
			return false;
		}
	}
	
	public function getResponse($request)
	{
			$this->request = $request;
			return $this->_getResponse();
	}
	
}



