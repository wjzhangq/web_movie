<?php
require 'init.php';

class page_index extends page{
	function get($respone){
		echo 'kkk';
	}
	
}


$page = new page_index();
$page->display();

?>