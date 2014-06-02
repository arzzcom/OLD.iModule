<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();
$mRelease = new ModuleRelease();
$member = $mMember->GetMemberInfo();

$action = Request('action');
$do = Request('do');

$return = array();
$errors = array();

if ($action == 'release') {
	if ($do == 'add' || $do == 'modify') {
		if ($do == 'add') {
			$mRelease = new ModuleRelease();
		} elseif ($do == 'modify') {
			$rid = Request('rid');
			$mRelease = new ModuleRelease($rid);
		}

		if ($mRelease->GetPermission('setup') == false) $errors['message'] = '관리권한이 없습니다.';

		$insert = array();
		$insert['title'] = Request('title');
		$insert['skin'] = Request('skin');
		$insert['width'] = Request('width') && preg_match('/^[^0]+[0-9]+(%)?$/',Request('width')) == true ? Request('width') : $errors['width'] = '릴리즈게시판가로크기를 정확하게 입력하여 주십시오.';
		
		$insert['listnum'] = Request('listnum');
		$insert['pagenum'] = Request('pagenum');
		$insert['view_alllist'] = Request('view_alllist') == 'on' ? 'TRUE' : 'FALSE';
		$insert['view_prevnext'] = Request('view_prevnext') == 'on' ? 'TRUE' : 'FALSE';
		$insert['use_ment'] = Request('use_ment') == 'on' ? 'TRUE' : 'FALSE';
		$insert['use_category'] = Request('use_category') == 'on' ? 'TRUE' : 'FALSE';
		if ($insert['use_category'] == 'TRUE') $insert['use_category'] = Request('use_category_option') == 'on' ? 'TRUE' : 'OPTION';
		$insert['use_charge'] = Request('use_charge') == 'on' ? 'TRUE' : 'FALSE';
		$insert['post_point'] = Request('post_point');
		$insert['ment_point'] = Request('ment_point');
		$insert['tax_point'] = Request('tax_point');
		$insert['permission'] = serialize(array('list'=>Request('permission_list'),'view'=>Request('permission_view'),'post'=>Request('permission_post'),'ment'=>Request('permission_ment'),'modify'=>Request('permission_modify'),'delete'=>Request('permission_delete')));

		if ($do == 'add') {
			$insert['rid'] = Request('rid') && preg_match('/^[a-z0-9_]+$/i',Request('rid')) == true ? Request('rid') : $errors['rid'] = '릴리즈게시판ID를 영문,숫자,_(언더바)를 이용하여 입력하여 주십시오.';

			if ($mDB->DBcount($mRelease->table['setup'],"where `rid`='{$insert['rid']}'") > 0) $errors['rid'] = '이미 사용중인 릴리즈게시판 ID입니다.';

			if (sizeof($errors) == 0) {
				$mDB->DBinsert($mRelease->table['setup'],$insert);
				$return['success'] = true;
				SaveAdminLog('release','['.$insert['rid'].'] 릴리즈게시판을 추가하였습니다.');
			} else {
				$return['success'] = false;
				$return['errors'] = $errors;
			}
		} else {
			$rid = Request('rid');

			if (sizeof($errors) == 0) {
				$mDB->DBupdate($mRelease->table['setup'],$insert,'',"where `rid`='$rid'");
				$return['success'] = true;
				SaveAdminLog('release','['.$rid.'] 릴리즈게시판을 수정하였습니다.');
			} else {
				$return['success'] = false;
				$return['errors'] = $errors;
			}
		}

		exit(json_encode($return));
	}

	if ($do == 'delete') {
		$rid = explode(',',Request('rid'));

		for ($i=0, $loop=sizeof($rid);$i<$loop;$i++) {
			$mDB->DBdelete($mRelease->table['setup'],"where `rid`='{$rid[$i]}'");
			$post = $mDB->DBfetchs($mRelease->table['post'],array('idx'),"where `rid`='{$rid[$i]}'");
			for ($j=0, $loopj=sizeof($post);$j<$loopj;$j++) {
				$mDB->DBdelete($mRelease->table['post'],"where `idx`={$post[$j]['idx']}");

				$file = $mDB->DBfetchs($mRelease->table['file'],array('idx','filetype','filepath'),"where `type`='POST' and `repto`={$post[$j]['idx']}");
				for ($k=0, $loopk=sizeof($file);$k<$loopk;$k++) {
					$mDB->DBdelete($mRelease->table['file'],"where `idx`={$file[$k]['idx']}");
					@unlink($_ENV['userfilePath'].$mRelease->userfile.$file[$k]['filepath']);
					if ($file[$k]['filetype'] == 'IMG') {
						@unlink($_ENV['userfilePath'].$mRelease->thumbnail.'/'.$file[$k]['idx'].'.thm');
					}
				}
				$mDB->DBdelete($mRelease->table['log'],"where `repto`={$post[$j]['idx']}");
				
				$version = $mDB->DBfetchs($mRelease->table['version'],'*',"where `repto`='{$post[$j]['idx']}'");
				for ($k=0, $loopk=sizeof($version);$k<$loopk;$k++) {
					@unlink($_ENV['userfilePath'].$mRelease->file.$version[$k]['file']);
				}
				$mDB->DBdelete($mRelease->table['version'],"where `repto`='{$post[$j]['idx']}'");
				
				@unlink($_ENV['userfilePath'].$this->logo.'/'.$post[$j]['idx'].'.thm');
			}

			$ment = $mDB->DBfetchs($mRelease->table['ment'],array('idx'),"where `rid`='{$rid[$i]}'");
			for ($j=0, $loopj=sizeof($ment);$j<$loopj;$j++) {
				$mDB->DBdelete($mRelease->table['ment'],"where `idx`={$ment[$j]['idx']}");

				$file = $mDB->DBfetchs($mRelease->table['file'],array('idx','filepath'),"where `type`='MENT' and `repto`={$ment[$j]['idx']}");
				for ($k=0, $loopk=sizeof($file);$k<$loopk;$k++) {
					$mDB->DBdelete($mRelease->table['file'],"where `idx`={$file[$k]['idx']}");
					@unlink($_ENV['path'].$file[$k]['filepath']);
				}
			}

			$mDB->DBdelete($mRelease->table['category'],"where `rid`='{$rid[$i]}'");
			$mDB->DBdelete($mRelease->table['log'],"where `rid`='{$rid[$i]}'");
			
			RemoveDirectory($_ENV['userfilePath'].$mRelease->skinThumbnail.'/'.$rid[$i]);

			SaveAdminLog('release','['.$rid[$i].'] 릴리즈게시판을 삭제하였습니다.');
		}

		$return['success'] = true;
		exit(json_encode($return));
	}
	
	if ($do == 'recount') {
		$mFlush = new Flush();
		
		$release = $mDB->DBfetchs($mRelease->table['setup'],array('rid','title'));
		for ($i=0, $loop=sizeof($release);$i<$loop;$i++) {
			$releasename = $release[$i]['title'].'('.$release[$i]['rid'].')';

			$post = $mDB->DBcount($mRelease->table['post'],"where `rid`='{$release[$i]['rid']}' and `is_delete`='FALSE'",'idx');
			$lastPost = $mDB->DBfetch($mRelease->table['post'],array('reg_date'),"where `rid`='{$release[$i]['rid']}' and `is_delete`='FALSE'",'reg_date,desc','0,1');
			$mDB->DBupdate($mRelease->table['setup'],array('post'=>$post,'post_time'=>$lastPost['reg_date']),'',"where `rid`='{$release[$i]['rid']}'");
			
			$category = $mDB->DBfetchs($mRelease->table['category'],array('idx'),"where `rid`='{$release[$i]['rid']}'");
			for ($j=0, $loopj=sizeof($category);$j<$loopj;$j++) {
				$post = $mDB->DBcount($mRelease->table['post'],"where `category`='{$category[$j]['idx']}' and `is_delete`='FALSE'",'idx');
				$lastPost = $mDB->DBfetch($mRelease->table['post'],array('reg_date'),"where `category`='{$category[$j]['idx']}' and `is_delete`='FALSE'",'reg_date,desc','0,1');
				$mDB->DBupdate($mRelease->table['category'],array('post'=>$post,'post_time'=>$lastPost['reg_date']),'',"where `idx`='{$category[$j]['idx']}'");
			}
			
			echo '<script type="text/javascript">top.RecountProgressControl("'.$releasename.'",'.($i+1).','.$loop.');</script>';
			$mFlush->flush();
		}
	}
}

if ($action == 'category') {
	if ($do == 'add') {
		$errors = array();
		$rid = Request('rid');
		$category = Request('category');
		$permission = Request('permission');

		if ($mDB->DBcount($mRelease->table['category'],"where `rid`='$rid' and `category`='$category'") > 0) {
			$errors['category'] = '이미 등록된 카테고리명입니다.';
		}

		if (sizeof($errors) == 0) {
			$sort = $mDB->DBcount($mRelease->table['category'],"where `rid`='$rid'");
			$idx = $mDB->DBinsert($mRelease->table['category'],array('rid'=>$rid,'category'=>$category,'permission'=>$permission,'sort'=>$sort));
			$return['success'] = true;
			$return['idx'] = $idx;
			$return['category'] = $category;
			$return['permission'] = $permission;
			$return['sort'] = $sort;
			
			SaveAdminLog('release','['.$rid.'] 릴리즈게시판에 ['.$category.'] 카테고리를 추가하였습니다.');
		} else {
			$return['success'] = false;
			$return['errors'] = $errors;
		}

		exit(json_encode($return));
	}

	if ($do == 'modify') {
		$data = json_decode(Request('data'),true);
		
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$mDB->DBupdate($mRelease->table['category'],array('category'=>$data[$i]['category'],'permission'=>$data[$i]['permission']),'',"where `idx`='{$data[$i]['idx']}'");
		}
		$return['success'] = true;
		exit(json_encode($return));
	}
	
	if ($do == 'sort') {
		$data = json_decode(Request('data'),true);
		
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$mDB->DBupdate($mRelease->table['category'],array('sort'=>$data[$i]['sort']),'',"where `idx`='{$data[$i]['idx']}'");
		}
		$return['success'] = true;
		exit(json_encode($return));
	}

	if ($do == 'delete') {
		$idx = Request('idx');
		$rid = Request('rid');
		$post = Request('post');
		
		if ($post == 'move') {
			$move = Request('move');
			if (in_array($move,explode(',',$idx)) == true) {
				$errors['message'] = '삭제할 카테고리에 게시물이 옮겨질 카테고리도 포함되어 있습니다.';
			}
		}
		
		if (sizeof($errors) == 0) {
			$mDB->DBdelete($mRelease->table['category'],"where `idx` IN ($idx) and `rid`='$rid'");
		
			if ($post == 'reset') {
				$mDB->DBupdate($mRelease->table['post'],array('category'=>'0'),'',"where `category` IN ($idx)");
			} elseif ($post == 'move') {
				$mDB->DBupdate($mRelease->table['post'],array('category'=>$move),'',"where `category` IN ($idx)");
			} elseif ($post == 'delete') {
				$mDB->DBupdate($mRelease->table['post'],array('is_delete'=>'TRUE','category'=>'0'),'',"where `category` IN ($idx)");
			}
			
			$data = $mDB->DBfetchs($mRelease->table['category'],array('idx'),"where `rid`='$rid'",'sort,asc');
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$mDB->DBupdate($mRelease->table['category'],array('sort'=>$i),'',"where `idx`='{$data[$i]['idx']}'");
			}
			SaveAdminLog('release','['.$rid.'] 릴리즈게시판의 카테고리를 삭제하였습니다.');
			$return['success'] = true;
		} else {
			$return['success'] = false;
			$return['errors'] = $errors;
		}
		
		$release = $mDB->DBfetchs($mRelease->table['setup'],array('rid'));
		for ($i=0, $loop=sizeof($release);$i<$loop;$i++) {
			$post = $mDB->DBcount($mRelease->table['post'],"where `rid`='{$release[$i]['rid']}' and `is_delete`='FALSE'",'idx');
			$mDB->DBupdate($mRelease->table['setup'],array('post'=>$post),'',"where `rid`='{$release[$i]['rid']}'");
		}
		
		$category = $mDB->DBfetchs($mRelease->table['category'],array('idx'));
		for ($i=0, $loop=sizeof($category);$i<$loop;$i++) {
			$post = $mDB->DBcount($mRelease->table['post'],"where `category`='{$category[$i]['idx']}' and `is_delete`='FALSE'",'idx');
			$mDB->DBupdate($mRelease->table['category'],array('post'=>$post),'',"where `idx`='{$category[$i]['idx']}'");
		}
		
		exit(json_encode($return));
	}
}

if ($action == 'post') {
	if ($do == 'delete') {
		$idx = Request('idx');
		$data = $mDB->DBfetchs($mRelease->table['post'],array('idx','rid','is_delete','mno','title'),"where `idx` IN ($idx)");
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			if ($data[$i]['is_delete'] == 'FALSE') {
				$check = $mDB->DBfetch($mRelease->table['setup'],array('post_point'),"where `rid`='{$data[$i]['rid']}'");
				$mMember->SendPoint($data[$i]['mno'],$check['post_point']*-1,$msg='관리자에 의한 프로그램 삭제 ('.GetCutString($data[$i]['title'],20).')','/module/release/release.php?rid='.$data[$i]['rid'].'&mode=view&idx='.$data[$i]['idx'],'release',true);

				$message = array('module'=>'release','mno'=>$member['idx'],'type'=>'delete','parent'=>$data[$i]['title'],'message'=>'관리자에 의해 프로그램이 삭제됨에 따라  '.number_format($check['post_point']).'포인트가 차감되었습니다.');
				$mMember->SendMessage($data[$i]['mno'],$message,'/module/release/release.php?rid='.$data[$i]['rid'].'&mode=view&idx='.$data[$i]['idx'],-1);
			}

			$mDB->DBupdate($mRelease->table['post'],array('is_delete'=>'TRUE'),'',"where `idx`={$data[$i]['idx']}");

			SaveAdminLog('release','['.$data[$i]['title'].'] 프로그램을 삭제하였습니다.','/module/release/release.php?rid='.$data[$i]['rid'].'&mode=view&idx='.$data[$i]['idx']);
		}
		
		$release = $mDB->DBfetchs($mRelease->table['setup'],array('rid'));
		for ($i=0, $loop=sizeof($release);$i<$loop;$i++) {
			$post = $mDB->DBcount($mRelease->table['post'],"where `rid`='{$release[$i]['rid']}' and `is_delete`='FALSE'",'idx');
			$mDB->DBupdate($mRelease->table['setup'],array('post'=>$post),'',"where `rid`='{$release[$i]['rid']}'");
		}
		
		$category = $mDB->DBfetchs($mRelease->table['category'],array('idx'));
		for ($i=0, $loop=sizeof($category);$i<$loop;$i++) {
			$post = $mDB->DBcount($mRelease->table['post'],"where `category`='{$category[$i]['idx']}' and `is_delete`='FALSE'",'idx');
			$mDB->DBupdate($mRelease->table['category'],array('post'=>$post),'',"where `idx`='{$category[$i]['idx']}'");
		}
		
		$return['success'] = true;
		exit(json_encode($return));
	}

	if ($do == 'move') {
		$rid = Request('rid');
		$category = Request('category');
		$idx = Request('idx');

		$data = $mDB->DBfetchs($mRelease->table['post'],array('idx','is_delete','mno','title'),"where `idx` IN ($idx)");
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			if ($data[$i]['is_delete'] == 'FALSE' && $data[$i]['mno'] != '0') {
				$check = $mDB->DBfetch($mRelease->table['setup'],array('title'),"where `rid`='$rid'");
				$message = array('module'=>'release','mno'=>$member['idx'],'type'=>'move','parent'=>$data[$i]['title'],'message'=>'관리자에 의해 게시물이 '.$check['title'].'릴리즈게시판으로 이동되었습니다.');
				$mMember->SendMessage($data[$i]['mno'],$message,'/module/release/release.php?rid='.$rid.'&mode=view&idx='.$data[$i]['idx'],-1);

				SaveAdminLog('release','['.$data[$i]['title'].'] 게시물을 '.$rid.'릴리즈게시판으로 이동하였습니다.','/module/release/release.php?rid='.$rid.'&mode=view&idx='.$data[$i]['idx']);
			}

			$mDB->DBupdate($mRelease->table['post'],array('rid'=>$rid,'category'=>$category),'',"where `idx`={$data[$i]['idx']}");
			$mDB->DBupdate($mRelease->table['ment'],array('rid'=>$rid),'',"where `repto`={$data[$i]['idx']}");
		}
		
		$release = $mDB->DBfetchs($mRelease->table['setup'],array('rid'));
		for ($i=0, $loop=sizeof($release);$i<$loop;$i++) {
			$post = $mDB->DBcount($mRelease->table['post'],"where `rid`='{$release[$i]['rid']}' and `is_delete`='FALSE'",'idx');
			$mDB->DBupdate($mRelease->table['setup'],array('post'=>$post),'',"where `rid`='{$release[$i]['rid']}'");
		}
		
		$category = $mDB->DBfetchs($mRelease->table['category'],array('idx'));
		for ($i=0, $loop=sizeof($category);$i<$loop;$i++) {
			$post = $mDB->DBcount($mRelease->table['post'],"where `category`='{$category[$i]['idx']}' and `is_delete`='FALSE'",'idx');
			$mDB->DBupdate($mRelease->table['category'],array('post'=>$post),'',"where `idx`='{$category[$i]['idx']}'");
		}
		
		$return['success'] = true;
		exit(json_encode($return));
	}
}

if ($action == 'ment') {
	if ($do == 'delete') {
		$idx = Request('idx');
		$data = $mDB->DBfetchs($mRelease->table['ment'],array('idx','rid','mno','password','parent','repto','search','is_select'),"where `idx` IN ($idx)");
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$post = $mDB->DBfetch($mRelease->table['post'],array('rid'),"where `idx`='{$data[$i]['repto']}'");
			$mRelease = new ModuleRelease($post['rid']);

			if ($mRelease->GetChildMent($data[$i]['idx']) == true) {
				$mDB->DBupdate($mRelease->table['ment'],array('is_delete'=>'TRUE'),'',"where `idx`={$data[$i]['idx']}");
			} else {
				$mDB->DBdelete($mRelease->table['ment'],"where `idx`={$data[$i]['idx']}");
			}

			$mRelease->CheckParentMent($data[$i]['parent']);

			$last_ment = $mDB->DBfetch($mRelease->table['ment'],array('reg_date'),"where `repto`={$data[$i]['repto']} and `is_delete`='FALSE'",'reg_date,desc','0,1');
			$last_ment = isset($last_ment['reg_date']) ==  true ? $last_ment['reg_date'] : 0;

			$mDB->DBupdate($mRelease->table['post'],array('last_ment'=>$last_ment),array('ment'=>'`ment`-1'),"where `idx`='{$data[$i]['repto']}'");

			$file = $mDB->DBfetchs($mRelease->table['file'],array('idx'),"where `type`='MENT' and `repto`={$data[$i]['idx']}");
			for ($j=0, $loopj=sizeof($file);$j<$loopj;$j++) {
				$mRelease->FileDelete($file[$j]['idx']);
			}

			if ($data[$i]['mno'] != '0') {
				$check = $mDB->DBfetch($mRelease->table['setup'],array('ment_point'),"where `rid`='{$data[$i]['rid']}'");
				$mMember->SendPoint($data[$i]['mno'],$check['ment_point']*-1,$msg='관리자에 의한 댓글 삭제 ('.GetCutString($data[$i]['search'],20).')','/module/release/release.php?rid='.$data[$i]['rid'].'&mode=view&idx='.$data[$i]['repto'],'release',true);

				$message = array('module'=>'release','mno'=>$member['idx'],'type'=>'delete','parent'=>$data[$i]['search'],'message'=>'관리자에 의해 댓글이 삭제됨에 따라  '.number_format($check['ment_point']).'포인트가 차감되었습니다.');
				$mMember->SendMessage($data[$i]['mno'],$message,'/module/release/release.php?rid='.$data[$i]['rid'].'&mode=view&idx='.$data[$i]['repto'],-1);
			}
			
			if ($data[$i]['is_select'] == 'TRUE') $mDB->DBupdate($mRelease->table['post'],array('is_select'=>'FALSE'),'',"where `idx`='{$data[$i]['repto']}'");

			SaveAdminLog('release','['.GetCutString($data[$i]['search'],20).'] 댓글을 삭제하였습니다.');
		}
		
		$return['success'] = true;
		exit(json_encode($return));
	}

	if ($do == 'spam') {
		$idx = Request('idx');
		$data = $mDB->DBfetchs($mRelease->table['ment'],array('idx','rid','mno','password','parent','repto','search','is_select','ip'),"where `idx` IN ($idx)");
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$post = $mDB->DBfetch($mRelease->table['post'],array('rid'),"where `idx`='{$data[$i]['repto']}'");
			$mRelease = new ModuleRelease($post['rid']);

			if ($mRelease->GetChildMent($data[$i]['idx']) == true) {
				$mDB->DBupdate($mRelease->table['ment'],array('is_delete'=>'TRUE'),'',"where `idx`={$data[$i]['idx']}");
			} else {
				$mDB->DBdelete($mRelease->table['ment'],"where `idx`={$data[$i]['idx']}");
			}

			$mRelease->CheckParentMent($data[$i]['parent']);

			$last_ment = $mDB->DBfetch($mRelease->table['ment'],array('reg_date'),"where `repto`={$data[$i]['repto']} and `is_delete`='FALSE'",'reg_date,desc','0,1');
			$last_ment = isset($last_ment['reg_date']) ==  true ? $last_ment['reg_date'] : 0;

			$mDB->DBupdate($mRelease->table['post'],array('last_ment'=>$last_ment),array('ment'=>'`ment`-1'),"where `idx`='{$data[$i]['repto']}'");

			if ($mDB->DBcount($_ENV['table']['ipban'],"where `ip`='{$data[$i]['ip']}'") == 0) {
				$mDB->DBinsert($_ENV['table']['ipban'],array('ip'=>$data[$i]['ip'],'memo'=>'스팸게시물 등록으로 인한 아이피차단','reg_date'=>GetGMT()));
			}
			
			if ($data[$i]['is_select'] == 'TRUE') $mDB->DBupdate($mRelease->table['post'],array('is_select'=>'FALSE'),'',"where `idx`='{$data[$i]['repto']}'");

			SaveAdminLog('release','['.GetCutString($data[$i]['search'],20).'] 댓글을 스팸차단하였습니다.');
		}
		
		$return['success'] = true;
		exit(json_encode($return));
	}
}

if ($action == 'file') {
	if ($do == 'retrench') {
		$mFlush = new Flush();
		$dirs = scandir($_ENV['userfilePath'].$mRelease->userfile.'/attach',0);
		
		for ($i=0, $loop=sizeof($dirs);$i<$loop;$i++) {
			$dirname = $dirs[$i];

			if ($dirname != '.' && $dirname != '..' && is_dir($_ENV['userfilePath'].$mRelease->userfile.'/attach/'.$dirname) == true) {
				$files = scandir($_ENV['userfilePath'].$mRelease->userfile.'/attach/'.$dirname,0);
			
				$totalFile = sizeof($files);
				$deleteFile = 0;
			
				for ($j=0;$j<$totalFile;$j++) {
					if (is_dir($_ENV['userfilePath'].$mRelease->userfile.'/attach/'.$dirname.'/'.$files[$j]) == false) {
						if ($mDB->DBcount($mRelease->table['file'],"where `filepath`='/attach/{$dirname}/{$files[$j]}'") == 0) {
							$deleteFile++;
							@unlink($_ENV['userfilePath'].$mRelease->userfile.'/attach/'.$dirname.'/'.$files[$j]) or $deleteFile--;
							
							echo '<script type="text/javascript">top.RetrenchProgressControl("'.$dirname.'",'.($i+1).','.$loop.','.$j.','.$totalFile.','.$deleteFile.');</script>';
							$mFlush->flush();
						}
					}
					
					if ($j%10 == 0) {
						echo '<script type="text/javascript">top.RetrenchProgressControl("'.$dirname.'",'.($i+1).','.$loop.','.$j.','.$totalFile.','.$deleteFile.');</script>';
						$mFlush->flush();
					}
				}
			}
			
			echo '<script type="text/javascript">top.RetrenchProgressControl("'.$dirname.'",'.($i+1).','.$loop.','.$loop.','.$totalFile.','.$deleteFile.');</script>';
			$mFlush->flush();
			sleep(1);
		}
		
		echo '<script type="text/javascript">top.RetrenchProgressControl("",'.$loop.','.$loop.',0,0,0);</script>';
		$mFlush->flush();
	}
	
	if ($do == 'norepto') {
		$mFlush = new Flush();
		
		$totalFile = $mDB->DBcount($mRelease->table['file'],"where `repto`!=0");
		$data = $mDB->DBfetchs($mRelease->table['file'],array('idx','type','repto'),"where `repto`>0");
		
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			if ($data[$i]['type'] == 'POST') {
				if ($mDB->DBcount($mRelease->table['post'],"where `idx`={$data[$i]['repto']}") == 0) {
					$mRelease->FileDelete($data[$i]['idx']);
					$deleteFile++;
				}
			} else {
				if ($mDB->DBcount($mRelease->table['ment'],"where `idx`={$data[$i]['repto']}") == 0) {
					$mRelease->FileDelete($data[$i]['idx']);
					$deleteFile++;
				}
			}
			
			if ($i%50 == 0) {
				echo '<script type="text/javascript">top.NoReptoProgressControl('.$i.','.$totalFile.','.$deleteFile.');</script>';
				$mFlush->flush();
			}
		}
		
		echo '<script type="text/javascript">top.NoReptoProgressControl('.$totalFile.','.$totalFile.','.$deleteFile.');</script>';
		$mFlush->flush();
	}

	if ($do == 'removetemp') {
		$mFlush = new Flush();
		
		$totalFile = $mDB->DBcount($mRelease->table['file'],"where `repto`=0");
		$data = $mDB->DBfetchs($mRelease->table['file'],array('idx'),"where `repto`=0");
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$deleteFile++;
			$mRelease->FileDelete($data[$i]['idx']);
			
			if ($i%50 == 0) {
				echo '<script type="text/javascript">top.TempProgressControl('.$i.','.$totalFile.','.$deleteFile.');</script>';
				$mFlush->flush();
			}
		}

		echo '<script type="text/javascript">top.TempProgressControl('.$totalFile.','.$totalFile.','.$deleteFile.');</script>';
		$mFlush->flush();
	}

	if ($do == 'delete') {
		$idx = explode(',',Request('idx'));
		for ($i=0, $loop=sizeof($idx);$i<$loop;$i++) {
			$mRelease->FileDelete($idx[$i]);
		}
		$return['success'] = true;
		exit(json_encode($return));
	}
	
	if ($do == 'reptodelete') {
		$idx = Request('idx');
		
		$data = $mDB->DBfetchs($mRelease->table['file'],'*',"where `idx` IN ($idx)");
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			if ($data[$i]['type'] == 'POST') {
				$post = $mDB->DBfetch($mRelease->table['post'],'*',"where `idx`='{$data[$i]['repto']}'");
				
				if (isset($post['idx']) == true && $post['is_delete'] == 'FALSE') {
					$mDB->DBupdate($mRelease->table['post'],array('is_delete'=>'TRUE'),'',"where `idx`='{$data[$i]['repto']}'");
				
					if ($post['mno'] != '0') {
						$check = $mDB->DBfetch($mRelease->table['setup'],array('post_point'),"where `rid`='{$post['rid']}'");
						$mMember->SendPoint($post['mno'],$check['post_point']*-1,$msg='관리자에 의한 게시물 삭제 ('.GetCutString($post['title'],20).')','/module/release/release.php?rid='.$post['rid'].'&mode=view&idx='.$post['idx'],'release',true);
	
						$message = array('module'=>'release','mno'=>$member['idx'],'type'=>'delete','parent'=>$post['title'],'message'=>'관리자에 의해 댓글이 삭제됨에 따라  '.number_format($check['post_point']).'포인트가 차감되었습니다.');
						$mMember->SendMessage($ment['mno'],$message,'/module/release/release.php?rid='.$post['rid'].'&mode=view&idx='.$post['idx'],-1);
					}
				}
			} else {
				$ment = $mDB->DBfetch($mRelease->table['ment'],'*',"where `idx`='{$data[$i]['repto']}'");
				
				if (isset($ment['idx']) == true && $ment['is_delete'] == 'FALSE') {
					if ($mRelease->GetChildMent($ment['idx']) == true) {
						$mDB->DBupdate($mRelease->table['ment'],array('is_delete'=>'TRUE'),'',"where `idx`={$ment['idx']}");
					} else {
						$mDB->DBdelete($mRelease->table['ment'],"where `idx`={$ment['idx']}");
					}
		
					$mRelease->CheckParentMent($ment['parent']);
		
					$last_ment = $mDB->DBfetch($mRelease->table['ment'],array('reg_date'),"where `repto`={$ment['repto']} and `is_delete`='FALSE'",'reg_date,desc','0,1');
					$last_ment = isset($last_ment['reg_date']) ==  true ? $last_ment['reg_date'] : 0;
		
					$mDB->DBupdate($mRelease->table['post'],array('last_ment'=>$last_ment),array('ment'=>'`ment`-1'),"where `idx`='{$ment['repto']}'");
		
					$file = $mDB->DBfetchs($mRelease->table['file'],array('idx'),"where `type`='MENT' and `repto`={$ment['idx']}");
					for ($j=0, $loopj=sizeof($file);$j<$loopj;$j++) {
						$mRelease->FileDelete($file[$j]['idx']);
					}
	
					if ($ment['mno'] != '0') {
						$check = $mDB->DBfetch($mRelease->table['setup'],array('ment_point'),"where `rid`='{$ment['rid']}'");
						$mMember->SendPoint($ment['mno'],$check['ment_point']*-1,$msg='관리자에 의한 댓글 삭제 ('.GetCutString($ment['search'],20).')','/module/release/release.php?rid='.$ment['rid'].'&mode=view&idx='.$ment['repto'],'release',true);
		
						$message = array('module'=>'release','mno'=>$member['idx'],'type'=>'delete','parent'=>$ment['search'],'message'=>'관리자에 의해 댓글이 삭제됨에 따라  '.number_format($check['ment_point']).'포인트가 차감되었습니다.');
						$mMember->SendMessage($ment['mno'],$message,'/module/release/release.php?rid='.$ment['rid'].'&mode=view&idx='.$ment['repto'],-1);
					}
					
					if ($ment['is_select'] == 'TRUE') $mDB->DBupdate($mRelease->table['post'],array('is_select'=>'FALSE'),'',"where `idx`='{$ment['repto']}'");
				}
			}
		}
		
		$return['success'] = true;
		exit(json_encode($return));
	}
}

if ($action == 'trash') {
	if ($do == 'recover') {
		$idx = Request('idx');
		$mDB->DBupdate($mRelease->table['post'],array('is_delete'=>'FALSE'),'',"where `idx` IN ($idx)");
		
		$release = $mDB->DBfetchs($mRelease->table['post'],'*');
		for ($i=0, $loop=sizeof($release);$i<$loop;$i++) {
			$post = $mDB->DBcount($mRelease->table['post'],"where `rid`='{$release[$i]['rid']}' and `is_delete`='FALSE'");
			$mDB->DBupdate($mRelease->table['post'],array('post'=>$post),'',"where `rid`='{$release[$i]['rid']}'");
		}
		
		$category = $mDB->DBfetchs($mRelease->table['category'],'*');
		for ($i=0, $loop=sizeof($category);$i<$loop;$i++) {
			$post = $mDB->DBcount($mRelease->table['post'],"where `category`='{$category[$i]['idx']}' and `is_delete`='FALSE'");
			$mDB->DBupdate($mRelease->table['post'],array('post'=>$post),'',"where `idx`='{$category[$i]['idx']}'");
		}
		
		$return['success'] = true;
		exit(json_encode($return));
	}
	
	if ($do == 'delete') {
		$idx = Request('idx');
		
		$post = $mDB->DBfetchs($mRelease->table['post'],array('idx'),"where `idx` IN ($idx) and `is_delete`='TRUE'");
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$ment = $mDB->DBfetchs($mRelease->table['ment'],array('idx'),"where `repto`={$post[$i]['idx']}");
			for ($j=0, $loopj=sizeof($ment);$j<$loopj;$j++) {
				$file = $mDB->DBfetchs($mRelease->table['file'],array('idx'),"where `type`='MENT' and `repto`={$ment[$j]['idx']}");
				for ($k=0, $loopk=sizeof($file);$k<$loopk;$k++) $mRelease->FileDelete($file[$k]['idx']);
			}
			$mDB->DBdelete($mRelease->table['ment'],"where `repto`={$post[$i]['idx']}");
			
			$version = $mDB->DBfetchs($mRelease->table['version'],'*',"where `repto`='{$post[$i]['idx']}'");
			for ($j=0, $loopj=sizeof($version);$j<$loopj;$j++) {
				@unlink($_ENV['userfilePath'].$mRelease->file.$version[$j]['file']);
			}
			$mDB->DBdelete($mRelease->table['version'],"where `repto`='{$post[$i]['idx']}'");
			
			@unlink($_ENV['userfilePath'].$mRelease->logo.'/'.$post[$i]['idx'].'.thm');
			$file = $mDB->DBfetchs($mRelease->table['file'],array('idx'),"where `type`='POST' and `repto`={$data[$i]['idx']}");
			for ($j=0, $loopj=sizeof($file);$j<$loopj;$j++) $mRelease->FileDelete($file[$j]['idx']);
			$mDB->DBdelete($mRelease->table['post'],"where `idx`='{$post[$i]['idx']}'");
		}
		
		$return['success'] = true;
		exit(json_encode($return));
	}

	if ($do == 'empty') {
		$post = $mDB->DBfetchs($mRelease->table['post'],array('idx'),"where `is_delete`='TRUE'");
		for ($i=0, $loop=sizeof($post);$i<$loop;$i++) {
			$ment = $mDB->DBfetchs($mRelease->table['ment'],array('idx'),"where `repto`={$post[$i]['idx']}");
			for ($j=0, $loopj=sizeof($ment);$j<$loopj;$j++) {
				$file = $mDB->DBfetchs($mRelease->table['file'],array('idx'),"where `type`='MENT' and `repto`={$ment[$j]['idx']}");
				for ($k=0, $loopk=sizeof($file);$k<$loopk;$k++) $mRelease->FileDelete($file[$k]['idx']);
			}
			$mDB->DBdelete($mRelease->table['ment'],"where `repto`={$post[$i]['idx']}");
			
			$version = $mDB->DBfetchs($mRelease->table['version'],'*',"where `repto`='{$post[$i]['idx']}'");
			for ($j=0, $loopj=sizeof($version);$j<$loopj;$j++) {
				@unlink($_ENV['userfilePath'].$mRelease->file.$version[$j]['file']);
			}
			$mDB->DBdelete($mRelease->table['version'],"where `repto`='{$post[$i]['idx']}'");
			
			@unlink($_ENV['userfilePath'].$mRelease->logo.'/'.$post[$i]['idx'].'.thm');
			
			$file = $mDB->DBfetchs($mRelease->table['file'],array('idx'),"where `type`='POST' and `repto`={$post[$i]['idx']}");
			for ($j=0, $loopj=sizeof($file);$j<$loopj;$j++) $mRelease->FileDelete($file[$j]['idx']);
		}
		$mDB->DBdelete($mRelease->table['post'],"where `is_delete`='TRUE'");
		$return['success'] = true;
		exit(json_encode($return));
	}
}

if ($action == 'log') {
	if ($do == 'delete') {
		$time = GetGMT() - 60*60*24*31;
		$mDB->DBdelete($mRelease->table['log'],"where `reg_date`<$time");
		$min = $mDB->DBfetch($mRelease->table['log'],array('MIN(idx)'));
		$min = $min[0];
		
		$mDB->DBupdate($mRelease->table['log'],'',array('idx'=>'`idx`-'.($min+1)));
		$mDB->DBreset($mRelease->table['log']);
		
		$return['success'] = true;
		exit(json_encode($return));
	}
}
?>