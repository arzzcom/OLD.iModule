<?php
REQUIRE_ONCE '../config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();
$member = $mMember->GetMemberInfo();

$mModule = new Module(Request('module'));
?>
<html lang="ko" xmlns:ext="http://www.extjs.com/docs">
<head>
<meta http-equiv="Content-Type" content="text/html" charset="UTF-8" />
<META http-equiv="X-UA-Compatible" content="IE=8" />
<title>사이트관리</title>
<link rel="shortcut icon" href="<?php echo $_ENV['dir']; ?>/favicon.ico" />
<?php if ($member['type'] == 'ADMINISTRATOR') { ?>
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/script/php2js.php"></script>
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/script/extjs.js"></script>
<link rel="stylesheet" href="<?php echo $_ENV['dir']; ?>/css/extjs.css" type="text/css" title="style" />
<?php } else { ?>
<link rel="stylesheet" href="<?php echo $_ENV['dir']; ?>/css/default.css" type="text/css" title="style" />
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/script/default.js"></script>
<?php } ?>
</head>
<body>

<div id="admin">
<?php if ($member['type'] == 'ADMINISTRATOR') { ?>
<script type="text/javascript">
Ext.QuickTips.init();
BasicLayoutClass = function() {
	return {
		init:function() {
			GlobalViewPort = this.viewport = new Ext.Viewport({
				id:"ModuleLayout",
				layout:"border",
				items:[this.CenterPanel = new ContentArea(this)]
			});
			this.viewport.doLayout();
			this.viewport.syncSize();
		}
	}
}();
</script>

<?php REQUIRE_ONCE $_ENV['path'].'/admin.extjs3/module.default.php'; ?>

<script type="text/javascript">
Ext.EventManager.onDocumentReady(BasicLayoutClass.init, BasicLayoutClass, true);

Ext.form.XmlErrorReader = function() {
	Ext.form.XmlErrorReader.superclass.constructor.call(this,{record:"field",success:"@success"},["id", "msg"]);
};
Ext.extend(Ext.form.XmlErrorReader, Ext.data.XmlReader);
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