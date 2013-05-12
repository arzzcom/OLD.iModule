<?php
REQUIRE_ONCE '../config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();
$member = $mMember->GetMemberInfo();

if ($member['type'] == 'ADMINISTRATOR') {
	if (isset($_SESSION['isAdminLog']) == false) {
		SaveAdminLog('admin','관리자 페이지에 접근하였습니다.');
		$_SESSION['isAdminLog'] = true;
	}
?>
<html lang="ko" xmlns:ext="http://www.extjs.com/docs">
<?php } else { ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<?php } ?>
<head>
<meta http-equiv="Content-Type" content="text/html" charset="UTF-8" />
<META http-equiv="X-UA-Compatible" content="IE=8" />
<title>사이트관리 ExtJS3</title>
<link rel="shortcut icon" href="<?php echo $_ENV['dir']; ?>/favicon.ico" />
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/script/php2js.php"></script>
<?php if ($member['type'] == 'ADMINISTRATOR') { ?>
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/script/extjs.js"></script>
<link rel="stylesheet" href="http://api.mobilis.co.kr/webfonts/css/?fontface=NanumGothicWeb" type="text/css" title="style" />
<link rel="stylesheet" href="<?php echo $_ENV['dir']; ?>/css/extjs.css" type="text/css" title="style" />
<?php } else { ?>
<link rel="stylesheet" href="<?php echo $_ENV['dir']; ?>/css/default.css" type="text/css" title="style" />
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/script/default.js"></script>
<?php } ?>
</head>
<body>

<div id="admin">
<?php
if ($member['type'] == 'ADMINISTRATOR') {
	$page = Request('page') ? Request('page') : 'main';
	$subpage = Request('subpage');
	$modules = $mDB->DBfetchs($_ENV['table']['module'],array('module','name','is_admin','is_admin_top'),'','module,asc');
?>
<div id="HeaderLayer">
	<table cellspacing="0" cellpadding="0" class="layoutfixed">
	<col width="150" /><col width="100%" /><col width="60" />
	<tr>
		<td rowspan="2" class="innerimg vTop"><a href="<?php echo $_ENV['dir']; ?>/admin/"><img src="<?php echo $_ENV['dir']; ?>/images/admin/logo.gif" /></a></td>
		<td rowspan="2" class="innerimg vTop">
			<!--<a href="<?php echo $_ENV['dir']; ?>/admin/?page=widget"><img src="<?php echo $_ENV['dir']; ?>/images/admin/menu_widget_<?php echo $page == 'widget' ? 'on' : 'off'; ?>.gif" alt="위젯관리" /></a>-->
			<a href="<?php echo $_ENV['dir']; ?>/admin/?page=module"><img src="<?php echo $_ENV['dir']; ?>/images/admin/menu_module_<?php echo $page == 'module' && $subpage == null ? 'on' : 'off'; ?>.gif" alt="모듈관리" /></a>
			<a href="<?php echo $_ENV['dir']; ?>/admin/?page=addon"><img src="<?php echo $_ENV['dir']; ?>/images/admin/menu_addon_<?php echo $page == 'addon' && $subpage == null ? 'on' : 'off'; ?>.gif" alt="애드온관리" /></a>
			<a href="<?php echo $_ENV['dir']; ?>/admin/?page=widget"><img src="<?php echo $_ENV['dir']; ?>/images/admin/menu_widget_<?php echo $page == 'widget' && $subpage == null ? 'on' : 'off'; ?>.gif" alt="통계관리" /></a>
			<a href="<?php echo $_ENV['dir']; ?>/admin/?page=status"><img src="<?php echo $_ENV['dir']; ?>/images/admin/menu_status_<?php echo $page == 'status' ? 'on' : 'off'; ?>.gif" alt="통계" /></a>
			<?php for ($i=0, $loop=sizeof($modules);$i<$loop;$i++) { if ($modules[$i]['is_admin'] == 'TRUE' && $modules[$i]['is_admin_top'] == 'TRUE') { ?>
			<?php if (isset($isModuleBar) == false) { $isModuleBar = true; echo '<img src="'.$_ENV['dir'].'/images/admin/menu_bar.gif" />'; } ?>
			<a href="<?php echo $_ENV['dir']; ?>/admin/?page=module&amp;subpage=<?php echo $modules[$i]['module']; ?>"><img src="<?php echo $_ENV['dir']; ?>/module/<?php echo $modules[$i]['module']; ?>/images/admin/menu_<?php echo $page == 'module' && $subpage == $modules[$i]['module'] ? 'on' : 'off'; ?>.gif" alt="<?php echo $modules[$i]['name']; ?>관리" /></a>
			<?php }} ?>
		</td>
		<td class="right innerimg" style="padding:2px 5px 0px 0px;">
			<a href="<?php echo $_ENV['dir']; ?>/exec/Member.do.php?action=logout&amp;redirect=reload" target="execFrame"><img src="<?php echo $_ENV['dir']; ?>/images/admin/btn_logout.gif" /></a>
		</td>
	</tr>
	<tr style="height:20px;">
		<td style="padding:5px 5px 0px 0px;" class="dotum f11 white right vTop"><span class="bold"><?php echo $member['name'];?></span>님</td>
	</tr>
	</table>
	<iframe name="execFrame" style="display:none;"></iframe>
</div>

<div id="FooterLayer">
copyright © iModule (www.imodule.kr) All Rights Reserved.
</div>

<script type="text/javascript">
Ext.QuickTips.init();
BasicLayoutClass = function() {
	return {
		init:function() {
			GlobalViewPort = this.viewport = new Ext.Viewport({
				id:"ModuleLayout",
				layout:"border",
				items:[this.NorthPanel = new TopArea(),this.WestPanel = new LeftArea(this),this.CenterPanel = new ContentArea(this),this.EastPanel = new RightArea(),this.SouthPanel = new BottomArea()]
			});
			this.viewport.doLayout();
			this.viewport.syncSize();
		}
	}
}();

/* Header Start */
TopArea = function() {
	TopArea.superclass.constructor.call(this,{
		region:"north",
		collapsible:false,
		height:40,
		border:false,
		minHeight:40,
		maxHeight:40,
		split:true,
		layout:"fit",
		contentEl:"HeaderLayer"
	});
};
Ext.extend(TopArea, Ext.Panel,{});
/* Header End */

/* Footer Start */
BottomArea = function() {
	BottomArea.superclass.constructor.call(this,{
		region:"south",
		collapsible:false,
		height:20,
		split:true,
		minHeight:20,
		maxHeight:20,
		layout:"fit",
		border:false,
		contentEl:"FooterLayer"
	});
};
Ext.extend(BottomArea, Ext.Panel,{});
/* Footer End */
</script>

<?php
if ($page == 'module') {
	if (Request('category') == 'default') {
		$mModule = new Module($subpage);
		REQUIRE_ONCE $_ENV['path'].'/module/'.$subpage.'/admin.extjs3/category.inc.php';
		REQUIRE_ONCE $_ENV['path'].'/admin.extjs3/module.default.php';
	} elseif ($subpage) {
		REQUIRE_ONCE $_ENV['path'].'/module/'.$subpage.'/admin.extjs3/category.inc.php';
		$category = Request('category') ? Request('category') : $categorys[0]['category'];
		REQUIRE_ONCE $_ENV['path'].'/module/'.$subpage.'/admin.extjs3/'.$category.'.php';
		
	} else {
		REQUIRE_ONCE $_ENV['path'].'/admin.extjs3/module.category.inc.php';
		$category = Request('category') ? Request('category') : $categorys[0]['category'];
		REQUIRE_ONCE $_ENV['path'].'/admin.extjs3/module.'.$category.'.php';
	}
} else {
	REQUIRE_ONCE $_ENV['path'].'/admin.extjs3/'.$page.'.category.inc.php';
	$category = Request('category') ? Request('category') : $categorys[0]['category'];
	REQUIRE_ONCE $_ENV['path'].'/admin.extjs3/'.$page.'.'.$category.'.php';
}
?>

<div id="category">
	<div style="height:1px; color:#FFFFFF; overflow:hidden;"></div>
	<?php if ($page == 'module' && $subpage != null) { ?>
	<a href="<?php echo $_ENV['dir']; ?>/admin/<?php echo GetQueryString(array('subpage'=>'','category'=>'')); ?>" style="background-image:url('<?php echo $_ENV['dir']; ?>/images/admin/icon_plugin.png');">모듈목록</a>
	<div class="bar"></div>
	<?php
	$mModule = new Module($subpage);
	if ($mModule->IsConfig() == true) {
	?>
	<a href="<?php echo $_ENV['dir']; ?>/admin/<?php echo GetQueryString(array('category'=>'default')); ?>" style="background-image:url('<?php echo $_ENV['dir']; ?>/images/admin/icon_cog.png');"<?php echo Request('category') == 'default' ? ' class="categoryon"' : ''; ?>>모듈기본설정</a>
	<?php } ?>
	<?php } ?>
	<?php for ($i=0, $loop=sizeof($categorys);$i<$loop;$i++) { ?>
	<a href="<?php echo $_ENV['dir']; ?>/admin/<?php echo GetQueryString(array('category'=>$categorys[$i]['category'])); ?>" style="background-image:url(<?php echo $categorys[$i]['icon']; ?>);"<?php echo $category == $categorys[$i]['category'] ? ' class="categoryon"' : ''; ?>><?php echo $categorys[$i]['name']; ?></a>
	<?php } ?>
</div>

<script type="text/javascript">
/* Category Start */
LeftArea = function(viewport){
	this.viewport = viewport;

	LeftArea.superclass.constructor.call(this,{
		region:"west",
		title:"카테고리",
		collapsible:true,
		collapsed:false,
		width:200,
		minWidth:200,
		maxWidth:200,
		split:true,
		layout:"fit",
		margins:"0 0 0 5",
		contentEl:"category"
	});
};
Ext.extend(LeftArea, Ext.Panel,{});
/* Category End */
</script>
<?php if ($page == 'module') { ?>
<div id="extend">
	<div style="height:1px; color:#FFFFFF; overflow:hidden;"></div>
	<?php for ($i=0, $loop=sizeof($modules);$i<$loop;$i++) { ?>
	<a href="<?php echo $_ENV['dir']; ?>/admin/<?php echo GetQueryString(array('page'=>'module','subpage'=>$modules[$i]['module'],'category'=>'')); ?>" style="background-image:url(<?php echo $_ENV['dir']; ?>/module/<?php echo $modules[$i]['module']; ?>/images/admin/icon_default.png);"<?php echo $subpage == $modules[$i]['module'] ? ' class="categoryon"' : ''; ?>><?php echo $modules[$i]['name']; ?>관리</a>
	<?php } ?>
</div>
<?php } else { ?>
<div id="extend">
부가기능이 없습니다.
</div>
<?php } ?>
<script type="text/javascript">
RightArea = function() {
	RightArea.superclass.constructor.call(this,{
		id:"right",
		region:"east",
		title:"설치된 모듈바로가기",
		collapsible:true,
		collapsed:<?php echo Request('iModuleAdminRightPanel','cookie') == null || Request('iModuleAdminRightPanel','cookie') == 'true' ? 'false' : 'true'; ?>,
		width:180,
		split:true,
		layout:"fit",
		margins:"0 5 0 0",
		contentEl:"extend",
		listeners:{
			collapse:{fn:function() {
				SetCookie('iModuleAdminRightPanel','false');
			}},
			expand:{fn:function() {
				SetCookie('iModuleAdminRightPanel','true');
			}}
		}
	})
}
Ext.extend(RightArea, Ext.Panel,{});
</script>

<script type="text/javascript">
Ext.EventManager.onDocumentReady(BasicLayoutClass.init, BasicLayoutClass, true);

Ext.form.XmlErrorReader = function() {
	Ext.form.XmlErrorReader.superclass.constructor.call(this,{record:"field",success:"@success"},["id", "msg"]);
};
Ext.extend(Ext.form.XmlErrorReader, Ext.data.XmlReader);
<?php if (Request('IE6Alert','session') == null) { $_SESSION['IE6Alert'] = true; ?>
Ext.onReady(function(){
	if (Ext.isIE6 == true) {
		Ext.Msg.show({title:"IE6에서 접속하셨습니다.",msg:"iModule사이트관리는 IE6에서 일부기능제한 및 디자인오류가 있을 수 있습니다.<br />IE8, FF3, Safari4, Opera10, Chrome등의 최신 브라우져를 권장합니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
	}
});
<?php } ?>
</script>

<?php
} else {
	$mMember = new ModuleMember();
	$mMember->PrintLoginForm('admin');
}
?>
</div>

</body>
</html>
