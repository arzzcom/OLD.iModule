<?php $id = 'Premium'; $title = '프리미엄 매물관리'; $actionTarget = 'premium'; $actionTitle = '프리미엄'; ?>
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
		var store = new Ext.data.JsonStore({
			proxy:{
				type:"ajax",
				simpleSortMode:true,
				url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php",
				reader:{type:"json",root:"lists",totalProperty:"totalCount"},
				extraParams:{action:"<?php echo $actionTarget; ?>",get:"pointlist"}
			},
			pageSize:50,
			fields:["idx","status","ino","title","start_time","end_time","hit"]
		});
		
		var desktop = this.app.getDesktop();
		var win = desktop.getWindow("<?php echo $id; ?>");
		if (!win) {
			win = desktop.createWindow({
				id:"<?php echo $id; ?>",
				title:"<?php echo $title; ?>",
				width:800,
				height:500,
				icon:"./images/<?php echo $id; ?>16.png",
				shim:false,
				animCollapse:false,
				constrainHeader:true,
				layout:"fit",
				resizable:false,
				maximizable:true,
				tbar:[
					new Ext.Button({
						text:"<?php echo $actionTitle; ?>매물등록",
						icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_building_add.png",
						handler:function() {
							var ItemStore = new Ext.data.JsonStore({
								proxy:{
									type:"ajax",
									simpleSortMode:true,
									url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php",
									reader:{type:"json",root:"lists",totalProperty:"totalCount"},
									extraParams:{action:"item",get:"list",agent:"0",dealer:"0",region1:"0",region2:"0",region3:"0",category1:"0",category2:"0",category3:"0",keyword:"",is_open:"TRUE"}
								},
								remoteSort:true,
								sorters:[{property:"idx",direction:"DESC"}],
								autoLoad:true,
								pageSize:50,
								fields:["idx","category","region","title","price","hit","is_open","is_premium","is_regionitem","end_date"]
							});
							
							new Ext.Window({
								id:"<?php echo $id; ?>BuyItemWindow",
								title:"<?php echo $actionTitle; ?>매물등록",
								width:700,
								height:400,
								modal:true,
								layout:"fit",
								tbar:[
									new Ext.form.NumberField({
										width:80,
										emptyText:"매물번호",
										hideTrigger:true,
										mouseWheelEnabled:false,
										checkChangeBuffer:500,
										listeners:{change:{fn:function(form) {
											ItemStore.getProxy().setExtraParam("idx",form.getValue());
											ItemStore.reload();
										}}}
									}),
									'-',
									new Ext.form.ComboBox({
										typeAhead:true,
										triggerAction:"all",
										store:new Ext.data.JsonStore({
											proxy:{
												type:"ajax",
												simpleSortMode:true,
												url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php",
												reader:{type:"json",root:"lists",totalProperty:"totalCount"},
												extraParams:{"action":"category","is_all":"true"}
											},
											sorters:[{property:"sort",direction:"ASC"}],
											autoLoad:true,
											fields:["idx","title",{name:"sort",type:"int"}]
										}),
										width:90,
										editable:false,
										displayField:"title",
										valueField:"idx",
										emptyText:"1차카테고리",
										listeners:{
											select:{fn:function(form,selected) {
												Ext.getCmp("<?php echo $id; ?>BuyItemPanel").getStore().getProxy().setExtraParam("category1",form.getValue());
												Ext.getCmp("<?php echo $id; ?>BuyItemPanel").getStore().reload();
											}}
										}
									}),
									new Ext.form.ComboBox({
										typeAhead:true,
										triggerAction:"all",
										store:new Ext.data.JsonStore({
											proxy:{
												type:"ajax",
												simpleSortMode:true,
												url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php",
												reader:{type:"json",root:"lists",totalProperty:"totalCount"},
												extraParams:{"action":"region","is_all":"true"}
											},
											sorters:[{property:"sort",direction:"ASC"}],
											autoLoad:true,
											fields:["idx","title",{name:"sort",type:"int"}]
										}),
										width:90,
										editable:false,
										displayField:"title",
										valueField:"idx",
										emptyText:"1차지역",
										listeners:{
											select:{fn:function(form,selected) {
												Ext.getCmp("<?php echo $id; ?>BuyItemPanel").getStore().getProxy().setExtraParam("region1",form.getValue());
												Ext.getCmp("<?php echo $id; ?>BuyItemPanel").getStore().reload();
											}}
										}
									}),
									new Ext.form.TextField({
										id:"<?php echo $id; ?>Keyword",
										width:150,
										emptyText:"검색어 입력"
									}),
									new Ext.Button({
										text:"검색",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_magnifier.png",
										handler:function() {
											Ext.getCmp("<?php echo $id; ?>BuyItemPanel").getStore().getProxy().setExtraParam("keyword",Ext.getCmp("<?php echo $id; ?>Keyword").getValue());
											Ext.getCmp("<?php echo $id; ?>BuyItemPanel").getStore().reload();
										}
									})
								],
								items:[
									new Ext.grid.GridPanel({
										id:"<?php echo $id; ?>BuyItemPanel",
										border:false,
										autoScroll:true,
										columns:[
											new Ext.grid.RowNumberer({
												header:"구매번호",
												dataIndex:"idx",
												align:"left",
												width:60,
												renderer:function(value,p,record) {
													p.tdCls = Ext.baseCSSPrefix + 'grid-cell-special';
													return GridNumberFormat(value);
												}
											}),{
												header:"상태",
												width:80,
												renderer:function(value,p,record) {
													var sHTML = '';
													if (record.data.is_open == "TRUE") {
														if (record.data.is_premium == "TRUE") sHTML+= '<span style="color:red;">프리미엄</span>';
														if (record.data.is_premium == "TRUE" && record.data.is_regionitem == "TRUE") sHTML+= '/<span style="color:blue;">지역</span>';
														if (record.data.is_premium == "FALSE" && record.data.is_regionitem == "TRUE") sHTML+= '<span style="color:blue;">지역추천</span>';
														if (!sHTML) sHTML+= '일반(공개중)';
													} else {
														sHTML+= '대기(비공개)';
													}
													return sHTML;
												}
											},{
												header:"매물명",
												dataIndex:"title",
												minWidth:250,
												flex:1,
												renderer:function(value,p,record) {
													return '<span class="blue">['+record.data.category+']</span> '+value;
												}
											},{
												header:"지역",
												dataIndex:"region",
												width:100
											},{
												header:"조회",
												dataIndex:"hit",
												width:40,
												renderer:GridNumberFormat
											},{
												header:"만료일",
												dataIndex:"end_date",
												width:70,
												renderer:function(value,p,record) {
													if (record.data.is_open == "TRUE" && value == 0) {
														return '<div style="text-align:center; color:blue;">무제한</div>';
													} else if (value == 0) {
														return '<div style="text-align:center; color:red;">공개중아님</div>';
													} else {
														return '<div style="font-family:tahoma;">'+value+'</div>';
													}
												}
											}
										],
										sortableColumns:false,
										columnLines:true,
										store:ItemStore,
										selModel:new Ext.selection.CheckboxModel({injectCheckbox:"last"}),
										bbar:new Ext.PagingToolbar({
											store:ItemStore,
											displayInfo:true
										})
									})
								],
								buttons:[
									new Ext.toolbar.TextItem({
										id:"<?php echo $id; ?>BuyInfo",
										price:0,
										text:"등록비 : 계산중... / 나의 포인트 : 계산중... / 구매가능수 : 계산중...",
										listeners:{render:{fn:function(button) {
											Ext.Ajax.request({
												url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php",
												success:function(response) {
													var data = Ext.JSON.decode(response.responseText);
													var text = "등록비 : ";
													text+= data.price == "0" ? "무료" : GetNumberFormat(data.price)+"포인트";
													text+= " / 나의 포인트 : "+GetNumberFormat(data.point)+"포인트";
													text+= " / 구매가능수 : ";
													text+= data.limit == "-1" ? "무제한" : GetNumberFormat(data.limit)+"개";
													button.price = parseInt(data.price);
													button.setText(text);
												},
												failure:function() {
												},
												headers:{},
												params:{"action":"<?php echo $actionTarget; ?>","get":"pointinfo"}
											});
										}}}
									}),
									'->',
									new Ext.Button({
										text:"<?php echo $actionTitle; ?>매물등록",
										handler:function() {
											var checked = Ext.getCmp("<?php echo $id; ?>BuyItemPanel").getSelectionModel().getSelection();
											if (checked.length == 0) {
												Ext.Msg.show({title:"에러",msg:"<?php echo $actionTitle; ?>매물에 등록할 매물을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
											} else {
												var idxs = new Array();
												for (var i=0, loop=checked.length;i<loop;i++) {
													idxs.push(checked[i].get("idx"));
												}
												
												Ext.Msg.show({title:"확인",msg:"<?php echo $actionTitle; ?>매물로 등록시 <?php echo $mOneroom->GetConfig($actionTarget.'_time'); ?>일 만큼 노출되며, 매물만료일이 그 기간이전일 경우, 자동으로 연장됩니다.<br />이미 <?php echo $actionTitle; ?>에 등록된매물을 재등록할 경우, 노출기간이 합산됩니다.<br />"+checked.length+"개의 매물을 "+(Ext.getCmp("<?php echo $id; ?>BuyInfo").price == 0 ? "무료" : GetNumberFormat(Ext.getCmp("<?php echo $id; ?>BuyInfo").price * checked.length)+"포인트")+"에 등록하시겠습니까?<br />구매하는 즉시 적용됩니다.",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
													if (button == "yes") {
														Ext.Msg.wait("선택한 매물을 <?php echo $actionTitle; ?>매물에 등록중입니다.","잠시만 기다려주십시오.");
														Ext.Ajax.request({
															url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.do.php",
															success:function(response) {
																var data = Ext.JSON.decode(response.responseText);
																if (data.success == true) {
																	Ext.Msg.show({title:"안내",msg:"성공적으로 등록하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
																		Ext.getCmp("<?php echo $id; ?>ListPanel").getStore().reload();
																		Ext.getCmp("<?php echo $id; ?>BuyItemWindow").close();
																	}});
																} else {
																	Ext.Msg.show({title:"안내",msg:data.message,buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
																}
															},
															failure:function() {
																Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
															},
															params:{"action":"<?php echo $actionTarget; ?>","do":"buy","idx":idxs.join(",")}
														});
													}
												}});
											}
										}
									}),
									new Ext.Button({
										text:"취소",
										handler:function() {
											Ext.getCmp("<?php echo $id; ?>BuyItemWindow").close();
										}
									})
								]
							}).show();
						}
					}),
					'-',
					new Ext.Button({
						text:"선택한 <?php echo $actionTitle; ?>매물을 <?php echo $actionTitle; ?>매물에서 삭제",
						icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_building_delete.png",
						handler:function() {
							var checked = Ext.getCmp("<?php echo $id; ?>ListPanel").getSelectionModel().getSelection();
							if (checked.length != 1) {
								Ext.Msg.show({title:"에러",msg:"<?php echo $actionTitle; ?>매물을 삭제할 매물을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								return;
							}
							
							var idxs = new Array();
							for (var i=0, loop=checked.length;i<loop;i++) {
								idxs.push(checked[i].get("idx"));
							}
							
							Ext.Msg.show({title:"확인",msg:"선택한 <?php echo $actionTitle; ?>매물을 <?php echo $actionTitle; ?>에서 삭제하시겠습니까?<br />기간이 남아있는경우라도 포인트는 환불되지 않습니다.",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
								if (button == "yes") {
									Ext.Msg.wait("선택한 <?php echo $actionTitle; ?>매물을 <?php echo $actionTitle; ?>에서 삭제중입니다.","잠시만 기다려주십시오.");
									Ext.Ajax.request({
										url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.do.php",
										success:function(response) {
											var data = Ext.JSON.decode(response.responseText);
											if (data.success == true) {
												Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
													Ext.getCmp("<?php echo $id; ?>ListPanel").getStore().reload();
												}});
											} else {
												Ext.Msg.show({title:"안내",msg:data.message,buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
											}
										},
										failure:function() {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
										},
										params:{"action":"<?php echo $actionTarget; ?>","do":"sale","idx":checked[0].get("idx")}
									});
								}
							}});
						}
					}),
					'->',
					{xtype:"tbtext",text:"목록마우스우클릭 : 상세메뉴 / 목록더블클릭 : 할당매물보기"}
				],
				items:[
					new Ext.grid.GridPanel({
						id:"<?php echo $id; ?>ListPanel",
						layout:"fit",
						border:false,
						autoScroll:true,
						columns:[
							new Ext.grid.RowNumberer({
								header:"번호",
								dataIndex:"idx",
								sortable:true,
								align:"left",
								width:60,
								renderer:function(value,p,record) {
									p.tdCls = Ext.baseCSSPrefix + 'grid-cell-special';
									return GridNumberFormat(value);
								}
							}),
							{
								header:"상태",
								dataIndex:"status",
								sortable:false,
								width:70,
								renderer:function(value,p,record) {
									if (value == "ACTIVE") {
										return '<span style="color:blue;">활성화</span>';
									} else {
										return '<span style="color:#666666;">기간종료</span>';
									}
								}
							},{
								header:"시작일",
								dataIndex:"start_time",
								width:130
							},{
								header:"종료일",
								dataIndex:"end_time",
								minWidth:130
							},{
								header:"할당된 매물명",
								dataIndex:"title",
								sortable:false,
								minWidth:140,
								flex:1,
								renderer:function(value,p,record) {
									if (record.data.ino == "0") return '<span style="color:#666666;">할당된 매물이 없습니다.</span>';
									else return '<span style="color:#EF5600;">[#'+record.data.ino+']</span> '+value;
								}
							},{
								header:"조회",
								dataIndex:"hit",
								width:40,
								renderer:GridNumberFormat
							}
						],
						columnLines:true,
						store:store,
						selModel:new Ext.selection.CheckboxModel({injectCheckbox:"last"}),
						bbar:new Ext.PagingToolbar({
							store:store,
							displayInfo:true
						}),
						listeners:{
							itemcontextmenu:{fn:function(grid,record,row,index,e) {
								grid.getSelectionModel().select(index);
								var menu = new Ext.menu.Menu();
								
								menu.add('<b class="menu-title">구매번호 #'+record.data.idx+'</b>');
								
								if (record.data.ino != "0") {
									menu.add({
										text:"<?php echo $actionTitle; ?>매물삭제",
										handler:function() {
											Ext.Msg.show({title:"확인",msg:"선택한 <?php echo $actionTitle; ?>매물을 <?php echo $actionTitle; ?>에서 삭제하시겠습니까?<br />기간이 남아있는경우라도 포인트는 환불되지 않습니다.",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
												if (button == "yes") {
													Ext.Msg.wait("선택한 <?php echo $actionTitle; ?>매물을 <?php echo $actionTitle; ?>에서 삭제중입니다.","잠시만 기다려주십시오.");
													Ext.Ajax.request({
														url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.do.php",
														success:function(response) {
															var data = Ext.JSON.decode(response.responseText);
															if (data.success == true) {
																Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
																	Ext.getCmp("<?php echo $id; ?>ListPanel").getStore().reload();
																}});
															} else {
																Ext.Msg.show({title:"안내",msg:data.message,buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
															}
														},
														failure:function() {
															Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
														},
														params:{"action":"<?php echo $actionTarget; ?>","do":"sale","idx":record.data.idx}
													});
												}
											}});
										}
									});
									
									menu.add('-');
									
									menu.add({
										text:"할당된 매물정보수정",
										handler:function() {
											ItemForm(record.data.ino,Ext.getCmp("<?php echo $id; ?>ListPanel"));
										}
									});
								}
								
								e.stopEvent();
								menu.showAt(e.getXY());
							}},
							itemdblclick:{fn:function(grid,record) {
								if (record.data.ino != "0") {
									new Ext.Window({
										title:record.data.title,
										width:800,
										height:500,
										modal:true,
										resizable:false,
										html:'<iframe src="./preview.php?idx='+record.data.ino+'" style="width:100%; height:100%;" frameborder="0"></iframe>'
									}).show();
								} else {
									Ext.Msg.show({title:"에러",msg:"할당할 매물이 없습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								}
							}}
						}
					})
				],
				listeners:{show:{fn:function() {
					store.load();
				}}}
			}).show();
		}
	}
});

ManagerModules.push(new MyDesktop.<?php echo $id; ?>());
ManagerShortcuts.push({name:"<?php echo $title; ?>",icon:"./images/<?php echo $id; ?>48.png",module:"<?php echo $id; ?>"});
</script>