<?php

if (isset($_POST['b']) && isset($_POST['m']) && isset($_POST['a'])){
	require 'init.php';
	
	$body = urldecode($_POST['b']);
	$md5 = $_POST['m'];
	$auth = $_POST['a'];

	
	if ($auth != $config['auth']){
		die('error:Need Auth');
	}

	if (md5($body) != $md5){
		die('error:md5 error!');
	}
	
	$data = json_decode($body);
	unset($body);
	
	if (!$data){
		die('error:data error');
	}
	
	update_list($data);
	
	die('ok');
}else{
	die('error:No Data');
}


function update_list($list){
	$db = _mod('db');

	$day = date('Y-m-d');
	wjlog(sprintf('update %d raw list', count($list)));
	if ($list){
		$set = array();
		foreach($list as $v){
			$v = (array) $v;
			$set[] = sprintf("('%s', '%s', '%s', %d, '%s')", $v['uni_name'], $v['en_name'], $day, 0, $v['cday']);
		}
	}
	$sql = "INSERT INTO `wj_movie` (`uni_name`, `en_name`, `update`, `is_del`, `cday`) VALUES". 
		implode(',', $set) . " ON duplicate KEY UPDATE `update` = '{$day}', `is_del` = 0;";
	
	$ret = $db->query($sql);
	wjlog('end update');
	
	$sql = "UPDATE `wj_movie` SET `is_del` = 1 WHERE `update` < '{$day}'";
	$db->query($sql);
	$num = $db->affected_rows();
	$db->close();
	wjlog(sprintf('del %d movie', $num));
}

?>