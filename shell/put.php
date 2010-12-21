<?php
$body = file_get_contents('fileList.txt');
$body = urlencode($body);

$api = 'http://movie.zhangwenjin.com/shell/revice.php';

$ch = curl_init();
curl_setopt($ch,CURLOPT_URL,$api);
curl_setopt($ch,CURLOPT_POSTFIELDS,array('b'=>$body, 'm'=>md5($body)));
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2');
$data = curl_exec($ch);
curl_close($ch);
var_dump($data);

?>