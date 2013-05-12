<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html" charset="UTF-8" />
<META http-equiv="X-UA-Compatible" content="IE=8">
<title>현장관리 - 로그인</title>
<link rel="shortcut icon" href="<?php echo $this->moduleDir; ?>/favicon.ico" />
<link rel="stylesheet" href="<?php echo $_ENV['dir']; ?>/css/default.css" type="text/css" title="style" />
<link rel="stylesheet" href="<?php echo $this->moduleDir; ?>/css/default.css" type="text/css" title="style" />
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/script/default.js"></script>
</head>
<body class="darkgray">

<div id="workspace">



<table cellspacing="0" cellpadding="0" class="layoutfixed">
<tr class="height100">
	<td></td>
</tr>
<tr class="loginTitle">
	<td><div></div></td>
</tr>
<tr>
	<td>
		<div class="loginBox">
			<form name="OutLoginadmin" method="post" action="<?php echo $_ENV['dir']; ?>/exec/Member.do.php" target="loginFrameadmin" onsubmit="return MemberLoginCheck(this.name);">
			<input type="hidden" name="action" value="login" />

			<table cellspacing="0" cellpadding="0" class="layoutfixed">
			<col width="105" /><col width="100%" />
			<tr>
				<td><img src="<?php echo $this->moduleDir; ?>/images/workspace/text_user_id.gif" alt="아이디" /></td>
				<td><input type="text" name="user_id" class="inputbox" msg="아이디를 입력하여 주십시오." /></td>
			</tr>
			<tr class="height10">
				<td colspan="2"></td>
			</tr>
			<tr>
				<td><img src="<?php echo $this->moduleDir; ?>/images/workspace/text_password.gif" alt="패스워드" /></td>
				<td><input type="password" name="password" class="inputbox" msg="패스워드를 입력하여 주십시오." /></td>
			</tr>
			</table>

			<div class="loginButton">
				<table cellspacing="0" cellpadding="0" class="layoutfixed">

				<col width="100%" /><col width="205" /><col width="65" />
				<tr>
					<td><input type="checkbox" class="checkbox" id="autologin" name="autologin" value="1" onclick="MemberLoginAutoLogin(this.id);" msg="공용컴퓨터에서 자동로그인을 설정할 경우, 개인정보가 유출될 수 있습니다.<br />자동로그인을 하도록 설정하시겠습니까?" /></td>
					<td><label for="autologin" onclick="MemberLoginAutoLogin('autologin');"><img src="<?php echo $this->moduleDir; ?>/images/workspace/text_autologin.gif" alt="자동로그인" /></label></td>
					<td><input type="image" src="<?php echo $this->moduleDir; ?>/images/workspace/btn_login.gif" /></td>
				</tr>
				</table>
			</div>
			</form>

			<iframe name="loginFrameadmin" style="display:none;"></iframe>

		</div>
	</td>
</tr>
</table>

</div>
</body>
</html>