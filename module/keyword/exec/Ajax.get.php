<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();

$action = Request('action');
$return = array();

if ($action == 'keyword') {
	$keyword = GetAjaxParam('keyword');
	$mKeyword = new ModuleKeyword();
	$keycode = $mKeyword->GetUTF8Code($keyword);
	$engcode = $mKeyword->GetEngCode($keycode);

	$data = $mDB->DBfetchs($mKeyword->table['keyword'],array('keyword'),"where `keycode` like '$keycode%' or `engcode` like '$engcode%'",'hit,asc','0,10');

	for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
		$returnXML.= '<item keyword="'.GetString($data[$i]['keyword'],'xml').'" viewword="'.GetString($mKeyword->GetMatchKeyword($keyword,$data[$i]['keyword'],'class="liveSearchMatch"'),'xml').'" />';
	}
}

if ($action == 'liveKeyword') {
	$nums = Request('nums');
	$limit = Request('limit');

	if ($type == 'realtime') $data = $mDB->DBfetchs($mKeyword->table['keyword'],array('keyword','last_search'),'','last_search,desc','0,'.$nums);
	else $data = $mDB->DBfetchs($mKeyword->table['keyword'],array('keyword','last_search'),'','hit,asc','0,'.$nums);

	$returnXML.= '<item time="'.GetTime('Y.m.d h:i:s A').'" />';
	for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
		$time = GetTimer($data[$i]['last_search']-GetGMT());

		$returnXML.= '<item keyword="'.GetString($data[$i]['keyword'],'xml').'" viewword="'.GetString(GetCutString($data[$i]['keyword'],$limit),'xml').'" time="'.$time.'" />';
	}
}
?>