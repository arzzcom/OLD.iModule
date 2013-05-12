<?php
REQUIRE_ONCE '../../config/default.conf.php';

$mDB = &DB::instance();
$mMember = new ModuleMember();
$mOneroom = new ModuleOneroom();
$member = $mMember->GetMemberInfo();
$category = Request('category') ? Request('category') : 'item';

if ($mOneroom->CheckAgent() == false && $mOneroom->CheckDealer() == false) {
	GetDefaultHeader('중개업소/담당자 관리');
	$mMember->PrintLoginForm('oneroom_manager');
} else {
?>
<html lang="ko" xmlns:ext="http://www.extjs.com/docs">
<head>
<meta http-equiv="Content-Type" content="text/html" charset="UTF-8" />
<META http-equiv="X-UA-Compatible" content="IE=8" />
<title>중개업소/담당자 관리</title>
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/script/php2js.php"></script>
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/script/extjs.js"></script>
<link rel="stylesheet" href="<?php echo $_ENV['dir']; ?>/css/extjs.css" type="text/css" title="style" />
</head>
<body>

<div id="admin">
<div id="HeaderLayer">
	<table cellspacing="0" cellpadding="0" class="layoutfixed">
	<col width="100%" /><col width="120" />
	<tr>
		<td></td>
		<td style="padding:2px 5px 2px 0px;">
			<div class="right"><a href="<?php echo $_ENV['dir']; ?>/exec/Member.do.php?action=logout&amp;redirect=reload" target="execFrame"><img src="<?php echo $_ENV['dir']; ?>/images/admin/btn_logout.gif" /></a>
			<div class="height5"></div>
			<div class="right white"><span class="bold"><?php echo $member['name'];?></span>님 환영합니다.</div>
		</td>
	</tr>
	</table>
</div>

<div id="FooterLayer">
copyright © <?php echo $_SERVER['HTTP_HOST']; ?> All Rights Reserved.
</div>

<script type="text/javascript">
Ext.QuickTips.init();
BasicLayoutClass = function() {
	return {
		init:function() {
			GlobalViewPort = this.viewport = new Ext.Viewport({
				id:"ModuleLayout",
				layout:"border",
				items:[this.NorthPanel = new TopArea(),this.WestPanel = new LeftArea(this),this.CenterPanel = new ContentArea(this),this.SouthPanel = new BottomArea()]
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

<?php REQUIRE_ONCE './manager/'.$category.'.php'; ?>

<div id="category">
	<div style="height:1px; color:#FFFFFF; overflow:hidden;"></div>
	<a href="<?php echo $_SERVER['PHP_SELF']; ?>?category=item" style="background-image:url(<?php echo $mOneroom->moduleDir; ?>/images/admin/icon_building.png);"<?php if ($category == 'item') echo ' class="categoryon"'; ?>>매물관리</a>
	<?php if ($mOneroom->CheckAgent() == true) { ?>
	<a href="<?php echo $_SERVER['PHP_SELF']; ?>?category=dealer" style="background-image:url(<?php echo $mOneroom->moduleDir; ?>/images/admin/icon_group.png);"<?php if ($category == 'dealer') echo ' class="categoryon"'; ?>>중개담당자관리</a>
	<?php } ?>
	<?php if ($mOneroom->CheckPrivateDealer() == false) { ?>
	<a href="<?php echo $_SERVER['PHP_SELF']; ?>?category=premium_auction" style="background-image:url(<?php echo $mOneroom->moduleDir; ?>/images/admin/icon_coins.png);"<?php if ($category == 'premium_auction') echo ' class="categoryon"'; ?>>프리미엄구매</a>
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

<script type="text/javascript">
Ext.EventManager.onDocumentReady(BasicLayoutClass.init, BasicLayoutClass, true);

Ext.form.XmlErrorReader = function() {
	Ext.form.XmlErrorReader.superclass.constructor.call(this,{record:"field",success:"@success"},["id", "msg"]);
};
Ext.extend(Ext.form.XmlErrorReader, Ext.data.XmlReader);
</script>
</div>

</body>
</html>
<?php } ?>