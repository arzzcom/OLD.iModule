<?php
REQUIRE_ONCE '../../config/default.conf.php';

$mode = Request('mode');
$idx = Request('idx');

$mKin = new ModuleKin();
$mKin->PrintMent($mode,$idx);
?>