<?php
main('//10.10.0.10/HD');

function main($host, $user='Guest', $pwd=''){
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
	
	

	foreach($FileList as $k=>$v){
		wjlog(sprintf('search "%s" from douban', $v['en_name']));
		$id = douban_search($v['en_name']);
		if ($id > 0){
			$entry = douban_movie($id);
			$FileList[$k] = array_merge($entry, $v);
		}
	}
	
	file_put_contents('fileList.txt', var_export($FileList, true));
}


function __split_by_tab($v){
	return preg_split('/\s+/', $v);
}


function get_uni_name($name){
	$name = strtolower($name);
	$name = str_replace(array('-', '.'), array('_', '_'), $name);
	return preg_replace('/[^a-z|0-9|\_]+/', '', $name);
}

function douban_search($en_name){
    $api = "http://api.douban.com/movie/subjects?start-index=1&max-results=15&q=";
    
    $search_name = str_replace('.', ' ', $en_name);
    $match_list = array(strtolower($en_name), strtolower($search_name));

    $opts = array('http'=>array('method'=>"GET",'timeout'=>5,));
    $context = stream_context_create($opts);

    $url = $api . urlencode($search_name);

	sleep(11);
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
	sleep(1);
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

function wjlog($str){
	echo date('Y-m-d H:i:s') . "\t" . $str . "\n";
}
?>