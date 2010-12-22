<?php
require 'init.php';
main($config['movie_path']);

function main($host, $user='Guest', $pwd=''){
	global $config;
	wjlog('start get remote file');
	$cmd = "smbclient -c \"ls\" {$host} -U {$user}%{$pwd}";
	$rawFileList = `$cmd`;
	$tmp = array_map('__split_by_tab', explode("\n",$rawFileList));
	$mouth_map = array('Jan'=>'01','Feb'=>'02','Mar'=>'03','Apr'=>'04','May'=>'05','Jun'=>'06','Jul'=>'07','Aug'=>'08','Sep'=>'09','Oct'=>'10','Nov'=>'11','Dec'=>'12',);
	$FileList = array();
	if ($tmp){
		foreach($tmp as $row){
			if (count($row) == 9 && $row[2]=='D' && !in_array($row[1], array('.', '..'))){
				$item = array('uni_name'=>get_uni_name($row[1]), 'en_name'=>$row[1], 'cday'=>$row[8] . '-' . $mouth_map[$row[5]] . '-' . str_pad($row[6], 2, '0', STR_PAD_LEFT));
				$FileList[$item['uni_name']] = $item;
			}
		}
	}
	
	
	$body =  json_encode(array_values($FileList));
	
	$url = $config['revice_url'];

	$data = array('b'=>$body, 'm'=>md5($body), 'a'=>$config['auth']);
	$ret = http_post($url, $data);
	wjlog($ret);
}



?>