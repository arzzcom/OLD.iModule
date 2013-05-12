<?php REQUIRE_ONCE '../../config/default.conf.php'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html" charset="UTF-8" />
<META http-equiv="X-UA-Compatible" content="IE=8" />
<title>게시물보기</title>
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/script/default.js"></script>
<link rel="stylesheet" href="<?php echo $_ENV['dir']; ?>/css/default.css" title="style" />
<link rel="stylesheet" href="http://api.mobilis.co.kr/webfonts/css/?fontface=NanumGothicWeb" type="text/css" title="style" />
</head>
<body>
<div style="padding:5px;">
<?php
$bid = Request('bid');
$mBoard = new ModuleBoard($bid);
$mBoard->PrintBoard();
?>
</div>
</body>
</html>