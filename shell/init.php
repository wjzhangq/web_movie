<?php
define('APP_ROOT', dirname(__FILE__));
date_default_timezone_set("Asia/Shanghai");
require APP_ROOT . '/lib.php';

if (is_file(APP_ROOT . '/config.php')){
	$config = include(APP_ROOT . '/config.php');
}else{
	$config = array();
}

$default_config = array(
		'movie_path' => '//10.10.0.10/HD',
		'revice_url' => 'http://127.0.0.1/web_movie/shell/revice.php',
		//'db_host' => '10.10.221.12',
		'db_host' => '127.0.0.1',
		'db_user' => 'root',
		//'db_pwd' => 'yhnji-db-yoqoo', 
		'db_pwd'=>'root',
		'db_name' => 'wenjin_movie',
		'auth' => 'movie',
	);

$config = array_merge($default_config, $config); //merge
?>