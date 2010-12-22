<?php
require 'init.php';

main();


function main(){
	$path = '/Volumes/mountMovie';
	mount_smb($path);
	$dir_list = read_list($path);
	unmount_smb($path);
	
	update_list($dir_list);

	$en2zh_list = en2zh_list();
	if ($en2zh_list){
		$update_list = array();
		foreach($en2zh_list as $row){
			wjlog('start find name ' . $row['en_name']);
			$zh_name = get_zh_name($row['en_name']);
			wjlog('end name ' . $row['en_name'] . "\t '" . $zh_name . "'");
			if ($zh_name){
				update_zh_name($row['id'], $zh_name);
			}
		}
	}
}

function mount_smb($path){
	$res = `mount`;
	if (strpos($res, '10.10.0.10') !== false){
		throw new Exception('resource is alread mount!');
	}
	if (!file_exists($path)){
		$ret = mkdir($path);
		if (!$ret){
			throw new Exception($path . ' is not exist');
		}
	}
	
	wjlog('start mount');
	$cmd = "mount -t smbfs //Guest:@10.10.0.10/HD {$path}";
	$ret = `$cmd`;
	if (!empty($ret)){
		throw new Exception($ret);
	}
}

function unmount_smb($path){
	$cmd = "umount {$path}";
	`$cmd`;
}

function read_list($path){
	$cmd = "ls -lc {$path}";
	$ls = `$cmd`;
	$als = explode("\n", $ls);
	$curr_year = date("Y");
	$list = array();
	foreach($als as $k=>$v){
		$tmp = preg_split('/\s+/', $v);
		if (count($tmp) == 9){
			$en_name = trim($tmp[8]);
			if (strpos($tmp[7], ':') === false){
				$year = trim($tmp[7]);
			}else{
				$year = $curr_year;
			}
			
			$v = array('en_name'=>$en_name, 'uni_name'=>get_uni_name($en_name), 'size'=>'', 'cday'=> $year . '-' . str_pad($tmp[5], 2, '0', STR_PAD_LEFT) . '-' . str_pad($tmp[6], 2, '0', STR_PAD_LEFT));
			$list[$v['uni_name']] = $v;
		}
		
	}
	
	$cmd = "du -h {$path}";
	$du = `$cmd`;
	$adu = explode("\n", $du);
	foreach($adu as $k=>$v){
		$tmp = preg_split('/\s+/', $v);
		if (count($tmp) == 2){
			$name = ltrim(str_replace($path, '', $tmp[1]), '/');
			if ($name){
				$size = trim($tmp[0]);
				$uni_name = get_uni_name($name);
				if (isset($list[$uni_name])){
					$list[$uni_name]['size'] = $size;
				}
			}
		}
	}
	
	return $list;
}

function update_list($list){
	$db = new simpleMysql();
	$db->connect('10.10.221.12', 'root', 'yhnji-db-yoqoo');
	$db->select_db('wenjin_movie');

	$day = date('Y-m-d');
	wjlog(sprintf('update %d raw list', count($list)));
	if ($list){
		$set = array();
		foreach($list as $v){
			$set[] = sprintf("('%s', '%s', '%s', %d, '%s', '%s')", $v['uni_name'], $v['en_name'], $day, 0, $v['cday'], $v['size']);
		}
	}
	$sql = "INSERT INTO `movie` (`uni_name`, `en_name`, `update`, `is_del`, `cday`, `size`) VALUES". 
		implode(',', $set) . " ON duplicate KEY UPDATE `update` = '{$day}', `is_del` = 0;";
	
	$ret = $db->query($sql);
	wjlog('end update');
	
	$sql = "UPDATE `movie` SET `is_del` = 1 WHERE `update` < '{$day}'";
	$db->query($sql);
	$num = $db->affected_rows();
	$db->close();
	wjlog(sprintf('del %d movie', $num));
}

function en2zh_list(){
	$db = new simpleMysql();
	$db->connect('10.10.221.12', 'root', 'yhnji-db-yoqoo');
	$db->select_db('wenjin_movie');
	
	$sql = "SELECT `en_name`, `id` From `movie` WHERE `zh_name`='' AND `is_del` = 0";
	
	$raw_list = $db->getAll($sql);
	
	$db->close();
	return $raw_list;
}

function update_zh_name($id, $zh_name){
	$db = new simpleMysql();
	$db->connect('10.10.221.12', 'root', 'yhnji-db-yoqoo');
	$db->select_db('wenjin_movie');
	$db->query('set names utf8');
	$sql = "UPDATE `movie` SET `zh_name`='" . addslashes($zh_name) . "' WHERE `id`=" . intval($id);
	$db->query($sql);
	$db->close();
}

function get_zh_name($name){
	// http://shooter.cn/search/Total+Recall/
	$api_url = 'http://shooter.cn/search/';
	$search_name = str_replace('.', ' ', $name);
	$url = $api_url . urlencode($search_name) . '/';
	
	$opts = array( 
		'http'=>array( 
			'method'=>'GET', 
			'timeout'=>5,) 
	); 
	$context = stream_context_create($opts);
	$str = file_get_contents($url, false, $context);
	$list = split_start_end('<span class="sublist_box_title_l">', '</span>', $str);
	if ($list){
		$list = array_map('strip_tags', $list);
		$pname = strtolower($name);
		$psearch_name = strtolower($search_name);
		$names = array();
		foreach($list as $k=>$v){
			$v = trim($v, ' /');
			$tmp = array_map('trim', explode('/', $v));
			foreach($tmp as $_k =>$_v){
				$pv = strtolower($_v);
				if ($pv == $pname || $pv == $psearch_name){
					foreach($tmp as $a){
						$_a = strtolower($a);
						if ($_a != $psearch_name && $_a != $pname){
							$names[] = $a;
						}
					}
				}
			}
		}
		switch(count($names)){
			case 0:
				return '';
			case 1:
				return $names[0];
			case 2:
				return strlen($names[0]) > strlen($names[1]) ?  $names[1] : $names[0];
			default:
				$tmp = array_count_values($names);
				natsort($tmp);
				end($tmp);
				return key($tmp);
		}
	}
	return '';
}



?>
