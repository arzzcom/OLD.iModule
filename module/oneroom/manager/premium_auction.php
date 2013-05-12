<?php
$mOneroom = new ModuleOneroom();
?>
<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"프리미엄구매",
		layout:"fit",
		margins:"0 5 0 0",
		items:[
			new Ext.Panel({
				border:false,
				layout:"border",
				items:[
					new Ext.Panel({
						region:"west",
						width:300,
						margins:"5 5 5 5",
						autoScroll:true,
						title:"<?php echo date('m',mktime(0,0,0,date('m')+1,1,date('Y'))); ?>월 프리미엄구매 경매참여하기",
						items:[
							new Ext.form.FormPanel({
								id:"AuctionForm",
								border:false,
								errorReader:new Ext.form.XmlErrorReader(),
								items:[
									new Ext.form.FieldSet({
										title:"경매안내",
										style:"margin:10px;",
										html:'<span class="bold">경매기간 : </span>매달 <?php echo $mOneroom->GetConfig('premium_auction_start'); ?>일부터 <?php echo $mOneroom->GetConfig('premium_auction_end'); ?>일까지<br /><span class="bold">경매참가포인트 : </span><span class="red bold"><?php echo number_format($mOneroom->GetConfig('premium_auction_point')); ?>포인트</span><br /><span class="darkgray">(입찰시마다 입찰포인트와 별도로 차감)</span><br /><span class="bold">최소입찰포인트 : </span><span class="blue bold"><?php echo number_format($mOneroom->GetConfig('premium_point')); ?>포인트</span><br /><span class="bold">낙찰범위 : </span>상위 <span class="bold red"><?php echo number_format($mOneroom->GetConfig('premium_x')*$mOneroom->GetConfig('premium_y')); ?>위</span>까지<br /><span class="bold">낙찰가 : </span>경매마감시의 <span class="bold blue"><?php echo number_format($mOneroom->GetConfig('premium_x')*$mOneroom->GetConfig('premium_y')); ?>위 입찰가</span><br /><span class="darkgray">(낙찰가를 초과하는 포인트는 환급됩니다.)</span><br /><span class="bold">프리미엄이용기간 : </span>다음달 1일부터 한달간'
									}),
									new Ext.form.FieldSet({
										title:"나의 경매참여정보",
										style:"margin:10px;",
										html:'<span class="bold">입찰가능포인트 : </span><span id="MyPoint" class="blue bold"><?php echo number_format($member['point']); ?></span>포인트<br /><span class="bold">남은입찰횟수 : </span><span id="MyCount" class="blue bold"><?php echo $mOneroom->GetConfig('premium_auction_limit') - $mOneroom->GetMyPremiumActionCount(); ?></span>회'
									}),
									new Ext.form.FieldSet({
										title:"경매입찰",
										style:"margin:10px;",
										labelWidth:80,
										labelAlign:"right",
										items:[
											new Ext.form.NumberField({
												fieldLabel:"입찰가",
												name:"point",
												width:100,
												allowBlank:false,
												minValue:<?php echo $mOneroom->GetConfig('premium_point'); ?>
											})
										]
									})
								],
								buttons:[
									new Ext.Button({
										text:"경매입찰하기",
										handler:function() {
											if (<?php echo date('j'); ?> < <?php echo $mOneroom->GetConfig('premium_auction_start'); ?> || <?php echo date('j'); ?> > <?php echo $mOneroom->GetConfig('premium_auction_end'); ?>) {
												Ext.Msg.show({title:"에러",msg:"현재는 경매기간이 아닙니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
												return false;
											}
											
											if (<?php echo $mOneroom->GetConfig('premium_point'); ?> > Ext.getCmp("AuctionForm").getForm().findField("point").getValue()) {
												Ext.Msg.show({title:"에러",msg:"최소입찰금액보다 입찰가가 작습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
												return false;
											}
											
											if (<?php echo $mOneroom->GetConfig('premium_auction_point'); ?> + Ext.getCmp("AuctionForm").getForm().findField("point").getValue() > <?php echo $member['point']; ?>) {
												Ext.Msg.show({title:"에러",msg:"포인트가 부족합니다. 포인트를 충전하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
												return false;
											}
											
											if (<?php echo $mOneroom->GetConfig('premium_auction_limit'); ?> <= <?php echo $mOneroom->GetMyPremiumActionCount(); ?>) {
												Ext.Msg.show({title:"에러",msg:"이번달 경매참여횟수를 모두 사용하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
												return false;
											}
											
											Ext.getCmp("AuctionForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.do.php?action=premium_auction",waitMsg:"입찰중입니다."});
										}
									})
								],
								listeners:{actioncomplete:{fn:function(form,action) {
									if (action.type == "submit") {
										var point = FormSubmitReturnValue(action);
										document.getElementById("MyPoint").innerHTML = point;
										document.getElementById("MyCount").innerHTML = parseInt(document.getElementById("MyCount").innerHTML) - 1;
										form.reset();
										Ext.getCmp("ListPanel").getStore().reload();
									}
								}}}
							})
						]
					}),
					new Ext.grid.GridPanel({
						region:"center",
						id:"ListPanel",
						layout:"fit",
						margins:"5 5 5 0",
						autoScroll:true,
						title:"경매현황",
						cm:new Ext.grid.ColumnModel([
							new Ext.grid.RowNumberer(),
							{
								header:"입찰자",
								dataIndex:"user",
								sortable:false,
								width:60,
								sortable:false
							},{
								header:"입찰금액",
								dataIndex:"point",
								sortable:true,
								width:100,
								sortable:false,
								renderer:GridNumberFormat
							},{
								header:"입찰일자",
								dataIndex:"reg_date",
								sortable:true,
								width:120,
								sortable:false,
								renderer:function(value) {
									return '<span style="font-family:tahoma;">'+value+'</span>';
								}
							}
						]),
						store:new Ext.data.Store({
							proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php"}),
							reader:new Ext.data.JsonReader({
								root:"lists",
								totalProperty:"totalCount",
								fields:["user","point","reg_date","status"]
							}),
							remoteSort:false,
							sortInfo:{field:"point",direction:"DESC"},
							autoLoad:true,
							baseParams:{action:"premium_auction",get:"list"}
						}),
						listeners:{
							render:{fn:function(grid) {
								grid.getStore().load({params:{start:0,limit:50}});
							}},
							rowdblclick:{fn:function(grid,idx,e) {
								var data = grid.getStore().getAt(idx);
								ItemFunction(data.get("idx"));
							}}
						}
					})
				]
			})
		]
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>