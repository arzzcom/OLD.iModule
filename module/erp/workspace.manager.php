<html lang="ko" xmlns:ext="http://www.extjs.com/docs">
<head>
<meta http-equiv="Content-Type" content="text/html" charset="UTF-8" />
<META http-equiv="X-UA-Compatible" content="IE=8" />
<title>현장관리</title>
<link rel="shortcut icon" href="<?php echo $this->moduleDir; ?>/favicon.ico" />
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/script/php2js.php"></script>
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/script/extjs.js"></script>
<script type="text/javascript" src="<?php echo $this->moduleDir; ?>/script/default.js"></script>
<link rel="stylesheet" href="<?php echo $_ENV['dir']; ?>/css/extjs.css" type="text/css" title="style" />
<link rel="stylesheet" href="<?php echo $this->moduleDir; ?>/css/default.css" type="text/css" title="style" />
</head>
<body>

<div id="HeaderLayer">
	<table cellspacing="0" cellpadding="0" class="layoutfixed">
	<col width="125" /><col width="100%" /><col width="200" />
	<tr>
		<td rowspan="2" class="innerimg"><a href="<?php echo $this->defaultURL; ?>"><img src="<?php echo $this->moduleDir; ?>/images/workspace/logo.gif" /></a></td>
		<td rowspan="2" class="innerimg">
			<a href="<?php echo $this->defaultURL; ?>&amp;page=work"><img src="<?php echo $this->moduleDir; ?>/images/workspace/menu_work_<?php echo $page == 'work' ? 'on' : 'off'; ?>.gif" alt="작업일보" /></a>
			<a href="<?php echo $this->defaultURL; ?>&amp;page=worker"><img src="<?php echo $this->moduleDir; ?>/images/workspace/menu_worker_<?php echo $page == 'worker' ? 'on' : 'off'; ?>.gif" alt="근로자관리" /></a>
			<a href="<?php echo $this->defaultURL; ?>&amp;page=outsourcing"><img src="<?php echo $this->moduleDir; ?>/images/workspace/menu_outsourcing_<?php echo $page == 'outsourcing' ? 'on' : 'off'; ?>.gif" alt="하도급관리" /></a>
			<a href="<?php echo $this->defaultURL; ?>&amp;page=itemorder"><img src="<?php echo $this->moduleDir; ?>/images/workspace/menu_itemorder_<?php echo $page == 'itemorder' ? 'on' : 'off'; ?>.gif" alt="자재발주관리" /></a>
			<a href="<?php echo $this->defaultURL; ?>&amp;page=payment"><img src="<?php echo $this->moduleDir; ?>/images/workspace/menu_payment_<?php echo $page == 'payment' ? 'on' : 'off'; ?>.gif" alt="경비관리" /></a>
			<a href="<?php echo $this->defaultURL; ?>&amp;page=monthly"><img src="<?php echo $this->moduleDir; ?>/images/workspace/menu_monthly_<?php echo $page == 'monthly' ? 'on' : 'off'; ?>.gif" alt="기성관리" /></a>
			<?php $mModule = new Module('webhard'); if ($mModule->IsSetup() == true) { ?><a href="<?php echo $this->defaultURL; ?>&amp;page=webhard"><img src="<?php echo $this->moduleDir; ?>/images/workspace/menu_webhard_<?php echo $page == 'webhard' ? 'on' : 'off'; ?>.gif" alt="웹하드" /></a><?php } ?>
		</td>
		<td class="right innerimg" style="padding-right:5px;">
			<a href="<?php echo $_ENV['dir']; ?>/exec/Member.do.php?action=logout&amp;redirect=reload" target="execFrame"><img src="<?php echo $_ENV['dir']; ?>/images/admin/btn_logout.gif" /></a>
		</td>
	</tr>
	<tr style="height:20px;">
		<td style="padding-right:5px;" class="dotum f11 white right"><span class="bold"><?php echo $this->member['name'];?></span>님 환영합니다</td>
	</tr>
	</table>
	<iframe name="execFrame" style="display:none;"></iframe>
</div>

<script type="text/javascript">
var thisTime = <?php echo time()*1000; ?>;
Ext.QuickTips.init();
BasicLayoutClass = function() {
	return {
		init:function() {
			GlobalViewPort = this.viewport = new Ext.Viewport({
				id:"ModuleLayout",
				layout:"border",
				items:[this.NorthPanel = new TopArea(),this.WestPanel = new LeftArea(this),this.CenterPanel = new ContentArea(this),this.EastPanel = new RightArea(),this.SouthPanel = new BottomArea()]
			});
			this.viewport.doLayout();
			this.viewport.syncSize();
		}
	}
}();


/* Header Start */
TopArea = function() {
	TopArea.superclass.constructor.call(this,{
		region:"north",
		collapsible:false,
		height:40,
		border:false,
		minHeight:40,
		maxHeight:40,
		split:true,
		layout:"fit",
		contentEl:"HeaderLayer"
	});
};
Ext.extend(TopArea, Ext.Panel,{});
/* Header End */

/* Footer Start */
BottomArea = function() {
	BottomArea.superclass.constructor.call(this,{
		region:"south",
		collapsible:false,
		height:0,
		maxHeight:0,
		split:true,
		layout:"fit",
		border:false,
		bbar:new Ext.ux.StatusBar({
			enableOverflow:false,
			items:[
				new Ext.Toolbar.TextItem({
					cls:"x-status-text-panel",
					style:"margin-right:2px; padding:4px 5px 0px 3px;",
					height:24,
					text:"<?php echo $this->workspace['title']; ?>"
				}),
				new Ext.Toolbar.TextItem({
					id:"Clock",
					cls:"x-status-text-panel",
					style:"margin-right:2px; padding:4px 5px 0px 3px;",
					height:24,
					listeners:{render:{fn:function() {
						var RealTimeClock = {
							run: function(){
								thisTime = thisTime + 1000;
								Ext.fly("Clock").update(new Date(thisTime).format('Y년 m월 d일 h:i:s A'));
							},
							interval:1000 //1 second
						}
						Ext.TaskMgr.start(RealTimeClock);
					}}}
				})
			]
		})
	});
};
Ext.extend(BottomArea, Ext.Panel,{});
/* Footer End */
</script>

<?php REQUIRE_ONCE $this->modulePath.'/workspace/'.$page.'.'.$category.'.php'; ?>

<div id="category">
	<div style="height:1px; color:#FFFFFF; overflow:hidden;"></div>
	<?php for ($i=0, $loop=sizeof($categorys);$i<$loop;$i++) { ?>
	<a href="<?php echo $this->defaultURL; ?>&amp;page=<?php echo $page; ?>&amp;category=<?php echo $categorys[$i]['category']; ?>" style="background-image:url(<?php echo $categorys[$i]['icon']; ?>);"<?php echo $category == $categorys[$i]['category'] ? ' class="categoryon"' : ''; ?>><?php echo $categorys[$i]['name']; ?></a>
	<?php } ?>
</div>

<div id="TodayWorkspace">
	<?php
	$attend_member = $this->mDB->DBcount($this->table['attend_member'],"where `wno`={$this->wno} and `date`='".GetTime('Y-m-d')."'");
	$workreport_member = $this->mDB->DBcount($this->table['attend_member'],"where `wno`={$this->wno} and `date`='".GetTime('Y-m-d')."' and `work`!=''");
	$outsourcing = $this->mDB->DBcount($this->table['attend_outsourcing'],"where `wno`={$this->wno} and `date`='".GetTime('Y-m-d')."'");
	$dayworker = $this->mDB->DBcount($this->table['attend_dayworker'],"where `wno`={$this->wno} and `date`='".GetTime('Y-m-d')."'");
	?>
	<?php if ($this->CheckWorkReport($this->wno,GetTime('Y-m-d')) == false) { ?>
	<div id="WorkReportAlert" onclick="location.href='<?php echo $_SERVER['PHP_SELF']; ?><?php echo GetQueryString(array('page'=>'work','category'=>'daily')); ?>';">
	오늘자 현장일일상황일지가 작성되지 않았거나, 변경된 사항이 있습니다.
	</div>
	<?php } ?>
	<div class="title" style="background-image:url(<?php echo $this->moduleDir; ?>/images/common/icon_report.png)" onclick="location.href='<?php echo $this->defaultURL; ?>&page=attend&category=work';">작업일보</div>
	<div class="content-<?php echo $attend_member == $workreport_member ? 'on' : 'off'; ?>">
		출근 : <span class="bold"><?php echo $attend_member; ?></span>명<br />
		직원 : <span class="bold"><?php echo $workreport_member; ?></span>명 작성<br />
		외주 : <span class="bold"><?php echo $outsourcing; ?></span>개 업체 작성<br />
		일용직 : <span class="bold"><?php echo $dayworker; ?></span>명 작성
	</div>
	<?php
	$itemorder = $this->mDB->DBfetchs($this->table['itemorder'],array('idx'),"where `wno`={$this->wno} and `status`!='COMPLETE'");
	$repto = array();
	for ($i=0, $loop=sizeof($itemorder);$i<$loop;$i++) $repto[] = $itemorder[$i]['idx'];
	if (sizeof($repto) > 0) {
		$repto = implode(',',$repto);
		$incomming = $this->mDB->DBcount($this->table['itemorder_item'],"where `repto` IN ($repto)");
		$incomed = $this->mDB->DBcount($this->table['itemorder_income'],"where `wno`={$this->wno} and `date`='".GetTime('Y-m-d')."'");
	} else {
		$incomming = $incomed = 0;
	}
	?>
	<div class="title" style="background-image:url(<?php echo $this->moduleDir; ?>/images/common/icon_lorry.png)" onclick="location.href='<?php echo $this->defaultURL; ?>&page=item&category=income';">자재입고</div>
	<div class="content-off">
		오늘입고 : <span class="bold"><?php echo $incomed; ?></span>개 품목<br />
		입고예정 : <span class="bold"><?php echo $incomming; ?></span>개 품목<br />
	</div>
</div>

<script type="text/javascript">
/* Category Start */
LeftArea = function(viewport){
	this.viewport = viewport;

	LeftArea.superclass.constructor.call(this,{
		region:"west",
		title:"카테고리",
		collapsible:true,
		collapsed:false,
		width:200,
		minWidth:200,
		maxWidth:200,
		split:true,
		layout:"fit",
		margins:"0 0 0 5",
		contentEl:"category"
	});
};
Ext.extend(LeftArea, Ext.Panel,{});
/* Category End */

RightArea = function() {
	RightArea.superclass.constructor.call(this,{
		id:"right",
		region:"east",
		title:"오늘의 현장일지",
		collapsible:true,
		collapsed:<?php echo Request('iModuleErpRightPanel','cookie') == null || Request('iModuleErpRightPanel','cookie') == 'true' ? 'false' : 'true'; ?>,
		minWidth:180,
		width:180,
		split:true,
		layout:"fit",
		margins:"0 5 0 0",
		contentEl:"TodayWorkspace",
		listeners:{
			collapse:{fn:function() {
				SetCookie('iModuleErpRightPanel','false');
			}},
			expand:{fn:function() {
				SetCookie('iModuleErpRightPanel','true');
			}}
		}
	})
}
Ext.extend(RightArea, Ext.Panel,{});

Ext.EventManager.onDocumentReady(BasicLayoutClass.init, BasicLayoutClass, true);

Ext.form.XmlErrorReader = function() {
	Ext.form.XmlErrorReader.superclass.constructor.call(this,{record:"field",success:"@success"},["id", "msg"]);
};
Ext.extend(Ext.form.XmlErrorReader, Ext.data.XmlReader);
</script>

</body>
</html>