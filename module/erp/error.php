<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html" charset="UTF-8" />
<META http-equiv="X-UA-Compatible" content="IE=8" />
<title>에러발생</title>
<link rel="shortcut icon" href="<?php echo $this->moduleDir; ?>/favicon.ico" />
<link rel="stylesheet" href="<?php echo $_ENV['dir']; ?>/css/default.css" type="text/css" title="style" />
<link rel="stylesheet" href="<?php echo $this->moduleDir; ?>/css/default.css" type="text/css" title="style" />
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/script/default.js"></script>
</head>
<body class="darkgray">

<table cellspacing="0" cellpadding="0" class="layoutfixed">
<tr class="height100">
	<td></td>
</tr>
<tr class="errorTitle">
	<td><div></div></td>
</tr>
<tr>
	<td>
		<div class="errorBox">
			<?php echo $msg; ?>
		</div>
	</td>
</tr>
</table>


</body>
</html>