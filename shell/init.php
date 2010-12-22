<?php
define('APP_ROOT', dirname(__FILE__));
require APP_ROOT . '/lib.php';

if (is_file(APP_ROOT . '/config.php')){
	$config = incldue(APP_ROOT . '/config.php');
}else{
	$config = array();
}

$default_config = array(
		'movie_path' => '//10.10.0.10/HD',
		'revice_url' => 'http://127.0.0.1/web_movie/shell/revice.php',
		'db_host' => '10.10.221.12',
		'db_user' => 'root',
		'db_pwd' => 'yhnji-db-yoqoo', 
		'db_name' => 'wenjin_movie',
		'auth' => 'movie',
	);

$config = array_merge($default_config, $config); //merge
?>