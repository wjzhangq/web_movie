<?php
$list = array('Air.Force.One','3.Idiots','36.Quai.Des.Orfevres','50.First.Dates','A.Bridge.Too.Far','A.Clockwork.Orange','A.Few.Good.Men','A.Fistful.of.Dollars','A.History.Of.Violence','A.Nightmare.On.Elm.Street','A.River.Runs.Through.It','After.Life','After.The.Sunset','Air.Force.One','Alexander','Alien','Alien.3','Alien.Resurrection','Aliens','Almost.Famous','Alvin.And.The.Chipmunks','Amadeus','American.Beauty','American.Gangster','American.History.X','American.Pie');
foreach($list as $en_name){
    $douban_id = douban_search($en_name);
    if ($douban_id){
        $entry = douban_movie($douban_id);
        print_r($entry);
    }
}



function douban_search($en_name){
    $api = "http://api.douban.com/movie/subjects?start-index=1&max-results=15&q=";
    
    $search_name = str_replace('.', ' ', $en_name);
    $match_list = array(strtolower($en_name), strtolower($search_name));

    $opts = array('http'=>array('method'=>"GET",'timeout'=>5,));
    $context = stream_context_create($opts);

    $url = $api . urlencode($search_name);

	sleep(6);
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
	sleep(6);
    $str = file_get_contents($url, false, $context);
    $str = str_replace(array('db:attribute', 'db:tag'), array('dbattribute', 'dbtag'), $str);
    $xml = simplexml_load_string($str);
    $entry = array('en_name'=>trim((string) $xml->title), 'summary'=>trim((string) $xml->summary));
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