<?php
REQUIRE_ONCE '../../../config/default.conf.php';

GetDefaultHeader('사용자정보');
?>
<div style="background:#FFFFFF; padding:5px;">
<?php
$mMember = new ModuleMember();
$mMember->PrintMyInfo('default');
?>
</div>
<?php
GetDefaultFooter();
?>