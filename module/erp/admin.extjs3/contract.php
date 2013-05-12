<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	var ContractStore = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:[{name:"idx",type:"int"},"title",{name:"totalarea",type:"float"},{name:"price",type:"int"}]
		}),
		remoteSort:true,
		sortInfo:{field:"title",direction:"ASC"},
		baseParams:{"action":"contract","get":"list"}
	});

	function ContractItemAddFunction(idx,group,worktype) {
		var ItemStore = new Ext.data.Store({
			proxy:new Ext.data.ScriptTagProxy({url:"/module/erp/exec/Admin.get.php"}),
			reader:new Ext.data.JsonReader({
				root:'lists',
				totalProperty:'totalCount',
				fields:[{name:"idx",type:"int"},"group","worktype","title","size","unit","cost1","cost2","cost3"]
			}),
			remoteSort:true,
			sortInfo:{field:"title",direction:"ASC"},
			baseParams:{"action":"item","get":"list","keyword":"","group":"","worktype":""}
		});

		if (group && worktype) {
			var TopBar = [
				new Ext.form.TextField({
					id:"WorkspaceCostKeyword",
					width:120,
					emptyText:"검색어를 입력하세요"
				}),
				' ',
				new Ext.Button({
					text:"검색",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/admin/icon_magnifier.png",
					handler:function() {
						ItemStore.baseParams.keyword = Ext.getCmp("WorkspaceCostKeyword").getValue();
						ItemStore.load({params:{start:0,limit:30}});
					}
				})
			];
			ItemStore.baseParams.group = group;
			ItemStore.baseParams.worktype = worktype;
		} else {
			var TopBar = [
				new Ext.form.ComboBox({
					id:"WorkspaceCostGroup",
					typeAhead:true,
					triggerAction:"all",
					lazyRender:true,
					store:new Ext.data.Store({
						proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
						reader:new Ext.data.JsonReader({
							root:"lists",
							totalProperty:"totalCount",
							fields:["group","value"]
						}),
						remoteSort:false,
						sortInfo:{field:"value",direction:"ASC"},
						baseParams:{"action":"item","get":"group"},
					}),
					width:100,
					editable:false,
					mode:"local",
					displayField:"group",
					valueField:"value",
					listeners:{render:{fn:function() {
						Ext.getCmp("WorkspaceCostGroup").getStore().load();
						Ext.getCmp("WorkspaceCostGroup").getStore().on("load",function(store) {
							Ext.getCmp("WorkspaceCostGroup").setValue(store.getAt(0).get("value"));
							Ext.getCmp("WorkspaceCostWorkType").getStore().baseParams.group = store.getAt(0).get("value");
							Ext.getCmp("WorkspaceCostWorkType").getStore().load();
						});
					}}}
				}),
				' ',
				new Ext.form.ComboBox({
					id:"WorkspaceCostWorkType",
					typeAhead:true,
					triggerAction:"all",
					lazyRender:true,
					store:new Ext.data.Store({
						proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
						reader:new Ext.data.JsonReader({
							root:"lists",
							totalProperty:"totalCount",
							fields:["worktype","value"]
						}),
						remoteSort:false,
						sortInfo:{field:"value",direction:"ASC"},
						baseParams:{"action":"item","get":"worktype","group":""},
					}),
					width:100,
					editable:false,
					mode:"local",
					displayField:"worktype",
					valueField:"value",
					emptyText:"공종명"
				}),
				' ',
				new Ext.form.TextField({
					id:"WorkspaceCostKeyword",
					width:120,
					emptyText:"검색어를 입력하세요"
				}),
				' ',
				new Ext.Button({
					text:"검색",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/admin/icon_magnifier.png",
					handler:function() {
						ItemStore.baseParams.keyword = Ext.getCmp("WorkspaceCostKeyword").getValue();
						ItemStore.baseParams.group = Ext.getCmp("WorkspaceCostGroup").getValue();
						ItemStore.baseParams.worktype = Ext.getCmp("WorkspaceCostWorkType").getValue();
						ItemStore.load({params:{start:0,limit:30}});
					}
				})
			];
		}

		ItemStore.load({params:{start:0,limit:30}});

		new Ext.Window({
			id:"ContractItemAddWindow",
			title:"추가하기",
			width:900,
			height:520,
			modal:true,
			maximizable:true,
			layout:"fit",
			items:[
				new Ext.Panel({
					border:false,
					layout:"border",
					items:[
						new Ext.grid.GridPanel({
							id:"WorkspaceItemAddItemList",
							title:"품명DB검색",
							region:"north",
							height:200,
							layout:"fit",
							margins:"5 5 0 5",
							split:true,
							collapsible:true,
							tbar:TopBar,
							cm:new Ext.grid.ColumnModel([
								new Ext.grid.CheckboxSelectionModel(),
								{
									header:"그룹",
									dataIndex:"group",
									width:80,
									sortable:false
								},{
									header:"공종명",
									dataIndex:"worktype",
									width:150,
									sortable:false
								},{
									header:"품명",
									dataIndex:"title",
									width:250,
									sortable:false
								},{
									header:"규격",
									dataIndex:"size",
									width:100,
									editor:new Ext.form.TextField({selectOnFocus:true})
								},{
									header:"단위",
									dataIndex:"unit",
									width:40,
									sortable:false,
									renderer:function(value) {
										return '<div style="text-align:center;">'+value+'</div>';
									},
									editor:new Ext.form.TextField({selectOnFocus:true})
								}
							]),
							sm:new Ext.grid.CheckboxSelectionModel(),
							store:ItemStore,
							bbar:new Ext.PagingToolbar({
								pageSize:30,
								store:ItemStore,
								displayInfo:true,
								displayMsg:"{0} - {1} of {2}",
								emptyMsg:"데이터없음"
							})
						}),
						new Ext.grid.EditorGridPanel({
							id:"WorkspaceItemAddInsertList",
							title:"데이터입력",
							region:"center",
							layout:"fit",
							margins:"0 5 5 5",
							tbar:[
								new Ext.Button({
									text:"선택항목추가하기",
									icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/admin/icon_arrow_down.png",
									handler:function() {
										var checked = Ext.getCmp("WorkspaceItemAddItemList").selModel.getSelections();

										if (checked.length == 0) {
											Ext.Msg.show({title:"에러",msg:"추가할 항목을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
											return false;
										}

										var record = Ext.data.Record.create([{name:"itemno",type:"int"},{name:"group",type:"string"},{name:"worktype",type:"string"},{name:"title",type:"string"},{name:"size",type:"string"},{name:"unit",type:"string"},"avgcost1","avgcost2","avgcost3"]);

										Ext.getCmp("WorkspaceItemAddInsertList").stopEditing();
										for (var i=0, loop=checked.length;i<loop;i++) {
											var data = checked[i];
											var row = new record({itemno:data.get("idx"),group:data.get("group"),worktype:data.get("worktype"),title:data.get("title"),size:data.get("size"),unit:data.get("unit"),avgcost1:data.get("cost1"),avgcost2:data.get("cost2"),avgcost3:data.get("cost3")});

											Ext.getCmp("WorkspaceItemAddInsertList").getStore().add(row);
										}
										Ext.getCmp("WorkspaceItemAddInsertList").startEditing(0,0);
									}
								}),
								new Ext.Button({
									text:"선택항목삭제하기",
									icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/admin/icon_arrow_up.png",
									handler:function() {
										var checked = Ext.getCmp("WorkspaceItemAddInsertList").selModel.getSelections();

										if (checked.length == 0) {
											Ext.Msg.show({title:"에러",msg:"삭제할 항목을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
											return false;
										}

										Ext.getCmp("WorkspaceItemAddInsertList").stopEditing();
										for (var i=0, loop=checked.length;i<loop;i++) {
											Ext.getCmp("WorkspaceItemAddInsertList").getStore().remove(checked[i]);
										}
										Ext.getCmp("WorkspaceItemAddInsertList").startEditing(0,0);
									}
								})
							],
							cm:new Ext.grid.ColumnModel([
								new Ext.grid.CheckboxSelectionModel(),
								{
									header:"그룹",
									dataIndex:"group",
									width:60,
									sortable:false
								},{
									header:"공종명",
									dataIndex:"worktype",
									width:80,
									sortable:false
								},{
									dataIndex:"itemno",
									hidden:true,
									hideable:false
								},{
									header:"품명",
									dataIndex:"title",
									width:150,
									sortable:false
								},{
									header:"규격",
									dataIndex:"size",
									width:100,
									sortable:false
								},{
									header:"단위",
									dataIndex:"unit",
									width:40,
									sortable:false
								},{
									header:"수량",
									dataIndex:"ea",
									width:40,
									sortable:false,
									renderer:function(value,p,record) {
										if (!value) record.data.ea = 0;
										return GridNumberFormat(value);
									},
									editor:new Ext.form.NumberField({selectOnFocus:true})
								},{
									header:"단가",
									dataIndex:"cost1",
									width:60,
									sortable:false,
									renderer:function(value,p,record) {
										if (!value) record.data.cost1 = 0;
										return GridNumberFormat(value);
									},
									editor:new Ext.form.NumberField({selectOnFocus:true})
								},{
									header:"금액",
									width:80,
									sortable:false,
									renderer:function(value,p,record) {
										return GridNumberFormat(record.data.ea*record.data.cost1);
									}
								},{
									header:"단가",
									dataIndex:"cost2",
									width:60,
									sortable:false,
									renderer:function(value,p,record) {
										if (!value) record.data.cost2 = 0;
										return GridNumberFormat(value);
									},
									editor:new Ext.form.NumberField({selectOnFocus:true})
								},{
									header:"금액",
									width:80,
									sortable:false,
									renderer:function(value,p,record) {
										return GridNumberFormat(record.data.ea*record.data.cost2);
									}
								},{
									header:"단가",
									dataIndex:"cost3",
									width:60,
									sortable:false,
									renderer:function(value,p,record) {
										if (!value) record.data.cost3 = 0;
										return GridNumberFormat(value);
									},
									editor:new Ext.form.NumberField({selectOnFocus:true})
								},{
									header:"금액",
									width:80,
									sortable:false,
									renderer:function(value,p,record) {
										return GridNumberFormat(record.data.ea*record.data.cost3);
									}
								},{
									header:"단가",
									dataIndex:"total_cost",
									width:80,
									sortable:false,
									renderer:function(value,p,record) {
										return GridNumberFormat(record.data.cost1+record.data.cost2+record.data.cost3);
									}
								},{
									header:"금액",
									dataIndex:"total_price",
									width:80,
									sortable:false,
									renderer:function(value,p,record) {
										return GridNumberFormat(record.data.ea*record.data.total_cost);
									}
								}
							]),
							clicksToEdit:1,
							trackMouseOver:true,
							sm:new Ext.grid.CheckboxSelectionModel(),
							store:new Ext.data.SimpleStore({
								fields:[{name:"itemno",type:"int"},"group","worktype","title","size","unit",{name:"ea",type:"int"},{name:"cost1",type:"int"},{name:"cost2",type:"int"},{name:"cost3",type:"int"},"avgcost1","avgcost2","avgcost3",{name:"total_cost",type:"int"},{name:"total_price",type:"int"}]
							}),
							plugins:[new Ext.ux.plugins.GroupHeaderGrid({
								rows:[[
									{},
									{},
									{},
									{},
									{},
									{},
									{},
									{},
									{header:"재료비",colspan:2,align:"center"},
									{header:"노무비",colspan:2,align:"center"},
									{header:"경비",colspan:2,align:"center"},
									{header:"합계",colspan:2,align:"center"}
								]],
								hierarchicalColMenu:true
							})]
						})
					]
				})
			],
			buttons:[
				new Ext.Button({
					text:"확인",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/admin/icon_tick.png",
					handler:function() {
						var data = GetGridData(Ext.getCmp("WorkspaceItemAddInsertList"));
						Ext.Ajax.request({
							url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php",
							success:function() {
								Ext.Msg.show({title:"안내",msg:"성공적으로 추가하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
								if (worktype) {
									Ext.getCmp("ContractCostSubList").getStore().reload();
								} else {
									Ext.getCmp("ContractCostList").getStore().reload();
								}
								Ext.getCmp("ContractItemAddWindow").close();
							},
							failure:function() {
								Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 추가하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
							},
							headers:{},
							params:{"action":"contract","do":"cost","mode":"add","cno":idx,"data":data}
						});
					}
				}),
				new Ext.Button({
					text:"취소",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/admin/icon_cross.png",
					handler:function() {
						Ext.getCmp("ContractItemAddWindow").close();
					}
				})
			]
		}).show();
	}

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"계약내역서관리",
		layout:"fit",
		tbar:[
			new Ext.Button({
				icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/admin/icon_page_white_paste.png",
				text:"신규계약내역등록",
				handler:function() {
					new Ext.Window({
						id:"ContractAddWindow",
						title:"신규계약내역등록",
						width:400,
						height:140,
						resizable:false,
						modal:true,
						layout:"fit",
						style:"",
						items:[
							new Ext.form.FormPanel({
								id:"ContractAddForm",
								labelAlign:"right",
								labelWidth:85,
								border:false,
								autoWidth:true,
								errorReader:new Ext.form.XmlErrorReader(),
								style:"padding:10px; background:#FFFFFF;",
								items:[
									new Ext.form.TextField({
										fieldLabel:"계약명",
										name:"title",
										width:250,
										allowBlank:false
									}),
									new Ext.form.NumberField({
										fieldLabel:"연면적",
										name:"totalarea",
										width:250,
										allowBlank:false
									})
								],
								listeners:{actioncomplete:{fn:function(form,action) {
									if (action.type == "submit") {
										Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
										ContractStore.reload();
										Ext.getCmp("ContractAddWindow").close();
									}
								}}}
							})
						],
						buttons:[
							new Ext.Button({
								text:"확인",
								icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/admin/icon_tick.png",
								handler:function() {
									Ext.getCmp("ContractAddForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php?action=contract&do=list&mode=add",waitMsg:"신규계약내역을 등록중입니다."});
								}
							}),
							new Ext.Button({
								text:"취소",
								icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/admin/icon_cross.png",
								handler:function() {
									Ext.getCmp("ContractAddWindow").close();
								}
							})
						]
					}).show();
				}
			})
		],
		items:[
			new Ext.grid.GridPanel({
				id:"ListTab",
				border:false,
				autoScroll:true,
				cm:new Ext.grid.ColumnModel([
					new Ext.grid.RowNumberer(),
					{
						dataIndex:"idx",
						hidden:true,
						hidable:false
					},{
						header:"계약명",
						dataIndex:"title",
						sortable:true,
						width:250
					},{
						header:"연면적",
						dataIndex:"totalarea",
						sortable:true,
						width:100,
						renderer:function(value) {
							return '<div style="text-align:right; font-family:arial;">'+GetNumberFormat(value)+'<span style="font-size:12px;">㎡</span></div>';
						}
					},{
						header:"계약금액",
						dataIndex:"price",
						sortable:true,
						width:120,
						renderer:GridNumberFormat
					}
				]),
				store:ContractStore,
				trackMouseOver:true,
				loadMask:{msg:"데이터를 로딩중입니다."},
				viewConfig:{forceFit:false},
				bbar:new Ext.PagingToolbar({
					pageSize:30,
					store:ContractStore,
					displayInfo:true,
					displayMsg:'{0} - {1} of {2}',
					emptyMsg:"데이터가 없습니다."
				}),
				listeners:{rowdblclick:{fn:function(grid,row,event) {
					var idx = grid.getStore().getAt(row).get("idx");

					new Ext.Window({
						id:"ContractPriceWindow",
						title:"계약내역서",
						modal:true,
						maximizable:true,
						layout:"fit",
						width:950,
						height:550,
						tbar:[
							new Ext.Button({
								text:"변경사항 저장하기",
								icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/admin/icon_disk.png",
								handler:function() {
									var data = GetGridData(Ext.getCmp("ContractPriceList"));
									Ext.Ajax.request({
										url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php",
										success:function() {
											Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
											Ext.getCmp("ContractPriceList").getStore().commitChanges();
										},
										failure:function() {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 추가하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
										},
										headers:{},
										params:{"action":"contract","do":"price","cno":idx,"data":data}
									});
								}
							}),
							'-',
							new Ext.Button({
								text:"공종별 계약내역서",
								icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/admin/icon_table.png",
								handler:function() {
									new Ext.Window({
										title:"공종별 계약내역서",
										width:880,
										height:500,
										layout:"fit",
										tbar:[
											new Ext.Button({
												text:"추가하기",
												icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/admin/icon_coins_add.png",
												handler:function() {
													ContractItemAddFunction(idx);
												}
											})
										],
										items:[
											new Ext.grid.GridPanel({
												id:"ContractCostList",
												border:false,
												cm:new Ext.grid.ColumnModel([
													new Ext.grid.CheckboxSelectionModel(),
													{
														dataIndex:"group",
														hidden:true,
														hideable:false
													},{
														header:"공종명",
														dataIndex:"worktype",
														width:180,
														sortable:true,
														summaryType:"data",
														summaryRenderer:function(value) {
															return "소계";
														}
													},{
														header:"단가",
														dataIndex:"cost1",
														width:75,
														sortable:false,
														summaryType:"sum",
														renderer:GridNumberFormat
													},{
														header:"금액",
														dataIndex:"price1",
														width:80,
														sortable:false,
														summaryType:"sum",
														renderer:GridNumberFormat
													},{
														header:"단가",
														dataIndex:"cost2",
														width:75,
														sortable:false,
														summaryType:"sum",
														renderer:GridNumberFormat
													},{
														header:"금액",
														dataIndex:"price2",
														width:80,
														sortable:false,
														summaryType:"sum",
														renderer:GridNumberFormat
													},{
														header:"단가",
														dataIndex:"cost3",
														width:75,
														sortable:false,
														summaryType:"sum",
														renderer:GridNumberFormat
													},{
														header:"금액",
														dataIndex:"price3",
														width:80,
														sortable:false,
														summaryType:"sum",
														renderer:GridNumberFormat
													},{
														header:"단가",
														dataIndex:"total_cost",
														width:75,
														sortable:false,
														summaryType:"sum",
														renderer:function(value,p,record) {
															return GridNumberFormat(record.data.cost1+record.data.cost2+record.data.cost3);
														}
													},{
														header:"금액",
														dataIndex:"total_price",
														width:80,
														sortable:false,
														summaryType:"sum",
														renderer:function(value,p,record) {
															return GridNumberFormat(record.data.price1+record.data.price2+record.data.price3);
														}
													}
												]),
												store:new Ext.data.GroupingStore({
													proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
													reader:new Ext.data.JsonReader({
														root:"lists",
														totalProperty:"totalCount",
														fields:["group","worktype",{name:"cost1",type:"int"},{name:"price1",type:"int"},{name:"cost2",type:"int"},{name:"price2",type:"int"},{name:"cost3",type:"int"},{name:"price3",type:"int"}]
													}),
													remoteSort:false,
													groupField:"group",
													sortInfo:{field:"worktype",direction:"ASC"},
													baseParams:{"action":"contract","get":"cost","cno":idx}
												}),
												plugins:[new Ext.ux.plugins.GroupHeaderGrid({
													rows:[[
														{},
														{},
														{},
														{header:"재료비",colspan:2,align:"center"},
														{header:"노무비",colspan:2,align:"center"},
														{header:"경비",colspan:2,align:"center"},
														{header:"합계",colspan:2,align:"center"}
													]],
													hierarchicalColMenu:true
												}),new Ext.ux.grid.GroupSummary()],
												view:new Ext.grid.GroupingView({
													enableGroupingMenu:false,
													hideGroupedColumn:true,
													showGroupName:false,
													enableNoGroups:false,
													headersDisabled:false
												}),
												listeners:{rowdblclick:function(grid,row,event) {
													var data = grid.getStore().getAt(row);

													var SubItemStore = new Ext.data.Store({
														proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
														reader:new Ext.data.JsonReader({
															root:'lists',
															totalProperty:'totalCount',
															fields:[{name:"idx",type:"int"},"worktype","title","size","unit",{name:"ea",type:"int"},{name:"cost1",type:"int"},{name:"cost2",type:"int"},{name:"cost3",type:"int"}]
														}),
														remoteSort:true,
														sortInfo:{field:"idx",direction:"ASC"},
														baseParams:{"action":"contract","get":"cost","group":data.get("group"),"worktype":data.get("worktype"),"cno":idx}
													});

													SubItemStore.load({params:{start:0,limit:30}});

													new Ext.Window({
														title:data.get("group")+"-"+data.get("worktype")+" 세부정보",
														modal:true,
														width:900,
														height:500,
														layout:"fit",
														items:[
															new Ext.grid.EditorGridPanel({
																id:"ContractCostSubList",
																layout:"fit",
																margins:"0 5 5 5",
																border:false,
																tbar:[
																	new Ext.Button({
																		text:"추가하기",
																		icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/admin/icon_coins_add.png",
																		handler:function() {
																			if (Ext.getCmp("ContractCostSubList").getStore().getModifiedRecords().length != 0) {
																				Ext.Msg.show({title:"안내",msg:"변경된 사항이 있습니다. 변경사항을 먼저 저장하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
																				return false;
																			}

																			ContractItemAddFunction(idx,data.get("group"),data.get("worktype"));
																		}
																	}),
																	new Ext.Button({
																		text:"삭제하기",
																		icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/admin/icon_coins_delete.png",
																		handler:function() {
																			var checked = Ext.getCmp("ContractCostSubList").selModel.getSelections();

																			if (Ext.getCmp("ContractCostSubList").getStore().getModifiedRecords().length != 0) {
																				Ext.Msg.show({title:"안내",msg:"변경된 사항이 있습니다. 변경사항을 먼저 저장하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
																				return false;
																			}

																			if (checked.length == 0) {
																				Ext.Msg.show({title:"에러",msg:"삭제할 대상을 선택하세요.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
																				return false;
																			}

																			Ext.Msg.show({title:"안내",msg:"정말 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.INFO,fn:function(button) {
																				if (button == "ok") {
																					var inos = new Array();
																					for (var i=0, loop=checked.length;i<loop;i++) {
																						inos[i] = checked[i].get("idx");
																					}
																					var ino = inos.join(",");

																					Ext.Ajax.request({
																						url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php",
																						success: function() {
																							Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
																							Ext.getCmp("ContractCostSubList").getStore().reload();
																							Ext.getCmp("ContractCostList").getStore().reload();
																						},
																						failure: function() {
																							Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 삭제하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
																						},
																						headers:{},
																						params:{"action":"contract","do":"cost","mode":"delete","ino":ino,"cno":idx}
																					});
																				}
																			}});
																		}
																	}),
																	'-',
																	new Ext.Button({
																		text:"변경사항저장하기",
																		icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/admin/icon_disk.png",
																		handler:function() {
																			if (Ext.getCmp("ContractCostSubList").getStore().getModifiedRecords().length == 0) {
																				Ext.Msg.show({title:"안내",msg:"변경된 사항이 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
																				return false;
																			}
																			var data = GetGridData(Ext.getCmp("ContractCostSubList"));

																			Ext.Ajax.request({
																				url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php",
																				success: function() {
																					Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
																					Ext.getCmp("ContractCostSubList").getStore().commitChanges();
																					Ext.getCmp("ContractCostList").getStore().reload();
																				},
																				failure: function() {
																					Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 저장하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
																				},
																				headers:{},
																				params:{"action":"contract","do":"cost","mode":"modify","cno":idx,"data":data}
																			});

																		}
																	})
																],
																cm:new Ext.grid.ColumnModel([
																	new Ext.grid.CheckboxSelectionModel(),
																	{
																		dataIndex:"idx",
																		hidden:true,
																		hideable:false
																	},{
																		header:"품명",
																		dataIndex:"title",
																		width:150,
																		sortable:false
																	},{
																		header:"규격",
																		dataIndex:"size",
																		width:80,
																		sortable:false
																	},{
																		header:"단위",
																		dataIndex:"unit",
																		width:40,
																		sortable:false
																	},{
																		header:"수량",
																		dataIndex:"ea",
																		width:40,
																		sortable:false,
																		renderer:function(value,p,record) {
																			if (!value) record.data.ea = 0;
																			return GridNumberFormat(value);
																		},
																		editor:new Ext.form.NumberField({selectOnFocus:true})
																	},{
																		header:"단가",
																		dataIndex:"cost1",
																		width:60,
																		sortable:false,
																		renderer:function(value,p,record) {
																			if (!value) record.data.cost1 = 0;
																			return GridNumberFormat(value);
																		},
																		editor:new Ext.form.NumberField({selectOnFocus:true})
																	},{
																		header:"금액",
																		dataIndex:"price1",
																		width:80,
																		sortable:false,
																		renderer:function(value,p,record) {
																			return GridNumberFormat(record.data.ea*record.data.cost1);
																		}
																	},{
																		header:"단가",
																		dataIndex:"cost2",
																		width:60,
																		sortable:false,
																		renderer:function(value,p,record) {
																			if (!value) record.data.cost2 = 0;
																			return GridNumberFormat(value);
																		},
																		editor:new Ext.form.NumberField({selectOnFocus:true})
																	},{
																		header:"금액",
																		dataIndex:"price2",
																		width:80,
																		sortable:false,
																		renderer:function(value,p,record) {
																			return GridNumberFormat(record.data.ea*record.data.cost2);
																		}
																	},{
																		header:"단가",
																		dataIndex:"cost3",
																		width:60,
																		sortable:false,
																		renderer:function(value,p,record) {
																			if (!value) record.data.cost3 = 0;
																			return GridNumberFormat(value);
																		},
																		editor:new Ext.form.NumberField({selectOnFocus:true})
																	},{
																		header:"금액",
																		dataIndex:"price3",
																		width:80,
																		sortable:false,
																		renderer:function(value,p,record) {
																			return GridNumberFormat(record.data.ea*record.data.cost3);
																		}
																	},{
																		header:"단가",
																		dataIndex:"total_cost",
																		width:80,
																		sortable:false,
																		renderer:function(value,p,record) {
																			return GridNumberFormat(record.data.cost1+record.data.cost2+record.data.cost3);
																		}
																	},{
																		header:"금액",
																		dataIndex:"total_price",
																		width:80,
																		sortable:false,
																		renderer:function(value,p,record) {
																			return GridNumberFormat(record.data.ea*record.data.total_cost);
																		}
																	}
																]),
																clicksToEdit:1,
																trackMouseOver:true,
																sm:new Ext.grid.CheckboxSelectionModel(),
																store:SubItemStore,
																plugins:[new Ext.ux.plugins.GroupHeaderGrid({
																	rows:[[
																		{},
																		{},
																		{},
																		{},
																		{},
																		{},
																		{header:"재료비",colspan:2,align:"center"},
																		{header:"노무비",colspan:2,align:"center"},
																		{header:"경비",colspan:2,align:"center"},
																		{header:"합계",colspan:2,align:"center"}
																	]],
																	hierarchicalColMenu:true
																})],
																bbar:new Ext.PagingToolbar({
																	pageSize:30,
																	store:SubItemStore,
																	displayInfo:true,
																	displayMsg:"{0} - {1} of {2}",
																	emptyMsg:"데이터없음",
																	listeners:{beforechange:{fn:function(p,params) {
																		if (Ext.getCmp("ContractCostSubList").getStore().getModifiedRecords().length > 0) {
																			Ext.Msg.show({title:"안내",msg:"변경된 사항이 있습니다.<br />변경사항을 저장하지 않을경우, 변경사항이 반영되지 않습니다.<br />페이지를 이동하시겠습니까?.",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.INFO,fn:function(button) {
																				if (button == "ok") Ext.getCmp("ContractCostSubList").getStore().load({params:params});
																				else return false;
																			}});
																			return false;
																		}
																	}}}
																})
															})
														]
													}).show();
												}}
											})
										],
										listeners:{show:{fn:function() {
											Ext.getCmp("ContractCostList").getStore().load();
										}}}
									}).show();
								}
							}),
							new Ext.Button({
								text:"평당환산금액",
								icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/admin/icon_building.png",
								handler:function() {
									new Ext.Window({
										title:"평당환산금액",
										modal:true,
										maximizable:true,
										layout:"fit",
										width:600,
										height:500,
										tbar:[
											new Ext.Button({
												text:"공종추가",
												icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/admin/icon_textfield_add.png",
												handler:function() {
													new Ext.Window({
														id:"ContractUnitPriceAddWindow",
														title:"공종추가",
														modal:true,
														width:400,
														height:140,
														layout:"fit",
														items:[
															new Ext.form.FormPanel({
																id:"ContractUnitPriceAddForm",
																border:false,
																style:"padding:10px; background:#FFFFFF;",
																labelAlign:"right",
																labelWidth:85,
																autoWidth:true,
																items:[
																	new Ext.form.ComboBox({
																		fieldLabel:"공종분류",
																		name:"group",
																		typeAhead:true,
																		triggerAction:"all",
																		lazyRender:true,
																		store:new Ext.data.SimpleStore({
																			fields:["value"],
																			data:[["건축공사"],["전기공사"],["설비공사"],["소방공사"],["간접비용"]]
																		}),
																		width:150,
																		editable:false,
																		mode:"local",
																		displayField:"value",
																		valueField:"value"
																	}),
																	new Ext.form.TextField({
																		fieldLabel:"공종명",
																		name:"worktype",
																		width:200
																	})
																]
															})
														],
														buttons:[
															new Ext.Button({
																text:"확인",
																icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/admin/icon_tick.png",
																handler:function() {
																	if (!Ext.getCmp("ContractUnitPriceAddForm").getForm().findField("group").getValue()) {
																		Ext.Msg.show({title:"에러",msg:"공종분류를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
																		return false;
																	}

																	if (!Ext.getCmp("ContractUnitPriceAddForm").getForm().findField("worktype").getValue()) {
																		Ext.Msg.show({title:"에러",msg:"공종명을 입력하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
																		return false;
																	}

																	for (var i=0, loop=Ext.getCmp("ContractUnitPrice").getStore().getCount();i<loop;i++) {
																		if (Ext.getCmp("ContractUnitPrice").getStore().getAt(i).get("group") == Ext.getCmp("ContractUnitPriceAddForm").getForm().findField("group").getValue() && Ext.getCmp("ContractUnitPrice").getStore().getAt(i).get("worktype") == Ext.getCmp("ContractUnitPriceAddForm").getForm().findField("worktype").getValue()) {
																			Ext.Msg.show({title:"에러",msg:"공종분류에 해당 공종명이 이미 존재합니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
																			return false;
																		}
																	}

																	var record = new Ext.data.Record.create([{name:"is_write",type:"string"},{name:"group",type:"string"},{name:"worktype",type:"string"},{name:"price",type:"int"},{name:"unit_price",type:"int"},{name:"etc",type:"string"}]);

																	Ext.getCmp("ContractUnitPrice").stopEditing();
																	Ext.getCmp("ContractUnitPrice").getStore().add(new record({is_write:"TRUE",group:Ext.getCmp("ContractUnitPriceAddForm").getForm().findField("group").getValue(),worktype:Ext.getCmp("ContractUnitPriceAddForm").getForm().findField("worktype").getValue(),price:0,unit_price:0,etc:""}));

																	Ext.getCmp("ContractUnitPriceAddWindow").close();
																}
															}),
															new Ext.Button({
																text:"취소",
																icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/admin/icon_cross.png",
																handler:function() {
																	Ext.getCmp("ContractUnitPriceAddWindow").close();
																}
															})
														]
													}).show();
												}
											}),
											new Ext.Button({
												text:"삭제",
												icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/admin/icon_textfield_delete.png",
												handler:function() {
													var checked = Ext.getCmp("ContractUnitPrice").selModel.getSelections();

													if (checked.length == 0) {
														Ext.Msg.show({title:"에러",msg:"삭제할 항목을 선택하세요.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
														return false;
													}

													for (var i=0, loop=checked.length;i<loop;i++) {
														if (checked[i].get("is_write") == "FALSE") {
															Ext.Msg.show({title:"에러",msg:"자동으로 집계된 항목은 삭제할 수 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
															return false;
														}
													}

													for (var i=0, loop=checked.legnth;i<loop;i++) {
														Ext.getCmp("ContractUnitPrice").getStore().remove(checked[i]);
													}
												}
											}),
											'-',
											new Ext.Button({
												text:"변경사항 저장하기",
												icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/admin/icon_disk.png",
												handler:function() {
													var data = GetGridData(Ext.getCmp("ContractUnitPrice"));
													Ext.Ajax.request({
														url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php",
														success:function() {
															Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
															Ext.getCmp("ContractUnitPrice").getStore().commitChanges();
														},
														failure:function() {
															Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 추가하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
														},
														headers:{},
														params:{"action":"contract","do":"unit_price","cno":idx,"data":data}
													});
												}
											})
										],
										items:[
											new Ext.grid.EditorGridPanel({
												id:"ContractUnitPrice",
												border:false,
												cm:new Ext.grid.ColumnModel([
													new Ext.grid.CheckboxSelectionModel(),
													{
														dataIndex:"is_write",
														hidden:true,
														hideable:false
													},{
														dataIndex:"group",
														hidden:true,
														hideable:false
													},{
														header:"공종",
														dataIndex:"worktype",
														width:150,
														summaryType:"data",
														summaryRenderer:function(value) {
															return "계";
														}
													},{
														header:"공사금액",
														dataIndex:"price",
														width:100,
														summaryType:"sum",
														renderer:GridNumberFormat,
														editor:new Ext.form.NumberField({selectOnFocus:true})
													},{
														header:"평당환산금액",
														dataIndex:"unit_price",
														width:100,
														summaryType:"sum",
														renderer:GridNumberFormat
													},{
														header:"비고",
														dataIndex:"etc",
														width:190,
														editor:new Ext.form.TextField({selectOnFocus:true})
													}
												]),
												plugins:new Ext.ux.grid.GroupSummary(),
												clicksToEdit:1,
												trackMouseOver:true,
												sm:new Ext.grid.CheckboxSelectionModel(),
												store:new Ext.data.GroupingStore({
													proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
													reader:new Ext.data.JsonReader({
														root:"lists",
														totalProperty:"totalCount",
														fields:["is_write","group","worktype",{name:"price",type:"int"},{name:"unit_price",type:"int"},"etc"]
													}),
													groupField:"group",
													remoteSort:false,
													sortInfo:{field:"worktype",direction:"ASC"},
													baseParams:{"action":"contract","get":"unit_price","cno":idx}
												}),
												clicksToEdit:1,
												trackMouseOver:true,
												view:new Ext.grid.GroupingView({
													enableGroupingMenu:false,
													hideGroupedColumn:true,
													showGroupName:false,
													enableNoGroups:false,
													headersDisabled:false
												}),
												listeners:{
													beforeedit:{fn:function(object) {
														if (object.record.data.is_write == "FALSE" && object.field == "price") return false;
														else return true;
													}},
													afteredit:{fn:function(object) {
														if (object.field == "price") {
															var totalarea = Ext.getCmp("ListTab").getStore().getAt(Ext.getCmp("ListTab").getStore().find("idx",idx,false,false)).get("totalarea");
															object.grid.getStore().getAt(object.row).set("unit_price",(totalarea > 0 ? Math.floor(Math.round(object.value/(totalarea*0.3025))/10)*10 : 0));
														}
													}}
												}
											})
										],
										listeners:{show:{fn:function() {
											Ext.getCmp("ContractUnitPrice").getStore().load();
										}}}
									}).show();
								}
							})
						],
						items:[
							new Ext.grid.EditorGridPanel({
								id:"ContractPriceList",
								border:false,
								cm:new Ext.grid.ColumnModel([
									new Ext.grid.RowNumberer(),
									{
										dataIndex:"idx",
										sortable:false,
										hidden:true,
										hideable:false,
										width:100
									},{
										dataIndex:"is_write",
										hidden:true,
										hideable:false
									},{
										header:"분류",
										dataIndex:"group",
										sortable:false
									},{
										header:"분류",
										dataIndex:"type",
										sortable:false,
										width:100,
										summaryType:"data",
										summaryRenderer:function(value,p,record) {
											if (value == "이윤") {
												return "공급가액";
											} else if (value == "부가가치세") {
												return "총공사비";
											} else if (value == "경비") {
												return '<div class="x-grid3-summary-double">경비</div><div class="x-grid3-summary-double">계</div>';
											} else {
												return value;
											}
										}
									},{
										header:"세부항목",
										dataIndex:"category",
										sortable:true,
										width:150,
										summaryType:"data",
										summaryRenderer:function(value,p,record) {
											if (record.data["type"] != "이윤" && record.data["type"] != "부가가치세") return "소계"
										}
									},{
										header:"금액",
										dataIndex:"price",
										sortable:false,
										width:150,
										renderer:function(value,p,record) {
											if (record.data.category == "간접노무비") {
												record.data.price = Math.round(Ext.getCmp("ContractPriceList").getStore().getAt(3).get("price")*(record.data.percent/100));
												return GridNumberFormat(record.data.price);
											} else if (record.data.category == "산재보험료" || record.data.category == "고용보험료") {
												record.data.price = Math.round((Ext.getCmp("ContractPriceList").getStore().getAt(3).get("price")+Ext.getCmp("ContractPriceList").getStore().getAt(4).get("price"))*(record.data.percent/100));
												return GridNumberFormat(record.data.price);
											} else if (record.data.category == "국민건강보험료" || record.data.category == "국민연금보험료") {
												record.data.price = Math.round(Ext.getCmp("ContractPriceList").getStore().getAt(3).get("price")*(record.data.percent/100));
												return GridNumberFormat(record.data.price);
											} else if (record.data.category == "노인장기요양보험료") {
												record.data.price = Math.round(Ext.getCmp("ContractPriceList").getStore().getAt(8).get("price")*(record.data.percent/100));
												return GridNumberFormat(record.data.price);
											} else if (record.data.category == "산업안전보건관리비") {
												record.data.price = Math.round((Ext.getCmp("ContractPriceList").getStore().getAt(0).get("price")+Ext.getCmp("ContractPriceList").getStore().getAt(1).get("price")+Ext.getCmp("ContractPriceList").getStore().getAt(2).get("price")+Ext.getCmp("ContractPriceList").getStore().getAt(3).get("price"))*(record.data.percent/100));
												return GridNumberFormat(record.data.price);
											} else if (record.data.type == "일반관리비" || record.data.type == "이윤") {
												var total = 0;
												for (var i=0;i<=11;i++) {
													total+= Ext.getCmp("ContractPriceList").getStore().getAt(i).get("price");
												}
												record.data.price = Math.round(total*(record.data.percent/100));
												return GridNumberFormat(record.data.price);
											} else if (record.data.type == "부가가치세") {
												var total = 0;
												for (var i=0;i<=13;i++) {
													total+= Ext.getCmp("ContractPriceList").getStore().getAt(i).get("price");
												}
												record.data.price = Math.round(total*(record.data.percent/100));
												return GridNumberFormat(record.data.price);
											} else {
												return GridNumberFormat(value);
											}
										},
										summaryType:"sum",
										editor:new Ext.form.NumberField({selectOnFocus:true}),
										summaryRenderer:function(value,p,record,data) {
											if (record.data["type"] == "경비") {
												var sHTML = '<div class="x-grid3-summary-double">'+GridNumberFormat(value)+'</div>';

												var total = 0;
												for (var i=0;i<=11;i++) {
													total+= Ext.getCmp("ContractPriceList").getStore().getAt(i).get("price");
												}
												sHTML+= '<div class="x-grid3-summary-double">'+GridNumberFormat(total)+'</div>';

												return sHTML;
											} else if (record.data["type"] == "이윤") {
												var total = 0;
												for (var i=0;i<=13;i++) {
													total+= Ext.getCmp("ContractPriceList").getStore().getAt(i).get("price");
												}
												return GridNumberFormat(total);
											} else if (record.data["type"] == "부가가치세") {
												var total = 0;
												for (var i=0;i<=13;i++) {
													total+= Ext.getCmp("ContractPriceList").getStore().getAt(i).get("price");
												}
												return GridNumberFormat(total+Math.round(total/100*10));
											} else {
												return GridNumberFormat(value);
											}
										}
									},{
										header:"구성비",
										dataIndex:"percent",
										sortable:false,
										width:180,
										renderer:function(value,p,record) {
											if (record.data.category == "간접노무비") {
												return "직접노무비 * "+value+"%";
											} else if (record.data.category == "산재보험료" || record.data.category == "고용보험료") {
												return "노무비 * "+value+"%";
											} else if (record.data.category == "국민건강보험료" || record.data.category == "국민연금보험료") {
												return "직접노무비 * "+value+"%";
											} else if (record.data.category == "노인장기요양보험료") {
												return "국민건강보험료 * "+value+"%";
											} else if (record.data.category == "산업안전보건관리비") {
												return "[재료비+직접노무비] * "+value+"%";
											} else if (record.data.type == "일반관리비" || record.data.type == "이윤") {
												return "순공사비 * "+value+"%";
											} else if (record.data.type == "부가가치세") {
												return "공급가액 * "+value+"%";
											}
										},
										editor:new Ext.form.TextField({selectOnFocus:true})
									},{
										header:"비고",
										dataIndex:"etc",
										sortable:false,
										width:300,
										editor:new Ext.form.TextField({selectOnFocus:true})
									}
								]),
								plugins:new Ext.ux.grid.GroupSummary(),
								store:new Ext.data.GroupingStore({
									proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
									reader:new Ext.data.JsonReader({
										root:"lists",
										totalProperty:"totalCount",
										fields:[{name:"idx",type:"int"},"is_write","group","type","category",{name:"price",type:"int"},{name:"percent",type:"float"},"etc"]
									}),
									groupField:"group",
									remoteSort:false,
									sortInfo:{field:"idx",direction:"ASC"},
									baseParams:{"action":"contract","get":"price","cno":idx}
								}),
								clicksToEdit:1,
								trackMouseOver:true,
								view:new Ext.grid.GroupingView({
									enableGroupingMenu:false,
									hideGroupedColumn:true,
									showGroupName:false,
									enableNoGroups:false,
									headersDisabled:false,
									showGroupHeader:false
								}),
								listeners:{
									beforeedit:{fn:function(object) {
										if (object.field == "price" && object.record.data.is_write == "FALSE") return false;
										else if (object.field == "percent" && object.record.data.percent == "") return false;
										else return true;
									}},
									afteredit:{fn:function(object) {
										if (object.field == "price") {
											Ext.getCmp("ContractPriceList").getStore().sort("idx","ASC");
										} else if (object.field == "percent") {
											if (object.value == "") {
												object.grid.getStore().getAt(object.row).set("percent",object.originalValue);
											}
										}
									}}
								}
							})
						],
						listeners:{show:{fn:function() {
							Ext.getCmp("ContractPriceList").getStore().load();
						}}}
					}).show();
				}}}
			})
		]
	});

	ContractStore.load({params:{start:0,limit:30}});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>