<?php
REQUIRE_ONCE '../../config/default.conf.php';

$category = Request('category');
$mTracker = new ModuleTracker();

$mTracker->PrintWriteInner($category);
?>