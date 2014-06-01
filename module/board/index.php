<?php
REQUIRE_ONCE '../../config/default.conf.php';

$bid = Request('bid');
$mBoard = new ModuleBoard($bid);

GetDefaultHeader($mBoard->GetSetup('title'));
?>
<div style="padding:5px;">
<?php $mBoard->PrintBoard(); ?>
</div>
<?php GetDefaultFooter(); ?>