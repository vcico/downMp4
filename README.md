# downMp4
下载短视频爬虫


// //div[contains(@class,'pagination')][1]//li/a[@class='active']/../following-sibling::li[1]
// ../ 父节点
// following-sibling 之后的节点 



## 解决乱码问题
$xs = Selector::loadHTML(mb_convert_encoding($response->content, 'HTML-ENTITIES', 'UTF-8'));
$dom = new DOMDocument('1.0','utf-8');
utf8_decode($name->extract())