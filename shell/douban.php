<?php
require 'init.php';
main(date('Y-m-d'));

function main($day){
	$raw_list = get_raw_list();
	
    foreach($raw_list as $k=>$v){
        //search
        wjlog(sprintf('start search "%s"', $v));
        $douban_id = douban_search($v);
        if ($douban_id > 0){
            wjlog(sprintf('"%s" douban id is %s', $v, $douban_id));
            insert_douban(array('douban_id'=>$douban_id, 'mid'=>$k, 'en_name'=>$v));
        }
    }
    exit;
    

	$raw_list = get_undouban_list();
	foreach($raw_list as $k=>$v){
		wjlog(sprintf('update douban %s, %s', $k, $v));
		$entry = douban_movie($v);
		update_douban($k, $entry);
	}
}

function get_raw_list(){
	$db = _mod('db');
	
	$sql = "SELECT `mid`, `en_name` FROM `wj_movie` WHERE `is_del` = 0";
	$tmp = $db->getAll($sql, false);
	$raw_list = array();
	foreach($tmp as $row){
		$raw_list[$row[0]] = $row[1];
	}

	if ($raw_list){
		$sql = "SELECT `mid` FROM `wj_douban` WHERE `mid` IN (" . implode(',', array_keys($raw_list)) . ");";
		$tmp = $db->getAll($sql, false);
		foreach($tmp as $row){
			unset($raw_list[$row[0]]);
		}
	}
	
	return $raw_list;
}

function get_undouban_list(){
	$conn = get_conn();
	$db = _mod('db');
	$db->conn = $conn;
	
	$sql = "SELECT `mid`, `douban_id` FROM `wj_douban` WHERE `pubdate` IS NULL";
	$raw_list = $db->getAll($sql, false);
	
	$ret = array();
	foreach($raw_list as $row){
		$ret[$row[0]] = $row[1];
	}
	
	return $ret;
}

function insert_douban($row){
	$conn = get_conn();
	$sql = "INSERT IGNORE INTO `wj_douban` (`mid`, `douban_id`, `en_name`) VALUES";
	$sql = $sql . sprintf("(%d, '%s', '%s')", $row['mid'], $row['douban_id'], addslashes($row['en_name']));
	
	$ret = mysql_query($sql, $conn);
	mysql_close($conn);
}

function update_douban($row){
    
}

function douban_search($en_name){
    $api = "http://api.douban.com/movie/subjects?start-index=1&max-results=15&q=";
    
    $search_name = str_replace('.', ' ', $en_name);
    $match_list = array(strtolower($en_name), strtolower($search_name));

    $opts = array('http'=>array('method'=>"GET",'timeout'=>5,));
    $context = stream_context_create($opts);

    $url = $api . urlencode($search_name);

	sleep(7);
    $str = file_get_contents($url, false, $context);

    $xml = simplexml_load_string($str);
    $aim = 0;
    foreach($xml as $entry){
        if (isset($entry->title)){
            $lower_tile = strtolower(trim($entry->title));
            if (in_array($lower_tile, $match_list)){
                $aim = basename($entry->id);
                break;
            }
        }
    }
    
    return $aim;    
}

function douban_movie($id){
    $api = 'http://api.douban.com/movie/subject/';
    
    $url = $api . $id;
    $opts = array('http'=>array('method'=>"GET",'timeout'=>5,));
    $context = stream_context_create($opts);
	sleep(7);
    $str = file_get_contents($url, false, $context);
    $str = str_replace(array('db:attribute', 'db:tag'), array('dbattribute', 'dbtag'), $str);
    $xml = simplexml_load_string($str);
    $entry = array('douban_id'=>$id, 'douban_en_name'=>trim((string) $xml->title), 'summary'=>trim((string) $xml->summary), 'douban_id'=>$id);
    if (isset($xml->dbattribute)){
        foreach($xml->dbattribute as $row){
            $attr = $row->attributes();
            if (isset($attr->lang)){
                $entry['zh_name'] = trim((string) $row);
            }
            if (isset($attr->name)){
                switch($attr->name){
                    case 'pubdate':
                        $entry['pubdate'] = trim((string) $row);
                        break;
                    case 'imdb':
                        $entry['imdb'] = trim((string) $row);
                        break;
                    break;
                }
            }
        }
    }
    if (isset($xml->link)){
        foreach($xml->link as $row){
            $attr = $row->attributes();
            if ($attr->rel == 'image'){
                $entry['img_url'] = trim((string) $attr->href);
            }
        }
    }
    
    if (isset($xml->dbtag)){
        $tags = array();
        foreach($xml->dbtag as $row){
            $attr = $row->attributes();
            $tags[] = trim((string) $attr->name);
        }
        $entry['tags'] = implode(',', $tags);
    }
    
    return $entry;
}

?>