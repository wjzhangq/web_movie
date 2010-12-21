<?php
$body = file_get_contents('fileList.txt');






$api = 'http://127.0.0.1/web_movie/revice.php';

$post_string = http_build_query(array('b'=>$body, 'm'=>md5($body)));
$context = array( 
	'http'=>array( 
	'method'=>'POST', 
	'header'=>'Content-type: application/x-www-form-urlencoded'."\r\n". 
		"User-Agent : wj\r\n". 
		'Content-length: '.strlen($post_string)+8, 
		'content'=>$post_string) 
);
$stream_context = stream_context_create($context); 
$ret = file_get_contents($api, false, $stream_context);
var_dump($ret);

?>