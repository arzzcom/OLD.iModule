<?php
class Flush {
	function __construct() {
		@ini_set('zlib.output_compression',0);
		@ini_set('implicit_flush',1);
		@ob_end_clean();
		set_time_limit(0);
		ob_start('ob_gzhandler');
		ob_flush();
		ob_implicit_flush(1);
	}
	
	function flush() {
		echo str_repeat(' ',1024*64);
		ob_flush();
	}
}
?>