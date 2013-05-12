<?php
/**
* Smarty plugin
* @package Smarty
* @subpackage plugins
*/


/**
* Smarty number_format modifier plugin
*
* Type: modifier<br>
* Name: number_format<br>
* Purpose: format number via number_format
* @link
http://smarty.php.n­et/manual/en/languag­e.modifier.number.fo­rmat.php
* number_format (Smarty online manual)
* @param float
* @param int
* @param string
* @param string
* @return string
*/
function smarty_modifier_substr($str,$start,$limit) {
	return mb_substr($str,$start,$limit,'UTF-8');
}
/* vim: set expandtab: */

?>