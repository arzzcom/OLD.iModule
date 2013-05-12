<?php $id = 'RegionItem'; $title = '지역추천 매물관리'; $actionTarget = 'regionitem'; $actionTitle = '지역추천'; ?>
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
				extraParams:{action:"<?php echo $actionTarget; ?>",get:"myslot",type:"<?php echo strtoupper($actionTarget); ?>"}
			},
			pageSize:50,
			fields:["idx","status","type","ino","title","start_time","end_time"]
		});
		
		var <?php echo $id; ?>InsertSlot = function() {
			var checked = Ext.getCmp("<?php echo $id; ?>ListPanel").getSelectionModel().getSelection();
			if (checked.length != 1) {
				Ext.Msg.show({title:"에러",msg:"매물을 할당할 슬롯을 1개만 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
				return;
			}
			
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
				id:"<?php echo $id; ?>SlotLinkWindow",
				title:"슬롯할당",
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
								Ext.getCmp("<?php echo $id; ?>SlotLinkPanel").getStore().getProxy().setExtraParam("category1",form.getValue());
								Ext.getCmp("<?php echo $id; ?>SlotLinkPanel").getStore().reload();
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
								Ext.getCmp("<?php echo $id; ?>SlotLinkPanel").getStore().getProxy().setExtraParam("region1",form.getValue());
								Ext.getCmp("<?php echo $id; ?>SlotLinkPanel").getStore().reload();
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
							Ext.getCmp("<?php echo $id; ?>SlotLinkPanel").getStore().getProxy().setExtraParam("keyword",Ext.getCmp("<?php echo $id; ?>Keyword").getValue());
							Ext.getCmp("<?php echo $id; ?>SlotLinkPanel").getStore().reload();
						}
					})
				],
				items:[
					new Ext.grid.GridPanel({
						id:"<?php echo $id; ?>SlotLinkPanel",
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
								sortable:true,
								minWidth:250,
								flex:1,
								renderer:function(value,p,record) {
									return '<span class="blue">['+record.data.category+']</span> '+value;
								}
							},{
								header:"지역",
								dataIndex:"region",
								sortable:false,
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
						columnLines:true,
						store:ItemStore,
						selModel:new Ext.selection.CheckboxModel({injectCheckbox:"last",mode:"SINGLE"}),
						bbar:new Ext.PagingToolbar({
							store:ItemStore,
							displayInfo:true
						})
					})
				],
				buttons:[
					new Ext.Button({
						text:"슬롯할당",
						handler:function() {
							var checked = Ext.getCmp("<?php echo $id; ?>SlotLinkPanel").getSelectionModel().getSelection();
							if (checked.length == 0) {
								Ext.Msg.show({title:"에러",msg:"할당할 매물을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
							} else {
								var slot = Ext.getCmp("<?php echo $id; ?>ListPanel").getSelectionModel().getSelection()[0];
								Ext.Msg.show({title:"확인",msg:checked[0].get("title")+"매물을 <?php echo $actionTitle; ?>슬롯에 할당하시겠습니까?<br />슬롯에 할당하는 즉시 적용됩니다.",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
									if (button == "yes") {
										Ext.Msg.wait("선택한 매물을 <?php echo $actionTitle; ?>슬롯에 할당중입니다.","잠시만 기다려주십시오.");
										Ext.Ajax.request({
											url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.do.php",
											success:function(response) {
												var data = Ext.JSON.decode(response.responseText);
												if (data.success == true) {
													Ext.Msg.show({title:"안내",msg:"성공적으로 할당하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
														Ext.getCmp("<?php echo $id; ?>ListPanel").getStore().reload();
														Ext.getCmp("<?php echo $id; ?>SlotLinkWindow").close();
													}});
												} else {
													Ext.Msg.show({title:"안내",msg:data.message,buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
												}
											},
											failure:function() {
												Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
											},
											params:{"action":"<?php echo $actionTarget; ?>","do":"slot_link","idx":checked[0].get("idx"),"slot":slot.get("idx")}
										});
									}
								}});
							}
						}
					}),
					new Ext.Button({
						text:"취소",
						handler:function() {
							Ext.getCmp("<?php echo $id; ?>SlotLinkWindow").close();
						}
					})
				]
			}).show();
		}
		
		var <?php echo $id; ?>RemoveSlot = function() {
			var checked = Ext.getCmp("<?php echo $id; ?>ListPanel").getSelectionModel().getSelection();
			if (checked.length == 0) {
				Ext.Msg.show({title:"에러",msg:"매물을 할당해제할 슬롯을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
				return;
			}
			
			var idxs = new Array();
			for (var i=0, loop=checked.length;i<loop;i++) {
				idxs.push(checked[i].get("idx"));
			}
			
			Ext.Msg.show({title:"확인",msg:"선택슬롯에 할당된 매물을 해제하겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
				if (button == "yes") {
					Ext.Msg.wait("선택한 슬롯을 비우고 있습니다.","잠시만 기다려주십시오.");
					Ext.Ajax.request({
						url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.do.php",
						success:function(response) {
							var data = Ext.JSON.decode(response.responseText);
							if (data.success == true) {
								Ext.Msg.show({title:"안내",msg:"성공적으로 해제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
									Ext.getCmp("<?php echo $id; ?>ListPanel").getStore().reload();
								}});
							} else {
								Ext.Msg.show({title:"안내",msg:data.message,buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
							}
						},
						failure:function() {
							Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
						},
						params:{"action":"<?php echo $actionTarget; ?>","do":"slot_unlink","idx":idxs.join(",")}
					});
				}
			}});
		}
		
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
						text:"슬롯구매",
						icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_coins.png",
						handler:function() {
							new Ext.Window({
								id:"<?php echo $id; ?>SlotWindow",
								title:"슬롯구매",
								width:500,
								height:300,
								layout:"fit",
								items:[
									new Ext.grid.GridPanel({
										id:"<?php echo $id; ?>SlotList",
										border:false,
										columns:[
											{
												header:"상품명",
												dataIndex:"term",
												flex:1,
												renderer:function(value) {
													return '<?php echo $actionTitle; ?>슬롯 ('+value+'일)';
												}
											},{
												header:"구매가격",
												dataIndex:"price",
												width:120,
												renderer:function(value) {
													return '<div style="text-align:right;">'+GetNumberFormat(value)+'포인트</div>';
												}
											}
										],
										store:new Ext.data.JsonStore({
											proxy:{
												type:"ajax",
												simpleSortMode:false,
												url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php",
												reader:{type:"json",root:"lists",totalProperty:"totalCount",point:"point"},
												extraParams:{action:"<?php echo $actionTarget; ?>",get:"slot",type:"<?php echo strtoupper($actionTarget); ?>"}
											},
											remoteSort:false,
											sorters:[{property:"price",direction:"ASC"}],
											autoLoad:true,
											pageSize:50,
											fields:["idx","type","term",{name:"price",type:"int"}]
										}),
										columnLines:true,
										selModel:new Ext.selection.CheckboxModel({mode:"SINGLE"})
									})
								],
								buttons:[
									new Ext.Toolbar.TextItem({
										id:"<?php echo $id; ?>MyPoint",
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
									}),
									'->',
									new Ext.Button({
										text:"선택상품구매",
										handler:function() {
											var checked = Ext.getCmp("<?php echo $id; ?>SlotList").getSelectionModel().getSelection();
											if (checked.length == 0) {
												Ext.Msg.show({title:"에러",msg:"구매할 상품을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
											} else {
												Ext.Msg.show({title:"확인",msg:"<?php echo $actionTitle; ?>슬롯 ("+checked[0].get("term")+"일) 상품을 "+GetNumberFormat(checked[0].get("price"))+"포인트에 구매하시겠습니까?<br />선택하신 상품은 구매 즉시 적용됩니다.",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
													if (button == "yes") {
														Ext.Msg.wait("선택한 상품을 구매중입니다.","잠시만 기다려주십시오.");
														Ext.Ajax.request({
															url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.do.php",
															success:function(response) {
																var data = Ext.JSON.decode(response.responseText);
																if (data.success == true) {
																	Ext.Msg.show({title:"안내",msg:"성공적으로 구매하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
																		Ext.getCmp("<?php echo $id; ?>ListPanel").getStore().reload();
																		Ext.getCmp("<?php echo $id; ?>SlotWindow").close();
																	}});
																} else {
																	Ext.Msg.show({title:"안내",msg:data.message,buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
																}
															},
															failure:function() {
																Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
															},
															params:{"action":"<?php echo $actionTarget; ?>","do":"slot_buy","idx":checked[0].get("idx")}
														});
													}
												}});
											}
										}
									}),
									new Ext.Button({
										text:"취소",
										handler:function() {
											Ext.getCmp("<?php echo $id; ?>SlotWindow").close();
										}
									})
								]
							}).show();
						}
					}),
					'-',
					new Ext.Button({
						text:"슬롯할당",
						icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_link.png",
						handler:function() {
							<?php echo $id; ?>InsertSlot();
						}
					}),
					new Ext.Button({
						text:"슬롯해제",
						icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_link_break.png",
						handler:function() {
							<?php echo $id; ?>RemoveSlot();
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
								header:"슬롯번호",
								dataIndex:"idx",
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
								width:70,
								renderer:function(value,p,record) {
									if (value == "ACTIVE") {
										if (record.data.ino == "0") return '<span style="color:red;">빈슬롯</span>';
										else return '<span style="color:blue;">활성화</span>';
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
						sortableColumns:false,
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
								
								menu.add('<b class="menu-title">슬롯번호 #'+record.data.idx+'</b>');
								
								menu.add({
									text:"슬롯할당",
									handler:function() {
										<?php echo $id; ?>InsertSlot();
									}
								});
								
								if (record.data.ino != "0") {
									menu.add({
										text:"슬롯해제",
										handler:function() {
											<?php echo $id; ?>RemoveSlot();
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