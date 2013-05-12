<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();
$member = $mMember->GetMemberInfo();

$action == Request('action');
$do = Request('do');
$mShop = new ModuleShop();

if ($action == 'category') {
	if ($do == 'add') {
		header('Content-type: text/xml; charset="UTF-8"', true);
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");

		$depth = Request('depth');
		$repto = Request('repto');

		$sort = $mDB->DBfetch($mShop->table['category'],array('sort'),"where `depth`=$depth",'sort,desc','0,1');
		$sort = isset($sort['sort']) == false ? 1 : $sort['sort']+1;
		$idx = $mDB->DBinsert($mShop->table['category'],array('title'=>'새 카테고리','depth'=>$depth,'repto'=>$repto,'sort'=>$sort));

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="true">';
		echo '</message>';
	}

	if ($do == 'modify') {
		header('Content-type: text/xml; charset="UTF-8"', true);
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");

		$Error = array();
		$idx = Request('idx');

		$insert['title'] = Request('title') ? Request('title') : $Error['title'] = '카테고리명을 입력하여 주십시오.';
		$insert['permission'] = Request('permission');

		if (sizeof($Error) == 0) {
			$mDB->DBupdate($mShop->table['category'],$insert,'',"where `idx`=$idx");

			if (CreateDirectory($_ENV['path'].'/userfile/shop/category') == true) {
				if ($_FILES['image']['tmp_name']) {
					@move_uploaded_file($_FILES['image']['tmp_name'],$_ENV['path'].'/userfile/shop/category/'.$idx);
				}
			}

			if (Request('is_del') != null) {
				@unlink($_ENV['path'].'/userfile/shop/category/'.$idx);
			}
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

	if ($do == 'delete') {
		$idxs = split(',',Request('idxs'));
		$depth = Request('depth');

		if ($depth == '1') {
			for ($i=0, $loop=sizeof($idxs);$i<$loop;$i++) {
				$depth2 = $mDB->DBfetchs($mShop->table['category'],array('idx'),"where `depth`=2 and `repto`={$idxs[$i]}");
				for ($j=0, $loopj=sizeof($depth2);$j<$loopj;$j++) {
					$depth3 = $mDB->DBfetchs($mShop->table['category'],array('idx'),"where `depth`=3 and `repto`={$depth2[$j]['idx']}");
					for ($m=0, $loopm=sizeof($depth3);$m<$loopm;$m++) {
						$mDB->DBdelete($mShop->table['category'],"where `idx`={$depth3[$m]['idx']}");
					}
					$mDB->DBdelete($mShop->table['category'],"where `idx`={$depth2[$j]['idx']}");
				}
				$mDB->DBdelete($mShop->table['category'],"where `idx`={$idxs[$i]}");
			}
		} elseif ($depth == '2') {
			for ($i=0, $loop=sizeof($idxs);$i<$loop;$i++) {
				$depth3 = $mDB->DBfetchs($mShop->table['category'],array('idx'),"where `depth`=3 and `repto`={$idxs[$i]}");
				for ($j=0, $loopj=sizeof($depth3);$j<$loopj;$j++) {
					$mDB->DBdelete($mShop->table['category'],"where `idx`={$depth3[$j]['idx']}");
				}
				$mDB->DBdelete($mShop->table['category'],"where `idx`={$idxs[$i]}");
			}
		} else {
			$mDB->DBdelete($mShop->table['category'],"where `idx` IN ($idxs)");
		}

		$category = $mDB->DBfetchs($mShop->table['category'],array('idx'),"where `depth`=1",'sort,asc');
		$sortnum = 1;
		for ($i=0, $loop=sizeof($category);$i<$loop;$i++) {
			$mDB->DBupdate($mShop->table['category'],array('sort'=>$sortnum),'',"where `idx`={$category[$i]['idx']}");
			$sortnum++;
		}

		$category = $mDB->DBfetchs($mShop->table['category'],array('idx'),"where `depth`=2",'sort,asc');
		$sortnum = 1;
		for ($i=0, $loop=sizeof($category);$i<$loop;$i++) {
			$mDB->DBupdate($mShop->table['category'],array('sort'=>$sortnum),'',"where `idx`={$category[$i]['idx']}");
			$sortnum++;
		}

		$category = $mDB->DBfetchs($mShop->table['category'],array('idx'),"where `depth`=3",'sort,asc');
		$sortnum = 1;
		for ($i=0, $loop=sizeof($category);$i<$loop;$i++) {
			$mDB->DBupdate($mShop->table['category'],array('sort'=>$sortnum),'',"where `idx`={$category[$i]['idx']}");
			$sortnum++;
		}

		// 상품삭제

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="true">';
		echo '</message>';
	}

	if ($do == 'up') {
		header('Content-type: text/xml; charset="UTF-8"', true);
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");

		$idxs = split(',',Request('idxs'));
		$depth = Request('depth');

		$sort = array();

		$category = $mDB->DBfetchs($mShop->table['category'],array('idx'),"where `depth`=$depth",'sort,asc');
		for ($i=0, $loop=sizeof($category);$i<$loop;$i++) {
			$sort[] = $category[$i]['idx'];
		}

		for ($i=1, $loop=sizeof($sort);$i<$loop;$i++) {
			if (in_array($sort[$i],$idxs) == true) {
				$temp = $sort[$i-1];
				$sort[$i-1] = $sort[$i];
				$sort[$i] = $temp;
			}
		}

		$sortnum = 1;
		for ($i=0, $loop=sizeof($sort);$i<$loop;$i++) {
			$mDB->DBupdate($mShop->table['category'],array('sort'=>$sortnum),'',"where `idx`={$sort[$i]}");
			$sortnum++;
		}

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="true">';
		echo '</message>';
	}

	if ($do == 'down') {
		header('Content-type: text/xml; charset="UTF-8"', true);
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");

		$idxs = split(',',Request('idxs'));
		$depth = Request('depth');

		$sort = array();

		$category = $mDB->DBfetchs($mShop->table['category'],array('idx'),"where `depth`=$depth",'sort,asc');
		for ($i=0, $loop=sizeof($category);$i<$loop;$i++) {
			$sort[] = $category[$i]['idx'];
		}

		for ($i=sizeof($sort)-2;$i>=0;$i--) {
			if (in_array($sort[$i],$idxs) == true) {
				$temp = $sort[$i+1];
				$sort[$i+1] = $sort[$i];
				$sort[$i] = $temp;
			}
		}

		$sortnum = 1;
		for ($i=0, $loop=sizeof($sort);$i<$loop;$i++) {
			$mDB->DBupdate($mShop->table['category'],array('sort'=>$sortnum),'',"where `idx`={$sort[$i]}");
			$sortnum++;
		}

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="true">';
		echo '</message>';
	}
}

if ($action == 'banner') {
	if ($do == 'add') {
		header('Content-type: text/xml; charset="UTF-8"', true);
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");

		$Error = array();

		$insert['code'] = Request('code') ? Request('code') : $Error['code'] = '배너코드을 입력하여 주십시오.';
		$insert['code'] = eregi("^[[:alnum:]]+$",$insert['code']) == true ? $insert['code'] : $Error['code'] = '배너코드는 영문과 숫자만 가능합니다.';
		$insert['info'] = Request('info') ? Request('info') : $Error['code'] = '배너설명을 입력하여 주십시오.';

		if ($mDB->DBcount($mShop->table['banner'],"where `code`='{$insert['code']}'") == 1) $Error['code'] = '배너코드가 중복됩니다.';

		if (sizeof($Error) == 0) {
			$mDB->DBinsert($mShop->table['banner'],$insert);
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
			echo '<field><id></id></field>';
			echo '</errors>';
		}

		echo '</message>';
	}

	if ($do == 'delete') {
		$codes = split(',',Request('codes'));
		for ($i=0, $loop=sizeof($codes);$i<$loop;$i++) {
			$code = $codes[$i];
			$mDB->DBdelete($mShop->table['banner'],"where `code`='$code'");

			$image = $mDB->DBfetchs($mShop->table['bannerlist'],array('idx','filepath'),"where `code`='$code'");
			for ($j=0, $loopj=sizeof($image);$j<$loopj;$j++) {
				@unlink($_ENV['path'].$image[$j]['filepath']);
				$mDB->DBdelete($mShop->table['bannerlist'],"where `idx`={$image[$j]['idx']}");
			}
		}

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="true">';
		echo '</message>';
	}

	if ($do == 'addimage') {
		header('Content-type: text/xml; charset="UTF-8"', true);
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");

		$Error = array();

		if ($_FILES['image']['tmp_name']) {
			$check = getimagesize($_FILES['image']['tmp_name']);

			if (in_array($check[2],array('1','2','3')) == true) {
				$insert['type'] == 'IMG';
			} elseif ($check[2] == '4') {
				$insert['type'] == 'SWF';
			} else {
				$Error['image'] = '이미지 또는 플래시파일을 선택하여 주십시오.';
			}
		} else {
			$Error['image'] = '이미지 또는 플래시파일을 선택하여 주십시오.';
		}

		$insert['code'] = Request('code');
		$insert['url'] = Request('url') ? Request('url') : $Error['url'] = '링크주소를 입력하여 주십시오.';
		$insert['target'] = Request('link_target') ? Request('link_target') : $Error['link_target'] = '타겟을 선택하여 주십시오.';;

		if (sizeof($Error) == 0) {
			$filedir = '/userfile/shop/banner';
			$filepath = $filedir.'/'.md5_file($_FILES['image']['tmp_name']).'.'.rand(100,999).'.'.GetFileExec($_FILES['image']['name']);
			$insert['filepath'] = $filepath;

			if (CreateDirectory($_ENV['path'].$filedir) == true) {
				@move_uploaded_file($_FILES['image']['tmp_name'],$_ENV['path'].$filepath);
				$idx = $mDB->DBinsert($mShop->table['bannerlist'],$insert);
			}
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
			echo '<field><id></id></field>';
			echo '</errors>';
		}

		echo '</message>';
	}

	if ($do == 'deleteimage') {
		$idxs = Request('idxs');
		$image = $mDB->DBfetchs($mShop->table['bannerlist'],array('idx','filepath'),"where `idx` IN ($idxs)");
		for ($i=0, $loop=sizeof($image);$i<$loop;$i++) {
			@unlink($_ENV['path'].$image[$i]['filepath']);
			$mDB->DBdelete($mShop->table['bannerlist'],"where `idx`={$image[$i]['idx']}");
		}

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="true">';
		echo '</message>';
	}
}

if ($action == 'item') {
	if ($do == 'add' || $do == 'modify') {
		header('Content-type: text/xml; charset="UTF-8"', true);
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");

		$Error = array();
		$insert['title'] = Request('title') ? Request('title') : $Error['title'] = '상품명을 입력하여 주십시오.';
		$insert['code'] = Request('code') ? Request('code') : '';

		if (Request('category') != null) {
			$category = split(',',Request('category'));
			$insert['category1'] = $category[0];
			$insert['category2'] = $category[1];
			$insert['category3'] = $category[2];
		} else {
			$Error['category'] = '카테고리를 선택하여 주십시오.';
		}

		$insert['cost'] = Request('cost') ? (eregi("^[0-9]+$",str_replace(',','',Request('cost'))) == true ? str_replace(',','',Request('cost')) : $Error['cost'] = '숫자만 입력하여 주십시오.') : '0';
		$insert['type'] = Request('type');
		$insert['price'] = Request('price') ? (eregi("^[0-9]+$",str_replace(',','',Request('price'))) == true ? str_replace(',','',Request('price')) : $Error['price'] = '숫자만 입력하여 주십시오.') : $Error['price'] = '가격을 입력하여 주십시오.';
		$insert['point'] = Request('point_setup');
		$insert['delivery_price'] = Request('delivery_price') ? (eregi("^[0-9]+$",str_replace(',','',Request('delivery_price'))) == true ? str_replace(',','',Request('delivery_price')) : $Error['delivery_price'] = '숫자만 입력하여 주십시오.') : $Error['delivery_price'] = '가격을 입력하여 주십시오.';
		$insert['selltype'] = Request('selltype');
		$insert['withitem'] = Request('withitem');
		$insert['is_hot'] = Request('is_hot') ? 'TRUE' : 'FALSE';
		$insert['is_new'] = Request('is_new') ? 'TRUE' : 'FALSE';
		$insert['is_package'] = Request('is_package') ? 'TRUE' : 'FALSE';
		$insert['is_sale'] = Request('is_sale') ? 'TRUE' : 'FALSE';

		if ($insert['selltype'] == '1') {
			$insert['pay_cash'] = Request('pay_cash') ? 'TRUE' : 'FALSE';
			$insert['pay_card'] = Request('pay_card') ? 'TRUE' : 'FALSE';
			$insert['pay_point'] = Request('pay_point') ? 'TRUE' : 'FALSE';
			$insert['pay_with'] = Request('pay_with') ? 'TRUE' : 'FALSE';
		} elseif ($insert['selltype'] == '2') {
			$insert['pay_cash'] = 'FALSE';
			$insert['pay_card'] = 'FALSE';
			$insert['pay_point'] = 'TRUE';
			$insert['pay_with'] = 'FALSE';
		} elseif ($insert['selltype'] == '3') {
			$insert['pay_cash'] = 'TRUE';
			$insert['pay_card'] = 'FALSE';
			$insert['pay_point'] = Request('pay_point') ? 'TRUE' : 'FALSE';
			$insert['pay_with'] = 'FALSE';
		}

		$insert['limit'] = Request('limit') ? (eregi("^[0-9]+$",str_replace(',','',Request('limit'))) == true ? str_replace(',','',Request('limit')) : $Error['limit'] = '숫자만 입력하여 주십시오.') : '0';
		$insert['is_soldout'] = Request('is_soldout') ? 'TRUE' : 'FALSE';
		$insert['content'] = Request('content');

		if ($do == 'add') {
			if (isset($_FILES['list_image']['tmp_name']) == true && $_FILES['list_image']['tmp_name']) {
				$check = getimagesize($_FILES['list_image']['tmp_name']);

				if (in_array($check[2],array('1','2','3')) == true) {
					$filedir = '/userfile/shop/item/'.date('Ym');
					$filepath = $filedir.'/L'.md5_file($_FILES['list_image']['tmp_name']).'.'.rand(1000,9999).'.'.GetFileExec($_FILES['list_image']['name']);

					if (CreateDirectory($_ENV['path'].$filedir) == true) {
						if ($check[0] > 150 || $check[1] > 150) {
							if (GetThumbneil($_FILES['list_image']['tmp_name'],$_ENV['path'].$filepath,150,150) == true) $insert['list_image'] = $filepath;
						} else {
							@move_uploaded_file($_FILES['list_image']['tmp_name'],$_ENV['path'].$filepath);
							$insert['list_image'] = $filepath;
						}
					}
				} else {
					$Error['list_image'] = '이미지파일만 업로드하여 주십시오. (GIF, JPG, PNG)';
				}
			}

			if (isset($_FILES['view_image']['tmp_name']) == true && $_FILES['view_image']['tmp_name']) {
				$check = getimagesize($_FILES['view_image']['tmp_name']);

				if (in_array($check[2],array('1','2','3')) == true) {
					$filedir = '/userfile/shop/item/'.date('Ym');
					$filepath = $filedir.'/V'.md5_file($_FILES['view_image']['tmp_name']).'.'.rand(1000,9999).'.'.GetFileExec($_FILES['view_image']['name']);

					if (CreateDirectory($_ENV['path'].$filedir) == true) {
						@move_uploaded_file($_FILES['view_image']['tmp_name'],$_ENV['path'].$filepath);
						$insert['view_image'] = $filepath;
					}
				} else {
					$Error['view_image'] = '이미지파일만 업로드하여 주십시오. (GIF, JPG, PNG)';
				}
			}

			if (sizeof($Error) == 0) {
				$idx = $mDB->DBinsert($mShop->table['item'],$insert);

				$files = Request('file');
				for ($i=0, $loop=sizeof($files);$i<$loop;$i++) {
					$file = explode('|',$files[$i]);
					$fidx = $file[0];

					if (sizeof($file) == 1) {
						$data = $mDB->DBfetch($mShop->table['file'],array('filepath'),"where `idx`='$fidx'");
						if (isset($data['filepath']) == true) @unlink($_ENV['path'].$data['filepath']);
						$mDB->DBdelete($mShop->table['file'],"where `idx`='$fidx'");
					} else {
						$mDB->DBupdate($mShop->table['file'],array('repto'=>$idx),'',"where `idx`='$fidx'");
					}
				}

				$optionno = $mDB->DBinsert($mShop->table['option'],array('itemno'=>$idx,'title'=>'옵션'));
				$option1 = $mDB->DBinsert($mShop->table['optionlist'],array('itemno'=>$idx,'optionno'=>$optionno,'value'=>'','price'=>0));
				$mDB->DBinsert($mShop->table['remain'],array('itemno'=>$idx,'option1'=>$option1,'remain'=>0));
			}
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
}
?>