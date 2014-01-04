<?php
REQUIRE_ONCE './config/default.conf.php';

print_r($_ENV);
if (file_exists('./config/db.conf.php') == false || file_exists('./config/key.conf.php') == false) {
	if (isset($_ENV['key']) == false && isset($_ENV['db']) == false) {
		header("location:http://{$_SERVER['HTTP_HOST']}{$_ENV['dir']}/install.php");
	} else {
		header("location:http://{$_SERVER['HTTP_HOST']}{$_ENV['dir']}/admin");
	}
}
?>