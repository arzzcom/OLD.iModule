<?php
class Flush {
	function __construct() {
		if (isset($_SERVER['REMOTE_ADDR']) == true) {
			@ini_set('zlib.output_compression',0);
			@ini_set('implicit_flush',1);
			@ob_end_clean();
			ob_start('ob_gzhandler');
			ob_flush();
			ob_implicit_flush(1);
		}
		set_time_limit(0);
	}
	
	function flush() {
		if (isset($_SERVER['REMOTE_ADDR']) == true) {
			echo str_repeat(' ',1024*64);
			ob_flush();
		} else {
			flush();
		}
	}
}
?>