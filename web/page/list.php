<?php
require 'init.php';

class page_list extends page{
	function get($respone){
		$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
		$sort = isset($_GET['sort']) ? trim($_GET['sort']) : '';
		
		if ($sort && in_array($sort{0}, array('+-'))){
			if ($sort{0} == '-'){
				$order == 'DESC';
			}else{
				$order = 'ASC';
			}
			
			$sort = ltrim($sort, '+-') . ' ' . $order;
		}
		

		$a = new model_douban();
		$a->listAll(array('page'=>$page, 'order'=>$sort));
	}
	
}


$page = new page_list();
$page->display();
?>