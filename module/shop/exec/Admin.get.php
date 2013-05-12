<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();
$member = $mMember->GetMemberInfo();

$action == Request('action');
$get = Request('get');
$start = Request('start');
$limit = Request('limit');
$sort = Request('sort');
$dir = Request('dir') ? Request('dir') : 'desc';
$callbackStart = Request('callback') ? Request('callback').'(' : '';
$callbackEnd = Request('callback') ? ');' : '';
$limiter = $start != null && $limit != null ? $start.','.$limit : '';
$orderer = $sort != null && $dir != null ? $sort.','.$dir : '';

$list = array();

$mShop = new ModuleShop();

if ($action == 'category') {
	$depth = Request('get');
	$find = '';

	if ($depth == '1') {
		$data = $mDB->DBfetchs($mShop->table['category'],'*',"where `depth`=$depth");
	} else {
		$repto = Request('repto');
		$data = $mDB->DBfetchs($mShop->table['category'],'*',"where `depth`=$depth and `repto`=$repto");
	}

	for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
		$list[$i] = '{';
		$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
		$list[$i].= '"sort":"'.$data[$i]['sort'].'",';
		$list[$i].= '"title":"'.GetString($data[$i]['title'],'ext').'",';
		$list[$i].= '"permission":"'.GetString($data[$i]['permission'],'ext').'",';
		$list[$i].= '"image":"'.(file_exists($_ENV['path'].'/userfile/shop/category/'.$data[$i]['idx']) == true ? $_ENV['dir'].'/userfile/shop/category/'.$data[$i]['idx'] : '').'"';
		$list[$i].= '}';
	}
}

if ($action == 'categoryform') {
	$loopnum = 0;
	$depth1 = $mDB->DBfetchs($mShop->table['category'],array('idx','title'),"where `depth`=1",'sort,asc');
	for ($i=0, $loop=sizeof($depth1);$i<$loop;$i++) {
		$list[$loopnum] = '{"category":"'.$depth1[$i]['idx'].',0,0","title":"'.GetString($depth1[$i]['title'],'ext').'","display":"'.GetString($depth1[$i]['title'],'ext').'"}';

		$depth2 = $mDB->DBfetchs($mShop->table['category'],array('idx','title'),"where `depth`=2 and `repto`={$depth1[$i]['idx']}",'sort,asc');
		for ($j=0, $loopj=sizeof($depth2);$j<$loopj;$j++) {
			$loopnum++;
			$list[$loopnum] = '{"category":"'.$depth1[$i]['idx'].','.$depth2[$j]['idx'].',0","title":"&nbsp;└'.GetString($depth2[$j]['title'],'ext').'","display":"> '.GetString($depth2[$j]['title'],'ext').'"}';

			$depth3 = $mDB->DBfetchs($mShop->table['category'],array('idx','title'),"where `depth`=3 and `repto`={$depth2[$j]['idx']}",'sort,asc');
			for ($m=0, $loopm=sizeof($depth3);$m<$loopm;$m++) {
				$loopnum++;
				$list[$loopnum] = '{"category":"'.$depth1[$i]['idx'].','.$depth2[$j]['idx'].','.$depth3[$m]['idx'].'","title":"&nbsp;&nbsp;&nbsp;└'.GetString($depth3[$m]['title'],'ext').'","display":">> '.GetString($depth3[$m]['title'],'ext').'"}';
			}
		}
		$loopnum++;
	}
}

if ($action == 'item') {
	$find = '';
	$data = $mDB->DBfetchs($mShop->table['item'],array('idx','code','category1','category2','category3','title','price','point','selltype','limit','is_soldout','list_image'),$find,$orderer,$limiter);
	$total = $mDB->DBcount($mShop->table['item'],$find);

	for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
		if ($data[$i]['category3'] != '0') {
			$categoryNum = $data[$i]['category3'];
			$category = '>> ';
		} elseif ($data[$i]['category2'] != '0') {
			$categoryNum = $data[$i]['category2'];
			$category = '> ';
		} else {
			$categoryNum = $data[$i]['category1'];
			$category = '';
		}

		$categoryData = $mDB->DBfetch($mShop->table['category'],array('title'),"where `idx`=$categoryNum");
		$category.= GetString($categoryData['title'],'ext');

		$remainData = $mDB->DBfetch($mShop->table['remain'],array('SUM(remain)'),"where `itemno`={$data[$i]['idx']}");
		$remain = isset($remainData[0]) == true && $remainData[0] ? $remainData[0] : 0;

		$list[$i] = '{';
		$list[$i].= '"category":"'.$category.'",';
		$list[$i].= '"title":"'.GetString($data[$i]['title'],'ext').'",';
		$list[$i].= '"price":"'.$data[$i]['price'].'",';
		$list[$i].= '"point":"'.$data[$i]['point'].'",';
		$list[$i].= '"selltype":"'.$data[$i]['selltype'].'",';
		$list[$i].= '"remain":"'.$remain.'",';
		$list[$i].= '"limit":"'.$data[$i]['limit'].'",';
		$list[$i].= '"is_soldout":"'.$data[$i]['is_soldout'].'",';
		$list[$i].= '"image":"'.($data[$i]['list_image'] ? $_ENV['dir'].$data[$i]['list_image'] : '').'"';
		$list[$i].= '}';
	}
}

if ($action == 'option') {

}

if ($action == 'banner') {
	if ($get == 'list') {
		$data = $mDB->DBfetchs($mShop->table['banner'],'*');

		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$list[$i] = '{';
			$list[$i].= '"code":"'.$data[$i]['code'].'",';
			$list[$i].= '"info":"'.GetString($data[$i]['info'],'ext').'",';
			$list[$i].= '"banner":"'.$mDB->DBcount($mShop->table['bannerlist'],"where `code`='{$data[$i]['code']}'").'"';
			$list[$i].= '}';
		}
	}

	if ($get == 'image') {
		$code = Request('code');
		$data = $mDB->DBfetchs($mShop->table['bannerlist'],'*',"where `code`='$code'");

		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$list[$i] = '{';
			$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
			$list[$i].= '"type":"'.$data[$i]['type'].'",';
			$list[$i].= '"url":"'.GetString($data[$i]['url'],'ext').'",';
			$list[$i].= '"filepath":"'.$data[$i]['filepath'].'"';
			$list[$i].= '}';
		}
	}
}

$total = isset($total) == true ? $total : sizeof($list);
$lists = isset($lists) == true ? $lists : implode(',',$list);
echo $callbackStart;
echo '{"totalCount":"'.$total.'","lists":['.$lists.']}';
echo $callbackEnd;
?>