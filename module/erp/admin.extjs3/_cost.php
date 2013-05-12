<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/module/erp/script/workspace.js"></script>
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/module/erp/script/cost.js"></script>
<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	function CostFunction(grid,row,e) {
		new Ext.Window({
			title:grid.getStore().getAt(row).get("title"),
			modal:true,
			width:960,
			height:550,
			layout:"fit",
			items:[
				new Ext.TabPanel({
					id:"CostListTab",
					tabPosition:"bottom",
					border:false,
					activeTab:0,
					items:[
						new Ext.grid.GridPanel({
							title:"견적내역서",
							id:"Cost1",
							tbar:[
								new Ext.Button({
									text:"새 견적내역서 작성",
									icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_page_white_paste.png",
									handler:function() {
										CostAdd(grid.getStore().getAt(row),Ext.getCmp("CostListTab").getActiveTab().getStore().baseParams.type);
									}
								})
							],
							cm:new Ext.grid.ColumnModel([
								new Ext.grid.RowNumberer(),
								{
									dataIndex:"idx",
									hidden:true,
									hideable:false
								},{
									header:"견적내역서명",
									dataIndex:"title",
									width:430,
									sortable:true,
									renderer:function(value,p,record) {
										if (record.data.is_apply == "TRUE") {
											return '<img src="<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_apply.gif" style="margin:-1px 5px -1px 0px; vertical-align:middle" />'+value;
										} else {
											return value;
										}
									}
								},{
									header:"품목수",
									dataIndex:"itemnum",
									width:80,
									sortable:true,
									renderer:GridNumberFormat
								},{
									header:"금액",
									dataIndex:"price",
									width:130,
									sortable:true,
									renderer:GridNumberFormat
								},{
									header:"작성일",
									dataIndex:"reg_date",
									width:120,
									sortable:true,
									renderer:GridDateTimeFormat
								},{
									header:"최종수정일",
									dataIndex:"modify_date",
									width:120,
									sortable:true,
									renderer:GridDateTimeFormat
								},
								new Ext.grid.CheckboxSelectionModel()
							]),
							store:new Ext.data.Store({
								proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
								reader:new Ext.data.JsonReader({
									root:"lists",
									totalProperty:"totalCount",
									fields:["idx","wno","title",{name:"itemnum",type:"int"},{name:"price",type:"int"},"is_apply","reg_date","modify_date"]
								}),
								remoteSort:false,
								sortInfo:{field:"reg_date",direction:"DESC"},
								baseParams:{"action":"cost","get":"list","type":"ESTIMATE","wno":grid.getStore().getAt(row).get("idx")}
							}),
							listeners:{
								render:{fn:function() {
									Ext.getCmp("Cost1").getStore().load();
								}},
								rowdblclick:{fn:function(grid,idx,e) {
									CostView(Ext.getCmp("CostListTab").getActiveTab().getStore().baseParams.type,grid.getStore().getAt(idx).get("wno"),grid.getStore().getAt(idx).get("idx"),grid.getStore().getAt(idx).get("title"));
								}},
								rowcontextmenu:CostMenu
							}
						}),
						new Ext.grid.GridPanel({
							title:"실행내역서",
							id:"Cost2",
							tbar:[
								new Ext.Button({
									text:"새 실행내역서 작성",
									icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_page_white_paste.png",
									handler:function() {
										CostAdd(grid.getStore().getAt(row),Ext.getCmp("CostListTab").getActiveTab().getStore().baseParams.type);
									}
								})
							],
							cm:new Ext.grid.ColumnModel([
								new Ext.grid.RowNumberer(),
								{
									dataIndex:"idx",
									hidden:true,
									hideable:false
								},{
									header:"실행내역서명",
									dataIndex:"title",
									width:430,
									sortable:true,
									renderer:function(value,p,record) {
										if (record.data.is_apply == "TRUE") {
											return '<img src="<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_apply.gif" style="margin:-1px 5px -1px 0px; vertical-align:middle" />'+value;
										} else {
											return value;
										}
									}
								},{
									header:"품목수",
									dataIndex:"itemnum",
									width:80,
									sortable:true,
									renderer:GridNumberFormat
								},{
									header:"금액",
									dataIndex:"price",
									width:130,
									sortable:true,
									renderer:GridNumberFormat
								},{
									header:"작성일",
									dataIndex:"reg_date",
									width:120,
									sortable:true,
									renderer:GridDateTimeFormat
								},{
									header:"최종수정일",
									dataIndex:"modify_date",
									width:120,
									sortable:true,
									renderer:GridDateTimeFormat
								},
								new Ext.grid.CheckboxSelectionModel()
							]),
							store:new Ext.data.Store({
								proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
								reader:new Ext.data.JsonReader({
									root:"lists",
									totalProperty:"totalCount",
									fields:["idx","wno","title",{name:"itemnum",type:"int"},{name:"price",type:"int"},"is_apply","reg_date","modify_date"]
								}),
								remoteSort:false,
								sortInfo:{field:"reg_date",direction:"DESC"},
								baseParams:{"action":"cost","get":"list","type":"EXEC","wno":grid.getStore().getAt(row).get("idx")}
							}),
							listeners:{
								render:{fn:function() {
									Ext.getCmp("Cost2").getStore().load();
								}},
								rowdblclick:{fn:function(grid,row,e) {
									CostView(Ext.getCmp("CostListTab").getActiveTab().getStore().baseParams.type,grid.getStore().getAt(row).get("wno"),grid.getStore().getAt(row).get("idx"),grid.getStore().getAt(row).get("title"));
								}},
								rowcontextmenu:CostMenu
							}
						}),
						new Ext.grid.GridPanel({
							title:"계약내역서",
							id:"Cost3",
							tbar:[
								new Ext.Button({
									text:"새 계약내역서 작성",
									icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_page_white_paste.png",
									handler:function() {
										CostAdd(grid.getStore().getAt(row),Ext.getCmp("CostListTab").getActiveTab().getStore().baseParams.type);
									}
								})
							],
							cm:new Ext.grid.ColumnModel([
								new Ext.grid.RowNumberer(),
								{
									dataIndex:"idx",
									hidden:true,
									hideable:false
								},{
									header:"계약내역서명",
									dataIndex:"title",
									width:430,
									sortable:true,
									renderer:function(value,p,record) {
										if (record.data.is_apply == "TRUE") {
											return '<img src="<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_apply.gif" style="margin:-1px 5px -1px 0px; vertical-align:middle" />'+value;
										} else {
											return value;
										}
									}
								},{
									header:"품목수",
									dataIndex:"itemnum",
									width:80,
									sortable:true,
									renderer:GridNumberFormat
								},{
									header:"금액",
									dataIndex:"price",
									width:130,
									sortable:true,
									renderer:GridNumberFormat
								},{
									header:"작성일",
									dataIndex:"reg_date",
									width:120,
									sortable:true,
									renderer:GridDateTimeFormat
								},{
									header:"최종수정일",
									dataIndex:"modify_date",
									width:120,
									sortable:true,
									renderer:GridDateTimeFormat
								},
								new Ext.grid.CheckboxSelectionModel()
							]),
							store:new Ext.data.Store({
								proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
								reader:new Ext.data.JsonReader({
									root:"lists",
									totalProperty:"totalCount",
									fields:["idx","wno","title",{name:"itemnum",type:"int"},{name:"price",type:"int"},"is_apply","reg_date","modify_date"]
								}),
								remoteSort:false,
								sortInfo:{field:"reg_date",direction:"DESC"},
								baseParams:{"action":"cost","get":"list","type":"CONTRACT","wno":grid.getStore().getAt(row).get("idx")}
							}),
							listeners:{
								render:{fn:function() {
									Ext.getCmp("Cost3").getStore().load();
								}},
								rowdblclick:{fn:function(grid,row,e) {
									CostView(Ext.getCmp("CostListTab").getActiveTab().getStore().baseParams.type,grid.getStore().getAt(row).get("wno"),grid.getStore().getAt(row).get("idx"),grid.getStore().getAt(row).get("title"));
								}},
								rowcontextmenu:CostMenu
							}
						})
					]
				})
			],
			listeners:{
				close:{fn:function() {
					if (Ext.getCmp("ListTab1")) Ext.getCmp("ListTab1").getStore().reload();
					if (Ext.getCmp("ListTab2")) Ext.getCmp("ListTab2").getStore().reload();
					if (Ext.getCmp("ListTab3")) Ext.getCmp("ListTab3").getStore().reload();
					if (Ext.getCmp("ListTab4")) Ext.getCmp("ListTab4").getStore().reload();
				}
			}}
		}).show();
	}

	function CostAdd(workspace,type) {
		if (type == "ESTIMATE") {
			var title = "견적내역서";
			var list = "Cost1";
		} else if (type == "EXEC") {
			var title = "실행내역서";
			var list = "Cost2";
		} else if (type == "CONTRACT") {
			var title = "계약내역서";
			var list = "Cost3";
		}

		new Ext.Window({
			title:"새 "+title+" 작성",
			id:"CostAddWindow",
			modal:true,
			width:400,
			height:120,
			layout:"fit",
			items:[
				new Ext.form.FormPanel({
					id:"CostAddForm",
					border:false,
					style:"padding:10px; background:#FFFFFF;",
					labelAlign:"right",
					labelWidth:80,
					autoWidth:true,
					errorReader:new Ext.form.XmlErrorReader(),
					items:[
						new Ext.form.TextField({
							fieldLabel:title+"명",
							width:280,
							name:"title",
							value:new Date().format("Y년 m월 d일")+" "+title,
							allowBlank:false
						})
					],
					listeners:{actioncomplete:{fn:function(form,action) {
						if (action.type == "submit") {
							var idx;
							Ext.each(action.result.errors,function(item,index,allItems) { idx = item.id; });
							CostView(type,workspace.get("idx"),idx,Ext.getCmp("CostAddForm").getForm().findField("title").getValue());
							Ext.getCmp(list).getStore().reload();
							Ext.getCmp("CostAddWindow").close();
						}
					}}}
				})
			],
			buttons:[
				new Ext.Button({
					text:"확인",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_tick.png",
					handler:function() {
						Ext.getCmp("CostAddForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php?action=cost&do=add&type="+type+"&wno="+workspace.get("idx"),waitMsg:title+"를 추가중입니다."});
					}
				}),
				new Ext.Button({
					text:"취소",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
					handler:function() {
						Ext.getCmp("CostAddWindow").close();
					}
				})
			]
		}).show();
	}

	function CostModify(idx,type,value) {
		if (type == "ESTIMATE") {
			var title = "견적내역서";
			var list = "Cost1";
		} else if (type == "EXEC") {
			var title = "실행내역서";
			var list = "Cost2";
		} else if (type == "CONTRACT") {
			var title = "계약내역서";
			var list = "Cost3";
		} else if (type == "CHANGE") {
			var title = "설계변경내역서";
			var list = "Cost4";
		}

		new Ext.Window({
			title:title+"명 수정",
			id:"CostModifyWindow",
			modal:true,
			width:400,
			height:120,
			layout:"fit",
			items:[
				new Ext.form.FormPanel({
					id:"CostModifyForm",
					border:false,
					style:"padding:10px; background:#FFFFFF;",
					labelAlign:"right",
					labelWidth:80,
					autoWidth:true,
					errorReader:new Ext.form.XmlErrorReader(),
					items:[
						new Ext.form.TextField({
							fieldLabel:title+"명",
							width:280,
							name:"title",
							value:value,
							allowBlank:false
						})
					],
					listeners:{actioncomplete:{fn:function(form,action) {
						if (action.type == "submit") {
							Ext.getCmp(list).getStore().reload();
							Ext.getCmp("CostModifyWindow").close();
						}
					}}}
				})
			],
			buttons:[
				new Ext.Button({
					text:"확인",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_tick.png",
					handler:function() {
						Ext.getCmp("CostModifyForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php?action=cost&do=modify&idx="+idx,waitMsg:title+"를 수정중입니다."});
					}
				}),
				new Ext.Button({
					text:"취소",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
					handler:function() {
						Ext.getCmp("CostModifyWindow").close();
					}
				})
			]
		}).show();
	}

	function CostMenu(grid,idx,e) {
		GridContextmenuSelect(grid,idx);

		if (grid.getId() == "Cost1") {
			var typeText = "견적내역서";
			var type = "ESTIMATE";
		} else if (grid.getId() == "Cost2") {
			var typeText = "실행내역서";
			var type = "EXEC";
		} else if (grid.getId() == "Cost3") {
			var typeText = "계약내역서";
			var type = "CONTRACT";
		} else if (grid.getId() == "Cost4") {
			var typeText = "설계변경내역서";
			var type = "CHANGE";
		}

		var data = grid.getStore().getAt(idx);
		var menu = new Ext.menu.Menu();
		menu.add('<b class="menu-title">'+data.get("title")+'</b>');
		menu.add({
			text:typeText+"명 수정",
			icon:ENV.dir+"/module/erp/images/common/icon_page_white_edit.png",
			handler:function(item) {
				CostModify(data.get("idx"),type,data.get("title"));
			}
		});
		menu.add({
			text:typeText+" 삭제",
			icon:ENV.dir+"/module/erp/images/common/icon_page_white_delete.png",
			handler:function(item) {
				if (data.get("is_apply") == "TRUE") {
					Ext.Msg.show({title:"에러",msg:"현장에 반영된 "+typeText+"는 삭제할 수 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
					return false;
				}

				Ext.Msg.show({title:"안내",msg:"정말 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
					if (button == "ok") {
						Ext.Ajax.request({
							url:ENV.dir+"/module/erp/exec/Admin.do.php",
							success:function() {
								Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
								grid.getStore().load();
							},
							failure:function() {
								Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
							},
							headers:{},
							params:{"action":"cost","do":"delete","idx":data.get("idx")}
						});
					}
				}});
			}
		});
		menu.add({
			text:"현장에 적용하기",
			icon:ENV.dir+"/module/erp/images/common/icon_page_copy.png",
			handler:function(item) {
				Ext.Msg.show({title:"안내",msg:"현장에 적용한 뒤, 변경사항은 자동으로 현장에 반영됩니다.<br />현재의 "+typeText+"를 현장에 반영하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
					if (button == "ok") {
						Ext.Ajax.request({
							url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php",
							success:function() {
								Ext.Msg.show({title:"안내",msg:"성공적으로 적용하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
								grid.getStore().load();
							},
							failure:function() {
								Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 적용하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
							},
							headers:{},
							params:{"action":"cost","do":"apply","idx":data.get("idx"),"type":type}
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
		title:"견적 및 계약관리",
		layout:"fit",
		tbar:[
			new Ext.form.ComboBox({
				id:"year",
				typeAhead:true,
				triggerAction:"all",
				lazyRender:true,
				store:new Ext.data.SimpleStore({
					fields:["year","display"],
					data:[<?php $year = array(); for ($i=2000, $loop=date('Y');$i<=$loop;$i++) $year[] = '["'.$i.'","'.$i.'년"]'; echo implode(',',$year); ?>]
				}),
				width:80,
				editable:false,
				mode:"local",
				displayField:"display",
				valueField:"year",
				emptyText:"년도별",
				listeners:{select:{fn:function(form) {
					Ext.getCmp("ListTab").getActiveTab().getStore().baseParams.year = form.getValue();
					Ext.getCmp("ListTab").getActiveTab().getStore().reload();
				}}}
			}),
			' ',
			new Ext.form.TextField({
				id:"keyword",
				width:120,
				emptyText:"검색어를 입력하세요.",
				enableKeyEvents:true,
				listeners:{keydown:{fn:function(form,e) {
					if (e.keyCode == 13) {
						Ext.getCmp("ListTab").getActiveTab().getStore().baseParams.keyword = Ext.getCmp("keyword").getValue();
						Ext.getCmp("ListTab").getActiveTab().getStore().reload();
					}
				}}}
			}),
			' ',
			new Ext.Button({
				text:"검색",
				icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_magnifier.png",
				handler:function() {
					Ext.getCmp("ListTab").getActiveTab().getStore().baseParams.keyword = Ext.getCmp("keyword").getValue();
					Ext.getCmp("ListTab").getActiveTab().getStore().reload();
				}
			}),
			'-',
			new Ext.Button({
				icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_building_add.png",
				text:"신규현장등록",
				handler:function() {
					WorkspaceFormFunction("add");
				}
			})
		],
		items:[
			new Ext.TabPanel({
				id:"ListTab",
				tabPosition:"bottom",
				activeTab:0,
				border:false,
				items:[
					new Ext.grid.GridPanel({
						id:"ListTab1",
						title:"공사현장",
						border:false,
						autoScroll:true,
						cm:WorkspaceListCm,
						store:WorkspaceListstore1,
						trackMouseOver:true,
						loadMask:{msg:"데이터를 로딩중입니다."},
						viewConfig:{forceFit:false},
						bbar:new Ext.PagingToolbar({
							pageSize:30,
							store:WorkspaceListstore1,
							displayInfo:true,
							displayMsg:'{0} - {1} of {2}',
							emptyMsg:"데이터가 없습니다."
						}),
						listeners:{
							rowcontextmenu:{fn:WorkspaceMenuFunction},
							rowdblclick:{fn:CostFunction}
						}
					}),
					new Ext.grid.GridPanel({
						id:"ListTab2",
						title:"견적현장",
						border:false,
						autoScroll:true,
						cm:WorkspaceListCm,
						store:WorkspaceListstore2,
						trackMouseOver:true,
						loadMask:{msg:"데이터를 로딩중입니다."},
						viewConfig:{forceFit:false},
						bbar:new Ext.PagingToolbar({
							pageSize:30,
							store:WorkspaceListstore2,
							displayInfo:true,
							displayMsg:'{0} - {1} of {2}',
							emptyMsg:"데이터가 없습니다."
						}),
						listeners:{
							rowcontextmenu:{fn:WorkspaceMenuFunction},
							rowdblclick:{fn:CostFunction}
						}
					}),
					new Ext.grid.GridPanel({
						id:"ListTab3",
						title:"완료현장",
						border:false,
						autoScroll:true,
						cm:WorkspaceListCm,
						store:WorkspaceListstore3,
						trackMouseOver:true,
						loadMask:{msg:"데이터를 로딩중입니다."},
						viewConfig:{forceFit:false},
						bbar:new Ext.PagingToolbar({
							pageSize:30,
							store:WorkspaceListstore3,
							displayInfo:true,
							displayMsg:'{0} - {1} of {2}',
							emptyMsg:"데이터가 없습니다."
						}),
						listeners:{
							rowcontextmenu:{fn:WorkspaceMenuFunction},
							rowdblclick:{fn:CostFunction}
						}
					})
				],
				listeners:{tabchange:{fn:function(tabs,tab) {
					Ext.getCmp(tab.getId()).getStore().load({params:{start:0,limit:30}});
				}}}
			})
		]
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>