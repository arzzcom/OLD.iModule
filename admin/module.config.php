<?php
REQUIRE_ONCE '../config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();
$member = $mMember->GetMemberInfo();


if ($member['type'] == 'ADMINISTRATOR') {
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
</style>
</head>
<body>

<script type="text/javascript">
Ext.require(['*']);
</script>

<?php REQUIRE_ONCE './module.default.php'; ?>

<script type="text/javascript">
Ext.onReady(function () {
	new Ext.Viewport({
		layout:{type:"border"},
		items:[
			new ContentArea(this)
		]
	}).updateLayout();
});
</script>

</body>
</html>
<?php
} else {
	GetDefaultHeader('사이트관리 로그인');
	$mMember = new ModuleMember();
	$mMember->PrintLoginForm('admin');
	GetDefaultFooter();
}
?>
