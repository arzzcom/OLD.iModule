<?php
$updateVersion = '2.0.0';
$board = $this->mDB->DBfetchs($_ENV['code'].'_board_table','*');
for ($i=0, $loop=sizeof($board);$i<$loop;$i++) {
	$post = $this->mDB->DBcount($_ENV['code'].'_board_post_table',"where `bid`='{$board[$i]['bid']}' and `is_delete`='FALSE'");
	$lastPost = $this->mDB->DBfetch($_ENV['code'].'_board_post_table',array('reg_date'),"where `bid`='{$board[$i]['bid']}' and `is_delete`='FALSE'",'loop,asc','0,1');
	$this->mDB->DBupdate($_ENV['code'].'_board_table',array('post'=>$post,'post_time'=>$lastPost['reg_date']),'',"where `bid`='{$board[$i]['bid']}'");
}

$category = $this->mDB->DBfetchs($_ENV['code'].'_board_category_table','*');
for ($i=0, $loop=sizeof($category);$i<$loop;$i++) {
	$post = $this->mDB->DBcount($_ENV['code'].'_board_post_table',"where `category`='{$category[$i]['idx']}' and `is_delete`='FALSE'");
	$lastPost = $this->mDB->DBfetch($_ENV['code'].'_board_post_table',array('reg_date'),"where `category`='{$category[$i]['idx']}' and `is_delete`='FALSE'",'loop,asc','0,1');
	$this->mDB->DBupdate($_ENV['code'].'_board_category_table',array('post'=>$post,'post_time'=>$lastPost['reg_date']),'',"where `idx`='{$category[$i]['idx']}'");
}
?>