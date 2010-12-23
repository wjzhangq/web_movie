<?php
require 'init.php';
$sql = 'Select * from `wj_movie` where 1 limit 1';
$db = _mod('db');
var_dump($db->getAll($sql));

sleep(31);
var_dump($db->getAll($sql));



?>