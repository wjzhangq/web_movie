<?php

if (isset($_POST['b']) && isset($_POST['m'])){
	$body = $_POST['b'];
	$md5 = $_POST['m'];
	var_dump($md5, $body);
	if (md5($body) != $md5){
		die('error:md5 error!');
	}
	
	$body = urldecode($body);
	
	$day = date('Y-m-d');
	$path = dirname(__FILE__) . '/tmp/movieList_' . $day . '.php';
	
	$content = "<?php\nreturn " . $body . ';\n?>';
	file_put_contents($path, $content); 
	
	die('ok');
}else{
	die('error:No Data');
}

?>