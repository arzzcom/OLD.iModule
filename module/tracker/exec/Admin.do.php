<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();
$mTracker = new ModuleTracker();
$member = $mMember->GetMemberInfo();

$action = Request('action');
$do = Request('do');

if ($action == 'category') {
	header('Content-type: text/xml; charset="UTF-8"', true);
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");

	if ($do == 'add') {
		$Error = array();
		$insert['title'] = Request('title') ? Request('title') : $Error['title'] = '카테고리명을 입력하여 주십시오.';
		if (Request('parent') == '0') {
			$insert['form_layout'] = Request('form_layout') ? Request('form_layout') : '등록 레이아웃을 선택하여 주십시오.';
			$insert['view_layout'] = Request('view_layout') ? Request('view_layout') : '보기 레이아웃을 선택하여 주십시오.';
			$insert['search_layout'] = Request('search_layout') ? Request('search_layout') : '검색 레이아웃을 선택하여 주십시오.';
			$insert['artist_layout'] = Request('artist_layout') ? Request('artist_layout') : '배우 레이아웃을 선택하여 주십시오.';
		}
		$insert['parent'] = Request('parent');
		
		$sort = $mDB->DBfetch($mTracker->table['category'],array('MAX(sort)'),"where `parent`='{$insert['parent']}'");
		$insert['sort'] = isset($sort[0]) == true ? $sort[0]+1 : 0;
		
		if (sizeof($Error) == 0) {
			$mDB->DBinsert($mTracker->table['category'],$insert);
		}

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="'.(sizeof($Error) == 0 ? 'true' : 'false').'">';

		if (sizeof($Error) > 0) {
			echo '<errors>';
			foreach ($Error as $id=>$msg) {
				echo '<field><id>'.$id.'</id><msg><![CDATA['.$msg.']]></msg></field>';
			}
			echo '</errors>';
		} else {
			echo '<errors>';
			echo '<field><id>'.$idx.'</id></field>';
			echo '</errors>';
		}

		echo '</message>';
	}
	
	if ($do == 'modify') {
		$data = GetExtData('data');

		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$mDB->DBupdate($mTracker->table['category'],array('title'=>$data[$i]['title'],'form_layout'=>$data[$i]['form_layout'],'view_layout'=>$data[$i]['view_layout'],'search_layout'=>$data[$i]['search_layout'],'artist_layout'=>$data[$i]['artist_layout'],'sort'=>$i),'',"where `idx`={$data[$i]['idx']}");
		}

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="true">';
		echo '</message>';
	}

	if ($do == 'delete') {
		$idx = explode(',',Request('idx'));

		for ($i=0, $loop=sizeof($idx);$i<$loop;$i++) {
			$mDB->DBdelete($mTracker->table['category'],"where `idx`='{$idx[$i]}'");
			$subRegion = $mDB->DBfetchs($mTracker->table['category'],array('idx'),"where `parent`='{$idx[$i]}'");
			for ($j=0, $loopj=sizeof($subRegion);$j<$loopj;$j++) {
				$mDB->DBdelete($mTracker->table['category'],"where `idx`={$subRegion[$j]['idx']}");
				$mDB->DBdelete($mTracker->table['category'],"where `parent`={$subRegion[$j]['idx']}");
			}
		}

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="true">';
		echo '</message>';
	}
}

if ($action == 'tag') {
	header('Content-type: text/xml; charset="UTF-8"', true);
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");

	if ($do == 'add') {
		$Error = array();
		$insert['title'] = Request('title') ? Request('title') : $Error['title'] = '태그명을 입력하여 주십시오.';
		$insert['category1'] = Request('category');
		
		$sort = $mDB->DBfetch($mTracker->table['tag'],array('MAX(sort)'),"where `category1`='{$insert['category']}'");
		$insert['sort'] = isset($sort[0]) == true ? $sort[0]+1 : 0;
		
		if (sizeof($Error) == 0) {
			$mDB->DBinsert($mTracker->table['tag'],$insert);
		}

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="'.(sizeof($Error) == 0 ? 'true' : 'false').'">';

		if (sizeof($Error) > 0) {
			echo '<errors>';
			foreach ($Error as $id=>$msg) {
				echo '<field><id>'.$id.'</id><msg><![CDATA['.$msg.']]></msg></field>';
			}
			echo '</errors>';
		} else {
			echo '<errors>';
			echo '<field><id>'.$idx.'</id></field>';
			echo '</errors>';
		}

		echo '</message>';
	}
	
	if ($do == 'modify') {
		$data = GetExtData('data');

		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$mDB->DBupdate($mTracker->table['tag'],array('title'=>$data[$i]['title'],'sort'=>$i),'',"where `idx`={$data[$i]['idx']}");
		}

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="true">';
		echo '</message>';
	}

	if ($do == 'delete') {
		$idx = explode(',',Request('idx'));

		for ($i=0, $loop=sizeof($idx);$i<$loop;$i++) {
			$mDB->DBdelete($mTracker->table['tag'],"where `idx`='{$idx[$i]}'");
			$subRegion = $mDB->DBfetchs($mTracker->table['tag'],array('idx'),"where `parent`='{$idx[$i]}'");
			for ($j=0, $loopj=sizeof($subRegion);$j<$loopj;$j++) {
				$mDB->DBdelete($mTracker->table['tag'],"where `idx`={$subRegion[$j]['idx']}");
				$mDB->DBdelete($mTracker->table['tag'],"where `parent`={$subRegion[$j]['idx']}");
			}
		}

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="true">';
		echo '</message>';
	}
}
?>