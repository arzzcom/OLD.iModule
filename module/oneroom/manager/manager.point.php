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
		
		var store2 = new Ext.data.JsonStore({
			proxy:{
				type:"ajax",
				simpleSortMode:true,
				url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php",
				reader:{type:"json",root:"lists",totalProperty:"totalCount"},
				extraParams:{action:"point",get:"paylist"}
			},
			remoteSort:true,
			sorters:[{property:"reg_date",direction:"DESC"}],
			pageSize:50,
			fields:["idx","reg_date","payment","point","price","status"]
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
								layout:"fit",
								resizable:false,
								items:[
									new Ext.form.FormPanel({
										border:false,
										id:"<?php echo $id; ?>BuyForm",
										fieldDefaults:{labelWidth:85,labelAlign:"right",anchor:"100%",allowBlank:false},
										bodyPadding:"5 5 0 5",
										items:[
											new Ext.form.FieldSet({
												title:"결제방식 선택",
												items:[
													new Ext.form.ComboBox({
														fieldLabel:"결제방식",
														name:"payment",
														typeAhead:true,
														lazyRender:false,
														store:new Ext.data.JsonStore({
															proxy:{
																type:"ajax",
																simpleSortMode:true,
																url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php",
																reader:{type:"json",root:"lists",totalProperty:"totalCount"},
																extraParams:{action:"point",get:"payment"}
															},
															remoteSort:false,
															sorters:[{property:"sort",direction:"ASC"}],
															autoLoad:true,
															pageSize:50,
															fields:["idx","display","type","min_point","max_point"]
														}),
														editable:false,
														mode:"local",
														displayField:"display",
														valueField:"idx",
														triggerAction:"all",
														emptyText:"결제방식을 선택하세요.",
														listeners:{select:{fn:function(form,select) {
															var selected = select.shift().data;
															if (selected.type == "BANKING") {
																Ext.getCmp("<?php echo $id; ?>BuyFormBANKING").show();
																Ext.getCmp("<?php echo $id; ?>BuyFormBANKING").enable();
															}
															
															Ext.getCmp("<?php echo $id; ?>BuyForm").getForm().findField("point").setMinValue(selected.min_point);
															Ext.getCmp("<?php echo $id; ?>BuyForm").getForm().findField("point").setMaxValue(selected.max_point == "0" ? "" : selected.max_point);
															var minPoint = selected.min_point == "0" ? "제한없음" : GetNumberFormat(selected.min_point)+"포인트";
															var maxPoint = selected.max_point == "0" ? "제한없음" : GetNumberFormat(selected.max_point)+"포인트";
															Ext.getCmp("<?php echo $id; ?>BuyFormLimit").setValue("최소구매포인트 : "+minPoint+" / 최대구매포인트 : "+maxPoint);
														}}}
													})
												]
											}),
											new Ext.form.FieldSet({
												title:"포인트 구매",
												items:[
													new Ext.form.FieldContainer({
														fieldLabel:"구매포인트",
														layout:"hbox",
														items:[
															new Ext.form.NumberField({
																name:"point",
																width:100,
																value:1000,
																step:1000,
																listeners:{
																	blur:{fn:function(form) {
																		form.setValue(Math.floor(form.getValue()/1000)*1000);
																	}},
																	change:{fn:function(form) {
																		Ext.getCmp("<?php echo $id; ?>BuyFormPrice").setValue(GetNumberFormat(Math.ceil(Ext.getCmp("<?php echo $id; ?>BuyForm").getForm().findField("point").getValue()/Ext.getCmp("<?php echo $id; ?>BuyFormPrice").ratio/100)*100)+" 원");
																	}}
																}
															}),
															new Ext.form.DisplayField({
																value:"&nbsp;포인트 (1000포인트 단위)"
															})
														]
													}),
													new Ext.form.DisplayField({
														id:"<?php echo $id; ?>BuyFormLimit",
														fieldLabel:"구매제한안내",
														value:"최소구매포인트 : 제한없음 / 최대구매포인트 : 제한없음"
													}),
													new Ext.form.DisplayField({
														id:"<?php echo $id; ?>BuyFormPrice",
														fieldLabel:"결제금액",
														ratio:1,
														value:"0 원"
													})
												]
											}),
											new Ext.form.FieldSet({
												id:"<?php echo $id; ?>BuyFormBANKING",
												title:"입금자정보 (선택하신 입금계좌로 결제금액을 입금하여 주십시오.)",
												hidden:true,
												disabled:true,
												items:[
													new Ext.form.FieldContainer({
														fieldLabel:"입금예정일",
														layout:"hbox",
														items:[
															new Ext.form.DateField({
																name:"banking1",
																format:"Y-m-d",
																value:Ext.Date.format(new Date(),"Y-m-d"),
																width:100
															}),
															new Ext.form.DisplayField({
																value:"&nbsp;(입금예정일로부터 7일이상 미입금시 자동취소)"
															})
														],
													}),
													new Ext.form.TextField({
														fieldLabel:"입금자명",
														name:"banking2"
													})
												]
											})
										]
									})
								],
								buttons:[
									new Ext.Button({
										text:"포인트구매",
										handler:function() {
											Ext.getCmp("<?php echo $id; ?>BuyForm").getForm().submit({
												url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.do.php?action=point&do=payment",
												submitEmptyText:false,
												waitTitle:"잠시만 기다려주십시오.",
												waitMsg:"포인트구매신청중입니다.",
												success:function(form,action) {
													if (action.result.type == "BANKING") {
														Ext.Msg.show({title:"안내",msg:"성공적으로 처리되었습니다.<br />"+action.result.message,buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
															Ext.getCmp("<?php echo $id; ?>BuyWindow").close();
															Ext.getCmp("<?php echo $id; ?>ListTab").setActiveTab(1);
															Ext.getCmp("<?php echo $id; ?>ListPanel2").getStore().loadPage(1);
														}});
													}
												},
												failure:function(form,action) {
													Ext.Msg.show({title:"에러",msg:"입력내용에 오류가 있습니다.<br />입력내용을 다시 한번 확인하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
												}
											});
										}
									}),
									new Ext.Button({
										text:"취소",
										handler:function() {
											Ext.getCmp("<?php echo $id; ?>BuyWindow").close();
										}
									})
								],
								listeners:{
									show:{fn:function() {
										Ext.Ajax.request({
											url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php",
											success:function(response) {
												var data = Ext.JSON.decode(response.responseText);
												if (data.success == true) {
													Ext.getCmp("<?php echo $id; ?>BuyFormPrice").ratio = parseInt(data.ratio);
													Ext.getCmp("<?php echo $id; ?>BuyForm").getForm().findField("point").fireEvent("change",Ext.getCmp("<?php echo $id; ?>BuyForm").getForm().findField("point"));
												} else {
													Ext.Msg.show({title:"안내",msg:"지금은 포인트를 구매할 수 없습니다.<br />관리자에게 문의하여 주시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
														Ext.getCmp("<?php echo $id; ?>BuyWindow").close();
													}});
												}
											},
											failure:function() {
											},
											headers:{},
											params:{"action":"point","get":"buyinfo"}
										});
									}}
								}
							}).show();
						}
					}),
					'-',
					new Ext.toolbar.TextItem({
						text:"나의 포인트 : 계산중...",
						listeners:{render:{fn:function(button) {
							Ext.Ajax.request({
								url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php",
								success:function(response) {
									var data = Ext.JSON.decode(response.responseText);
									button.setText("나의 포인트 : "+GetNumberFormat(data.point)+"포인트");
								},
								failure:function() {
								},
								headers:{},
								params:{"action":"mypoint"}
							});
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
								id:"<?php echo $id; ?>ListPanel1",
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
							}),
							new Ext.grid.GridPanel({
								id:"<?php echo $id; ?>ListPanel2",
								title:"포인트구매내역",
								layout:"fit",
								border:false,
								autoScroll:true,
								columns:[
									new Ext.grid.RowNumberer(),
									{
										header:"구매일시",
										dataIndex:"reg_date",
										width:120,
										renderer:function(value) {
											return '<div style="font-family:tahoma;">'+value+'</div>'
										}
									},{
										header:"구매방법",
										dataIndex:"payment",
										minWidth:150,
										flex:1
									},{
										header:"구매포인트",
										dataIndex:"point",
										width:110,
										renderer:function(value) {
											return '<div style="text-align:right; color:blue;">'+GetNumberFormat(value)+' 포인트</div>';
										}
									},{
										header:"결제금액",
										dataIndex:"price",
										width:110,
										renderer:function(value) {
											return '<div style="text-align:right; color:red;">'+GetNumberFormat(value)+' 원</div>';
										}
									},{
										header:"상태",
										dataIndex:"status",
										width:80,
										renderer:function(value) {
											var status = {NEW:"<span style='color:red;'>결제확인중</span>",COMPLETE:"<span style='color:blue;'>충전완료</span>"};
											return status[value];
										}
									}
								],
								columnLines:true,
								store:store2,
								bbar:new Ext.PagingToolbar({
									store:store2,
									displayInfo:true
								})
							})
						]
					})
				],
				listeners:{show:{fn:function() {
					store1.load();
					store2.load();
				}}}
			}).show();
		}
	}
});

ManagerModules.push(new MyDesktop.<?php echo $id; ?>());
ManagerShortcuts.push({name:"<?php echo $title; ?>",icon:"./images/<?php echo $id; ?>48.png",module:"<?php echo $id; ?>"});
</script>