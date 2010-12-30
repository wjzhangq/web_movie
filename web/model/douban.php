<?php

class model_douban extends model{
	var $table = 'wj_douban';
	
	function listAll($opt){
		$mod = $this->db['wj_douban'];
		if (empty($opt['order'])){
			$order = 'mid DESC';
		}else{
			$order = $opt['order'];
		}
		$mod->order($order);
		
		if (empty($opt['page'])){
			$mod->limit(20);
		}else{
			$mod->limit($opt['page'],20);
		}
		
		$all = $mod[array('mid:>'=>0)];
		
		var_dump($all);
	}
}
?>