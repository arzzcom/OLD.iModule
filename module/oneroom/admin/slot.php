<script type="text/javascript">
var ContentArea = function(viewport) {
	this.viewport = viewport;

	var ItemContextMenu = function(grid,record,row,index,e) {
		grid.getSelectionModel().select(index);
		var menu = new Ext.menu.Menu();
		
		var title = grid.getStore().getProxy().extraParams.get == "PREMIUM" ? "프리미엄매물" : "지역추천매물";
		title+= " "+record.data.term+"일 이용권";
		
		menu.add('<b class="menu-title">'+title+'</b>');
		
		menu.add({
			text:"슬롯아이템삭제",
			handler:function() {
				Ext.Msg.show({title:"확인",msg:"해당 슬롯아이템을 삭제하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
					if (button == "yes") {
						Ext.Msg.wait("선택한 작업을 처리하고 있습니다.","잠시만 기다려주십시오.");
						Ext.Ajax.request({
							url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php",
							success:function(response) {
								var data = Ext.JSON.decode(response.responseText);
								if (data.success == true) {
									Ext.Msg.show({title:"안내",msg:"성공적으로 처리하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
										Ext.getCmp("ListTab").getActiveTab().getStore().remove(record);
										Ext.getCmp("ListTab").getActiveTab().getStore().sort("term","ASC");
									}});
								} else {
									Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
								}
							},
							failure:function() {
								Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
							},
							params:{"action":"slot","do":"delete","idx":record.data.idx}
						});
					}
				}});
			}
		});
		
		e.stopEvent();
		menu.showAt(e.getXY());
	}

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"슬롯아이템관리",
		layout:"fit",
		margin:"0 5 0 0",
		items:[
			new Ext.TabPanel({
				id:"ListTab",
				tabPosition:"bottom",
				activeTab:0,
				border:false,
				tbar:[
					new Ext.Button({
						text:"슬롯아이템추가",
						icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_tag_blue_add.png",
						handler:function() {
							new Ext.Window({
								id:"SlotAddWindow",
								title:"슬롯아이템추가",
								modal:true,
								width:400,
								items:[
									new Ext.form.FormPanel({
										id:"SlotAddForm",
										border:false,
										bodyPadding:"5 5 0 5",
										fieldDefaults:{labelAlign:"right",labelWidth:85,anchor:"100%",allowBlank:false},
										items:[
											new Ext.form.FieldContainer({
												fieldLabel:"기간",
												layout:"hbox",
												items:[
													new Ext.form.NumberField({
														name:"term",
														width:80,
														minValue:1
													}),
													new Ext.form.DisplayField({
														value:"&nbsp;일"
													})
												]
											}),
											new Ext.form.FieldContainer({
												fieldLabel:"구매가격",
												layout:"hbox",
												items:[
													new Ext.form.NumberField({
														name:"price",
														width:120,
														minValue:1
													}),
													new Ext.form.DisplayField({
														value:"&nbsp;포인트"
													})
												]
											})
										]
									})
								],
								buttons:[
									new Ext.Button({
										text:"확인",
										handler:function() {
											Ext.getCmp("SlotAddForm").getForm().submit({
												url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php?action=slot&do=add&type="+Ext.getCmp("ListTab").getActiveTab().getStore().getProxy().extraParams.get,
												submitEmptyText:false,
												waitTitle:"잠시만 기다려주십시오.",
												waitMsg:"슬롯아이템을 추가하고 있습니다.",
												success:function(form,action) {
													console.log(action);
													Ext.Msg.show({title:"확인",msg:"성공적으로 추가하였습니다.<br />계속해서 추가하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
														Ext.getCmp("ListTab").getActiveTab().getStore().add({idx:action.result.idx,term:action.result.term,price:action.result.price});
														Ext.getCmp("ListTab").getActiveTab().getStore().sort("term","ASC");
														if (button == "yes") {
															Ext.getCmp("UniversityAddWindow").getForm().reset();
															Ext.getCmp("UniversityAddForm").getForm().findField("term").focus(true,100);
														} else {
															Ext.getCmp("UniversityAddWindow").close();
														}
													}});
												},
												failure:function(form,action) {
													if (action.result) {
														if (action.result.errors.title) {
															Ext.Msg.show({title:"에러",msg:action.result.errors.title,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
															return false;
														}
													}
													Ext.Msg.show({title:"에러",msg:"입력내용에 오류가 있습니다.<br />입력내용을 다시 한번 확인하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
												}
											});
										}
									}),
									new Ext.Button({
										text:"취소",
										handler:function() {
											Ext.getCmp("SlotAddWindow").close();
										}
									})
								]
							}).show();
						}
					}),
					'-',
					new Ext.Button({
						text:"변경사항저장",
						icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_disk.png",
						handler:function() {
							var update = Ext.getCmp("ListTab").getActiveTab().getStore().getUpdatedRecords();
		
							if (update.length > 0) {
								var data = new Array();
								for (var i=0, loop=update.length;i<loop;i++) {
									data.push(update[i].data);
								}
								data = Ext.JSON.encode(data);
								
								Ext.Msg.wait("변경사항을 저장하고 있습니다.","잠시만 기다려주십시오.");
								Ext.Ajax.request({
									url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php",
									success:function(response) {
										var data = Ext.JSON.decode(response.responseText);
										if (data.success == true) {
											Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
											Ext.getCmp("ListTab").getActiveTab().getStore().commitChanges();
										} else {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
										}
									},
									failure:function() {
										Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
									},
									params:{"action":"slot","do":"modify","data":data}
								});
							} else {
								Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
							}
						}
					}),
					'-',
					new Ext.Button({
						text:"선택한 슬롯아이템을&nbsp;",
						icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_tick.png",
						menu:new Ext.menu.Menu({
							items:[{
								text:"선택슬롯아이템 삭제",
								handler:function() {
									var checked = Ext.getCmp("ListTab").getActiveTab().getSelectionModel().getSelection();
									if (checked.length == 0) {
										Ext.Msg.show({title:"에러",msg:"먼저 목록에서 슬롯아이템을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										return false;
									}
									
									var idxs = new Array();
									for (var i=0, loop=checked.length;i<loop;i++) {
										idxs[i] = checked[i].get("idx");
									}
									
									Ext.Msg.show({title:"확인",msg:"해당 슬롯아이템을 삭제하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
										if (button == "yes") {
											Ext.Msg.wait("선택한 작업을 처리하고 있습니다.","잠시만 기다려주십시오.");
											Ext.Ajax.request({
												url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php",
												success:function(response) {
													var data = Ext.JSON.decode(response.responseText);
													if (data.success == true) {
														Ext.Msg.show({title:"안내",msg:"성공적으로 처리하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
															Ext.getCmp("ListTab").getActiveTab().getStore().remove(checked);
															Ext.getCmp("ListTab").getActiveTab().getStore().sort("term","ASC");
														}});
													} else {
														Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
													}
												},
												failure:function() {
													Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
												},
												params:{"action":"slot","do":"delete","idx":idxs.join(",")}
											});
										}
									}});
								}
							}]
						})
					}),
					'->',
					{xtype:"tbtext",text:"목록마우스우클릭 : 상세메뉴 / 목록더블클릭 : 수정"}
				],
				items:[
					new Ext.grid.GridPanel({
						id:"ListPanel1",
						title:"프리미엄슬롯아이템",
						layout:"fit",
						border:false,
						autoScroll:true,
						columns:[
							new Ext.grid.RowNumberer(),
							{
								header:"슬롯명",
								minWidth:80,
								flex:1,
								renderer:function(value,p,record) {
									return "프리미엄매물 "+record.data.term+"일 이용권";
								}
							},{
								header:"기간",
								dataIndex:"term",
								width:80,
								renderer:function(value,p,record) {
									return '<div style="text-align:right;">'+GetNumberFormat(value)+'일</div>';
								},
								editor:new Ext.form.NumberField({selectOnFocus:true,allowBlank:false})
							},{
								header:"가격",
								dataIndex:"price",
								width:120,
								renderer:function(value,p,record) {
									return '<div style="text-align:right;">'+GetNumberFormat(value)+'포인트</div>';
								},
								editor:new Ext.form.NumberField({selectOnFocus:true,allowBlank:false})
							}
						],
						columnLines:true,
						sortableColumns:false,
						store:new Ext.data.JsonStore({
							proxy:{
								type:"ajax",
								simpleSortMode:true,
								url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php",
								reader:{type:"json",root:"lists",totalProperty:"totalCount"},
								extraParams:{action:"slot",get:"PREMIUM"}
							},
							remoteSort:false,
							sorters:[{property:"term",direction:"ASC"}],
							autoLoad:true,
							fields:["idx",{name:"term",type:"int"},{name:"price",type:"int"}]
						}),
						selModel:new Ext.ux.selection.CheckboxModel({checkOnly:true,injectCheckbox:"last"}),
						plugins:[new Ext.grid.plugin.CellEditing({clicksToEdit:2})],
						listeners:{
							itemcontextmenu:ItemContextMenu
						}
					}),
					new Ext.grid.GridPanel({
						id:"ListPanel2",
						title:"지역추천슬롯아이템",
						layout:"fit",
						border:false,
						autoScroll:true,
						columns:[
							new Ext.grid.RowNumberer(),
							{
								header:"슬롯명",
								minWidth:80,
								flex:1,
								renderer:function(value,p,record) {
									return "지역추천매물 "+record.data.term+"일 이용권";
								}
							},{
								header:"기간",
								dataIndex:"term",
								width:80,
								renderer:function(value,p,record) {
									return '<div style="text-align:right;">'+GetNumberFormat(value)+'일</div>';
								},
								editor:new Ext.form.NumberField({selectOnFocus:true,allowBlank:false})
							},{
								header:"가격",
								dataIndex:"price",
								width:120,
								renderer:function(value,p,record) {
									return '<div style="text-align:right;">'+GetNumberFormat(value)+'포인트</div>';
								},
								editor:new Ext.form.NumberField({selectOnFocus:true,allowBlank:false})
							}
						],
						columnLines:true,
						sortableColumns:false,
						store:new Ext.data.JsonStore({
							proxy:{
								type:"ajax",
								simpleSortMode:true,
								url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php",
								reader:{type:"json",root:"lists",totalProperty:"totalCount"},
								extraParams:{action:"slot",get:"REGIONITEM"}
							},
							remoteSort:false,
							sorters:[{property:"term",direction:"ASC"}],
							autoLoad:true,
							fields:["idx",{name:"term",type:"int"},{name:"price",type:"int"}]
						}),
						selModel:new Ext.ux.selection.CheckboxModel({checkOnly:true,injectCheckbox:"last"}),
						plugins:[new Ext.grid.plugin.CellEditing({clicksToEdit:2})],
						listeners:{
							itemcontextmenu:ItemContextMenu
						}
					})
				]
			})
		]
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>