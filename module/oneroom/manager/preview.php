<?php
REQUIRE_ONCE '../../../config/default.conf.php';

GetDefaultHeader('매물 미리보기');
$idx = Request('idx');
$_REQUEST['mode'] = 'view';
?>
<div style="background:#FFFFFF; padding:5px;">
<?php
$mOneroom = new ModuleOneroom();
$mOneroom->PrintOneroom();
?>
</div>
<?php
GetDefaultFooter();
?>