<?php
define('APP', dirname(dirname(__FILE__)));
define('FRAMEWORK', '/Users/zhangwenjin/Sites/st/framework');
require FRAMEWORK . '/init.php';

$dirs = array('model');
foreach($dirs  as $dir){
	$glob = glob(APP . '/' . $dir . '/*.php');
	if (empty($glob)) continue;
	$fnames = array_map(create_function('$a', 'return "' . $dir . '_" . strtolower(basename($a));'), $glob);
	$tmp = array_combine($fnames, $glob);
	autoload::append_list($tmp);
}

$db = new db('mysql:host=10.10.221.12;dbname=wenjin_movie', 'root', 'yhnji-db-yoqoo');
$db->query('set names utf8');

config::set('db', $db);
?>