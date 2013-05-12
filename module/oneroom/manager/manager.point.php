<?php $id = 'Point'; $title = '포인트관리 (포인트구매)'; ?>
<script type="text/javascript">
Ext.define('MyDesktop.<?php echo $id; ?>',{
	extend:"Ext.ux.desktop.Module",
	id:"<?php echo $id; ?>",
	requires:[
		'Ext.*'
	],
	init:function(){
		this.launcher = {
			text:"<?php echo $title; ?>",
			icon:"./images/<?php echo $id; ?>16.png"
		};
	},
	createWindow:function() {
		var store1 = new Ext.data.JsonStore({
			proxy:{
				type:"ajax",
				simpleSortMode:true,
				url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php",
				reader:{type:"json",root:"lists",totalProperty:"totalCount"},
				extraParams:{action:"point",get:"list"}
			},
			remoteSort:true,
			sorters:[{property:"reg_date",direction:"DESC"}],
			pageSize:50,
			fields:["idx","reg_date","msg",{name:"point",type:"int"}]
		});
		
		var desktop = this.app.getDesktop();
		var win = desktop.getWindow("<?php echo $id; ?>");
		if (!win) {
			win = desktop.createWindow({
				id:"<?php echo $id; ?>",
				title:"<?php echo $title; ?>",
				width:700,
				height:450,
				icon:"./images/<?php echo $id; ?>16.png",
				shim:false,
				animCollapse:false,
				constrainHeader:true,
				layout:"fit",
				resizable:false,
				maximizable:true,
				tbar:[
					new Ext.Button({
						text:"포인트구매",
						icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_coins.png",
						handler:function() {
							new Ext.Window({
								id:"<?php echo $id; ?>BuyWindow",
								title:"포인트구매",
								width:500,
								height:300,
								layout:"fit",
								items:[
									new Ext.grid.GridPanel({
										id:"<?php echo $id; ?>ItemList",
										border:false,
										columns:[
											{
												header:"포인트",
												dataIndex:"point",
												width:110,
												renderer:function(value) {
													return '<div style="text-align:right;">'+GetNumberFormat(value)+'포인트</div>';
												}
											},{
												header:"구매가격",
												dataIndex:"money",
												width:100,
												renderer:function(value) {
													return '<div style="text-align:right;">'+GetNumberFormat(value)+'원</div>';
												}
											},{
												header:"상품정보",
												dataIndex:"info",
												minWidth:120,
												flex:1
											}
										],
										store:new Ext.data.JsonStore({
											proxy:{
												type:"ajax",
												simpleSortMode:false,
												url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php",
												reader:{type:"json",root:"lists",totalProperty:"totalCount",point:"point"},
												extraParams:{action:"point",get:"item"}
											},
											remoteSort:false,
											sorters:[{property:"point",direction:"ASC"}],
											autoLoad:true,
											pageSize:50,
											fields:["idx",{name:"point",type:"int"},{name:"money",type:"int"},"info"]
										}),
										columnLines:true,
										selModel:new Ext.selection.CheckboxModel({mode:"SINGLE"}),
										bbar:[
											new Ext.form.ComboBox({
												flex:1,
												typeAhead:true,
												triggerAction:"all",
												lazyRender:true,
												store:new Ext.data.JsonStore({
													proxy:{
														type:"ajax",
														simpleSortMode:true,
														url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php",
														reader:{type:"json",root:"lists",totalProperty:"totalCount"},
														extraParams:{"action":"region"}
													},
													sorters:[{property:"sort",direction:"ASC"}],
													autoLoad:true,
													fields:["idx","title","sort"]
												}),
												width:100,
												editable:false,
												mode:"local",
												displayField:"title",
												valueField:"idx",
												emptyText:"결제방식을 선택하여 주십시오."
											})
										]
									})
								],
								buttons:[
									new Ext.Button({
										text:"선택상품구매",
										handler:function() {
											var checked = Ext.getCmp("<?php echo $id; ?>SlotList").getSelectionModel().getSelection();
											if (checked.length == 0) {
												Ext.Msg.show({title:"에러",msg:"구매할 상품을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
											} else {
												Ext.Msg.show({title:"확인",msg:checked[0].get("point")+"포인트를 "+GetNumberFormat(checked[0].get("money"))+"원에 구매하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
													if (button == "yes") {
														Ext.Msg.wait("포인트 구매중입니다.","잠시만 기다려주십시오.");
														Ext.Ajax.request({
															url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.do.php",
															success:function(response) {
																var data = Ext.JSON.decode(response.responseText);
																if (data.success == true) {
																	Ext.Msg.show({title:"안내",msg:"성공적으로 구매하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
																		Ext.getCmp("<?php echo $id; ?>BuyWindow").close();
																	}});
																} else {
																	Ext.Msg.show({title:"안내",msg:data.message,buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
																}
															},
															failure:function() {
																Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
															},
															params:{"action":"slot","do":"buy","idx":checked[0].get("idx")}
														});
													}
												}});
											}
										}
									}),
									new Ext.Button({
										text:"취소",
										handler:function() {
											Ext.getCmp("<?php echo $id; ?>BuyWindow").close();
										}
									})
								]
							}).show();
						}
					}),
					'-',
					new Ext.toolbar.TextItem({
						text:"나의 현재 포인트 : 계산중...",
						listeners:{render:{fn:function() {
							
						}}}
					})
				],
				items:[
					new Ext.TabPanel({
						id:"<?php echo $id; ?>ListTab",
						border:false,
						tabPosition:"bottom",
						items:[
							new Ext.grid.GridPanel({
								id:"ListPanel1",
								title:"포인트적립/사용내역",
								layout:"fit",
								border:false,
								autoScroll:true,
								columns:[
									new Ext.grid.RowNumberer(),
									{
										header:"적립/사용일시",
										dataIndex:"reg_date",
										width:120,
										renderer:function(value) {
											return '<div style="font-family:tahoma;">'+value+'</div>'
										}
									},{
										header:"내역",
										dataIndex:"msg",
										minWidth:150,
										flex:1
									},{
										header:"적립포인트",
										dataIndex:"point",
										width:110,
										renderer:function(value) {
											if (value >= 0) return '<div style="text-align:right; color:blue;">'+GetNumberFormat(value)+' 포인트</div>';
										}
									},{
										header:"사용포인트",
										dataIndex:"point",
										width:110,
										renderer:function(value) {
											if (value <= 0) return '<div style="text-align:right; color:red;">'+GetNumberFormat(value)+' 포인트</div>';
										}
									}
								],
								columnLines:true,
								store:store1,
								bbar:new Ext.PagingToolbar({
									store:store1,
									displayInfo:true
								})
							})
						]
					})
				],
				listeners:{show:{fn:function() {
					store1.load();
					//store2.load();
				}}}
			}).show();
		}
	}
});

ManagerModules.push(new MyDesktop.<?php echo $id; ?>());
ManagerShortcuts.push({name:"<?php echo $title; ?>",icon:"./images/<?php echo $id; ?>48.png",module:"<?php echo $id; ?>"});
</script>