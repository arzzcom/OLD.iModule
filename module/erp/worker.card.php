<?php
REQUIRE_ONCE '../../config/default.conf.php';

$mDB = &DB::instance();
$mErp = new ModuleErp();

$idx = Request('idx');
$workerspace = $mDB->DBfetch($mErp->table['workerspace'],'*',"where `idx`=$idx");
$pno = isset($workerspace['pno']) == true ? $workerspace['pno'] : '0';
$worker = $mDB->DBfetch($mErp->table['worker'],'*',"where `idx`={$pno}");

$mBarcode = new Barcode($workerspace['workernum']);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html" charset="UTF-8" />
<META http-equiv="X-UA-Compatible" content="IE=8" />
<title>근로자카드인쇄</title>
<link rel="stylesheet" href="<?php echo $_ENV['dir']; ?>/css/default.css" type="text/css" title="style" />
</head>
<body>

<div style="border:1px dotted #000000; width:445px; height:316px; background:#FFFFFF;">
<table cellpadding="0" cellspacing="0" style="table-layout:fixed; width:100%;">
<col width="222" /><col width="1" /><col width="222" />
<tr>
	<td style="text-align:center;">
		<div style="text-align:center; font:0/0 arial; margin-top:5px;"><img src="/images/kh/workercard_logo.gif" /></div>
		<div style="margin:5px 20px 10px 20px; width:180px; height:130px; border:1px solid #EEEEEE; position:relative; overflow:hidden;"><div style="position:absolute; top:0px; left:0px; height:130px;"><img src="<?php echo $_ENV['dir']; ?>/userfile/erp/worker/<?php echo $workerspace['pno']; ?>.jpg?rand=<?php echo rand(1000,9999); ?>" style="width:180px; height:130px;" /></div></div>

		<div style="text-align:center; font-size:14px; font-family:바탕; font-weight:bold; line-height:18px;"><span style="font-size:18px; letter-spacing:5px;"><?php echo $worker['name']; ?></span><?php echo $workspace['title']; ?><br /><?php echo $workernum; ?></div>
		<div style="margin:10px 0px 8px 0px;"><?php echo $mBarcode->GetBarcode(); ?></div>

		<div style="font-family:돋움; font-size:12px; text-align:center; font-weight:bold;">광 흥 건 설 (주)</div>
	</td>
	<td><div style="border-left:1px dotted #000000; width:0px; height:316px;"></div></td>
	<td>
		<div style="text-align:center; font:0/0 arial; margin-top:5px;"><img src="/images/kh/workercard_logo.gif" /></div>
		<div style="padding:20px; font-family:바탕; font-size:12px; font-weight:bold; height:202px; overflow:hidden;">
		<div style="color:blue; font-size:14px; line-height:150%; text-align:justify;">적토성산 (積土成山)의 정신과 꼼꼼함의 원칙이 만나 광흥건설의 공간미학이 탄생합니다.</div>
		<br /><br /><br />
		<div style="line-height:200%;">○ 설계는 치밀하게...<br />○ 시공은 세심하게...<br />○ 마감은 꼼꼼하게...</div>
		</div>
		<div style="font-family:돋움; font-size:12px; text-align:center; font-weight:bold;">광 흥 건 설 (주)</div>
	</td>
</tr>
</table>
</div>

</body>
</html>