<?php


return [

	/**
	 * 应用配置
	 */
	'spider_path' => __DIR__.'/spiders',  // 站点爬虫路径
	'spider_space' => '\\app\\spiders\\',	 // 站点爬虫的命名空间	
	
	'spider' => [ 	// 爬虫的配置
		'timeout' => 3,   // 超时时间
	],

	// 'max_length' =>  视频最大体积
	// 'min_length' =>  视频最小体积
	'download_path' => '',
	// 'Slice' => '', // 分片大小
	// 'temp_path'  图片下载的缓存路径
	
];