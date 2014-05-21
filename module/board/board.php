<?php
REQUIRE_ONCE '../../config/default.conf.php';

$bid = Request('bid');
$mBoard = new ModuleBoard($bid,array('view_alllist'=>'FALSE'));

GetDefaultHeader($mBoard->GetSetup('title'));
?>
<div style="padding:5px;">
<?php $mBoard->PrintBoard(); ?>
</div>
<?php GetDefaultFooter(); ?>