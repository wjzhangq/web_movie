<?php


function __split_by_tab($v){
	return preg_split('/\s+/', $v);
}

function get_uni_name($name){
	$name = strtolower($name);
	$name = str_replace(array('-', '.'), array('_', '_'), $name);
	return preg_replace('/[^a-z|0-9|\_]+/', '', $name);
}

function split_start_end($start, $end, $str){
	$curr_pos = 0;
	$len = strlen($str) - 1;
	$list = array();
	while($curr_pos < $len){
		$start_pos = strpos($str, $start, $curr_pos);
		if ($start_pos === false){
			break;
		}
		$curr_pos = $start_pos + strlen($start);
		$end_pos = strpos($str, $end, $curr_pos);
		if ($end_pos === false){
			break;
		}
		$curr_pos = $end_pos + strlen($end);
		
		$list[] = substr($str, $start_pos, $curr_pos - $start_pos);
	}
	
	return $list;
}

function get_conn(){
	global $config;
	$conn = mysql_connect($config['db_host'], $config['db_user'], $config['db_pwd']);
	if (!$conn){
		throw new Exception('no connect');
	}
	mysql_select_db($config['db_name'], $conn);
	return $conn;
}

function _mod($classname){
	global $config;
	static $mods = array();
	
	if (!isset($mods[$classname])){
		switch($classname){
			case 'db':
				$db = new simpleMysql();
				$db->connect($config['db_host'], $config['db_user'], $config['db_pwd']);
				$db->select_db($config['db_name'], $db->conn);
				$mods['db'] = $db;
				break;
			default:
				$mods[$classname] = false;
		}
	}else{
		if ($classname == 'db'){
			$db = $mods['db'];
			$db->close();
			$db = new simpleMysql();
			$db->connect($config['db_host'], $config['db_user'], $config['db_pwd']);
			$db->select_db($config['db_name']);
			$mods['db'] = $db;
		}
	}

	
	return $mods[$classname];
}

function wjlog($str){
	echo date('Y-m-d H:i:s') . "\t" . $str . "\n";
}

function http_post($url, $data){
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2');
	$ret = curl_exec($ch);

	curl_close($ch);
	
	return $ret;
}

class simpleMysql{
	public  $conn;
	
	function __construct(){
		
	}
	
	function getAll($sql, $assoc=true){
		$result = $this->query($sql, $this->conn);
		if (!$result){
			throw new Exception(mysql_error($this->conn));
		}
		$ret = array();
		if ($assoc){
			while ($row = mysql_fetch_assoc($result)){
				$ret[] = $row;
			}
		}else{
			while ($row = mysql_fetch_row($result)){
				$ret[] = $row;
			}
		}
		
		return $ret;
	}
	
	function __call($method, $param){
		$method = 'mysql_' . $method;
		if (!function_exists($method)){
			throw new Exception(sprintf('Unknown method "%s"', $method));
		}
		
		if ($method == 'mysql_connect'){
			$this->conn = call_user_func_array('mysql_connect', $param);
			if (!$this->conn) {
				throw new Exception(mysql_error());
			}
		}else{
			$ret = call_user_func_array($method, $param);
			if ($ret === false){
				throw new Exception(mysql_error());
			}
			return $ret;
		}
	}
}
?>