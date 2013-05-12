<?php
REQUIRE_ONCE '../config/default.conf.php';
header('Content-Type: application/x-javascript; charset=utf-8');
?>
var ENV = {};
ENV.dir = "<?php echo $_ENV['dir']; ?>";

function in_array(str,array) {
	for (var i=0, loop=array.length;i<loop;i++) {
		if (str == array[i]) return true;
	}
	
	return false;
}