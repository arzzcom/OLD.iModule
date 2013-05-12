<?php
REQUIRE_ONCE '../../config/default.conf.php';

$idx = Request('idx');
$mKin = new ModuleKin();
$mKin->PrintSelectAnswer($idx);
?>