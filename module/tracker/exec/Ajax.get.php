<?php
REQUIRE_ONCE '../../../config/default.conf.php';

header('Content-type: text/xml; charset=UTF-8', true);
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

$action = Request('action');
$mDB = &DB::instance();
$member = &Member::instance()->GetMemberInfo();
$mTracker = new ModuleTracker();

$returnXML = '<?xml version="1.0" encoding="UTF-8" ?><Ajax>';

if ($action == 'category') {
	$parent = Request('parent');
	
	$data = $mDB->DBfetchs($mTracker->table['category'],array('idx','title'),"where `parent`='$parent'",'sort,asc');
	for ($i=0,$loop=sizeof($data);$i<$loop;$i++) {
		$returnXML.= '<item idx="'.$data[$i]['idx'].'" title="'.GetString($data[$i]['title'],'xml').'" />';
	}
}

if ($action == 'group') {
	$category = Request('category');
	$addon = Request('addon');
	$keyword = GetAjaxParam('keyword');
	
	$mKeyword = new Keyword();
	$titlekey1 = $mKeyword->GetUTF8Code($keyword);
	$titlekey2 = $mKeyword->GetEngCode($titlekey1);
	
	$data = $mDB->DBfetchs($mTracker->table['group'],'*',"where `category1`='$category' and (`titlekey1` like '$titlekey1%' or `titlekey2` like '$titlekey2%' or `titlekey3` like '$titlekey1%')");
	
	for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
		$returnXML.= '<item idx="'.$data[$i]['idx'].'" title="'.GetString($data[$i]['title'],'xml').'" eng_title="'.GetString($data[$i]['eng_title'],'xml').'" season="'.$data[$i]['season'].'" nation="'.$data[$i]['nation'].'" artist="'.implode(', ',$mTracker->GetArtistName(explode(',',$data[$i]['artist']))).'" subartist="'.implode(', ',$mTracker->GetArtistName(explode(',',$data[$i]['subartist']))).'" year="'.$data[$i]['year'].'" date="'.$data[$i]['date'].'" thumbnail="'.(file_exists($_ENV['path'].$mTracker->groupImagePath.'/'.$data[$i]['idx'].'.small.thm') == true ? $_ENV['dir'].$mTracker->groupImagePath.'/'.$data[$i]['idx'].'.small.thm' : '').'" addon="false" />';
	}
	
	if ($addon == 'daummovie' || $addon == 'daumanimation') {
		$mCrawler = new Crawler();
		$apiData = $mCrawler->GetURLString('http://apis.daum.net/contents/movie?q='.urlencode($keyword).'&apikey='.$mTracker->GetConfig('daum_api'));
		$xml = new SimpleXMLElement($apiData);
		$list = $xml->item;
		
		$cache = $mDB->DBfetchs($mTracker->table['daum_movie'],'*',"where (`titlekey1` like '$titlekey1%' or `titlekey2` like '$titlekey2%' or `titlekey3` like '$titlekey1%')");
	
		$data = array();
		for ($i=0, $loop=sizeof($cache);$i<$loop;$i++) {
			if (($addon == 'daummovie' && $cache[$i]['type'] == 'MOVIE') || ($addon == 'daumanimation' && $cache[$i]['type'] == 'ANIMATION')) {
				$data[$cache[$i]['idx']] = $cache[$i];
			}
		}
		
		for ($i=0, $loop=sizeof($list);$i<$loop;$i++) {
			$daum = $mTracker->GetDaumMovieAPI($list[$i]);
			if (($addon == 'daummovie' && $daum['type'] == 'MOVIE') || ($addon == 'daumanimation' && $daum['type'] == 'ANIMATION')) {
				if (isset($data[$daum['idx']]) == false) $data[$daum['idx']] = $daum;
			}
		}
		
		foreach ($data as $idx=>$value) {
			if ($value['groupno'] == '0') {
				$returnXML.= '<item idx="0" addidx="'.$idx.'" title="'.GetString($value['title'],'xml').'" eng_title="'.GetString($value['eng_title'],'xml').'" season="0" nation="'.str_replace(',',', ',$value['nation']).'" genre="'.str_replace(',',', ',$value['genre']).'" artist="'.str_replace(',',', ',trim($value['artist'])).'" subartist="'.str_replace(',',', ',$value['subartist']).'" year="'.($value['year'] == '0' ? '' : $value['year']).'" date="'.$value['date'].'" thumbnail="'.$value['thumbnail'].'" addon="daummovie" />';
			}
		}
	}
	
	if ($addon == 'daumtv' || $addon == 'daumanimation') {
		$mCrawler = new Crawler();
		$apiData = $mCrawler->GetURLString('http://movie.daum.net/search.do?type=tv&q='.urlencode($keyword));

		$cache = $mDB->DBfetchs($mTracker->table['daum_tv'],'*',"where `titlekey1` like '$titlekey1%' or `titlekey2` like '$titlekey2%' or `titlekey3` like '$titlekey1%'");
		
		$data = array();
		for ($i=0, $loop=sizeof($cache);$i<$loop;$i++) {
			if (($addon == 'daumtv' && $cache[$i]['type'] == 'TV') || ($addon == 'daumanimation' && $cache[$i]['type'] == 'ANIMATION')) {
				$data[$cache[$i]['idx']] = $cache[$i];
			}
		}
		
		$mHTMLParser = new HTMLParser(null,true,true,'UTF-8',true,"\r\n"," ");
		$mHTMLParser->load($apiData,true,true);
		$object = $mHTMLParser->find('dl');
		
		for ($i=0, $loop=sizeof($object);$i<$loop;$i++) {
			$daum = $mTracker->GetDaumTVAPI($object[$i]);
			if (($addon == 'daumtv' && $data[$i]['type'] == 'TV') || ($addon == 'daumanimation' && $data[$i]['type'] == 'ANIMATION')) {
				$data[$daum['idx']] = $daum;
			}
		}
		
		foreach ($data as $idx=>$value) {
			$returnXML.= '<item idx="0" addidx="'.$idx.'" title="'.GetString($value['title'],'xml').'" eng_title="'.GetString($value['eng_title'],'xml').'" season="'.($value['season'] == '0' ? '' : $value['season']).'" nation="'.str_replace(',',', ',$value['nation']).'"  artist="'.str_replace(',',', ',trim($value['artist'])).'" subartist="'.str_replace(',',', ',$value['subartist']).'" year="'.($value['year'] == '0' ? '' : $value['year']).'" thumbnail="'.$value['thumbnail'].'" addon="daumtv"  />';
		}
	}
}
/*
if ($action == 'daummovie') {
	$keyword = GetAjaxParam('keyword');
	$mCrawler = new Crawler();
	$apiData = $mCrawler->GetURLString('http://apis.daum.net/contents/movie?q='.urlencode($keyword).'&apikey='.$mTracker->GetConfig('daum_api'));
	$xml = new SimpleXMLElement($apiData);
	$list = $xml->item;
	
	$mKeyword = new Keyword();
	$titlekey1 = $mKeyword->GetUTF8Code($keyword);
	$titlekey2 = $mKeyword->GetEngCode($titlekey1);
	
	$cache = $mDB->DBfetchs($mTracker->table['daum_movie'],'*',"where `titlekey1` like '$titlekey1%' or `titlekey2` like '$titlekey2%' or `titlekey3` like '$titlekey1%'");
	
	$data = array();
	for ($i=0, $loop=sizeof($cache);$i<$loop;$i++) {
		$data[$cache[$i]['idx']] = $cache[$i];
	}
	
	for ($i=0, $loop=sizeof($list);$i<$loop;$i++) {
		$daum = $mTracker->GetDaumMovieAPI($list[$i]);
		if (isset($data[$daum['idx']]) == false) $data[$daum['idx']] = $daum;
	}
	
	foreach ($data as $idx=>$value) {
		$returnXML.= '<item idx="'.$idx.'" title="'.GetString($value['title'],'xml').'" eng_title="'.GetString($value['eng_title'],'xml').'" nation="'.str_replace(',',', ',$value['nation']).'" genre="'.str_replace(',',', ',$value['genre']).'" director="'.str_replace(',',', ',$value['director']).'" actor="'.str_replace(',',', ',$value['actor']).'" year="'.($value['year'] == '0' ? '' : $value['year']).'" date="'.$value['date'].'" thumbnail="'.$value['thumbnail'].'" groupno="'.(isset($value['groupno']) ? $value['groupno'] : '0').'"  />';
	}
}

if ($action == 'daumtv') {
	$keyword = GetAjaxParam('keyword');
	$mCrawler = new Crawler();
	$apiData = $mCrawler->GetURLString('http://movie.daum.net/search.do?type=tv&q='.urlencode($keyword));

	$mKeyword = new Keyword();
	$titlekey1 = $mKeyword->GetUTF8Code($keyword);
	$titlekey2 = $mKeyword->GetEngCode($titlekey1);
	
	$cache = $mDB->DBfetchs($mTracker->table['daum_tv'],'*',"where `titlekey1` like '$titlekey1%' or `titlekey2` like '$titlekey2%' or `titlekey3` like '$titlekey1%'");
	
	$data = array();
	for ($i=0, $loop=sizeof($cache);$i<$loop;$i++) {
		$data[$cache[$i]['idx']] = $cache[$i];
	}
	
	$mHTMLParser = new HTMLParser(null,true,true,'UTF-8',true,"\r\n"," ");
	$mHTMLParser->load($apiData,true,true);
	$object = $mHTMLParser->find('dl');
	
	for ($i=0, $loop=sizeof($object);$i<$loop;$i++) {
		$daum = $mTracker->GetDaumTVAPI($object[$i]);
		if (preg_match('/ 시즌 ([0-9]+)$/',$daum['title'],$match) == true) {
			$daum['season'] = $match[1];
			$daum['title'] = str_replace($match[0],'',$daum['title']);
		} else {
			$daum['season'] = '';
		}
		$data[$daum['idx']] = $daum;
	}
	
	foreach ($data as $idx=>$value) {
		$returnXML.= '<item idx="'.$idx.'" title="'.GetString($value['title'],'xml').'" eng_title="'.GetString($value['eng_title'],'xml').'" season="'.$data['season'].'" nation="'.str_replace(',',', ',$value['nation']).'"  director="'.str_replace(',',', ',$value['director']).'" actor="'.str_replace(',',', ',$value['actor']).'" year="'.($value['year'] == '0' ? '' : $value['year']).'" thumbnail="'.$value['thumbnail'].'" groupno="'.(isset($value['groupno']) ? $value['groupno'] : '0').'"  />';
	}
}
*/
if ($action == 'artist') {
	$type = Request('type');
	$category = Request('category');
	$keyword = GetAjaxParam('keyword');
	
	$mKeyword = new Keyword();
	$namekey1 = $mKeyword->GetUTF8Code($keyword);
	$namekey2 = $mKeyword->GetEngCode($namekey1);
	
	$data = $mDB->DBfetchs($mTracker->table['artist'],'*',"where `category1`='$category' and `type`='$type' and (`namekey1` like '$namekey1%' or `namekey2` like '$namekey2%')");

	for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
		$returnXML.= '<item name="'.$data[$i]['name'].'" />';
	}
}

if ($action == 'episode') {
	$groupno = Request('groupno');
	
	$episode = $mDB->DBfetchs($mTracker->table['episode'],'*',"where `groupno`='$groupno'",'episode,desc');
	if (sizeof($episode) > 1 || $episode[0]['episode'] || $episode[0]['episode_title']) {
		for ($i=0, $loop=sizeof($episode);$i<$loop;$i++) {
			$returnXML.= '<item idx="'.$episode[$i]['idx'].'" date="'.$episode[$i]['date'].'" year="'.$episode[$i]['year'].'" episode="'.$episode[$i]['episode'].'" episode_title="'.GetString($episode[$i]['episode_title'],'xml').'" is_pack="'.$episode[$i]['is_pack'].'">'.GetString($episode[$i]['intro'],'xml').'</item>';
		}
	}
	
}

if ($action == 'file') {
	$torrentno = Request('torrentno');
	$torrent = $mDB->DBfetch($mTracker->table['torrent'],array('filedetail'),"where `idx`='$torrentno'");
	$filelist = explode("\n",$torrent['filedetail']);
	
	for ($i=0, $loop=sizeof($filelist);$i<$loop;$i++) {
		$temp = explode('/',trim($filelist[$i]));
		$filesize = GetFileSize(array_pop($temp));
		$filename = implode('/',$temp);
		$returnXML.= '<item filename="'.GetString($filename,'xml').'" filesize="'.$filesize.'" />';
	}
}

if ($action == 'peer') {
	$torrentno = Request('torrentno');
	$torrent = $mDB->DBfetch($mTracker->table['torrent'],array('idx','hash','filesize'),"where `idx`='$torrentno'");
	$peer = $mDB->DBfetchs($mTracker->table['peer'],array('mno','ip','port','left','client','last_check_date'),"where `hash`='{$torrent['hash']}' and `status`='ACTIVE'");
	
	for ($i=0, $loop=sizeof($peer);$i<$loop;$i++) {
		$snatch = $mDB->DBfetch($mTracker->table['snatch'],array('upload','download'),"where `torrentno`='{$torrent['idx']}' and `mno`='{$peer[$i]['mno']}'");
		$percent = $peer[$i]['left'] == '0' ? '100.0' : sprintf('%0.2f',($torrent['filesize']-$peer[$i]['left'])/$torrent['filesize']*100);

		$check = @fsockopen($peer[$i]['ip'],$peer[$i]['port'],$error,$errorstr,1);
		if (!$check) {
			$connectable = 'false';
		} else {
			$connectable = 'true';
		}
		$ratio = $snatch['download'] == 0 ? '∞' : sprintf('%0.3f',$snatch['upload']/$snatch['download']);
		$ratio = $snatch['upload'] == 0 ? '0.000' : $ratio;
		$returnXML.= '<item user_id="'.$mTracker->GetUserId($peer[$i]['mno']).'" upload="'.GetFileSize($snatch['upload']).'" download="'.GetFileSize($snatch['download']).'" ratio="'.$ratio.'" last_connect="'.GetTime('Y-m-d H:i:s',$peer[$i]['last_check_date']).'" percent="'.$percent.'" client="'.$peer[$i]['client'].'" connectable="'.$connectable.'" />';
	}
}

$returnXML.= '</Ajax>';
echo $returnXML;
?>