<?php
REQUIRE_ONCE '../../config/default.conf.php';

$bid = Request('bid');
$link = Request('link');

$mDB = &DB::instance();
$mMember = &Member::instance();
$mBoard = new ModuleBoard($bid);

$setup = $mDB->DBfetch($mBoard->table['setup'],array('bid','title','use_rss','use_category','rss_config'),"where `bid`='$bid'");

$rss = '<?xml version="1.0" encoding="UTF-8"?>';
$rss.= '<?xml-stylesheet href="/style/rss/style.xsl" type="text/xsl" media="screen"?>';
$rss.= '<rss version="2.0">';
$rss.= '<channel>';

if (isset($setup['bid']) == true) {
	$post = $mDB->DBfetchs($mBoard->table['post'],'*',"where `bid`='$bid' and `is_delete`='FALSE' and `is_secret`='FALSE'",'loop,asc','0,30');
	$config = $setup['rss_config'] && is_array(unserialize($setup['rss_config'])) == true ? unserialize($setup['rss_config']) : array('rss_limit'=>'30','rss_post_limit'=>'0','rss_link'=>'{$HTTP_HOST}'.$mBoard->moduleDir.'/board.php?bid='.$bid,'rss_description'=>'','rss_language'=>'ko');

	$config['rss_link'] = str_replace('{$HTTP_HOST}','http://'.$_SERVER['HTTP_HOST'],$config['rss_link']);
	$config['rss_link'] = str_replace('&','&amp;',$config['rss_link']);
	$config['rss_link_param'] = preg_match('/\?/',$config['rss_link']) == true ? '&amp;' : '?';
	$rss.= '<title>'.GetString($setup['title'],'xml').'</title>';
	$rss.= '<link>'.$config['rss_link'].'</link>';
	$rss.= '<description>'.GetString($config['rss_description'],'xml').'</description>';
	$rss.= '<language>'.$config['rss_language'].'</language>';
	$rss.= '<pubDate>'.(isset($post[0]) == true ? GetTime('r',$post[0]['reg_date']) : GetTime('r')).'</pubDate>';
	if (isset($setup['use_rss']) == true && $setup['use_rss'] == 'TRUE') {
		for ($i=0, $loop=sizeof($post);$i<$loop;$i++) {
			$rss.= '<item>';
			$rss.= '<title>'.GetString($post[$i]['title'],'xml').'</title>';
			$rss.= '<link>'.$config['rss_link'].$config['rss_link_param'].'mode=view&amp;idx='.$post[$i]['idx'].'</link>';
			$rss.= '<description>'.GetString($config['rss_post_limit'] == 0 ? $mBoard->GetContent($post[$i]['content']) : GetCutString($mBoard->GetContent($post[$i]['content']),$config['rss_post_limit'],true),'xml').'</description>';

			if ($setup['use_category'] == 'TRUE') {
				$rss.= '<category>'.GetString($mBoard->GetCategoryName($post[$i]['category']),'xml').'</category>';
			}

			$rss.= '<author>'.GetString($post[$i]['mno'] == '0' ? $post[$i]['name'] : $mMember->GetMemberName($post[$i]['mno'],'nickname',false),'xml').'</author>';
			$rss.= '<pubDate>'.GetTime('r',$post[$i]['reg_date']).'</pubDate>';
			$rss.= '</item>';
		}
	}

	$rss.= '</channel>';
	$rss.= '</rss>';
}




echo $rss;
?>