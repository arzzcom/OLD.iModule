<?php
REQUIRE_ONCE '../../config/default.conf.php';

$rid = Request('rid');
$mRelease = new ModuleRelease($rid,array('view_alllist'=>'FALSE','use_mode'=>'TRUE'));

GetDefaultHeader($mRelease->GetSetup('title'));
?>
<div style="padding:5px;">
<?php $mRelease->PrintRelease(); ?>
</div>
<?php GetDefaultFooter(); ?>