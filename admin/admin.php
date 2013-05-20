<?php
if (isset($_ENV['code']) == false) exit;
$page = Request('page') ? Request('page') : 'main';
$subpage = Request('subpage');
$modules = $mDB->DBfetchs($_ENV['table']['module'],array('module','name','is_admin','is_admin_top'),'','sort,asc');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>사이트관리 ExtJS4</title>
<link rel="shortcut icon" href="<?php echo $_ENV['dir']; ?>/favicon.ico" />
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/script/php2js.php"></script>
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/script/extjs4.js"></script>
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/script/extjs4.extend.js"></script>
<link rel="stylesheet" href="<?php echo $_ENV['dir']; ?>/css/extjs4.css" type="text/css" title="style" />
<style type="text/css">
* {margin:0px; padding:0px;}
html, body {overflow:hidden;}

#CategoryLayer A:link {color:#2F2F2F;text-decoration:none;display:block;font-family:NanumGothicWeb;font-size:12px;padding:3px 3px 3px 25px;margin:2px;border:1px solid #FFFFFF;background-repeat:no-repeat;background-position:5px 2px;}
#CategoryLayer A:active {color:#2F2F2F;text-decoration:none;display:block;font-family:NanumGothicWeb;font-size:12px;padding:3px 3px 3px 25px;margin:2px;border:1px solid #FFFFFF;background-repeat:no-repeat;background-position:5px 2px;}
#CategoryLayer A:visited {color:#2F2F2F;text-decoration:none;display:block;font-family:NanumGothicWeb;font-size:12px;padding:3px 3px 3px 25px;margin:2px;border:1px solid #FFFFFF;background-repeat:no-repeat;background-position:5px 2px;}
#CategoryLayer A:hover {color:#2F2F2F;text-decoration:none;display:block;font-family:NanumGothicWeb;font-size:12px;padding:3px 3px 3px 25px;margin:2px;border:1px dotted #CCCCCC;background-color:#EEEEEE;background-repeat:no-repeat;background-position:5px 2px;}
#CategoryLayer A.categoryon:link {font-weight:bold;color:#2F2F2F;text-decoration:none;display:block;font-family:NanumGothicWeb;font-size:12px;padding:3px 3px 3px 25px;margin:2px;border:1px dotted #99BBE8;background-color:#D2E1F4;background-repeat:no-repeat;background-position:5px 2px;}
#CategoryLayer A.categoryon:active {font-weight:bold;color:#2F2F2F;text-decoration:none;display:block;font-family:NanumGothicWeb;font-size:12px;padding:3px 3px 3px 25px;margin:2px;border:1px dotted #99BBE8;background-color:#D2E1F4;background-repeat:no-repeat;background-position:5px 2px;}
#CategoryLayer A.categoryon:visited {font-weight:bold;color:#2F2F2F;text-decoration:none;display:block;font-family:NanumGothicWeb;font-size:12px;padding:3px 3px 3px 25px;margin:2px;border:1px dotted #99BBE8;background-color:#D2E1F4;background-repeat:no-repeat;background-position:5px 2px;}
#CategoryLayer A.categoryon:hover {font-weight:bold;color:#2F2F2F;text-decoration:none;display:block;font-family:NanumGothicWeb;font-size:12px;padding:3px 3px 3px 25px;margin:2px;border:1px dotted #99BBE8;background-color:#D2E1F4;background-repeat:no-repeat;background-position:5px 2px;}
#CategoryLayer .bar {margin:5px 5px 0px 5px; border-top:1px dotted #CCCCCC; height:5px;}
#ExtendLayer A:link {color:#2F2F2F;text-decoration:none;display:block;font-family:NanumGothicWeb;font-size:12px;padding:3px 3px 3px 25px;margin:2px;border:1px solid #FFFFFF;background-repeat:no-repeat;background-position:5px 2px;}
#ExtendLayer A:active {color:#2F2F2F;text-decoration:none;display:block;font-family:NanumGothicWeb;font-size:12px;padding:3px 3px 3px 25px;margin:2px;border:1px solid #FFFFFF;background-repeat:no-repeat;background-position:5px 2px;}
#ExtendLayer A:visited {color:#2F2F2F;text-decoration:none;display:block;font-family:NanumGothicWeb;font-size:12px;padding:3px 3px 3px 25px;margin:2px;border:1px solid #FFFFFF;background-repeat:no-repeat;background-position:5px 2px;}
#ExtendLayer A:hover {color:#2F2F2F;text-decoration:none;display:block;font-family:NanumGothicWeb;font-size:12px;padding:3px 3px 3px 25px;margin:2px;border:1px dotted #CCCCCC;background-color:#EEEEEE;background-repeat:no-repeat;background-position:5px 2px;}
#ExtendLayer A.categoryon:link {font-weight:bold;color:#2F2F2F;text-decoration:none;display:block;font-family:NanumGothicWeb;font-size:12px;padding:3px 3px 3px 25px;margin:2px;border:1px dotted #99BBE8;background-color:#D2E1F4;background-repeat:no-repeat;background-position:5px 2px;}
#ExtendLayer A.categoryon:active {font-weight:bold;color:#2F2F2F;text-decoration:none;display:block;font-family:NanumGothicWeb;font-size:12px;padding:3px 3px 3px 25px;margin:2px;border:1px dotted #99BBE8;background-color:#D2E1F4;background-repeat:no-repeat;background-position:5px 2px;}
#ExtendLayer A.categoryon:visited {font-weight:bold;color:#2F2F2F;text-decoration:none;display:block;font-family:NanumGothicWeb;font-size:12px;padding:3px 3px 3px 25px;margin:2px;border:1px dotted #99BBE8;background-color:#D2E1F4;background-repeat:no-repeat;background-position:5px 2px;}
#ExtendLayer A.categoryon:hover {font-weight:bold;color:#2F2F2F;text-decoration:none;display:block;font-family:NanumGothicWeb;font-size:12px;padding:3px 3px 3px 25px;margin:2px;border:1px dotted #99BBE8;background-color:#D2E1F4;background-repeat:no-repeat;background-position:5px 2px;}
</style>
</head>
<body>

<script type="text/javascript">
Ext.require(['*']);
</script>

<?php
if ($page == 'module') {
	$category = Request('category');
	if ($category == 'default') {
		$mModule = new Module($subpage);
		REQUIRE_ONCE $_ENV['path'].'/module/'.$subpage.'/admin/category.inc.php';
		REQUIRE_ONCE $_ENV['path'].'/admin/module.default.php';
	} elseif ($subpage) {
		REQUIRE_ONCE $_ENV['path'].'/module/'.$subpage.'/admin/category.inc.php';
		$category = Request('category') ? Request('category') : $categorys[0]['category'];
		REQUIRE_ONCE $_ENV['path'].'/module/'.$subpage.'/admin/'.$category.'.php';
		
	} else {
		REQUIRE_ONCE $_ENV['path'].'/admin/module.category.inc.php';
		$category = Request('category') ? Request('category') : $categorys[0]['category'];
		REQUIRE_ONCE $_ENV['path'].'/admin/module.'.$category.'.php';
	}
} else {
	REQUIRE_ONCE $_ENV['path'].'/admin/'.$page.'.category.inc.php';
	$category = Request('category') ? Request('category') : $categorys[0]['category'];
	REQUIRE_ONCE $_ENV['path'].'/admin/'.$page.'.'.$category.'.php';
}
?>

<div style="display:none;">
	<div id="HeaderLayer">
		<table cellspacing="0" cellpadding="0" class="layoutfixed">
		<col width="150" /><col width="100%" /><col width="60" />
		<tr>
			<td rowspan="2" class="innerimg vTop"><a href="<?php echo $_ENV['dir']; ?>/admin/"><img src="<?php echo $_ENV['dir']; ?>/images/admin/logo.gif" /></a></td>
			<td rowspan="2" class="innerimg vTop">
				<a href="<?php echo $_ENV['dir']; ?>/admin/?page=module"><img src="<?php echo $_ENV['dir']; ?>/images/admin/menu_module_<?php echo $page == 'module' && $subpage == null ? 'on' : 'off'; ?>.gif" alt="모듈관리" /></a>
				<a href="<?php echo $_ENV['dir']; ?>/admin/?page=addon"><img src="<?php echo $_ENV['dir']; ?>/images/admin/menu_addon_<?php echo $page == 'addon' && $subpage == null ? 'on' : 'off'; ?>.gif" alt="애드온관리" /></a>
				<a href="<?php echo $_ENV['dir']; ?>/admin/?page=widget"><img src="<?php echo $_ENV['dir']; ?>/images/admin/menu_widget_<?php echo $page == 'widget' && $subpage == null ? 'on' : 'off'; ?>.gif" alt="위젯관리" /></a>
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
	iModule (www.imodule.kr) GPLV2.
	</div>
	
	<div id="CategoryLayer">
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
</div>

<script type="text/javascript">
Ext.onReady(function () {
	new Ext.Viewport({
		layout:{type:"border"},
		items:[
			new Ext.Panel({
				region:"north",
				collapsible:false,
				height:40,
				border:false,
				minHeight:40,
				maxHeight:40,
				split:true,
				layout:"fit",
				contentEl:"HeaderLayer"
			}),
			new Ext.Panel({
				region:"south",
				collapsible:false,
				height:20,
				split:true,
				minHeight:20,
				maxHeight:20,
				layout:"fit",
				border:false,
				contentEl:"FooterLayer"
			}),
			new Ext.Panel({
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
				<?php if ($page == 'module') { ?>
				tbar:[
					new Ext.form.ComboBox({
						flex:1,
						typeAhead:true,
						lazyRender:false,
						store:new Ext.data.JsonStore({
							proxy:{
								type:"ajax",
								simpleSortMode:true,
								url:"<?php echo $_ENV['dir']; ?>/exec/Admin.get.php",
								reader:{type:"json",root:"lists",totalProperty:"totalCount"},
								extraParams:{action:"module",get:"managerlist"}
							},
							remoteSort:false,
							sorters:[{property:"name",direction:"ASC"}],
							autoLoad:true,
							pageSize:50,
							fields:["module","name"]
						}),
						editable:false,
						mode:"local",
						displayField:"name",
						valueField:"module",
						triggerAction:"all",
						emptyText:"전체모듈관리",
						style:{margin:"1px 3px 1px 1px"},
						listeners:{select:{fn:function(form,selected) {
							location.href = "./?page=module&subpage="+selected.shift().data.module;
						}}}
					})
				],
				<?php } ?>
				contentEl:"CategoryLayer"
			}),
			new ContentArea(this)
		]
	}).updateLayout();
});
</script>

</body>
</html>
