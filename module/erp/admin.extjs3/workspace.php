<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/module/uploader/script/AzUploader.js"></script>
<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	var WorkspaceListstore1 = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:[{name:"idx",type:"int"},"title","orderer","workstart_date","workend_date","master","telephone",{name:"workpercent",type:"float"},{name:"totalarea",type:"float"},"type",{name:"worker",type:"int"},"estimate","contract","exec"]
		}),
		remoteSort:true,
		sortInfo:{field:"title",direction:"ASC"},
		baseParams:{"action":"workspace","get":"list","category":"working"}
	});

	var WorkspaceListstore2 = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:[{name:"idx",type:"int"},"title","orderer","workstart_date","workend_date","master","telephone",{name:"workpercent",type:"float"},{name:"totalarea",type:"float"},"type",{name:"worker",type:"int"},"estimate","contract","exec"]
		}),
		remoteSort:true,
		sortInfo:{field:"title",direction:"ASC"},
		baseParams:{"action":"workspace","get":"list","category":"estimate"}
	});

	var WorkspaceListstore3 = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:[{name:"idx",type:"int"},"title","orderer","workstart_date","workend_date","master","telephone",{name:"workpercent",type:"float"},{name:"totalarea",type:"float"},"type",{name:"worker",type:"int"},"estimate","contract","exec"]
		}),
		remoteSort:true,
		sortInfo:{field:"title",direction:"ASC"},
		baseParams:{"action":"workspace","get":"list","category":"end","year":"<?php echo date('Y'); ?>"}
	});

	var WorkspaceListstore4 = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:[{name:"idx",type:"int"},"title","orderer","workstart_date","workend_date","master","telephone",{name:"workpercent",type:"float"},{name:"totalarea",type:"float"},"type",{name:"worker",type:"int"},"estimate","contract","exec"]
		}),
		remoteSort:true,
		sortInfo:{field:"title",direction:"ASC"},
		baseParams:{"action":"workspace","get":"list","category":"backup","year":"<?php echo date('Y'); ?>"}
	});

	// 평단위환산
	function BlurPyung(form) {
		if (form.calcValue == parseFloat(form.getValue().replace(",",""))) return;

		if (form.getName().indexOf("_pyung") == -1) {
			var value = GetNumberFormat(Math.round(parseFloat(form.getValue().replace(",",""))/3.3058*100)/100);
			Ext.getCmp("WorkspaceForm").getForm().findField(form.getName()+"_pyung").setValue(value);
			Ext.getCmp("WorkspaceForm").getForm().findField(form.getName()+"_pyung").calcValue = value;
		} else {
			var value = GetNumberFormat(Math.round(parseFloat(form.getValue().replace(",",""))*3.3058*100)/100);
			Ext.getCmp("WorkspaceForm").getForm().findField(form.getName().replace("_pyung","")).setValue(value);
			Ext.getCmp("WorkspaceForm").getForm().findField(form.getName().replace("_pyung","")).calcValue = value;
		}

		BlurNumberFormat(form);
	}

	// 현장등록 및 수정
	function WorkspaceFunction(idx) {
		new Ext.Window({
			id:"WorkspaceWindow",
			title:(idx ? "현장정보수정" : "신규현장등록"),
			width:600,
			height:500,
			minWidth:600,
			minHeight:400,
			modal:true,
			maximizable:true,
			layout:"fit",
			style:"background:#FFFFFF;",
			items:[
				new Ext.form.FormPanel({
					id:"WorkspaceForm",
					labelAlign:"right",
					labelWidth:85,
					border:false,
					autoWidth:true,
					autoScroll:true,
					errorReader:new Ext.form.XmlErrorReader(),
					reader:new Ext.data.XmlReader(
						{record:"form",success:"@success",errormsg:"@errormsg"},
						["title","orderer","contract_date","workstart_date","workend_date","area","totalarea","size","structure","buildarea","buildingcoverage","buildpercent","purpose","zone","zipcode","address1","address2","telephone","master","master_view","architects","status"]
					),
					items:[
						new Ext.form.FieldSet({
							defaults:{msgTarget:"side"},
							title:"기본정보",
							autoWidth:true,
							autoHeight:true,
							style:"margin:10px",
							items:[
								new Ext.form.TextField({
									fieldLabel:"현장명",
									name:"title",
									width:190,
									allowBlank:false
								}),
								new Ext.form.TextField({
									fieldLabel:"발주처",
									name:"orderer",
									width:190,
									allowBlank:false
								}),
								new Ext.form.DateField({
									fieldLabel:"계약일자",
									format:"Y-m-d",
									name:"contract_date",
									width:100
								}),
								new Ext.form.DateField({
									fieldLabel:"공사시작일",
									format:"Y-m-d",
									name:"workstart_date",
									width:100
								}),
								new Ext.form.DateField({
									fieldLabel:"공사종료일",
									format:"Y-m-d",
									name:"workend_date",
									width:100
								}),
								new Ext.form.Hidden({
									name:"status"
								})
							]
						}),
						new Ext.form.FieldSet({
							defaults:{msgTarget:"side"},
							title:"현장정보",
							autoWidth:true,
							autoHeight:true,
							style:"margin:10px",
							items:[
								new Ext.form.CompositeField({
									labelWidth:85,
									labelAlign:"right",
									fieldLabel:"대지면적",
									width:300,
									items:[
										new Ext.form.TextField({
											name:"area",
											width:100,
											style:"text-align:right;",
											allowBlank:true,
											enableKeyEvents:true,
											listeners:{
												keydown:{fn:PressNumberOnly},
												blur:{fn:BlurPyung},
												focus:{fn:FocusNumberOnly}
											}
										}),
										new Ext.form.DisplayField({
											html:"㎡, "
										}),
										new Ext.form.TextField({
											name:"area_pyung",
											width:100,
											style:"text-align:right;",
											allowBlank:true,
											enableKeyEvents:true,
											listeners:{
												keydown:{fn:PressNumberOnly},
												blur:{fn:BlurPyung},
												focus:{fn:FocusNumberOnly}
											}
										}),
										new Ext.form.DisplayField({
											width:50,
											html:"평"
										})
									]
								}),
								new Ext.form.TextField({
									fieldLabel:"건물규모",
									name:"size",
									width:400,
									allowBlank:true
								}),
								new Ext.form.TextField({
									fieldLabel:"건축구조",
									name:"structure",
									width:400,
									allowBlank:true
								}),
								new Ext.form.CompositeField({
									labelWidth:85,
									labelAlign:"right",
									fieldLabel:"건축면적",
									width:300,
									items:[
										new Ext.form.TextField({
											fieldLabel:"건축면적",
											name:"buildarea",
											width:100,
											style:"text-align:right;",
											allowBlank:true,
											enableKeyEvents:true,
											listeners:{
												keydown:{fn:PressNumberOnly},
												blur:{fn:BlurPyung},
												focus:{fn:FocusNumberOnly}
											}
										}),
										new Ext.form.DisplayField({
											html:"㎡, "
										}),
										new Ext.form.TextField({
											fieldLabel:"건축면적",
											name:"buildarea_pyung",
											width:100,
											style:"text-align:right;",
											allowBlank:true,
											enableKeyEvents:true,
											listeners:{
												keydown:{fn:PressNumberOnly},
												blur:{fn:BlurPyung},
												focus:{fn:FocusNumberOnly}
											}
										}),
										new Ext.form.DisplayField({
											width:50,
											html:"평"
										})
									]
								}),
								new Ext.form.CompositeField({
									labelWidth:85,
									labelAlign:"right",
									fieldLabel:"연면적",
									width:300,
									items:[
										new Ext.form.TextField({
											fieldLabel:"연면적",
											name:"totalarea",
											width:100,
											style:"text-align:right;",
											allowBlank:true,
											enableKeyEvents:true,
											listeners:{
												keydown:{fn:PressNumberOnly},
												blur:{fn:BlurPyung},
												focus:{fn:FocusNumberOnly}
											}
										}),
										new Ext.form.DisplayField({
											html:"㎡, "
										}),
										new Ext.form.TextField({
											fieldLabel:"연면적",
											name:"totalarea_pyung",
											width:100,
											style:"text-align:right;",
											allowBlank:true,
											enableKeyEvents:true,
											listeners:{
												keydown:{fn:PressNumberOnly},
												blur:{fn:BlurPyung},
												focus:{fn:FocusNumberOnly}
											}
										}),
										new Ext.form.DisplayField({
											width:50,
											html:"평"
										})
									]
								}),
								new Ext.form.TextField({
									fieldLabel:"건폐율(%)",
									name:"buildingcoverage",
									width:100,
									style:"text-align:right;",
									emptyText:"%",
									allowBlank:true,
									enableKeyEvents:true,
									listeners:{
										keydown:{fn:PressNumberOnly},
										blur:{fn:BlurNumberFormat},
										focus:{fn:FocusNumberOnly}
									}
								}),
								new Ext.form.TextField({
									fieldLabel:"용적률(%)",
									name:"buildpercent",
									width:100,
									style:"text-align:right;",
									emptyText:"%",
									allowBlank:true,
									enableKeyEvents:true,
									listeners:{
										keydown:{fn:PressNumberOnly},
										blur:{fn:BlurNumberFormat},
										focus:{fn:FocusNumberOnly}
									}
								}),
								new Ext.form.TextField({
									fieldLabel:"건물용도",
									name:"purpose",
									width:400,
									allowBlank:true
								}),
								new Ext.form.TextField({
									fieldLabel:"지역/지구",
									name:"zone",
									width:400,
									allowBlank:true
								}),
								new Ext.form.TextField({
									fieldLabel:"설계사무소",
									name:"architects",
									width:400,
									allowBlank:true
								})
							]
						}),
						FormAddressFieldSet("WorkspaceForm"),
						new Ext.form.FieldSet({
							defaults:{msgTarget:"side"},
							title:"관리정보",
							autoHeight:true,
							autoWidth:true,
							layout:"table",
							layoutConfig:{columns:2},
							style:"margin:10px;",
							items:[
								{
									colspan:2,
									border:false,
									layout:"form",
									items:[
										new Ext.form.TextField({
											fieldLabel:"현장연락처",
											name:"telephone",
											style:"padding-top:2px;",
											width:200,
											emptyText:"'-' 는 제외하고 입력하세요.",
											listeners:{
												blur:{fn:BlurTelephoneFormat},
												focus:{fn:FocusNumberOnly}
											}
										})
									]
								},{
									border:false,
									width:290,
									layout:"form",
									items:[
										new Ext.form.Hidden({
											name:"master",
											allowBlank:true
										}),
										new Ext.form.TextField({
											fieldLabel:"현장소장",
											name:"master_view",
											width:200,
											readOnly:true,
											disabled:true,
											allowBlank:true
										})
									]
								},{
									border:false,
									width:95,
									style:"padding-left:5px; padding-bottom:4px;",
									items:[
										new Ext.Button({
											text:"현장소장검색",
											handler:function() {
												var WorkSpaceMasterSearchStore = new Ext.data.Store({
													proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/exec/Extjs.get.php"}),
													reader:new Ext.data.JsonReader({
														root:"lists",
														totalProperty:"totalCount",
														fields:["idx","name","nickname","user_id"]
													}),
													remoteSort:true,
													sortInfo:{field:"name",direction:"ASC"},
													baseParams:{action:"member",keyword:""}
												});

												new Ext.Window({
													id:"WorkspaceMasterWindow",
													title:"현장소장검색",
													width:520,
													height:400,
													modal:true,
													resizable:false,
													layout:"border",
													items:[
														new Ext.grid.GridPanel({
															id:"WorkspaceMasterMember",
															region:"west",
															title:"회원검색",
															width:335,
															margins:"5 5 5 5",
															cm:new Ext.grid.ColumnModel([
																new Ext.grid.CheckboxSelectionModel(),
																{
																	dataIndex:"idx",
																	hidden:true,
																	hideable:false
																},{
																	header:"이름",
																	dataIndex:"name",
																	sortable:true,
																	width:70
																},{
																	header:"닉네임",
																	dataIndex:"nickname",
																	sortable:true,
																	width:110
																},{
																	header:"아이디",
																	dataIndex:"user_id",
																	sortable:true,
																	width:110
																}
															]),
															sm:new Ext.grid.CheckboxSelectionModel(),
															store:WorkSpaceMasterSearchStore,
															tbar:[
																new Ext.form.TextField({
																	id:"WorkSpaceMasterSearchText",
																	width:150,
																	emptyText:"검색어를 입력하세요.",
																	enableKeyEvents:true,
																	listeners:{keydown:{fn:function(form,e) {
																		if (e.keyCode == 13) {
																			if (!Ext.getCmp("WorkSpaceMasterSearchText").getValue()) {
																				Ext.Msg.show({title:"에러",msg:"검색어(이름,닉네임,아이디)를 입력하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING,fn:function(){Ext.getCmp("WorkSpaceMasterSearchText").getValue().focus();}});
																				return false;
																			}
																			WorkSpaceMasterSearchStore.baseParams.keyword = Ext.getCmp("WorkSpaceMasterSearchText").getValue();
																			WorkSpaceMasterSearchStore.load({params:{start:0,limit:30}});
																			e.stopEvent();
																		}
																	}}}
																}),
																' ',
																new Ext.Button({
																	icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_magnifier.png",
																	text:"검색",
																	handler:function() {
																		if (!Ext.getCmp("WorkSpaceMasterSearchText").getValue()) {
																			Ext.Msg.show({title:"에러",msg:"검색어(이름,닉네임,아이디)를 입력하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING,fn:function(){Ext.getCmp("WorkSpaceMasterSearchText").getValue().focus();}});
																			return false;
																		}
																		WorkSpaceMasterSearchStore.baseParams.keyword = Ext.getCmp("WorkSpaceMasterSearchText").getValue();
																		WorkSpaceMasterSearchStore.load({params:{start:0,limit:30}});
																	}
																}),
																new Ext.Button({
																	icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_magifier_out.png",
																	text:"취소",
																	handler:function() {
																		Ext.getCmp("WorkSpaceMasterSearchText").setValue("");
																		WorkSpaceMasterSearchStore.baseParams.keyword = "";
																		WorkSpaceMasterSearchStore.load({params:{start:0,limit:30}});
																	}
																}),
																'->',
																'-',
																new Ext.Button({
																	icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_arrow_right.png",
																	iconAlign:"right",
																	text:"추가",
																	handler:function() {
																		var checked = Ext.getCmp("WorkspaceMasterMember").selModel.getSelections();
																		if (checked.length == 0) {
																			Ext.Msg.show({title:"에러",msg:"추가할 대상을 선택하세요.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
																			return false;
																		}

																		var record = Ext.data.Record.create([{name:"idx",type:"int"},{name:"name",type:"string"}]);
																		for (var i=0, loop=checked.length;i<loop;i++) {
																			if (Ext.getCmp("WorkspaceMasterList").getStore().find("idx",checked[i].get("idx"),0,false,false) == -1) {
																				Ext.getCmp("WorkspaceMasterList").getStore().add(new record({idx:checked[i].get("idx"),name:checked[i].get("name")}));
																			}
																		}
																		Ext.getCmp("WorkspaceMasterList").getStore().sort("name","ASC");
																	}
																})
															],
															bbar:new Ext.PagingToolbar({
																pageSize:30,
																store:WorkSpaceMasterSearchStore,
																displayInfo:true,
																displayMsg:'{0} - {1} of {2}',
																emptyMsg:"데이터없음"
															})
														}),
														new Ext.grid.GridPanel({
															id:"WorkspaceMasterList",
															margins:"5 5 5 0",
															region:"center",
															title:"등록된 현장소장목록",
															tbar:[
																new Ext.Button({
																	icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_arrow_left.png",
																	text:"삭제",
																	handler:function() {
																		var checked = Ext.getCmp("WorkspaceMasterList").selModel.getSelections();
																		if (checked.length == 0) {
																			Ext.Msg.show({title:"에러",msg:"삭제할 대상을 선택하세요.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
																			return false;
																		}

																		for (var i=0, loop=checked.length;i<loop;i++) {
																			Ext.getCmp("WorkspaceMasterList").getStore().remove(checked[i]);
																		}
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
																	header:"이름",
																	dataIndex:"name",
																	sortable:true,
																	width:110
																}
															]),
															sm:new Ext.grid.CheckboxSelectionModel(),
															store:new Ext.data.SimpleStore({
																fields:["idx","name"],
																data:[],
																sortInfo:{field:"name",direction:"ASC"}
															}),
															bbar:[
																'->',
																new Ext.Button({
																	icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_tick.png",
																	text:"확인",
																	handler:function() {
																		var data = Ext.getCmp("WorkspaceMasterList").getStore();

																		if (data.getCount() == 0) {
																			Ext.Msg.show({title:"에러",msg:"등록된 현장소장이 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
																			return false;
																		}

																		var workmasterValue = new Array();
																		var workmasterName = new Array();
																		for (var i=0, loop=data.getCount();i<loop;i++) {
																			workmasterValue.push(data.getAt(i).get("idx"));
																			workmasterName.push(data.getAt(i).get("name"));
																		}
																		workmasterValue = workmasterValue.join(",");
																		workmasterName.join(",");

																		Ext.getCmp("WorkspaceForm").getForm().findField("master").setValue(workmasterValue);
																		Ext.getCmp("WorkspaceForm").getForm().findField("master_view").setValue(workmasterName);

																		Ext.getCmp("WorkspaceMasterWindow").close();
																	}
																}),
																new Ext.Button({
																	icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
																	text:"취소",
																	handler:function() {
																		Ext.getCmp("WorkspaceMasterWindow").close();
																	}
																})
															]
														})
													],
													listeners:{show:{fn:function() {
														WorkSpaceMasterSearchStore.load({params:{start:0,limit:30}});
														if (Ext.getCmp("WorkspaceForm").getForm().findField("master").getValue()) {
															var master_idx = Ext.getCmp("WorkspaceForm").getForm().findField("master").getValue().split(",");
															var master_view = Ext.getCmp("WorkspaceForm").getForm().findField("master_view").getValue().split(",");
															var insert = new Array();
															for (var i=0, loop=master_idx.length;i<loop;i++) {
																insert[i] = {idx:master_idx[i],name:master_view[i]};
															}
															GridInsertRow(Ext.getCmp("WorkspaceMasterList"),insert);
															Ext.getCmp("WorkspaceMasterList").getStore().sort("name","ASC");
														}
													}}}
												}).show();
											}
										})
									]
								}
							]
						})
					],
					listeners:{
						actioncomplete:{fn:function(form,action) {
							if (action.type == "submit") {
								Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO,fn:function() {
									var status = Ext.getCmp("WorkspaceForm").getForm().findField("status").getValue();
									var contract_date = Ext.getCmp("WorkspaceForm").getForm().findField("contract_date").getValue();
									if (!status) {
										if (!contract_date) {
											Ext.getCmp("ListTab2").getStore().reload();
											Ext.getCmp("ListTab").setActiveTab(Ext.getCmp("ListTab2"));
										} else {
											Ext.getCmp("ListTab1").getStore().reload();
											Ext.getCmp("ListTab").setActiveTab(Ext.getCmp("ListTab1"));
										}
									} else if (status == "ESTIMATE") {
										if (contract_date) {
											Ext.getCmp("ListTab1").getStore().reload();
											Ext.getCmp("ListTab2").getStore().reload();
											Ext.getCmp("ListTab").setActiveTab(Ext.getCmp("ListTab1"));
										} else {
											Ext.getCmp("ListTab2").getStore().reload();
										}
									} else if (status == "WORKING") {
										if (!contract_date) {
											Ext.getCmp("ListTab1").getStore().reload();
											Ext.getCmp("ListTab2").getStore().reload();
											Ext.getCmp("ListTab").setActiveTab(Ext.getCmp("ListTab2"));
										} else {
											Ext.getCmp("ListTab1").getStore().reload();
										}
									} else {
										Ext.getCmp("ListTab").getActiveTab().getStore().reload();
									}
									Ext.getCmp("WorkspaceWindow").close();
								}});
							}

							if (action.type == "load") {
								BlurPyung(form.findField("area"));
								BlurPyung(form.findField("buildarea"));
								BlurPyung(form.findField("totalarea"));
							}
						}}
					}
				})
			],
			buttons:[
				new Ext.Button({
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_tick.png",
					text:"확인",
					handler:function() {
						if (idx) {
							Ext.getCmp("WorkspaceForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php?action=workspace&do=modify&idx="+idx,waitMsg:"현장정보를 수정중입니다."});
						} else {
							Ext.getCmp("WorkspaceForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php?action=workspace&do=add",waitMsg:"현장을 추가중입니다."});
						}
					}
				}),
				new Ext.Button({
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
					text:"취소",
					handler:function() {
						Ext.getCmp("WorkspaceWindow").close();
					}
				})
			],
			listeners:{show:{fn:function() {
				if (idx) {
					Ext.getCmp("WorkspaceForm").load({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php?action=workspace&get=data&idx="+idx,waitMsg:"정보를 로딩중입니다."});
				}
			}}}
		}).show();
	}

	// 현장소장 변동 내역
	function WorkspaceMaster(wno) {
		new Ext.Window({
			id:"WorkspaceMasterWindow",
			title:"현장소장변동내역",
			width:500,
			height:400,
			minWidth:500,
			minHeight:300,
			modal:true,
			layout:"fit",
			style:"background:#FFFFFF;",
			items:[
				new Ext.grid.GridPanel({
					id:"WorkspaceMasterList",
					border:false,
					defaults:{menuDisabled:true},
					cm:new Ext.grid.ColumnModel([
						new Ext.grid.CheckboxSelectionModel(),
						{
							header:"변경일",
							dataIndex:"reg_date",
							width:130
						},{
							header:"현장소장",
							dataIndex:"master",
							width:210
						},{
							header:"변경자",
							dataIndex:"register",
							width:100
						}
					]),
					store:new Ext.data.Store({
						proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
						reader:new Ext.data.JsonReader({
							root:"lists",
							totalProperty:"totalCount",
							fields:["idx","reg_date","master","register"]
						}),
						remoteSort:false,
						sortInfo:{field:"reg_date",direction:"desc"},
						baseParams:{"action":"workspace","get":"master","wno":wno},
					})
				})
			],
			listeners:{show:{fn:function() {
				Ext.getCmp("WorkspaceMasterList").getStore().load();
			}}}
		}).show();
	}

	// 현장메뉴
	function MenuFunction(grid,idx,e) {
		GridContextmenuSelect(grid,idx);
		var data = grid.getStore().getAt(idx);

		var menu = new Ext.menu.Menu();
		menu.add('<b class="menu-title">'+data.get("title")+'</b>');
		menu.add({
			text:"현장정보수정",
			icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_building_edit.png",
			handler:function(item) {
				WorkspaceFunction(data.get("idx"));
			}
		});
		if (grid.getId() == "ListTab1") {
			menu.add({
				text:"현장사진등록/관리",
				icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_picture.png",
				handler:function(item) {
					WorkspacePhotoFunction(data.get("idx"));
				}
			});
		}

		menu.add({
			text:"공정관리",
			icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_package.png",
			handler:function(item) {
				WorkGroupSetup(data.get("idx"));
			}
		});

		var ContractMenu = new Ext.menu.Menu();
		if (grid.getId() != "ListTab1" && grid.getId() != "ListTab3") {
			ContractMenu.add({
				text:"견적내역서",
				icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_page_white_paste.png",
				handler:function(item) {
					if (data.get("estimate") == "0") {
						Ext.Msg.show({title:"에러",msg:"등록된 견적내역서가 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
					} else {
						CostView("ESTIMATE",data.get("estimate"),data.get("idx"),"견적내역서");
					}
				}
			});
		}
		ContractMenu.add({
			text:"실행내역서",
			icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_page_white_paste.png",
			handler:function(item) {
				if (data.get("cost") == "0") {
					Ext.Msg.show({title:"에러",msg:"등록된 실행내역서가 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
				} else {
					CostView("EXEC",data.get("exec"),data.get("idx"),"실행내역서");
				}
			}
		});
		ContractMenu.add({
			text:"계약내역서",
			icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_page_white_paste.png",
			handler:function(item) {
				if (data.get("contract") == "0") {
					Ext.Msg.show({title:"에러",msg:"등록된 계약내역서가 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
				} else {
					CostView("CONTRACT",data.get("contract"),data.get("idx"),"계약내역서");
				}
			}
		});

		menu.add({
			text:"현장계약관리",
			icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_page_white_paste.png",
			handler:function() {
				CostFunction(data.get("idx"),data.get("title"));
			},
			menu:ContractMenu
		});

		menu.add({
			text:"현장소장변동내역",
			icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_user_go.png",
			handler:function() {
				WorkspaceMaster(data.get("idx"));
			}
		});

		if (grid.getId() == "ListTab1") {
			menu.add({
				text:"현장관리프로그램 실행",
				icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_building_go.png",
				handler:function(item) {
					window.open('<?php echo $_ENV['dir']; ?>/module/erp/workspace.php?wno='+data.get("idx")+'&mode=manager');
				}
			});
		}

		menu.add('-');
		if (grid.getId() == "ListTab1") {
			menu.add({
				text:"준공현장으로 변경",
				icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_stop.png",
				handler:function(item) {
					Ext.Msg.show({title:"안내",msg:"공사중인 현장의 상태를 변경하면, 더이상 현장관리프로그램을 사용할 수 없습니다.<br />현장의 상태를 변경하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
						if (button == "ok") {
							Ext.Msg.wait("처리중입니다.","Please Wait...");
							Ext.Ajax.request({
								url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php",
								success:function() {
									Ext.Msg.show({title:"안내",msg:"성공적으로 처리하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
									Ext.getCmp("ListTab").getActiveTab().getStore().reload();
									Ext.getCmp("ListTab3").getStore().reload();
								},
								failure:function() {
									Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
								},
								headers:{},
								params:{"action":"workspace","do":"status","type":"END","idx":data.get("idx")}
							});
						}
					}});
				}
			});
		}
		if (grid.getId() != "ListTab2") {
			menu.add({
				text:"견적현장으로 변경",
				icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_paste_plain.png",
				handler:function(item) {
					Ext.Msg.show({title:"안내",msg:"공사중인 현장의 상태를 변경하면, 더이상 현장관리프로그램을 사용할 수 없습니다.<br />현장의 상태를 변경하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
						if (button == "ok") {
							Ext.Msg.wait("처리중입니다.","Please Wait...");
							Ext.Ajax.request({
								url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php",
								success:function() {
									Ext.Msg.show({title:"안내",msg:"성공적으로 처리하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
									Ext.getCmp("ListTab").getActiveTab().getStore().reload();
									Ext.getCmp("ListTab2").getStore().reload();
								},
								failure:function() {
									Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
								},
								headers:{},
								params:{"action":"workspace","do":"status","type":"ESTIMATE","idx":data.get("idx")}
							});
						}
					}});
				}
			});
		}
		if (grid.getId() == "ListTab2") {
			menu.add({
				text:"미계약현장으로 변경",
				icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_paste_plain.png",
				handler:function(item) {
					Ext.Msg.show({title:"안내",msg:"미계약현장으로 변경하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
						if (button == "ok") {
							Ext.Msg.wait("처리중입니다.","Please Wait...");
							Ext.Ajax.request({
								url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php",
								success:function() {
									Ext.Msg.show({title:"안내",msg:"성공적으로 처리하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
									Ext.getCmp("ListTab").getActiveTab().getStore().reload();
									Ext.getCmp("ListTab4").getStore().reload();
								},
								failure:function() {
									Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
								},
								headers:{},
								params:{"action":"workspace","do":"status","type":"BACKUP","idx":data.get("idx")}
							});
						}
					}});
				}
			});
		}
		menu.add('-');
		menu.add({
			text:"현장삭제",
			icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
			handler:function(item) {
				Ext.Msg.show({title:"안내",msg:"현장을 삭제하면 해당현장의 모든 데이터가 삭제됩니다.<br />현장을 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
					if (button == "ok") {
						Ext.Msg.wait("처리중입니다.","Please Wait...");
						Ext.Ajax.request({
							url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php",
							success:function() {
								Ext.Msg.show({title:"안내",msg:"성공적으로 처리하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
								Ext.getCmp("ListTab").getActiveTab().getStore().reload();
							},
							failure:function() {
								Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
							},
							headers:{},
							params:{"action":"workspace","do":"delete","idx":data.get("idx")}
						});
					}
				}});
			}
		});
		e.stopEvent();
		menu.showAt(e.getXY());
	}
	// 현장 동관리
	function BuildTypeSetup(wno) {
		new Ext.Window({
			title:"동관리",
			modal:true,
			width:300,
			height:200,
			layout:"fit",
			items:[
				new Ext.grid.GridPanel({
					id:"BuildTypeSetupList",
					border:false,
					tbar:[
						new Ext.Button({
							text:"건설동추가",
							icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_building_add.png",
							handler:function() {
								new Ext.Window({
									id:"BuildTypeSetupAddTypeWindow",
									title:"건설동추가",
									width:300,
									height:110,
									layout:"fit",
									modal:true,
									resizable:false,
									items:[
										new Ext.form.FormPanel({
											id:"BuildTypeSetupAddTypeForm",
											border:false,
											style:"padding:10px; background:#FFFFFF;",
											labelAlign:"right",
											labelWidth:65,
											autoWidth:true,
											errorReader:new Ext.form.XmlErrorReader(),
											items:[
												new Ext.form.TextField({
													fieldLabel:"건설동명",
													width:180,
													name:"buildtype",
													allowBlank:false
												})
											],
											listeners:{actioncomplete:{fn:function(form,action) {
												if (action.type == "submit") {
													Ext.Msg.show({title:"안내",msg:"성공적으로 추가하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
													Ext.getCmp("BuildTypeSetupList").getStore().reload();
													Ext.getCmp("BuildTypeSetupAddTypeWindow").close();
												}
											}}}
										})
									],
									buttons:[
										new Ext.Button({
											text:"확인",
											icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_tick.png",
											handler:function() {
												Ext.getCmp("BuildTypeSetupAddTypeForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php?action=workspace&do=buildtype&mode=add&wno="+wno,waitMsg:"건설동을 추가중입니다."});
											}
										}),
										new Ext.Button({
											text:"취소",
											icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
											handler:function() {
												Ext.getCmp("BuildTypeSetupAddTypeWindow").close();
											}
										})
									]
								}).show();
							}
						}),
						new Ext.Button({
							text:"건설동삭제",
							icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_building_delete.png",
							handler:function() {
								var checked = Ext.getCmp("BuildTypeSetupList").selModel.getSelections();

								if (checked.length == 0) {
									Ext.Msg.show({title:"에러",msg:"삭제할 건설동을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
									return false;
								}

								var idxs = new Array();
								for (var i=0, loop=checked.length;i<loop;i++) {
									idxs[i] = checked[i].get("idx");
								}
								var idx = idxs.join(",");

								Ext.Msg.show({title:"안내",msg:"건설동을 삭제하면, 공정의 건설동별분류가 초기화됩니다.<br />건설동을 정말 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
									if (button == "ok") {
										Ext.Msg.wait("처리중입니다.","Please Wait...");
										Ext.Ajax.request({
											url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php",
											success:function() {
												Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
												Ext.getCmp("BuildTypeSetupList").getStore().reload();
											},
											failure:function() {
												Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 삭제하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
											},
											headers:{},
											params:{"action":"workspace","do":"buildtype","mode":"delete","wno":wno,"idx":idx}
										});
									}
								}});
							}
						})
					],
					defaults:{menuDisabled:true},
					cm:new Ext.grid.ColumnModel([
						new Ext.grid.CheckboxSelectionModel(),
						{
							dataIndex:"idx",
							hideable:false,
							hidden:true
						},{
							header:"건설동",
							dataIndex:"buildtype",
							width:250
						}
					]),
					sm:new Ext.grid.CheckboxSelectionModel(),
					store:new Ext.data.Store({
						proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
						reader:new Ext.data.JsonReader({
							root:"lists",
							totalProperty:"totalCount",
							fields:["idx","buildtype"]
						}),
						remoteSort:false,
						sortInfo:{field:"buildtype",direction:"ASC"},
						baseParams:{"action":"workspace","get":"buildtype","wno":wno},
					})
				})
			],
			listeners:{show:{fn:function() {
				Ext.getCmp("BuildTypeSetupList").getStore().load();
			}}}
		}).show();
	}

	// 현장 공정관리
	function WorkGroupSetup(wno) {
		new Ext.Window({
			title:"공정관리",
			modal:true,
			width:650,
			height:400,
			layout:"fit",
			items:[
				new Ext.Panel({
					border:false,
					layout:"border",
					items:[
						new Ext.grid.GridPanel({
							id:"WorkgroupSetupList1",
							title:"공정",
							region:"west",
							width:285,
							split:true,
							layout:"fit",
							margins:"5 0 5 5",
							tbar:[
								new Ext.Button({
									text:"동관리",
									icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_building_key.png",
									handler:function() {
										BuildTypeSetup(wno);
									}
								}),
								'|',
								new Ext.Button({
									text:"공정추가",
									icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_package_add.png",
									handler:function() {
										new Ext.Window({
											id:"WorkgroupSetupAddGroupWindow",
											title:"공정추가하기",
											width:300,
											height:190,
											layout:"fit",
											modal:true,
											resizable:false,
											items:[
												new Ext.form.FormPanel({
													id:"WorkgroupSetupAddGroupForm",
													border:false,
													style:"padding:10px; background:#FFFFFF;",
													labelAlign:"right",
													labelWidth:65,
													autoWidth:true,
													errorReader:new Ext.form.XmlErrorReader(),
													items:[
														new Ext.form.ComboBox({
															fieldLabel:"공정분류",
															width:180,
															hiddenName:"bgno",
															typeAhead:true,
															lazyRender:false,
															triggerAction:"all",
															store:new Ext.data.Store({
																proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
																reader:new Ext.data.JsonReader({
																	root:"lists",
																	totalProperty:"totalCount",
																	fields:["idx","workgroup","sort"]
																}),
																remoteSort:false,
																sortInfo:{field:"sort",direction:"ASC"},
																baseParams:{"action":"base","get":"workgroup"}
															}),
															editable:false,
															mode:"local",
															displayField:"workgroup",
															valueField:"idx",
															emptyText:"공정분류를 선택하세요.",
															listeners:{render:{fn:function(form) {
																form.getStore().load();
															}}}
														}),
														new Ext.form.ComboBox({
															fieldLabel:"건설동",
															width:180,
															hiddenName:"btno",
															typeAhead:true,
															lazyRender:false,
															triggerAction:"all",
															store:new Ext.data.Store({
																proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
																reader:new Ext.data.JsonReader({
																	root:"lists",
																	totalProperty:"totalCount",
																	fields:["idx","buildtype","sort"]
																}),
																remoteSort:false,
																sortInfo:{field:"sort",direction:"ASC"},
																baseParams:{"action":"workspace","get":"buildtype","is_notselect":"true","wno":wno}
															}),
															editable:false,
															mode:"local",
															displayField:"buildtype",
															valueField:"idx",
															emptyText:"건설동을 선택하세요.",
															listeners:{render:{fn:function(form) {
																form.getStore().load();
															}}}
														}),
														new Ext.form.TextField({
															fieldLabel:"공정명",
															width:180,
															name:"workgroup",
															allowBlank:false
														}),
														new Ext.ux.form.SpinnerField({
															fieldLabel:"정렬순서",
															width:180,
															minValue:1,
															maxValue:99,
															name:"sort",
															value:1
														})
													],
													listeners:{actioncomplete:{fn:function(form,action) {
														if (action.type == "submit") {
															Ext.Msg.show({title:"안내",msg:"성공적으로 추가하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
															Ext.getCmp("WorkgroupSetupList1").getStore().reload();
															Ext.getCmp("WorkgroupSetupList1").getStore().baseParams.gno = "";
															Ext.getCmp("WorkgroupSetupList2").getStore().reload();
															Ext.getCmp("WorkgroupSetupAddGroupWindow").close();
														}
													}}}
												})
											],
											buttons:[
												new Ext.Button({
													text:"확인",
													icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_tick.png",
													handler:function() {
														Ext.getCmp("WorkgroupSetupAddGroupForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php?action=workspace&do=workgroup&mode=add&wno="+wno,waitMsg:"공정을 추가중입니다."});
													}
												}),
												new Ext.Button({
													text:"취소",
													icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
													handler:function() {
														Ext.getCmp("WorkgroupSetupAddGroupWindow").close();
													}
												})
											]
										}).show();
									}
								}),
								new Ext.Button({
									text:"공정삭제",
									icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_package_delete.png",
									handler:function() {
										var checked = Ext.getCmp("WorkgroupSetupList1").selModel.getSelections();

										if (checked.length == 0) {
											Ext.Msg.show({title:"에러",msg:"삭제할 공정을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
											return false;
										}

										var idxs = new Array();
										for (var i=0, loop=checked.length;i<loop;i++) {
											idxs[i] = checked[i].get("idx");
										}
										var idx = idxs.join(",");

										Ext.Msg.show({title:"안내",msg:"공정을 삭제하면, 하위 공정의 모든 데이터가 삭제됩니다.<br />공정을 정말 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
											if (button == "ok") {
												Ext.Ajax.request({
													url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php",
													success:function() {
														Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
														Ext.getCmp("WorkgroupSetupList1").getStore().reload();
														Ext.getCmp("WorkgroupSetupList1").getStore().baseParams.gno = "";
														Ext.getCmp("WorkgroupSetupList2").getStore().reload();
														Ext.getCmp("WorkgroupSetupModifyGroupWindow").close();
													},
													failure:function() {
														Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 삭제하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
													},
													headers:{},
													params:{"action":"workspace","do":"workgroup","mode":"delete","wno":wno,"idx":idx}
												});
											}
										}});
									}
								})
							],
							defaults:{menuDisabled:true},
							cm:new Ext.grid.ColumnModel([
								new Ext.grid.CheckboxSelectionModel(),
								{
									dataIndex:"bgno",
									hideable:false,
									renderer:function(value,p,record) {
										return GetFixNumberLength(record.data.bgno,2)+"."+record.data.basegroup;
									}
								},{
									header:"정렬",
									dataIndex:"sort",
									width:50,
									sortable:false,
									renderer:function(value,p,record) {
										return GetFixNumberLength(record.data.bgno,2)+GetFixNumberLength(value,2);
									}
								},{
									header:"공정명",
									dataIndex:"workgroup",
									width:190,
									sortable:false,
									renderer:function(value,p,record) {
										if (record.data.buildtype) return '<span class="skyblue">['+record.data.buildtype+']</span>'+value;
										else return value;
									}
								}
							]),
							sm:new Ext.grid.CheckboxSelectionModel(),
							store:new Ext.data.GroupingStore({
								proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
								reader:new Ext.data.JsonReader({
									root:"lists",
									totalProperty:"totalCount",
									fields:["idx","bgno","btno","buildtype","basegroup","workgroup",{name:"sort",type:"int"}]
								}),
								remoteSort:false,
								groupField:"bgno",
								sortInfo:{field:"sort",direction:"ASC"},
								baseParams:{"action":"workspace","get":"workgroup","wno":wno},
							}),
							view:new Ext.grid.GroupingView({
								enableGroupingMenu:false,
								hideGroupedColumn:true,
								showGroupName:false,
								enableNoGroups:false,
								headersDisabled:false
							}),
							listeners:{
								render:{fn:function() {
									Ext.getCmp("WorkgroupSetupList1").getStore().load();
								}},
								rowclick:{fn:function(grid,row,e) {
									if (grid.selModel.getSelections().length == 1) {
										Ext.getCmp("WorkgroupSetupList2").getStore().baseParams.gno = grid.selModel.getSelections()[0].get("idx");
										Ext.getCmp("WorkgroupSetupList2Add").enable();
										Ext.getCmp("WorkgroupSetupList2Delete").enable();
									} else {
										Ext.getCmp("WorkgroupSetupList2").getStore().baseParams.gno = "";
										Ext.getCmp("WorkgroupSetupList2Add").disable();
										Ext.getCmp("WorkgroupSetupList2Delete").disable();
									}
									Ext.getCmp("WorkgroupSetupList2").getStore().reload();
								}},
								rowcontextmenu:{fn:function(grid,idx,e) {
									GridContextmenuSelect(grid,idx);

									var data = grid.getStore().getAt(idx);

									var menu = new Ext.menu.Menu();
									menu.add('<b class="menu-title">'+data.get("workgroup")+'</b>');
									menu.add({
										text:"공정수정",
										icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_package_edit.png",
										handler:function(item) {
											new Ext.Window({
												id:"WorkgroupSetupModifyGroupWindow",
												title:"공정수정",
												width:300,
												height:190,
												layout:"fit",
												modal:true,
												resizable:false,
												items:[
													new Ext.form.FormPanel({
														id:"WorkgroupSetupModifyGroupForm",
														border:false,
														style:"padding:10px; background:#FFFFFF;",
														labelAlign:"right",
														labelWidth:65,
														autoWidth:true,
														errorReader:new Ext.form.XmlErrorReader(),
														items:[
															new Ext.form.ComboBox({
																fieldLabel:"공정분류",
																width:180,
																hiddenName:"bgno",
																typeAhead:true,
																lazyRender:false,
																triggerAction:"all",
																store:new Ext.data.Store({
																	proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
																	reader:new Ext.data.JsonReader({
																		root:"lists",
																		totalProperty:"totalCount",
																		fields:["idx","workgroup","sort"]
																	}),
																	remoteSort:false,
																	sortInfo:{field:"sort",direction:"ASC"},
																	baseParams:{"action":"base","get":"workgroup"}
																}),
																editable:false,
																mode:"local",
																displayField:"workgroup",
																valueField:"idx",
																emptyText:"공정분류를 선택하세요.",
																listeners:{render:{fn:function(form) {
																	form.getStore().load();
																	form.getStore().on("load",function() {
																		form.setValue(data.get("bgno"));
																	});
																}}}
															}),
															new Ext.form.TextField({
																fieldLabel:"공정명",
																width:180,
																name:"workgroup",
																allowBlank:false,
																value:data.get("workgroup")
															}),
															new Ext.form.ComboBox({
																fieldLabel:"건설동",
																width:180,
																hiddenName:"btno",
																typeAhead:true,
																lazyRender:false,
																triggerAction:"all",
																store:new Ext.data.Store({
																	proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
																	reader:new Ext.data.JsonReader({
																		root:"lists",
																		totalProperty:"totalCount",
																		fields:["idx","buildtype","sort"]
																	}),
																	remoteSort:false,
																	sortInfo:{field:"sort",direction:"ASC"},
																	baseParams:{"action":"workspace","get":"buildtype","is_notselect":"true","wno":wno}
																}),
																editable:false,
																mode:"local",
																displayField:"buildtype",
																valueField:"idx",
																emptyText:"건설동을 선택하세요.",
																listeners:{render:{fn:function(form) {
																	form.getStore().load();
																	form.getStore().on("load",function() {
																		form.setValue(data.get("btno"));
																	});
																}}}
															}),
															new Ext.ux.form.SpinnerField({
																fieldLabel:"정렬순서",
																width:180,
																minValue:1,
																maxValue:99,
																name:"sort",
																value:data.get("sort")
															})
														],
														listeners:{actioncomplete:{fn:function(form,action) {
															if (action.type == "submit") {
																Ext.Msg.show({title:"안내",msg:"성공적으로 수정하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
																Ext.getCmp("WorkgroupSetupList1").getStore().reload();
																Ext.getCmp("WorkgroupSetupList2").getStore().baseParams.gno = "";
																Ext.getCmp("WorkgroupSetupList2").getStore().reload();
																Ext.getCmp("WorkgroupSetupModifyGroupWindow").close();
															}
														}}}
													})
												],
												buttons:[
													new Ext.Button({
														text:"확인",
														icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_tick.png",
														handler:function() {
															Ext.getCmp("WorkgroupSetupModifyGroupForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php?action=workspace&do=workgroup&mode=modify&wno="+wno+"&idx="+data.get("idx"),waitMsg:"공정을 수정중입니다."});
														}
													}),
													new Ext.Button({
														text:"취소",
														icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
														handler:function() {
															Ext.getCmp("WorkgroupSetupModifyGroupWindow").close();
														}
													})
												]
											}).show();
										}
									});
									menu.add({
										text:"공정삭제",
										icon:(Ext.isIE6 ? "" : "<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_package_delete.png"),
										handler:function(item) {
											Ext.Msg.show({title:"안내",msg:"공정을 삭제하면, 하위 공정의 모든 데이터가 삭제됩니다.<br />공정을 정말 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
												if (button == "ok") {
													Ext.Msg.wait("처리중입니다.","Please Wait...");
													Ext.Ajax.request({
														url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php",
														success:function() {
															Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
															Ext.getCmp("WorkgroupSetupList1").getStore().reload();
															Ext.getCmp("WorkgroupSetupList2").getStore().baseParams.gno = "";
															Ext.getCmp("WorkgroupSetupList2").getStore().reload();
														},
														failure:function() {
															Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 삭제하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
														},
														headers:{},
														params:{"action":"workspace","do":"workgroup","mode":"delete","wno":wno,"idx":data.get("idx")}
													});
												}
											}});
										}
									});

									e.stopEvent();
									menu.showAt(e.getXY());
								}}
							}
						}),
						new Ext.grid.GridPanel({
							id:"WorkgroupSetupList2",
							title:"공종",
							region:"center",
							split:true,
							layout:"fit",
							margins:"5 5 5 0",
							tbar:[
								new Ext.Button({
									id:"WorkgroupSetupList2Add",
									disabled:true,
									text:"공종추가",
									icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_package_add.png",
									handler:function() {
										new Ext.Window({
											id:"WorktypeSetupAddGroupWindow",
											title:"공종추가하기",
											width:300,
											height:140,
											layout:"fit",
											modal:true,
											resizable:false,
											items:[
												new Ext.form.FormPanel({
													id:"WorktypeSetupAddGroupForm",
													border:false,
													style:"padding:10px; background:#FFFFFF;",
													labelAlign:"right",
													labelWidth:65,
													autoWidth:true,
													errorReader:new Ext.form.XmlErrorReader(),
													items:[
														new Ext.form.TextField({
															fieldLabel:"공종명",
															width:180,
															name:"worktype",
															allowBlank:false
														}),
														new Ext.ux.form.SpinnerField({
															fieldLabel:"정렬순서",
															width:180,
															minValue:1,
															maxValue:99,
															name:"sort",
															value:1
														})
													],
													listeners:{actioncomplete:{fn:function(form,action) {
														if (action.type == "submit") {
															Ext.Msg.show({title:"안내",msg:"성공적으로 추가하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
															Ext.getCmp("WorkgroupSetupList2").getStore().reload();
															Ext.getCmp("WorktypeSetupAddGroupWindow").close();
														}
													}}}
												})
											],
											buttons:[
												new Ext.Button({
													text:"확인",
													icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_tick.png",
													handler:function() {
														Ext.getCmp("WorktypeSetupAddGroupForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php?action=workspace&do=worktype&mode=add&wno="+wno+"&gno="+Ext.getCmp("WorkgroupSetupList2").getStore().baseParams.gno,waitMsg:"공종을 추가중입니다."});
													}
												}),
												new Ext.Button({
													text:"취소",
													icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
													handler:function() {
														Ext.getCmp("WorktypeSetupAddGroupWindow").close();
													}
												})
											]
										}).show();
									}
								}),
								new Ext.Button({
									id:"WorkgroupSetupList2Delete",
									disabled:true,
									text:"공종삭제",
									icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_package_delete.png",
									handler:function() {
										var checked = Ext.getCmp("WorkgroupSetupList2").selModel.getSelections();

										if (checked.length == 0) {
											Ext.Msg.show({title:"에러",msg:"삭제할 공종을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
											return false;
										}

										var idxs = new Array();
										for (var i=0, loop=checked.length;i<loop;i++) {
											idxs[i] = checked[i].get("idx");
										}
										var idx = idxs.join(",");

										Ext.Msg.show({title:"안내",msg:"공종을 삭제하면, 해당공종의 모든 품목데이터가 삭제됩니다.<br />공종을 정말 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
											if (button == "ok") {
												Ext.Msg.wait("처리중입니다.","Please Wait...");
												Ext.Ajax.request({
													url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php",
													success:function() {
														Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
														Ext.getCmp("WorkgroupSetupList2").getStore().reload();
													},
													failure:function() {
														Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 삭제하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
													},
													headers:{},
													params:{"action":"workspace","do":"worktype","mode":"delete","wno":wno,"idx":idx}
												});
											}
										}});
									}
								})
							],
							cm:new Ext.grid.ColumnModel([
								new Ext.grid.CheckboxSelectionModel(),
								{
									header:"정렬",
									dataIndex:"sort",
									width:60,
									sortable:false,
									renderer:function(value,p,record) {
										return record.data.groupsort+GetFixNumberLength(value,2);
									}
								},{
									header:"공종명",
									dataIndex:"worktype",
									width:225,
									sortable:false
								}
							]),
							store:new Ext.data.Store({
								proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
								reader:new Ext.data.JsonReader({
									root:"lists",
									totalProperty:"totalCount",
									fields:["idx","worktype","groupsort",{name:"sort",type:"int"}]
								}),
								remoteSort:false,
								sortInfo:{field:"sort",direction:"ASC"},
								baseParams:{"action":"workspace","get":"worktype","workgroup":"","wno":wno,"gno":"","is_all":"false"},
							}),
							listeners:{
								rowcontextmenu:{fn:function(grid,idx,e) {
									GridContextmenuSelect(grid,idx);

									var data = grid.getStore().getAt(idx);

									var menu = new Ext.menu.Menu();
									menu.add('<b class="menu-title">'+data.get("worktype")+'</b>');
									menu.add({
										text:"공종수정",
										icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_package_edit.png",
										handler:function(item) {
											new Ext.Window({
												id:"WorktypeSetupModifyGroupWindow",
												title:"공종수정",
												width:300,
												height:140,
												layout:"fit",
												modal:true,
												resizable:false,
												items:[
													new Ext.form.FormPanel({
														id:"WorktypeSetupModifyGroupForm",
														border:false,
														style:"padding:10px; background:#FFFFFF;",
														labelAlign:"right",
														labelWidth:65,
														autoWidth:true,
														errorReader:new Ext.form.XmlErrorReader(),
														items:[
															new Ext.form.TextField({
																fieldLabel:"공종명",
																width:180,
																name:"worktype",
																allowBlank:false,
																value:data.get("worktype")
															}),
															new Ext.ux.form.SpinnerField({
																fieldLabel:"정렬순서",
																width:180,
																minValue:1,
																maxValue:99,
																name:"sort",
																value:data.get("sort")
															})
														],
														listeners:{actioncomplete:{fn:function(form,action) {
															if (action.type == "submit") {
																Ext.Msg.show({title:"안내",msg:"성공적으로 수정하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
																Ext.getCmp("WorkgroupSetupList2").getStore().reload();
																Ext.getCmp("WorktypeSetupModifyGroupWindow").close();
															}
														}}}
													})
												],
												buttons:[
													new Ext.Button({
														text:"확인",
														icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_tick.png",
														handler:function() {
															Ext.getCmp("WorktypeSetupModifyGroupForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php?action=workspace&do=worktype&mode=modify&wno="+wno+"&idx="+data.get("idx"),waitMsg:"공종명을 수정중입니다."});
														}
													}),
													new Ext.Button({
														text:"취소",
														icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
														handler:function() {
															Ext.getCmp("WorktypeSetupModifyGroupWindow").close();
														}
													})
												]
											}).show();
										}
									});
									menu.add({
										text:"공종삭제",
										icon:(Ext.isIE6 ? "" : "<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_package_delete.png"),
										handler:function(item) {
											Ext.Msg.show({title:"안내",msg:"공종을 삭제하면, 해당공종의 모든 품목데이터가 삭제됩니다.<br />공종을 정말 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
												if (button == "ok") {
													Ext.Msg.wait("처리중입니다.","Please Wait...");
													Ext.Ajax.request({
														url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php",
														success:function() {
															Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
															Ext.getCmp("WorkgroupSetupList2").getStore().reload();
														},
														failure:function() {
															Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 삭제하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
														},
														headers:{},
														params:{"action":"workspace","do":"worktype","mode":"delete","wno":wno,"idx":data.get("idx")}
													});
												}
											}});
										}
									});

									e.stopEvent();
									menu.showAt(e.getXY());
								}}
							}
						})
					]
				})
			],
			listeners:{close:{fn:function() {
				if (Ext.getCmp("CostList")) Ext.getCmp("CostList").getStore().reload();
				if (Ext.getCmp("CostAddGroupList")) Ext.getCmp("CostAddGroupList").getStore().reload();
			}}}
		}).show();
	}

	// 현장 계약 관리
	function CostFunction(wno,title) {
		// 목록메뉴
		var CostMenu = function(grid,idx,e) {
			GridContextmenuSelect(grid,idx);

			if (grid.getId() == "CostList1") {
				var typeText = "견적내역서";
				var type = "ESTIMATE";
			} else if (grid.getId() == "CostList2") {
				var typeText = "실행내역서";
				var type = "EXEC";
			} else if (grid.getId() == "CostList3") {
				var typeText = "계약내역서";
				var type = "CONTRACT";
			} else if (grid.getId() == "CostList4") {
				var typeText = "설계변경서";
				var type = "CHANGE";
			}

			var CostModify = function(idx,value) {
				new Ext.Window({
					title:typeText+"명 수정",
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
									fieldLabel:typeText+"명",
									width:280,
									name:"title",
									value:value,
									allowBlank:false
								})
							],
							listeners:{actioncomplete:{fn:function(form,action) {
								if (action.type == "submit") {
									grid.getStore().reload();
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
								Ext.getCmp("CostModifyForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php?action=workspace&do=cost&mode=modify&idx="+idx,waitMsg:typeText+"를 수정중입니다."});
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
			};

			var data = grid.getStore().getAt(idx);
			var menu = new Ext.menu.Menu();
			menu.add('<b class="menu-title">'+data.get("title")+'</b>');
			menu.add({
				text:typeText+"명 수정",
				icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_page_white_edit.png",
				handler:function(item) {
					CostModify(data.get("idx"),data.get("title"));
				}
			});
			menu.add({
				text:typeText+" 삭제",
				icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_page_white_delete.png",
				handler:function(item) {
					if (data.get("is_apply") == "TRUE") {
						Ext.Msg.show({title:"에러",msg:"현장에 반영된 "+typeText+"는 삭제할 수 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
						return false;
					}

					Ext.Msg.show({title:"안내",msg:"정말 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
						Ext.Msg.wait("처리중입니다.","Please Wait...");
						if (button == "ok") {
							Ext.Ajax.request({
								url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php",
								success:function() {
									Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
									grid.getStore().load();
								},
								failure:function() {
									Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
								},
								headers:{},
								params:{"action":"workspace","do":"cost","mode":"delete","idx":data.get("idx")}
							});
						}
					}});
				}
			});
			menu.add({
				text:"현장적용",
				icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_page_copy.png",
				handler:function(item) {
					Ext.Msg.show({title:"안내",msg:"현장에 적용한 뒤, 변경사항은 자동으로 현장에 반영됩니다.<br />현재의 "+typeText+"를 현장에 반영하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
						if (button == "ok") {
							Ext.Msg.wait("처리중입니다.","Please Wait...");
							Ext.Ajax.request({
								url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php",
								success:function() {
									Ext.Msg.show({title:"안내",msg:"성공적으로 적용하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
									grid.getStore().load();
									Ext.getCmp("ListTab").getActiveTab().getStore().reload();
								},
								failure:function() {
									Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 적용하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
								},
								headers:{},
								params:{"action":"workspace","do":"cost","mode":"apply","idx":data.get("idx"),"type":type}
							});
						}
					}});
				}
			});
			e.stopEvent();
			menu.showAt(e.getXY());
		};

		// 추가
		var CostAdd = function() {
			var type = Ext.getCmp("CostPanel").getActiveTab().getStore().baseParams.type;

			if (type == "ESTIMATE") {
				var subTitle = "견적내역서";
				var list = "CostList1";
			} else if (type == "EXEC") {
				var subTitle = "실행내역서";
				var list = "CostList2";
			} else if (type == "CONTRACT") {
				var subTitle = "계약내역서";
				var list = "CostList3";
			} else if (type == "CHANGE") {
				var subTitle = "설계변경서";
				var list = "CostList4";
			}

			if (type == "CHANGE") {
				new Ext.Window({
					title:"새 "+subTitle+" 작성",
					id:"CostAddWindow",
					modal:true,
					width:400,
					height:135,
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
									fieldLabel:subTitle+"명",
									width:280,
									name:"title",
									value:title+" ("+new Date().format("Y년 m월 d일")+" "+subTitle+")",
									allowBlank:false
								}),
								new Ext.form.ComboBox({
									fieldLabel:"기준계약서",
									width:280,
									hiddenName:"baseno",
									typeAhead:true,
									triggerAction:"all",
									lazyRender:true,
									store:new Ext.data.Store({
										proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
										reader:new Ext.data.JsonReader({
											root:"lists",
											totalProperty:"totalCount",
											fields:["idx","title"]
										}),
										remoteSort:false,
										sortInfo:{field:"title",direction:"ASC"},
										baseParams:{"action":"workspace","get":"cost","mode":"allcost","wno":wno,"idx":"0"},
									}),
									editable:false,
									allowBlank:false,
									mode:"local",
									displayField:"title",
									valueField:"idx",
									emptyText:"기존내역을 선택하여 주세요.",
									listeners:{render:{fn:function(form) {
										form.getStore().load();
									}}}
								})
							],
							listeners:{actioncomplete:{fn:function(form,action) {
								if (action.type == "submit") {
									var idx = FormSubmitReturnValue(action);
									CostView(type,idx,wno,form.findField("title").getValue());
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
								Ext.getCmp("CostAddForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php?action=workspace&do=cost&mode=add&type="+type+"&wno="+wno,waitMsg:title+"를 추가중입니다."});
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
			} else {
				new Ext.Window({
					title:"새 "+subTitle+" 작성",
					id:"CostAddWindow",
					modal:true,
					width:400,
					height:110,
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
									fieldLabel:subTitle+"명",
									width:280,
									name:"title",
									value:title+" ("+new Date().format("Y년 m월 d일")+" "+subTitle+")",
									allowBlank:false
								})
							],
							listeners:{actioncomplete:{fn:function(form,action) {
								if (action.type == "submit") {
									var idx = FormSubmitReturnValue(action);
									CostView(type,idx,wno,form.findField("title").getValue());
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
								Ext.getCmp("CostAddForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php?action=workspace&do=cost&mode=add&type="+type+"&wno="+wno,waitMsg:title+"를 추가중입니다."});
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
		};

		new Ext.Window({
			id:"CostWindow",
			title:title,
			modal:true,
			width:980,
			height:550,
			layout:"fit",
			items:[
				new Ext.TabPanel({
					id:"CostPanel",
					tabPosition:"bottom",
					border:false,
					activeTab:0,
					tbar:[
						new Ext.Button({
							text:"새 내역서 작성",
							icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_page_white_paste.png",
							handler:function() {
								CostAdd();
							}
						})
					],
					items:[
						new Ext.grid.GridPanel({
							title:"견적내역서",
							id:"CostList1",
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
								baseParams:{"action":"workspace","get":"cost","mode":"list","type":"ESTIMATE","wno":wno}
							}),
							listeners:{
								render:{fn:function() {
									Ext.getCmp("CostList1").getStore().load();
								}},
								rowdblclick:{fn:function(grid,idx,e) {
									CostView(grid.getStore().baseParams.type,grid.getStore().getAt(idx).get("idx"),wno,grid.getStore().getAt(idx).get("title"));
								}},
								rowcontextmenu:CostMenu
							}
						}),
						new Ext.grid.GridPanel({
							title:"실행내역서",
							id:"CostList2",
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
								baseParams:{"action":"workspace","get":"cost","mode":"list","type":"EXEC","wno":wno}
							}),
							listeners:{
								render:{fn:function() {
									Ext.getCmp("CostList2").getStore().load();
								}},
								rowdblclick:{fn:function(grid,idx,e) {
									CostView(grid.getStore().baseParams.type,grid.getStore().getAt(idx).get("idx"),wno,grid.getStore().getAt(idx).get("title"));
								}},
								rowcontextmenu:CostMenu
							}
						}),
						new Ext.grid.GridPanel({
							title:"계약내역서",
							id:"CostList3",
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
								baseParams:{"action":"workspace","get":"cost","mode":"list","type":"CONTRACT","wno":wno}
							}),
							listeners:{
								render:{fn:function() {
									Ext.getCmp("CostList3").getStore().load();
								}},
								rowdblclick:{fn:function(grid,idx,e) {
									CostView(grid.getStore().baseParams.type,grid.getStore().getAt(idx).get("idx"),wno,grid.getStore().getAt(idx).get("title"));
								}},
								rowcontextmenu:CostMenu
							}
						}),
						new Ext.grid.GridPanel({
							title:"설계변경내역서",
							id:"CostList4",
							cm:new Ext.grid.ColumnModel([
								new Ext.grid.RowNumberer(),
								{
									dataIndex:"idx",
									hidden:true,
									hideable:false
								},{
									header:"설계변경내역서명",
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
								baseParams:{"action":"workspace","get":"cost","mode":"list","type":"CHANGE","wno":wno}
							}),
							listeners:{
								render:{fn:function() {
									Ext.getCmp("CostList4").getStore().load();
								}},
								rowdblclick:{fn:function(grid,idx,e) {
									CostView(grid.getStore().baseParams.type,grid.getStore().getAt(idx).get("idx"),wno,grid.getStore().getAt(idx).get("title"));
								}},
								rowcontextmenu:CostMenu
							}
						})
					]
				})
			]
		}).show();
	}

	// 현장 계약서 보기
	function CostView(type,idx,wno,title) {
		if (type == "ESTIMATE") {
			var typeText = "견적내역서";
		} else if (type == "EXEC") {
			var typeText = "실행내역서";
		} else if (type == "CONTRACT") {
			var typeText = "계약내역서";
		} else if (type == "CHANGE") {
			var typeText = "설계변경서";
		}

		var CostAddItem = function(gno) {
			var ItemStore = new Ext.data.Store({
				proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
				reader:new Ext.data.JsonReader({
					root:'lists',
					totalProperty:'totalCount',
					fields:[{name:"idx",type:"int"},"itemcode","workgroup","bgno","worktype","btno","title","size","unit",{name:"cost1",type:"int"},{name:"cost2",type:"int"},{name:"cost3",type:"int"},"avgcost1","avgcost2","avgcost3"]
				}),
				remoteSort:true,
				sortInfo:{field:"title",direction:"ASC"},
				baseParams:{"action":"item","get":"list","bgno":"","btno":"","keyword":""}
			});
			ItemStore.load({params:{start:0,limit:30}});

			var WorktypeStore = new Ext.data.Store({
				proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
				reader:new Ext.data.JsonReader({
					root:"lists",
					totalProperty:"totalCount",
					fields:["idx","worktype","sort"]
				}),
				remoteSort:false,
				sortInfo:{field:"sort",direction:"ASC"},
				baseParams:{"action":"workspace","get":"worktype","gno":gno,"is_all":"false"},
			});
			WorktypeStore.load();

			new Ext.Window({
				id:"CostAddItemAddWindow",
				title:"품목추가하기",
				width:900,
				height:500,
				modal:true,
				maximizable:true,
				layout:"fit",
				items:[
					new Ext.Panel({
						border:false,
						layout:"border",
						items:[
							new Ext.grid.GridPanel({
								id:"CostAddItemAddItemList",
								title:"품명DB검색",
								region:"north",
								height:200,
								layout:"fit",
								margins:"5 5 0 5",
								split:true,
								collapsible:true,
								tbar:[
									new Ext.form.ComboBox({
										id:"CostAddItemWorkgroup",
										typeAhead:true,
										triggerAction:"all",
										lazyRender:true,
										store:new Ext.data.Store({
											proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
											reader:new Ext.data.JsonReader({
												root:"lists",
												totalProperty:"totalCount",
												fields:["idx","workgroup","sort"]
											}),
											remoteSort:false,
											sortInfo:{field:"sort",direction:"ASC"},
											baseParams:{"action":"base","get":"workgroup","is_all":"true"}
										}),
										width:80,
										editable:false,
										mode:"local",
										displayField:"workgroup",
										valueField:"idx",
										listeners:{
											render:{fn:function() {
												Ext.getCmp("CostAddItemWorkgroup").getStore().load();
												Ext.getCmp("CostAddItemWorkgroup").getStore().on("load",function(store) {
													Ext.getCmp("CostAddItemWorkgroup").setValue(store.getAt(0).get("idx"));
												});
											}},
											select:{fn:function(form) {
												Ext.getCmp("CostAddItemWorktype").getStore().baseParams.bgno = form.getValue();
												Ext.getCmp("CostAddItemWorktype").getStore().load();
											}}
										}
									}),
									' ',
									new Ext.form.ComboBox({
										id:"CostAddItemWorktype",
										typeAhead:true,
										triggerAction:"all",
										lazyRender:true,
										store:new Ext.data.Store({
											proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
											reader:new Ext.data.JsonReader({
												root:"lists",
												totalProperty:"totalCount",
												fields:["idx","worktype","value","sort"]
											}),
											remoteSort:false,
											sortInfo:{field:"sort",direction:"ASC"},
											baseParams:{"action":"base","get":"worktype","bgno":"0","is_all":"true"}
										}),
										width:120,
										editable:false,
										mode:"local",
										displayField:"worktype",
										valueField:"idx",
										listeners:{
											render:{fn:function() {
												Ext.getCmp("CostAddItemWorktype").getStore().load();
												Ext.getCmp("CostAddItemWorktype").getStore().on("load",function(store) {
													Ext.getCmp("CostAddItemWorktype").setValue(store.getAt(0).get("idx"));
												});
											}}
										}
									}),
									' ',
									new Ext.form.TextField({
										id:"CostAddItemKeyword",
										width:150,
										emptyText:"검색어를 입력하세요.",
										enableKeyEvents:true,
										listeners:{keydown:{fn:function(form,e) {
											if (e.keyCode == 13) {
												ItemStore.baseParams.keyword = Ext.getCmp("CostAddItemKeyword").getValue();
												ItemStore.baseParams.bgno = Ext.getCmp("CostAddItemWorkgroup").getValue();
												ItemStore.baseParams.btno = Ext.getCmp("CostAddItemWorktype").getValue();
												ItemStore.load({params:{start:0,limit:30}});
											}
										}}}
									}),
									' ',
									new Ext.Button({
										text:"검색",
										icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_magnifier.png",
										handler:function() {
											ItemStore.baseParams.keyword = Ext.getCmp("CostAddItemKeyword").getValue();
											ItemStore.baseParams.bgno = Ext.getCmp("CostAddItemWorkgroup").getValue();
											ItemStore.baseParams.btno = Ext.getCmp("CostAddItemWorktype").getValue();
											ItemStore.load({params:{start:0,limit:30}});
										}
									})
								],
								cm:new Ext.grid.ColumnModel([
									new Ext.grid.CheckboxSelectionModel(),
									{
										header:"공종명",
										dataIndex:"worktype",
										width:120,
										sortable:false,
										renderer:function(value,p,record) {
											return "["+record.data.workgroup+"]"+record.data.worktype;
										}
									},{
										header:"품명",
										dataIndex:"title",
										width:300,
										sortable:false
									},{
										header:"규격",
										dataIndex:"size",
										width:120
									},{
										header:"단위",
										dataIndex:"unit",
										width:40,
										sortable:false,
										renderer:function(value) {
											return '<div style="text-align:center;">'+value+'</div>';
										}
									},{
										header:"재료비",
										dataIndex:"cost1",
										width:80,
										sortable:false,
										renderer:function(value,p,record) {
											return GridItemAvgCost(value,record.data.avgcost1);
										}
									},{
										header:"노무비",
										dataIndex:"cost2",
										width:80,
										sortable:false,
										renderer:function(value,p,record) {
											return GridItemAvgCost(value,record.data.avgcost2);
										}
									},{
										header:"경비",
										dataIndex:"cost3",
										width:80,
										sortable:false,
										renderer:function(value,p,record) {
											return GridItemAvgCost(value,record.data.avgcost3);
										}
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
								id:"CostAddItemAddInsertList",
								title:"데이터입력",
								region:"center",
								layout:"fit",
								margins:"0 5 5 5",
								tbar:[
									new Ext.Button({
										text:"선택항목추가하기",
										icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_arrow_down.png",
										handler:function() {
											var checked = Ext.getCmp("CostAddItemAddItemList").selModel.getSelections();

											if (checked.length == 0) {
												Ext.Msg.show({title:"에러",msg:"추가할 항목을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
												return false;
											}

											var insert = new Array();
											Ext.getCmp("CostAddItemAddInsertList").stopEditing();
											for (var i=0, loop=checked.length;i<loop;i++) {
												var data = checked[i];
												insert[i] = {"is_new":"FALSE","itemcode":data.get("itemcode"),"worktype":data.get("worktype"),"title":data.get("title"),"size":data.get("size"),"unit":data.get("unit"),"ea":data.get("ea"),"cost1":data.get("cost1"),"cost2":data.get("cost2"),"cost3":data.get("cost3"),"avgcost1":data.get("avgcost1"),"avgcost2":data.get("avgcost2"),"avgcost3":data.get("avgcost3")};
											}
											GridInsertRow(Ext.getCmp("CostAddItemAddInsertList"),insert);
											Ext.getCmp("CostAddItemAddInsertList").startEditing(0,0);
										}
									}),
									'-',
									new Ext.Button({
										text:"엑셀임포트",
										icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_page_white_excel.png",
										handler:function() {
											new Ext.Window({
												id:"CostAddExcelWindow",
												title:"엑셀임포트",
												width:400,
												height:110,
												layout:"fit",
												modal:true,
												resizable:false,
												items:[
													new Ext.form.FormPanel({
														id:"CostAddExcelForm",
														border:false,
														style:"padding:10px; background:#FFFFFF;",
														labelAlign:"right",
														labelWidth:65,
														autoWidth:true,
														fileUpload:true,
														errorReader:new Ext.form.XmlErrorReader(),
														items:[
															new Ext.ux.form.FileUploadField({
																fieldLabel:"엑셀파일",
																name:"file",
																width:270,
																buttonText:"",
																buttonCfg:{iconCls:"upload-file"},
																emptyText:"규격에 맞는 엑셀파일을 선택하여 주세요.",
																listeners:{
																	focus:{fn:function(form) {
																		if (form.getValue()) {
																			Ext.Msg.show({title:"초기화선택",msg:"엑셀파일을 초기화 하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
																				if (button == "ok") {
																					form.reset();
																				}
																			}});
																		}
																	}},
																	invalid:{fn:function(form,text) {
																		if (form.getValue()) {
																			form.reset();
																			form.markInvalid(text);
																		}
																	}}
																}
															})
														],
														listeners:{actioncomplete:{fn:function(form,action) {
															if (action.type == "submit") {
																var code = FormSubmitReturnValue(action);
																Ext.Msg.wait("처리중입니다.","Please Wait...");

																var store = new Ext.data.Store({
																	proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
																	reader:new Ext.data.JsonReader({
																		root:'lists',
																		totalProperty:'totalCount',
																		fields:["itemcode","worktype","title","size","unit",{name:"ea",type:"float"},{name:"cost1",type:"int"},{name:"cost2",type:"int"},{name:"cost3",type:"int"},"avgcost1","avgcost2","avgcost3",{name:"sort",type:"int"}]
																	}),
																	remoteSort:true,
																	sortInfo:{field:"sort",direction:"ASC"},
																	baseParams:{"action":"workspace","get":"cost","mode":"excel","code":code}
																});
																store.load();

																store.on("load",function(store) {
																	var insert = new Array();
																	for (var i=0, loop=store.getCount();i<loop;i++) {
																		var data = store.getAt(i);
																		insert[i] = {"is_new":"FALSE","itemcode":data.get("itemcode"),"worktype":data.get("worktype"),"title":data.get("title"),"size":data.get("size"),"unit":data.get("unit"),"ea":data.get("ea"),"cost1":data.get("cost1"),"cost2":data.get("cost2"),"cost3":data.get("cost3"),"avgcost1":data.get("avgcost1"),"avgcost2":data.get("avgcost2"),"avgcost3":data.get("avgcost3")};
																	}
																	GridInsertRow(Ext.getCmp("CostAddItemAddInsertList"),insert);
																	Ext.Msg.show({title:"안내",msg:"엑셀파일을 성공적으로 불러왔습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
																	Ext.getCmp("CostAddExcelWindow").close();
																});
															}
														}}}
													})
												],
												buttons:[
													new Ext.Button({
														text:"확인",
														icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_tick.png",
														handler:function() {
															Ext.getCmp("CostAddExcelForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php?action=workspace&do=cost&mode=add_excel&idx="+idx,waitMsg:"품목을 추가중입니다."});
														}
													}),
													new Ext.Button({
														text:"취소",
														icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
														handler:function() {
															Ext.getCmp("CostAddExcelWindow").close();
														}
													})
												]
											}).show();
										}
									}),
									'-',
									new Ext.Button({
										text:"새항목추가하기",
										icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_row_insert.png",
										handler:function() {
											GridInsertRow(Ext.getCmp("CostAddItemAddInsertList"),{"is_new":"TRUE"});
										}
									}),
									new Ext.Button({
										text:"선택항목삭제하기",
										icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_row_delete.png",
										handler:function() {
											GridDeleteRow(Ext.getCmp("CostAddItemAddInsertList"));
										}
									})
								],
								cm:new Ext.grid.ColumnModel([
									new Ext.ux.grid.CheckboxSelectionModel(),
									{
										dataIndex:"itemcode",
										hidden:true,
										hideable:false
									},{
										header:"공종명",
										dataIndex:"worktype",
										width:80,
										sortable:false,
										renderer:function(value,p,record) {
											if (WorktypeStore.find("worktype",value,0,false,false) == -1) {
												return '<div style="color:#FF0000;" onmouseover="Tip(true,\'현재 공정에 없는 하위 공종명입니다.\',event)" onmouseout="Tip(false)">'+value+'</div>';
											} else {
												return value;
											}
										},
										editor:new Ext.form.TextField({selectOnFocus:true})
									},{
										header:"품명",
										dataIndex:"title",
										width:150,
										sortable:false,
										renderer:GridItemNotFound
									},{
										header:"규격",
										dataIndex:"size",
										width:100,
										sortable:false,
										renderer:GridItemNotFound,
										editor:new Ext.form.TextField({selectOnFocus:true})
									},{
										header:"단위",
										dataIndex:"unit",
										width:40,
										sortable:false,
										renderer:GridItemNotFound,
										editor:new Ext.form.TextField({selectOnFocus:true})
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
											return GridItemAvgCost(value,record.data.avgcost1);
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
											return GridItemAvgCost(value,record.data.avgcost2);
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
											return GridItemAvgCost(value,record.data.avgcost3);
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
											record.data.total_cost = record.data.cost1+record.data.cost2+record.data.cost3;
											return GridNumberFormat(record.data.total_cost);
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
								trackMouseOver:true,
								sm:new Ext.ux.grid.CheckboxSelectionModel(),
								store:new Ext.data.SimpleStore({
									fields:["is_new","itemcode","worktype","title","size","unit",{name:"ea",type:"float"},{name:"cost1",type:"int"},{name:"cost2",type:"int"},{name:"cost3",type:"int"},"avgcost1","avgcost2","avgcost3",{name:"total_cost",type:"int"},{name:"total_price",type:"int"}]
								}),
								plugins:[new Ext.ux.grid.ColumnHeaderGroup({
									rows:[[
										{},
										{},
										{header:"품목정보",colspan:5,align:"center"},
										{header:"재료비",colspan:2,align:"center"},
										{header:"노무비",colspan:2,align:"center"},
										{header:"경비",colspan:2,align:"center"},
										{header:"합계",colspan:2,align:"center"}
									]],
									hierarchicalColMenu:true
								})],
								listeners:{
									render:{fn:function() {
										GridEditorAutoMatchItem(Ext.getCmp("CostAddItemAddInsertList"));
									}},
									afteredit:{fn:function(object) {
										GridAutoMatchItem(object);

										if (object.field == "ea" || object.field == "cost1" || object.field == "cost2" || object.field == "cost3") {
											if (!object.value || parseInt(object.value) <= 0) object.grid.getStore().getAt(object.row).set(object.field,0);
										}
									}}
								}
							})
						]
					}),
					new Ext.form.FormPanel({
						id:"CostAddItemAddForm",
						errorReader:new Ext.form.XmlErrorReader(),
						items:[
							new Ext.form.Hidden({
								name:"data"
							})
						],
						listeners:{actioncomplete:{fn:function(form,action) {
							if (action.type == "submit") {
								var except = FormSubmitReturnValue(action);

								if (except == "0") {
									Ext.Msg.show({title:"안내",msg:"성공적으로 추가하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
								} else {
									Ext.Msg.show({title:"안내",msg:"기존의 품목과 중복되는 "+except+"개의 항목의 품명을 변경하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
								}
								Ext.getCmp("CostSubViewPanel").store.reload();
								Ext.getCmp("CostAddItemAddWindow").close();
							}
						}}}
					})
				],
				buttons:[
					new Ext.Button({
						text:"확인",
						icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_tick.png",
						handler:function() {
							var isNotMatch = false;
							for (var i=0, loop=Ext.getCmp("CostAddItemAddInsertList").getStore().getCount();i<loop;i++) {
								var data = Ext.getCmp("CostAddItemAddInsertList").getStore().getAt(i);
								if (!data.get("worktype") || !data.get("title")) {
									Ext.Msg.show({title:"에러",msg:"공종명 또는 품명이 빠진 항목이 있습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
									return false;
								}
								if (!data.get("itemcode")) isNotMatch = true;
							}
							Ext.getCmp("CostAddItemAddForm").getForm().findField("data").setValue(GetGridData(Ext.getCmp("CostAddItemAddInsertList")));

							if (isNotMatch == true) {
								Ext.Msg.show({title:"안내",msg:"품명DB에서 검색되지 않은 항목이 있습니다.<br />품명DB에서 검색되지 않은 항목을 그대로 저장하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.INFO,fn:function(button) {
									if (button == "ok") {
										Ext.Msg.show({title:"안내",msg:"품명DB에서 검색되지 않은 항목을 품명DB에 신규로 추가하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.INFO,fn:function(button) {
											if (button == "ok") {
												var is_insert = "true";
											} else {
												var is_insert = "false";
											}

											Ext.getCmp("CostAddItemAddForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php?action=workspace&do=cost&mode=add_item&gno="+gno+"&idx="+idx+"&is_insert="+is_insert,waitMsg:"데이터를 전송중입니다."});
										}});
									}
								}});
							} else {
								Ext.getCmp("CostAddItemAddForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php?action=workspace&do=cost&mode=add_item&gno="+gno+"&idx="+idx,waitMsg:"데이터를 전송중입니다."});
							}
						}
					}),
					new Ext.Button({
						text:"취소",
						icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
						handler:function() {
							Ext.getCmp("CostAddItemAddWindow").close();
						}
					})
				]
			}).show();
		};

		var CostAddAllItem = function() {
			var ItemStore = new Ext.data.Store({
				proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
				reader:new Ext.data.JsonReader({
					root:'lists',
					totalProperty:'totalCount',
					fields:[{name:"idx",type:"int"},"itemcode","workgroup","bgno","worktype","btno","title","size","unit",{name:"cost1",type:"int"},{name:"cost2",type:"int"},{name:"cost3",type:"int"},"avgcost1","avgcost2","avgcost3"]
				}),
				remoteSort:true,
				sortInfo:{field:"title",direction:"ASC"},
				baseParams:{"action":"item","get":"list","bgno":"","btno":"","keyword":""}
			});
			ItemStore.load({params:{start:0,limit:30}});

			var WorkgroupTypeStore = new Ext.data.Store({
				proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
				reader:new Ext.data.JsonReader({
					root:"lists",
					totalProperty:"totalCount",
					fields:["workgroup","worktype","workgrouptype"]
				}),
				remoteSort:false,
				sortInfo:{field:"sort",direction:"ASC"},
				baseParams:{"action":"workspace","get":"workgrouptype","wno":wno},
			});
			WorkgroupTypeStore.load();

			new Ext.Window({
				id:"CostAddItemAddWindow",
				title:"품목추가하기",
				width:900,
				height:500,
				modal:true,
				maximizable:true,
				layout:"fit",
				items:[
					new Ext.Panel({
						border:false,
						layout:"border",
						items:[
							new Ext.grid.GridPanel({
								id:"CostAddItemAddItemList",
								title:"품명DB검색",
								region:"north",
								height:200,
								layout:"fit",
								margins:"5 5 0 5",
								split:true,
								collapsible:true,
								tbar:[
									new Ext.form.ComboBox({
										id:"CostAddItemWorkgroup",
										typeAhead:true,
										triggerAction:"all",
										lazyRender:true,
										store:new Ext.data.Store({
											proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
											reader:new Ext.data.JsonReader({
												root:"lists",
												totalProperty:"totalCount",
												fields:["idx","workgroup","sort"]
											}),
											remoteSort:false,
											sortInfo:{field:"sort",direction:"ASC"},
											baseParams:{"action":"base","get":"workgroup","is_all":"true"}
										}),
										width:80,
										editable:false,
										mode:"local",
										displayField:"workgroup",
										valueField:"idx",
										listeners:{
											render:{fn:function() {
												Ext.getCmp("CostAddItemWorkgroup").getStore().load();
												Ext.getCmp("CostAddItemWorkgroup").getStore().on("load",function(store) {
													Ext.getCmp("CostAddItemWorkgroup").setValue(store.getAt(0).get("idx"));
												});
											}},
											select:{fn:function(form) {
												Ext.getCmp("CostAddItemWorktype").getStore().baseParams.bgno = form.getValue();
												Ext.getCmp("CostAddItemWorktype").getStore().load();
											}}
										}
									}),
									' ',
									new Ext.form.ComboBox({
										id:"CostAddItemWorktype",
										typeAhead:true,
										triggerAction:"all",
										lazyRender:true,
										store:new Ext.data.Store({
											proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
											reader:new Ext.data.JsonReader({
												root:"lists",
												totalProperty:"totalCount",
												fields:["idx","worktype","value","sort"]
											}),
											remoteSort:false,
											sortInfo:{field:"sort",direction:"ASC"},
											baseParams:{"action":"base","get":"worktype","bgno":"0","is_all":"true"}
										}),
										width:120,
										editable:false,
										mode:"local",
										displayField:"worktype",
										valueField:"idx",
										listeners:{
											render:{fn:function() {
												Ext.getCmp("CostAddItemWorktype").getStore().load();
												Ext.getCmp("CostAddItemWorktype").getStore().on("load",function(store) {
													Ext.getCmp("CostAddItemWorktype").setValue(store.getAt(0).get("idx"));
												});
											}}
										}
									}),
									' ',
									new Ext.form.TextField({
										id:"CostAddItemKeyword",
										width:150,
										emptyText:"검색어를 입력하세요.",
										enableKeyEvents:true,
										listeners:{keydown:{fn:function(form,e) {
											if (e.keyCode == 13) {
												ItemStore.baseParams.keyword = Ext.getCmp("CostAddItemKeyword").getValue();
												ItemStore.baseParams.bgno = Ext.getCmp("CostAddItemWorkgroup").getValue();
												ItemStore.baseParams.btno = Ext.getCmp("CostAddItemWorktype").getValue();
												ItemStore.load({params:{start:0,limit:30}});
											}
										}}}
									}),
									' ',
									new Ext.Button({
										text:"검색",
										icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_magnifier.png",
										handler:function() {
											ItemStore.baseParams.keyword = Ext.getCmp("CostAddItemKeyword").getValue();
											ItemStore.baseParams.bgno = Ext.getCmp("CostAddItemWorkgroup").getValue();
											ItemStore.baseParams.btno = Ext.getCmp("CostAddItemWorktype").getValue();
											ItemStore.load({params:{start:0,limit:30}});
										}
									})
								],
								cm:new Ext.grid.ColumnModel([
									new Ext.grid.CheckboxSelectionModel(),
									{
										header:"공종명",
										dataIndex:"worktype",
										width:120,
										sortable:false,
										renderer:function(value,p,record) {
											return "["+record.data.workgroup+"]"+record.data.worktype;
										}
									},{
										header:"품명",
										dataIndex:"title",
										width:300,
										sortable:false
									},{
										header:"규격",
										dataIndex:"size",
										width:120
									},{
										header:"단위",
										dataIndex:"unit",
										width:40,
										sortable:false,
										renderer:function(value) {
											return '<div style="text-align:center;">'+value+'</div>';
										}
									},{
										header:"재료비",
										dataIndex:"cost1",
										width:80,
										sortable:false,
										renderer:function(value,p,record) {
											return GridItemAvgCost(value,record.data.avgcost1);
										}
									},{
										header:"노무비",
										dataIndex:"cost2",
										width:80,
										sortable:false,
										renderer:function(value,p,record) {
											return GridItemAvgCost(value,record.data.avgcost2);
										}
									},{
										header:"경비",
										dataIndex:"cost3",
										width:80,
										sortable:false,
										renderer:function(value,p,record) {
											return GridItemAvgCost(value,record.data.avgcost3);
										}
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
								id:"CostAddItemAddInsertList",
								title:"데이터입력",
								region:"center",
								layout:"fit",
								margins:"0 5 5 5",
								tbar:[
									new Ext.Button({
										text:"선택항목추가하기",
										icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_arrow_down.png",
										handler:function() {
											var checked = Ext.getCmp("CostAddItemAddItemList").selModel.getSelections();

											if (checked.length == 0) {
												Ext.Msg.show({title:"에러",msg:"추가할 항목을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
												return false;
											}

											var insert = new Array();
											Ext.getCmp("CostAddItemAddInsertList").stopEditing();
											for (var i=0, loop=checked.length;i<loop;i++) {
												var data = checked[i];
												insert[i] = {"is_new":"FALSE","itemcode":data.get("itemcode"),"workgroup":"","worktype":data.get("worktype"),"title":data.get("title"),"size":data.get("size"),"unit":data.get("unit"),"ea":data.get("ea"),"cost1":data.get("cost1"),"cost2":data.get("cost2"),"cost3":data.get("cost3"),"avgcost1":data.get("avgcost1"),"avgcost2":data.get("avgcost2"),"avgcost3":data.get("avgcost3")};
											}
											GridInsertRow(Ext.getCmp("CostAddItemAddInsertList"),insert);
											Ext.getCmp("CostAddItemAddInsertList").startEditing(0,0);
										}
									}),
									'-',
									new Ext.Button({
										text:"엑셀임포트",
										icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_page_white_excel.png",
										handler:function() {
											new Ext.Window({
												id:"CostAddExcelWindow",
												title:"엑셀임포트",
												width:400,
												height:110,
												layout:"fit",
												modal:true,
												resizable:false,
												items:[
													new Ext.form.FormPanel({
														id:"CostAddExcelForm",
														border:false,
														style:"padding:10px; background:#FFFFFF;",
														labelAlign:"right",
														labelWidth:65,
														autoWidth:true,
														fileUpload:true,
														errorReader:new Ext.form.XmlErrorReader(),
														items:[
															new Ext.ux.form.FileUploadField({
																fieldLabel:"엑셀파일",
																name:"file",
																width:270,
																buttonText:"",
																buttonCfg:{iconCls:"upload-file"},
																emptyText:"규격에 맞는 엑셀파일을 선택하여 주세요.",
																listeners:{
																	focus:{fn:function(form) {
																		if (form.getValue()) {
																			Ext.Msg.show({title:"초기화선택",msg:"엑셀파일을 초기화 하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
																				if (button == "ok") {
																					form.reset();
																				}
																			}});
																		}
																	}},
																	invalid:{fn:function(form,text) {
																		if (form.getValue()) {
																			form.reset();
																			form.markInvalid(text);
																		}
																	}}
																}
															})
														],
														listeners:{actioncomplete:{fn:function(form,action) {
															if (action.type == "submit") {
																var code = FormSubmitReturnValue(action);
																Ext.Msg.wait("처리중입니다.","Please Wait...");

																var store = new Ext.data.Store({
																	proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
																	reader:new Ext.data.JsonReader({
																		root:'lists',
																		totalProperty:'totalCount',
																		fields:["itemcode","workgroup","worktype","title","size","unit",{name:"ea",type:"float"},{name:"cost1",type:"int"},{name:"cost2",type:"int"},{name:"cost3",type:"int"},"avgcost1","avgcost2","avgcost3",{name:"sort",type:"int"}]
																	}),
																	remoteSort:true,
																	sortInfo:{field:"sort",direction:"ASC"},
																	baseParams:{"action":"workspace","get":"cost","mode":"excel","code":code}
																});
																store.load();

																store.on("load",function(store) {
																	var insert = new Array();
																	for (var i=0, loop=store.getCount();i<loop;i++) {
																		var data = store.getAt(i);
																		insert[i] = {"is_new":"FALSE","itemcode":data.get("itemcode"),"workgroup":data.get("workgroup"),"worktype":data.get("worktype"),"title":data.get("title"),"size":data.get("size"),"unit":data.get("unit"),"ea":data.get("ea"),"cost1":data.get("cost1"),"cost2":data.get("cost2"),"cost3":data.get("cost3"),"avgcost1":data.get("avgcost1"),"avgcost2":data.get("avgcost2"),"avgcost3":data.get("avgcost3")};
																	}
																	GridInsertRow(Ext.getCmp("CostAddItemAddInsertList"),insert);
																	Ext.Msg.show({title:"안내",msg:"엑셀파일을 성공적으로 불러왔습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
																	Ext.getCmp("CostAddExcelWindow").close();
																});
															}
														}}}
													})
												],
												buttons:[
													new Ext.Button({
														text:"확인",
														icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_tick.png",
														handler:function() {
															Ext.getCmp("CostAddExcelForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php?action=workspace&do=cost&mode=add_excel&use_group=true&idx="+idx,waitMsg:"품목을 추가중입니다."});
														}
													}),
													new Ext.Button({
														text:"취소",
														icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
														handler:function() {
															Ext.getCmp("CostAddExcelWindow").close();
														}
													})
												]
											}).show();
										}
									}),
									'-',
									new Ext.Button({
										text:"새항목추가하기",
										icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_row_insert.png",
										handler:function() {
											GridInsertRow(Ext.getCmp("CostAddItemAddInsertList"),{"is_new":"TRUE"});
										}
									}),
									new Ext.Button({
										text:"선택항목삭제하기",
										icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_row_delete.png",
										handler:function() {
											GridDeleteRow(Ext.getCmp("CostAddItemAddInsertList"));
										}
									}),
								],
								newWorkgroup:{},
								cm:new Ext.grid.ColumnModel([
									new Ext.ux.grid.CheckboxSelectionModel(),
									{
										dataIndex:"itemcode",
										hidden:true,
										hideable:false
									},{
										header:"공정",
										dataIndex:"workgroup",
										width:80,
										sortable:false,
										renderer:function(value,p,record) {
											if (Ext.getCmp("CostAddItemAddInsertList").newWorkgroup[value] === undefined) {
												var checked = WorkgroupTypeStore.find("workgroup","["+value+"]",0,false,false) > -1;
											} else {
												var checked = Ext.getCmp("CostAddItemAddInsertList").newWorkgroup[value];
											}
											if (checked === false) {
												record.data.isInsert = "F";
												Ext.getCmp("CostAddItemAddInsertList").newWorkgroup[value] = false;
												return '<div style="color:#FF0000;" onmouseover="Tip(true,\'현재 현장에 없는 공정입니다.\',event)" onmouseout="Tip(false)">'+value+'</div>';
											} else {
												record.data.isInsert = "";
												Ext.getCmp("CostAddItemAddInsertList").newWorkgroup[value] = true;
												return value;
											}
										},
										editor:new Ext.form.TextField({selectOnFocus:true})
									},{
										header:"공종명",
										dataIndex:"worktype",
										width:80,
										sortable:false,
										renderer:function(value,p,record) {
											if (Ext.getCmp("CostAddItemAddInsertList").newWorkgroup[record.data.workgroup+"-"+value] === undefined) {
												var checked = WorkgroupTypeStore.find("workgrouptype","["+record.data.workgroup+"-"+value+"]",0,false,false) > -1;
											} else {
												var checked = Ext.getCmp("CostAddItemAddInsertList").newWorkgroup[value];
											}
											if (checked === false) {
												return '<div style="color:#FF0000;" onmouseover="Tip(true,\'현재 공정에 없는 하위 공종명입니다.\',event)" onmouseout="Tip(false)">'+value+'</div>';
											} else {
												return value;
											}
										},
										editor:new Ext.form.TextField({selectOnFocus:true})
									},{
										header:"품명",
										dataIndex:"title",
										width:150,
										sortable:false,
										renderer:GridItemNotFound
									},{
										header:"규격",
										dataIndex:"size",
										width:100,
										sortable:false,
										renderer:GridItemNotFound,
										editor:new Ext.form.TextField({selectOnFocus:true})
									},{
										header:"단위",
										dataIndex:"unit",
										width:40,
										sortable:false,
										renderer:GridItemNotFound,
										editor:new Ext.form.TextField({selectOnFocus:true})
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
											return GridItemAvgCost(value,record.data.avgcost1);
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
											return GridItemAvgCost(value,record.data.avgcost2);
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
											return GridItemAvgCost(value,record.data.avgcost3);
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
											record.data.total_cost = record.data.cost1+record.data.cost2+record.data.cost3;
											return GridNumberFormat(record.data.total_cost);
										}
									},{
										header:"금액",
										dataIndex:"total_price",
										width:80,
										sortable:false,
										renderer:function(value,p,record) {
											return GridNumberFormat(record.data.ea*record.data.total_cost);
										}
									},{
										dataIndex:"isInsert",
										hidden:true,
										hideable:false
									}
								]),
								trackMouseOver:true,
								sm:new Ext.ux.grid.CheckboxSelectionModel(),
								store:new Ext.data.SimpleStore({
									fields:["is_new","itemcode","workgroup","worktype","title","size","unit",{name:"ea",type:"float"},{name:"cost1",type:"int"},{name:"cost2",type:"int"},{name:"cost3",type:"int"},"avgcost1","avgcost2","avgcost3",{name:"total_cost",type:"int"},{name:"total_price",type:"int"},"isInsert"]
								}),
								plugins:[new Ext.ux.grid.ColumnHeaderGroup({
									rows:[[
										{},
										{},
										{header:"품목정보",colspan:6,align:"center"},
										{header:"재료비",colspan:2,align:"center"},
										{header:"노무비",colspan:2,align:"center"},
										{header:"경비",colspan:2,align:"center"},
										{header:"합계",colspan:2,align:"center"}
									]],
									hierarchicalColMenu:true
								})],
								listeners:{
									render:{fn:function() {
										GridEditorAutoMatchItem(Ext.getCmp("CostAddItemAddInsertList"));
									}},
									afteredit:{fn:function(object) {
										GridAutoMatchItem(object);

										if (object.field == "ea" || object.field == "cost1" || object.field == "cost2" || object.field == "cost3") {
											if (!object.value || parseInt(object.value) <= 0) object.grid.getStore().getAt(object.row).set(object.field,0);
										}
									}}
								}
							})
						]
					}),
					new Ext.form.FormPanel({
						id:"CostAddItemAddForm",
						errorReader:new Ext.form.XmlErrorReader(),
						items:[
							new Ext.form.Hidden({
								name:"data"
							})
						],
						listeners:{actioncomplete:{fn:function(form,action) {
							if (action.type == "submit") {
								var except = FormSubmitReturnValue(action);

								if (except == "0") {
									Ext.Msg.show({title:"안내",msg:"성공적으로 추가하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
								} else {
									Ext.Msg.show({title:"안내",msg:"기존의 품목과 중복되는 "+except+"개의 항목의 품명을 변경하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
								}
								Ext.getCmp("CostAddItemAddWindow").close();
							}
						}}}
					})
				],
				buttons:[
					new Ext.Button({
						text:"확인",
						icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_tick.png",
						handler:function() {
							if (Ext.getCmp("CostAddItemAddInsertList").getStore().find("isInsert","F",0,false,false) > -1) {
								Ext.Msg.show({title:"에러",msg:"현재 현장에 없는 공정이 있습니다.<br />먼저 공정을 추가한 뒤에 품목을 추가할 수 있습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
								return false;
							}
							var isNotMatch = false;
							for (var i=0, loop=Ext.getCmp("CostAddItemAddInsertList").getStore().getCount();i<loop;i++) {
								var data = Ext.getCmp("CostAddItemAddInsertList").getStore().getAt(i);
								if (!data.get("worktype") || !data.get("title")) {
									Ext.Msg.show({title:"에러",msg:"공종명 또는 품명이 빠진 항목이 있습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
									return false;
								}
								if (!data.get("itemcode")) isNotMatch = true;
							}
							Ext.getCmp("CostAddItemAddForm").getForm().findField("data").setValue(GetGridData(Ext.getCmp("CostAddItemAddInsertList")));

							if (isNotMatch == true) {
								Ext.Msg.show({title:"안내",msg:"품명DB에서 검색되지 않은 항목이 있습니다.<br />품명DB에서 검색되지 않은 항목을 그대로 저장하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.INFO,fn:function(button) {
									if (button == "ok") {
										Ext.Msg.show({title:"안내",msg:"품명DB에서 검색되지 않은 항목을 품명DB에 신규로 추가하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.INFO,fn:function(button) {
											if (button == "ok") {
												var is_insert = "true";
											} else {
												var is_insert = "false";
											}

											Ext.getCmp("CostAddItemAddForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php?action=workspace&do=cost&mode=add_item&idx="+idx+"&is_insert="+is_insert,waitMsg:"데이터를 전송중입니다."});
										}});
									}
								}});
							} else {
								Ext.getCmp("CostAddItemAddForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php?action=workspace&do=cost&mode=add_item&idx="+idx,waitMsg:"데이터를 전송중입니다."});
							}
						}
					}),
					new Ext.Button({
						text:"취소",
						icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
						handler:function() {
							Ext.getCmp("CostAddItemAddWindow").close();
						}
					})
				],
				listeners:{close:{fn:function() {
					Ext.getCmp("CostViewSheet").getStore().reload();
					Ext.getCmp("CostViewGroup").getStore().reload();
					Ext.getCmp("CostViewUnit").getStore().reload();
				}}}
			}).show();
		};

		var CostLoad = function(idx) {
			new Ext.Window({
				title:"기존내역적용",
				id:"CostLoadWindow",
				modal:true,
				width:400,
				height:110,
				layout:"fit",
				items:[
					new Ext.form.FormPanel({
						id:"CostLoadForm",
						border:false,
						style:"padding:10px; background:#FFFFFF;",
						labelAlign:"right",
						labelWidth:80,
						autoWidth:true,
						errorReader:new Ext.form.XmlErrorReader(),
						items:[
							new Ext.form.ComboBox({
								fieldLabel:"기존내역선택",
								width:280,
								hiddenName:"load",
								typeAhead:true,
								triggerAction:"all",
								lazyRender:true,
								store:new Ext.data.Store({
									proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
									reader:new Ext.data.JsonReader({
										root:"lists",
										totalProperty:"totalCount",
										fields:["idx","title"]
									}),
									remoteSort:false,
									sortInfo:{field:"title",direction:"ASC"},
									baseParams:{"action":"workspace","get":"cost","mode":"allcost","wno":wno,"idx":idx},
								}),
								editable:false,
								allowBlank:false,
								mode:"local",
								displayField:"title",
								valueField:"idx",
								emptyText:"기존내역을 선택하여 주세요.",
								listeners:{render:{fn:function(form) {
									form.getStore().load();
								}}}
							})
						],
						listeners:{actioncomplete:{fn:function(form,action) {
							if (action.type == "submit") {
								Ext.Msg.show({title:"안내",msg:"기존내역을 성공적으로 불러왔습니다.",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.INFO});
								Ext.getCmp("CostViewSheet").getStore().reload();
								Ext.getCmp("CostViewGroup").getStore().reload();
								Ext.getCmp("CostViewUnit").getStore().reload();
								Ext.getCmp("CostLoadWindow").close();
							}
						}}}
					})
				],
				buttons:[
					new Ext.Button({
						text:"확인",
						icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_tick.png",
						handler:function() {
							Ext.Msg.show({title:"안내",msg:"기존내역을 불러올 경우, 현재내역이 모두 초기화됩니다.<br />기존내역을 불러오시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.INFO,fn:function(button) {
								if (button == "ok") {
									Ext.getCmp("CostLoadForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php?action=workspace&do=cost&mode=load&idx="+idx,waitMsg:"기존내역을 로딩중입니다."});
								}
							}});
						}
					}),
					new Ext.Button({
						text:"취소",
						icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
						handler:function() {
							Ext.getCmp("CostLoadWindow").close();
						}
					})
				]
			}).show();
		};

		var tempTno = 0;
		var CostSubView = function(gno) {
			new Ext.Window({
				id:"CostSubViewWindow",
				title:"공종 및 품목관리하기",
				width:940,
				height:520,
				layout:"fit",
				modal:true,
				maximizable:true,
				tbar:[
					new Ext.form.TextField({
						id:"CostSubKeyword",
						width:200,
						emptyText:"검색어를 입력하세요.",
						enableKeyEvents:true,
						listeners:{keydown:{fn:function(form,e) {
							if (e.keyCode == 13) {
								Ext.getCmp("CostSubViewPanel").getActiveTab().getStore().baseParams.keyword = Ext.getCmp("CostSubKeyword").getValue();
								Ext.getCmp("CostSubViewPanel").getActiveTab().getStore().load({params:{start:0,limit:30}});
							}
						}}}
					}),
					new Ext.Toolbar.Spacer({
						id:"CostSubSpacer1"
					}),
					new Ext.Button({
						id:"CostSubSearchButton",
						text:"검색",
						icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_magnifier.png",
						handler:function() {
							Ext.getCmp("CostSubViewPanel").getActiveTab().getStore().baseParams.keyword = Ext.getCmp("CostSubKeyword").getValue();
							Ext.getCmp("CostSubViewPanel").getActiveTab().getStore().load({params:{start:0,limit:30}});
						}
					}),
					new Ext.Toolbar.Separator({
						id:"CostSubSeparator1"
					}),
					new Ext.Button({
						id:"CostSubAddButton",
						text:"품목추가하기",
						icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_brick_add.png",
						handler:function() {
							CostAddItem(gno);
						}
					}),
					new Ext.Button({
						id:"CostSubDeleteButton",
						text:"품목삭제하기",
						icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_brick_delete.png",
						handler:function() {
							var checked = Ext.getCmp("CostSubViewPanel").getActiveTab().selModel.getSelections();

							if (Ext.getCmp("CostSubViewPanel").getActiveTab().getStore().getModifiedRecords().length != 0) {
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

									Ext.Msg.wait("처리중입니다.","Please Wait...");
									Ext.Ajax.request({
										url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php",
										success: function() {
											Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
											Ext.getCmp("CostSubViewPanel").getActiveTab().getStore().reload();
											Ext.getCmp("CostSubViewPanel-0").getStore().reload();
										},
										failure: function() {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 삭제하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
										},
										headers:{},
										params:{"action":"workspace","do":"cost","mode":"del_item","ino":ino}
									});
								}
							}});
						}
					}),
					new Ext.Button({
						id:"CostSubWorkgroup",
						text:"공정관리",
						icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_package.png",
						handler:function() {
							WorkGroupSetup(wno);
						}
					}),
					'-',
					new Ext.Button({
						id:"CostSubCalcButton",
						text:"금액일괄수정",
						icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_calculator.png",
						handler:function() {
							new Ext.Window({
								id:"CalculatorWindow",
								title:"금액일괄수정",
								width:300,
								layout:"fit",
								modal:true,
								resizable:false,
								items:[
									new Ext.form.FormPanel({
										id:"CalculatorForm",
										border:false,
										style:"padding:10px; background:#FFFFFF;",
										labelAlign:"right",
										labelWidth:120,
										autoWidth:true,
										autoHeight:true,
										errorReader:new Ext.form.XmlErrorReader(),
										items:[
											new Ext.ux.form.SpinnerField({
												fieldLabel:"현재단가대비(%)",
												width:120,
												minValue:1,
												maxValue:200,
												name:"percent",
												value:100
											})
										]
									})
								],
								buttons:[
									new Ext.Button({
										text:"확인",
										icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_tick.png",
										handler:function() {
											Ext.Msg.show({title:"확인",msg:"전체단가를 일괄적으로 수정하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
												if (button == "ok") {
													Ext.Msg.wait("처리중입니다.","Please Wait...");
													for (var i=0, loop=Ext.getCmp("CostSubViewPanel").getActiveTab().getStore().getCount();i<loop;i++) {
														Ext.getCmp("CostSubViewPanel").getActiveTab().getStore().getAt(i).set("cost1",Math.floor(Ext.getCmp("CostSubViewPanel").getActiveTab().getStore().getAt(i).get("cost1")*Ext.getCmp("CalculatorForm").getForm().findField("percent").getValue()/100));
														Ext.getCmp("CostSubViewPanel").getActiveTab().getStore().getAt(i).set("cost2",Math.floor(Ext.getCmp("CostSubViewPanel").getActiveTab().getStore().getAt(i).get("cost2")*Ext.getCmp("CalculatorForm").getForm().findField("percent").getValue()/100));
														Ext.getCmp("CostSubViewPanel").getActiveTab().getStore().getAt(i).set("cost3",Math.floor(Ext.getCmp("CostSubViewPanel").getActiveTab().getStore().getAt(i).get("cost3")*Ext.getCmp("CalculatorForm").getForm().findField("percent").getValue()/100));
													}
													Ext.Msg.show({title:"안내",msg:"변경된 단가를 확인하신 후 변경사항을 저장하여 주시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
													Ext.getCmp("CalculatorWindow").close();
												}
											}});
										}
									}),
									new Ext.Button({
										text:"취소",
										icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
										handler:function() {
											Ext.getCmp("CalculatorWindow").close();
										}
									})
								]
							}).show();
						}
					}),
					new Ext.Button({
						id:"CostSubExcel",
						text:"엑셀임포트",
						icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_page_white_excel.png",
						handler:function() {
							new Ext.Window({
								id:"CostSubExcelWindow",
								title:"엑셀임포트",
								width:420,
								height:200,
								layout:"fit",
								modal:true,
								resizable:false,
								items:[
									new Ext.form.FormPanel({
										id:"CostSubExcelForm",
										border:false,
										style:"padding:10px; background:#FFFFFF;",
										labelAlign:"right",
										labelWidth:65,
										autoWidth:true,
										fileUpload:true,
										errorReader:new Ext.form.XmlErrorReader(),
										items:[
											new Ext.ux.form.FileUploadField({
												fieldLabel:"엑셀파일",
												name:"file",
												width:270,
												buttonText:"",
												buttonCfg:{iconCls:"upload-file"},
												emptyText:"규격에 맞는 엑셀파일을 선택하여 주세요.",
												listeners:{
													focus:{fn:function(form) {
														if (form.getValue()) {
															Ext.Msg.show({title:"초기화선택",msg:"엑셀파일을 초기화 하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
																if (button == "ok") {
																	form.reset();
																}
															}});
														}
													}},
													invalid:{fn:function(form,text) {
														if (form.getValue()) {
															form.reset();
															form.markInvalid(text);
														}
													}}
												}
											}),
											new Ext.form.Checkbox({
												name:"insert_worktype",
												boxLabel:"등록되지 않은 공종을 자동으로 추가합니다.",
												checked:true,
												listeners:{check:{fn:function(form,checked) {
													if (checked == true) {
														Ext.getCmp("CostSubExcelForm").getForm().findField("exception_worktype").setValue(false);
													} else {
														Ext.getCmp("CostSubExcelForm").getForm().findField("exception_worktype").setValue(true);
													}
												}}}
											}),
											new Ext.form.Checkbox({
												name:"exception_worktype",
												boxLabel:"등록되지 않은 공종의 품목을 추가하지 않습니다.",
												checked:false,
												listeners:{check:{fn:function(form,checked) {
													if (checked == true) {
														Ext.getCmp("CostSubExcelForm").getForm().findField("insert_worktype").setValue(false);
													} else {
														Ext.getCmp("CostSubExcelForm").getForm().findField("insert_worktype").setValue(true);
													}
												}}}
											}),
											new Ext.form.Checkbox({
												name:"insert_itemdb",
												boxLabel:"품목DB에 등록되지 않은 품목을 자동으로 추가합니다.",
												checked:true
											}),
											new Ext.form.Checkbox({
												name:"insert_duplication",
												boxLabel:"중복품목의 이름 자동으로 바꾸어 추가합니다.",
												checked:true
											})
										],
										listeners:{actioncomplete:{fn:function(form,action) {
											if (action.type == "submit") {
												Ext.Msg.show({title:"안내",msg:"엑셀파일을 성공적으로 임포트하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
												Ext.getCmp("CostSubViewPanel").removeAll();
												var store = Ext.getCmp("CostSubViewPanel").store.reload();
												Ext.getCmp("CostSubExcelWindow").close();
											}
										}}}
									})
								],
								buttons:[
									new Ext.Button({
										text:"확인",
										icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_tick.png",
										handler:function() {
											Ext.getCmp("CostSubExcelForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php?action=workspace&do=cost&mode=add_excel&idx="+idx+"&gno="+gno,waitMsg:"품목을 추가중입니다."});
										}
									}),
									new Ext.Button({
										text:"취소",
										icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
										handler:function() {
											Ext.getCmp("CostSubExcelWindow").close();
										}
									})
								]
							}).show();
						}
					}),
					'-',
					new Ext.Button({
						id:"CostSubSaveButton",
						text:"저장",
						icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_disk.png",
						handler:function() {
							if (Ext.getCmp("CostSubViewPanel").getActiveTab().getId() == "CostSubViewPanel-0") {
								var isModify = false;
								var store = Ext.getCmp("CostSubViewPanel").store;
								for (var i=0, loop=store.getCount();i<loop;i++) {
									if (Ext.getCmp("CostSubViewPanel-"+store.getAt(i).get("tab")).getStore().getModifiedRecords().length > 0) {
										isModify = true;
									}
								}

								if (isModify == true) {
									Ext.Msg.wait("처리중입니다.","Please Wait...");
									for (var i=0, loop=store.getCount();i<loop;i++) {
										if (Ext.getCmp("CostSubViewPanel-"+store.getAt(i).get("tab")).getStore().getModifiedRecords().length > 0) {
											var data = GetGridData(Ext.getCmp("CostSubViewPanel-"+store.getAt(i).get("tab")));

											Ext.Ajax.request({
												url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php",
												success: function() {
													Ext.getCmp("CostSubViewPanel-"+store.getAt(i).get("tab")).getStore().commitChanges();
												},
												failure: function() {
													Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 저장하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
												},
												headers:{},
												params:{"action":"workspace","do":"cost","mode":"mod_item","data":data,"idx":idx}
											});
										}
									}
									Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
									Ext.getCmp("CostSubViewPanel-0").getStore().reload();
								} else {
									if (Ext.getCmp("CostSubViewPanel").getActiveTab().getStore().getModifiedRecords().length == 0) {
										Ext.Msg.show({title:"안내",msg:"변경된 사항이 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
										return false;
									}
								}
							} else {
								if (Ext.getCmp("CostSubViewPanel").getActiveTab().getStore().getModifiedRecords().length == 0) {
									Ext.Msg.show({title:"안내",msg:"변경된 사항이 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
									return false;
								}

								Ext.Msg.wait("처리중입니다.","Please Wait...");
								var data = GetGridData(Ext.getCmp("CostSubViewPanel").getActiveTab());

								Ext.Ajax.request({
									url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php",
									success: function() {
										Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
										Ext.getCmp("CostSubViewPanel").getActiveTab().getStore().commitChanges();
										Ext.getCmp("CostSubViewPanel-0").getStore().reload();
									},
									failure: function() {
										Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 저장하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
									},
									headers:{},
									params:{"action":"workspace","do":"cost","mode":"mod_item","data":data,"idx":idx}
								});
							}
						}
					})
				],
				items:[
					new Ext.TabPanel({
						id:"CostSubViewPanel",
						tabPosition:"bottom",
						activeTab:0,
						enableTabScroll:true,
						border:false,
						store:new Ext.data.Store({
							proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
							reader:new Ext.data.JsonReader({
								root:'lists',
								totalProperty:'totalCount',
								fields:[{name:"tab",type:"int"},"title"]
							}),
							remoteSort:false,
							groupField:"group",
							sortInfo:{field:"tab", direction:"ASC"},
							baseParams:{"action":"workspace","get":"cost","mode":"subgroup","submode":"tab","idx":idx,"gno":gno}
						}),
						items:[
							new Ext.Panel({
								id:"LoadingTab",
								title:"로딩중...",
								html:'<div style="width:500px; margin:0 auto; margin-top:100px; border:1px solid #98C0F4; background:#DEEDFA; padding:10px; color:#15428B;" class="dotum f11 center">공종 및 품목을 로딩중입니다.</div>'
							})
						],
						listeners:{
							tabchange:{fn:function(tabs,tab) {
								if (tab) {
									var temp = tab.getId().split("-");
									if (temp.length == 2 && temp[1]) {
										tempTno = temp[1];
									}

									if (tempTno != "0") {
										Ext.getCmp("CostSubKeyword").show();
										Ext.getCmp("CostSubSpacer1").show();
										Ext.getCmp("CostSubSearchButton").show();
										Ext.getCmp("CostSubSeparator1").show();
										Ext.getCmp("CostSubExcel").hide();
										Ext.getCmp("CostSubAddButton").show();
										Ext.getCmp("CostSubDeleteButton").show();
										Ext.getCmp("CostSubWorkgroup").hide();
										Ext.getCmp("CostSubCalcButton").show();
									} else {
										Ext.getCmp("CostSubKeyword").hide();
										Ext.getCmp("CostSubSpacer1").hide();
										Ext.getCmp("CostSubSearchButton").hide();
										Ext.getCmp("CostSubSeparator1").hide();
										Ext.getCmp("CostSubExcel").show();
										Ext.getCmp("CostSubAddButton").hide();
										Ext.getCmp("CostSubDeleteButton").hide();
										Ext.getCmp("CostSubWorkgroup").show();
										Ext.getCmp("CostSubCalcButton").hide();
									}
									if (tab.getId() != "LoadingTab") Ext.getCmp("CostSubKeyword").setValue(Ext.getCmp(tab.getId()).getStore().baseParams.keyword);
								}
							}},
							add:{fn:function(tabs,tab) {
								if (tab.getId() == "CostSubViewPanel-0") {
									tabs.activate(tab.getId());
								}
							}}
						}
					})
				],
				listeners:{
					render:{fn:function() {
						Ext.getCmp("CostSubViewPanel").store.on("load",function(store) {
							if (Ext.getCmp("LoadingTab")) Ext.getCmp("CostSubViewPanel").remove(Ext.getCmp("LoadingTab"));

							for (var i=0, loop=store.getCount();i<loop;i++) {
								if (store.getAt(i).get("tab") == "0") {
									Ext.getCmp("CostSubViewWindow").setTitle(store.getAt(i).get("title")+" 공종 및 품목관리하기");

									if (Ext.getCmp("CostSubViewPanel-0")) {
										Ext.getCmp("CostSubViewPanel-0").getStore().reload();
									} else {
										Ext.getCmp("CostSubViewPanel").add(
											new Ext.grid.GridPanel({
												id:"CostSubViewPanel-0",
												title:store.getAt(i).get("title")+"집계",
												layout:"fit",
												cm:new Ext.grid.ColumnModel([
													{
														dataIndex:"group",
														hideable:false
													},{
														dataIndex:"workgroup",
														hidden:true,
														summaryType:"data",
														hideable:false
													},{
														header:"공종명",
														dataIndex:"worktype",
														width:195,
														sortable:true,
														summaryType:"data",
														rowIndex:0,
														summaryRenderer:function(value,p,record) {
															return "소계";
														}
													},{
														header:"단가",
														dataIndex:"cost1",
														width:80,
														sortable:false,
														summaryType:"sum",
														renderer:GridNumberFormat
													},{
														header:"금액",
														dataIndex:"price1",
														width:95,
														sortable:false,
														summaryType:"sum",
														renderer:GridNumberFormat
													},{
														header:"단가",
														dataIndex:"cost2",
														width:80,
														sortable:false,
														summaryType:"sum",
														renderer:GridNumberFormat
													},{
														header:"금액",
														dataIndex:"price2",
														width:95,
														sortable:false,
														summaryType:"sum",
														renderer:GridNumberFormat
													},{
														header:"단가",
														dataIndex:"cost3",
														width:80,
														sortable:false,
														summaryType:"sum",
														renderer:GridNumberFormat
													},{
														header:"금액",
														dataIndex:"price3",
														width:95,
														sortable:false,
														summaryType:"sum",
														renderer:GridNumberFormat
													},{
														header:"단가",
														dataIndex:"total_cost",
														width:80,
														sortable:false,
														summaryType:"sum",
														renderer:function(value,p,record) {
															record.data.total_cost = record.data.cost1+record.data.cost2+record.data.cost3;
															return GridNumberFormat(record.data.total_cost);
														},
														summaryRenderer:GridNumberFormat
													},{
														header:"금액",
														dataIndex:"total_price",
														width:100,
														sortable:false,
														summaryType:"sum",
														renderer:function(value,p,record) {
															record.data.total_price = record.data.price1+record.data.price2+record.data.price3;
															return GridNumberFormat(record.data.total_price);
														},
														summaryRenderer:GridNumberFormat
													}
												]),
												store:new Ext.data.GroupingStore({
													proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
													reader:new Ext.data.JsonReader({
														root:"lists",
														totalProperty:"totalCount",
														fields:["idx","group","gno","worktype","tno",{name:"cost1",type:"int"},{name:"price1",type:"int"},{name:"cost2",type:"int"},{name:"price2",type:"int"},{name:"cost3",type:"int"},{name:"price3",type:"int"},{name:"sort",type:"int"}]
													}),
													remoteSort:false,
													groupField:"group",
													sortInfo:{field:"sort",direction:"ASC"},
													baseParams:{"action":"workspace","get":"cost","mode":"subgroup","submode":"group","idx":idx,"gno":gno}
												}),
												plugins:[new Ext.ux.grid.ColumnHeaderGroup({
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
													headersDisabled:false,
													showGroupHeader:false
												}),
												listeners:{
													render:{fn:function() {
														Ext.getCmp("CostSubViewPanel-0").getStore().load();
													}},
													rowdblclick:{fn:function(grid,row,e) {
														if (grid.getStore().getAt(row).get("tno") != "0") Ext.getCmp("CostSubViewPanel").activate("CostSubViewPanel-"+grid.getStore().getAt(row).get("tno"));
													}}
												}
											})
										);
									}
								} else {
									if (Ext.getCmp("CostSubViewPanel-"+store.getAt(i).get("tab"))) {
										Ext.getCmp("CostSubViewPanel-"+store.getAt(i).get("tab")).getStore().reload();
									} else {
										CostSubViewCreateTabPanel(store.getAt(i).get("tab"),store.getAt(i).get("title"));
									}
								}
							}
						});
						Ext.getCmp("CostSubViewPanel").store.load();
					}},
					beforeclose:{fn:CostSubViewCheckSave},
					close:{fn:function() {
						tempTno = 0;
						Ext.getCmp("CostViewSheet").getStore().reload();
						Ext.getCmp("CostViewGroup").getStore().reload();
						Ext.getCmp("CostViewUnit").getStore().reload();
					}}
				}
			}).show();
		}
		var CostSubViewCheckSave = function() {
			var isModify = false;
			var store = Ext.getCmp("CostSubViewPanel").store;
			for (var i=0, loop=store.getCount();i<loop;i++) {
				if (Ext.getCmp("CostSubViewPanel-"+store.getAt(i).get("tab")).getStore().getModifiedRecords().length > 0) {
					isModify = true;
				}
			}

			if (isModify == true) {
				Ext.Msg.show({title:"안내",msg:"저장되지 않은 시트가 있습니다. 저장하지 않고 종료하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.QUESTION,fn:function(button) {
					if (button == "ok") {
						Ext.getCmp("CostSubViewWindow").removeListener("beforeclose",CostSubViewCheckSave);
						Ext.getCmp("CostSubViewWindow").close();
					}
				}});
				return false;
			} else {
				return true;
			}
		};
		var CostSubViewCreateTabPanel = function(tno,title) {
			Ext.getCmp("CostSubViewPanel").add(
				new Ext.grid.EditorGridPanel({
					id:"CostSubViewPanel-"+tno,
					title:title,
					layout:"fit",
					cm:new Ext.grid.ColumnModel([
						new Ext.ux.grid.CheckboxSelectionModel(),
						{
							dataIndex:"idx",
							hidden:true,
							hideable:false
						},{
							dataIndex:"group",
							hidden:true,
							hideable:false
						},{
							header:"품명",
							dataIndex:"title",
							width:145,
							sortable:false,
							summaryType:"count",
							summaryRenderer:GridSummaryCount
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
							dataIndex:"origin_ea",
							width:40,
							sortable:false,
							hidden:(type != "CHANGE"),
							renderer:GridNumberFormat
						},{
							header:"단가",
							dataIndex:"origin_cost1",
							width:60,
							sortable:false,
							summaryType:"sum",
							hidden:(type != "CHANGE"),
							renderer:GridNumberFormat
						},{
							header:"금액",
							dataIndex:"origin_price1",
							width:80,
							sortable:false,
							summaryType:"sum",
							hidden:(type != "CHANGE"),
							renderer:function(value,p,record) {
								record.data.origin_price1 = Math.floor(record.data.origin_ea*record.data.origin_cost1);
								return GridNumberFormat(record.data.origin_price1);
							}
						},{
							header:"단가",
							dataIndex:"origin_cost2",
							width:60,
							sortable:false,
							summaryType:"sum",
							hidden:(type != "CHANGE"),
							renderer:GridNumberFormat
						},{
							header:"금액",
							dataIndex:"origin_price2",
							width:80,
							sortable:false,
							summaryType:"sum",
							hidden:(type != "CHANGE"),
							renderer:function(value,p,record) {
								record.data.origin_price2 = Math.floor(record.data.origin_ea*record.data.origin_cost2);
								return GridNumberFormat(record.data.origin_price2);
							}
						},{
							header:"단가",
							dataIndex:"origin_cost3",
							width:60,
							sortable:false,
							summaryType:"sum",
							hidden:(type != "CHANGE"),
							renderer:GridNumberFormat
						},{
							header:"금액",
							dataIndex:"origin_price3",
							width:80,
							sortable:false,
							summaryType:"sum",
							hidden:(type != "CHANGE"),
							renderer:function(value,p,record) {
								record.data.origin_price3 = Math.floor(record.data.origin_ea*record.data.origin_cost3);
								return GridNumberFormat(record.data.origin_price3);
							}
						},{
							header:"단가",
							dataIndex:"origin_total_cost",
							width:80,
							sortable:false,
							summaryType:"sum",
							hidden:(type != "CHANGE"),
							renderer:function(value,p,record) {
								record.data.origin_total_cost = record.data.origin_cost1+record.data.origin_cost2+record.data.origin_cost3;
								return GridNumberFormat(record.data.origin_total_cost);
							}
						},{
							header:"금액",
							dataIndex:"origin_total_price",
							width:80,
							sortable:false,
							summaryType:"sum",
							hidden:(type != "CHANGE"),
							renderer:function(value,p,record) {
								record.data.origin_total_price = Math.floor(record.data.origin_ea*record.data.origin_total_cost);
								return GridNumberFormat(record.data.origin_total_price);
							}
						},{
							header:"수량",
							dataIndex:"ea",
							width:40,
							sortable:false,
							renderer:GridNumberFormat,
							editor:new Ext.form.NumberField({selectOnFocus:true})
						},{
							header:"단가",
							dataIndex:"cost1",
							width:60,
							sortable:false,
							summaryType:"sum",
							renderer:function(value,p,record) {
								return GridItemAvgCost(value,record.data.avgcost1);
							},
							summaryRenderer:GridNumberFormat,
							editor:new Ext.form.NumberField({selectOnFocus:true})
						},{
							header:"금액",
							dataIndex:"price1",
							width:80,
							sortable:false,
							summaryType:"sum",
							renderer:function(value,p,record) {
								record.data.price1 = Math.floor(record.data.ea*record.data.cost1);
								return GridNumberFormat(record.data.price1);
							},
							summaryRenderer:GridNumberFormat
						},{
							header:"단가",
							dataIndex:"cost2",
							width:60,
							sortable:false,
							summaryType:"sum",
							renderer:function(value,p,record) {
								return GridItemAvgCost(value,record.data.avgcost2);
							},
							summaryRenderer:GridNumberFormat,
							editor:new Ext.form.NumberField({selectOnFocus:true})
						},{
							header:"금액",
							dataIndex:"price2",
							width:80,
							sortable:false,
							summaryType:"sum",
							renderer:function(value,p,record) {
								record.data.price2 = Math.floor(record.data.ea*record.data.cost2);
								return GridNumberFormat(record.data.price2);
							},
							summaryRenderer:GridNumberFormat
						},{
							header:"단가",
							dataIndex:"cost3",
							width:60,
							sortable:false,
							summaryType:"sum",
							renderer:function(value,p,record) {
								return GridItemAvgCost(value,record.data.avgcost3);
							},
							summaryRenderer:GridNumberFormat,
							editor:new Ext.form.NumberField({selectOnFocus:true})
						},{
							header:"금액",
							dataIndex:"price3",
							width:80,
							sortable:false,
							summaryType:"sum",
							renderer:function(value,p,record) {
								record.data.price3 = Math.floor(record.data.ea*record.data.cost3);
								return GridNumberFormat(record.data.price3);
							},
							summaryRenderer:GridNumberFormat
						},{
							header:"단가",
							dataIndex:"total_cost",
							width:80,
							sortable:false,
							summaryType:"sum",
							renderer:function(value,p,record) {
								record.data.total_cost = record.data.cost1+record.data.cost2+record.data.cost3;
								return GridNumberFormat(record.data.total_cost);
							},
							summaryRenderer:GridNumberFormat
						},{
							header:"금액",
							dataIndex:"total_price",
							width:80,
							sortable:false,
							summaryType:"sum",
							renderer:function(value,p,record) {
								record.data.total_price = Math.floor(record.data.ea*record.data.total_cost);
								return GridNumberFormat(record.data.total_price);
							},
							summaryRenderer:GridNumberFormat
						},{
							header:"비고",
							dataIndex:"etc",
							width:80,
							sortable:false,
							editor:new Ext.form.TextField({selectOnFocus:true})
						}
					]),
					trackMouseOver:true,
					sm:new Ext.ux.grid.CheckboxSelectionModel(),
					store:new Ext.data.GroupingStore({
						proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
						reader:new Ext.data.JsonReader({
							root:'lists',
							totalProperty:'totalCount',
							fields:[{name:"idx",type:"int"},"group","itemcode","title","size","unit",{name:"origin_ea",type:"float"},{name:"origin_cost1",type:"int"},{name:"origin_cost2",type:"int"},{name:"origin_cost3",type:"int"},{name:"ea",type:"float"},{name:"cost1",type:"int"},{name:"cost2",type:"int"},{name:"cost3",type:"int"},"avgcost1","avgcost2","avgcost3","etc"]
						}),
						remoteSort:true,
						sortInfo:{field:"idx",direction:"ASC"},
						groupField:"group",
						baseParams:{"action":"workspace","get":"cost","mode":"subgroup","submode":"tabdata","keyword":"","idx":idx,"tno":tno}
					}),
					plugins:[new Ext.ux.grid.ColumnHeaderGroup({
						rows:[[
							{},
							{},
							{},
							{header:"품목정보",colspan:3,align:"center"},
							{header:"당초",colspan:1,align:"left"},
							{header:"당초재료비",colspan:2,align:"center"},
							{header:"당초노무비",colspan:2,align:"center"},
							{header:"당초경비",colspan:2,align:"center"},
							{header:"당초합계",colspan:2,align:"center"},
							{header:(type == "CHANGE" ? "변경" : ""),colspan:1,align:"left"},
							{header:(type == "CHANGE" ? "변경" : "")+"재료비",colspan:2,align:"center"},
							{header:(type == "CHANGE" ? "변경" : "")+"노무비",colspan:2,align:"center"},
							{header:(type == "CHANGE" ? "변경" : "")+"경비",colspan:2,align:"center"},
							{header:(type == "CHANGE" ? "변경" : "")+"합계",colspan:2,align:"center"},
							{}
						]],
						hierarchicalColMenu:true
					}),new Ext.ux.grid.GroupSummary()],
					view:new Ext.grid.GroupingView({
						enableGroupingMenu:false,
						hideGroupedColumn:true,
						showGroupName:false,
						enableNoGroups:false,
						headersDisabled:false,
						showGroupHeader:false
					}),
					listeners:{
						render:{fn:function() {
							Ext.getCmp("CostSubViewPanel-"+tno).getStore().load();
						}},
						beforeedit:{fn:function(object) {
							GridEditorAutoMatchPrice(object);
						}},
						afteredit:{fn:function(object) {
							if (object.field == "ea" || object.field == "cost1" || object.field == "cost2" || object.field == "cost3") {
								if (!object.value) object.grid.getStore().getAt(object.row).set(object.field,0);
							}
						}}
					}
				})
			);
		};

		new Ext.Window({
			id:"CostViewWindow",
			title:(title ? title : typeText),
			modal:true,
			layout:"fit",
			width:950,
			height:540,
			items:[
				new Ext.TabPanel({
					id:"CostViewPanel",
					border:false,
					tabPosition:"bottom",
					activeTab:0,
					items:[
						new Ext.grid.EditorGridPanel({
							id:"CostViewSheet",
							title:typeText,
							border:false,
							tbar:[
								new Ext.Button({
									text:"공정관리",
									icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_package.png",
									handler:function() {
										WorkGroupSetup(wno);
									}
								}),
								new Ext.Button({
									text:"기존내역적용",
									icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_paste_plain.png",
									disabled:(type == "CHANGE"),
									handler:function() {
										CostLoad(idx);
									}
								}),
								new Ext.Button({
									text:"현장적용",
									icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_page_copy.png",
									hidden:Ext.getCmp("CostListTab") ? false : true,
									handler:function() {
										Ext.Msg.show({title:"안내",msg:"현장에 적용한 뒤, 변경사항은 자동으로 현장에 반영됩니다.<br />현재의 "+typeText+"를 현장에 반영하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
											if (button == "ok") {
												Ext.Msg.wait("처리중입니다.","Please Wait...");
												Ext.Ajax.request({
													url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php",
													success:function() {
														Ext.Msg.show({title:"안내",msg:"성공적으로 적용하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
													},
													failure:function() {
														Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 적용하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
													},
													headers:{},
													params:{"action":"workspace","do":"cost","mode":"apply","idx":idx,"type":type}
												});
											}
										}});
									}
								}),
								'-',
								new Ext.Button({
									text:"저장",
									icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_disk.png",
									handler:function() {
										Ext.Msg.wait("처리중입니다.","Please Wait...");
										var data = GetGridData(Ext.getCmp("CostViewSheet"));
										Ext.Ajax.request({
											url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php",
											success:function() {
												Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
												Ext.getCmp("CostViewSheet").getStore().commitChanges();
												Ext.getCmp("CostUnit").getStore().reload();
											},
											failure:function() {
												Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 추가하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
											},
											headers:{},
											params:{"action":"workspace","do":"cost","mode":"sheet","idx":idx,"data":data}
										});
									}
								}),
								'-',
								new Ext.Button({
									text:"엑셀변환",
									icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_page_white_excel.png",
									handler:function() {
										ExcelConvert("<?php echo $_ENV['dir']; ?>/module/erp/exec/GetExcel.do.php?action=commander&get=contract&idx="+idx);
									}
								})
							],
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
										if (value == "견적") {
											return "공급가액";
										} else if (value == "부가가치세") {
											return "총공사비";
										} else if (value == "경비") {
											return '<div>경비</div><div class="x-grid3-summary-double">계</div>';
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
										if (record.data["type"] != "견적" && record.data["type"] != "부가가치세") return "소계"
									}
								},{
									header:"금액",
									dataIndex:"price",
									sortable:false,
									width:150,
									renderer:function(value,p,record) {
										if (record.data.category == "간접노무비") {
											record.data.origin_price = record.data.price = Math.floor(Ext.getCmp("CostViewSheet").getStore().getAt(3).get("price")*(record.data.percent/100));
										} else if (record.data.category == "산재보험료" || record.data.category == "고용보험료") {
											record.data.origin_price = record.data.price = Math.floor((Ext.getCmp("CostViewSheet").getStore().getAt(3).get("price")+Ext.getCmp("CostViewSheet").getStore().getAt(4).get("price"))*(record.data.percent/100));
										} else if (record.data.category == "국민건강보험료" || record.data.category == "국민연금보험료") {
											record.data.origin_price = record.data.price = Math.floor(Ext.getCmp("CostViewSheet").getStore().getAt(3).get("price")*(record.data.percent/100));
										} else if (record.data.category == "노인장기요양보험료") {
											record.data.origin_price = record.data.price = Math.floor(Ext.getCmp("CostViewSheet").getStore().getAt(8).get("price")*(record.data.percent/100));
										} else if (record.data.category == "산업안전보건관리비") {
											record.data.origin_price = record.data.price = Math.floor((Ext.getCmp("CostViewSheet").getStore().getAt(0).get("price")+Ext.getCmp("CostViewSheet").getStore().getAt(1).get("price")+Ext.getCmp("CostViewSheet").getStore().getAt(2).get("price")+Ext.getCmp("CostViewSheet").getStore().getAt(3).get("price"))*(record.data.percent/100)+3294000);
										} else if (record.data.type == "일반관리비" || record.data.type == "이윤") {
											var total = 0;
											for (var i=0;i<=11;i++) {
												total+= Ext.getCmp("CostViewSheet").getStore().getAt(i).get("price");
											}
											record.data.origin_price = record.data.price = Math.floor(total*(record.data.percent/100));
										} else if (record.data.type == "부가가치세") {
											var total = 0;
											for (var i=0;i<=14;i++) {
												total+= Ext.getCmp("CostViewSheet").getStore().getAt(i).get("price");
											}
											record.data.origin_price = record.data.price = Math.floor(total*(record.data.percent/100));
										}

										if (record.data.nego) {
											if (record.data.nego.indexOf("%") > -1) {
												var nego = parseInt(record.data.nego.replace("%",""));
												record.data.price = Math.floor(record.data.origin_price + (record.data.origin_price*nego/100));
											} else {
												var nego = parseInt(record.data.nego);
												record.data.price = record.data.origin_price + nego;
											}
										} else {
											record.data.price = record.data.origin_price;
										}

										return GridNumberFormat(record.data.price);
									},
									summaryType:"sum",
									editor:new Ext.form.NumberField({selectOnFocus:true}),
									summaryRenderer:function(value,p,record,data) {
										if (record.data["type"] == "경비") {
											var sHTML = '<div>'+GridNumberFormat(value)+'</div>';

											var total = 0;
											for (var i=0;i<=11;i++) {
												total+= Ext.getCmp("CostViewSheet").getStore().getAt(i).get("price");
											}
											sHTML+= '<div class="x-grid3-summary-double">'+GridNumberFormat(total)+'</div>';

											return sHTML;
										} else if (record.data["type"] == "견적") {
											var total = 0;
											for (var i=0;i<=14;i++) {
												total+= Ext.getCmp("CostViewSheet").getStore().getAt(i).get("price");
											}
											return GridNumberFormat(total);
										} else if (record.data["type"] == "부가가치세") {
											var total = 0;
											for (var i=0;i<=15;i++) {
												total+= Ext.getCmp("CostViewSheet").getStore().getAt(i).get("price");
											}
											return GridNumberFormat(total);
										} else {
											return GridNumberFormat(value);
										}
									}
								},{
									dataIndex:"origin_price",
									hidden:true,
									hideable:false
								},{
									header:"구성비",
									dataIndex:"percent",
									sortable:false,
									width:280,
									renderer:function(value,p,record) {
										if (record.data.nego && record.data.nego != "0") return "";
										if (record.data.category == "간접노무비") {
											return "직접노무비 * "+value+"%";
										} else if (record.data.category == "산재보험료" || record.data.category == "고용보험료") {
											return "노무비 * "+value+"%";
										} else if (record.data.category == "국민건강보험료" || record.data.category == "국민연금보험료") {
											return "직접노무비 * "+value+"%";
										} else if (record.data.category == "노인장기요양보험료") {
											return "국민건강보험료 * "+value+"%";
										} else if (record.data.category == "산업안전보건관리비") {
											return "[재료비+직접노무비+관자] * "+value+"%+3,294,000";
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
									width:200,
									editor:new Ext.form.TextField({selectOnFocus:true})
								},{
									dataIndex:"nego",
									hidden:true,
									hideable:false
								}
							]),
							plugins:new Ext.ux.grid.GroupSummary(),
							store:new Ext.data.GroupingStore({
								proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
								reader:new Ext.data.JsonReader({
									root:"lists",
									totalProperty:"totalCount",
									fields:[{name:"idx",type:"int"},"is_write","group","type","category",{name:"price",type:"int"},{name:"origin_price",type:"int"},{name:"percent",type:"float"},"etc","nego"]
								}),
								groupField:"group",
								remoteSort:false,
								sortInfo:{field:"idx",direction:"ASC"},
								baseParams:{"action":"workspace","get":"cost","mode":"sheet","idx":idx}
							}),
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
									else if (object.field == "percent" && object.record.data.percent == "-1") return false;
									else return true;
								}},
								afteredit:{fn:function(object) {
									if (object.field == "price") {
										if (!object.value) {
											object.grid.getStore().getAt(object.row).set("price",0);
											object.grid.getStore().getAt(object.row).set("origin_price",0);
										} else {
											object.grid.getStore().getAt(object.row).set("origin_price",object.value);
										}
										Ext.getCmp("CostViewSheet").getStore().sort("idx","ASC");
									} else if (object.field == "percent") {
										if (!object.value || object.value < 0) object.grid.getStore().getAt(object.row).set("percent","0");
										Ext.getCmp("CostViewSheet").getStore().sort("idx","ASC");
									}
								}},
								rowcontextmenu:{fn:function(grid,idx,e) {
									var data = grid.getStore().getAt(idx);

									var title = data.get("type")+(data.get("category") ? " > "+data.get("category") : "");
									var menu = new Ext.menu.Menu();
									menu.add({
										text:"<b>"+title+"</b> 증감액설정",
										icon:(Ext.isIE6 ? "" : "<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_coins.png")
									});
									menu.add(new Ext.menu.Separator({}));
									menu.add(
										new Ext.form.TextField({
											width:200,
											emptyText:"% 또는 숫자로 입력하세요.",
											getListParent:function() {
												return this.el.up('.x-menu');
											},
											iconCls:'no-icon',
											enableKeyEvents:true,
											listeners:{
												keydown:{fn:function(form,e) {
													if (e.keyCode == 13) {
														data.set("nego",form.getValue());
														grid.getStore().sort("idx","ASC");
														menu.hide();
													}
												}},
												render:{fn:function(form) {
													form.setValue(data.get("nego"));
												}}
											}
										})
									);

									e.stopEvent();
									menu.showAt(e.getXY());
								}}
							}
						}),
						new Ext.grid.GridPanel({
							id:"CostViewGroup",
							title:"공정별집계표",
							border:false,
							tbar:[
								new Ext.Button({
									text:"공정관리",
									icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_package.png",
									handler:function() {
										WorkGroupSetup(wno);
									}
								})
							],
							cm:new Ext.grid.ColumnModel([
								{
									dataIndex:"basegroup",
									hidden:true,
									summaryType:"data",
									hideable:false
								},{
									header:"공정",
									dataIndex:"workgroup",
									width:250,
									sortable:true,
									summaryType:"data",
									summaryRenderer:function(value,p,record) {
										var lastRow = Ext.getCmp("CostViewGroup").getStore().getAt(Ext.getCmp("CostViewGroup").getStore().getCount()-1);
										if (lastRow.get("workgroup") == record.data.workgroup && lastRow.get("basegroup") == record.data.basegroup) {
											return '<div>소계</div><div class="x-grid3-summary-double">계</div>';
										} else {
											return "소계";
										}
									}
								},{
									header:"재료비",
									dataIndex:"price1",
									width:160,
									sortable:false,
									summaryType:"sum",
									renderer:GridNumberFormat,
									summaryRenderer:function(value,p,record) {
										var lastRow = Ext.getCmp("CostViewGroup").getStore().getAt(Ext.getCmp("CostViewGroup").getStore().getCount()-1);
										if (lastRow.get("workgroup") == record.data.workgroup && lastRow.get("basegroup") == record.data.basegroup) {
											var total = 0;
											for (var i=0, loop=Ext.getCmp("CostViewGroup").getStore().getCount();i<loop;i++) {
												if (Ext.getCmp("CostViewGroup").getStore().getAt(i).get("tno") != "0") total+= Ext.getCmp("CostViewGroup").getStore().getAt(i).get("price1");
											}
											return '<div>'+GridNumberFormat(value)+'</div><div class="x-grid3-summary-double">'+GridNumberFormat(total)+'</div>';
										} else {
											return GridNumberFormat(value);
										}
									}
								},{
									header:"노무비",
									dataIndex:"price2",
									width:160,
									sortable:false,
									summaryType:"sum",
									renderer:GridNumberFormat,
									summaryRenderer:function(value,p,record) {
										var lastRow = Ext.getCmp("CostViewGroup").getStore().getAt(Ext.getCmp("CostViewGroup").getStore().getCount()-1);
										if (lastRow.get("workgroup") == record.data.workgroup && lastRow.get("basegroup") == record.data.basegroup) {
											var total = 0;
											for (var i=0, loop=Ext.getCmp("CostViewGroup").getStore().getCount();i<loop;i++) {
												if (Ext.getCmp("CostViewGroup").getStore().getAt(i).get("tno") != "0") total+= Ext.getCmp("CostViewGroup").getStore().getAt(i).get("price2");
											}
											return '<div>'+GridNumberFormat(value)+'</div><div class="x-grid3-summary-double">'+GridNumberFormat(total)+'</div>';
										} else {
											return GridNumberFormat(value);
										}
									}
								},{
									header:"경비",
									dataIndex:"price3",
									width:160,
									sortable:false,
									summaryType:"sum",
									renderer:GridNumberFormat,
									summaryRenderer:function(value,p,record) {
										var lastRow = Ext.getCmp("CostViewGroup").getStore().getAt(Ext.getCmp("CostViewGroup").getStore().getCount()-1);
										if (lastRow.get("workgroup") == record.data.workgroup && lastRow.get("basegroup") == record.data.basegroup) {
											var total = 0;
											for (var i=0, loop=Ext.getCmp("CostViewGroup").getStore().getCount();i<loop;i++) {
												if (Ext.getCmp("CostViewGroup").getStore().getAt(i).get("tno") != "0") total+= Ext.getCmp("CostViewGroup").getStore().getAt(i).get("price3");
											}
											return '<div>'+GridNumberFormat(value)+'</div><div class="x-grid3-summary-double">'+GridNumberFormat(total)+'</div>';
										} else {
											return GridNumberFormat(value);
										}
									}
								},{
									header:"합계",
									dataIndex:"total_price",
									width:160,
									sortable:false,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.total_price = record.data.price1+record.data.price2+record.data.price3;
										return GridNumberFormat(record.data.total_price);
									},
									summaryRenderer:function(value,p,record) {
										var lastRow = Ext.getCmp("CostViewGroup").getStore().getAt(Ext.getCmp("CostViewGroup").getStore().getCount()-1);
										if (lastRow.get("workgroup") == record.data.workgroup && lastRow.get("basegroup") == record.data.basegroup) {
											var total = 0;
											for (var i=0, loop=Ext.getCmp("CostViewGroup").getStore().getCount();i<loop;i++) {
												if (Ext.getCmp("CostViewGroup").getStore().getAt(i).get("group") != "0") total+= Ext.getCmp("CostViewGroup").getStore().getAt(i).get("total_price");
											}
											return '<div>'+GridNumberFormat(value)+'</div><div class="x-grid3-summary-double">'+GridNumberFormat(total)+'</div>';
										} else {
											return GridNumberFormat(value);
										}
									}
								}
							]),
							store:new Ext.data.GroupingStore({
								proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
								reader:new Ext.data.JsonReader({
									root:"lists",
									totalProperty:"totalCount",
									fields:["basegroup","gno","workgroup",{name:"price1",type:"int"},{name:"price2",type:"int"},{name:"price3",type:"int"},{name:"sort",type:"int"}]
								}),
								remoteSort:false,
								groupField:"basegroup",
								sortInfo:{field:"sort",direction:"ASC"},
								baseParams:{"action":"workspace","get":"cost","mode":"group","idx":idx}
							}),
							plugins:new Ext.ux.grid.GroupSummary(),
							view:new Ext.grid.GroupingView({
								enableGroupingMenu:false,
								hideGroupedColumn:true,
								showGroupName:false,
								enableNoGroups:false,
								headersDisabled:false
							}),
							listeners:{rowdblclick:function(grid,row,event) {
								CostSubView(grid.getStore().getAt(row).get("gno"));
							}}
						}),
						new Ext.grid.EditorGridPanel({
							id:"CostViewUnit",
							title:"평당환산금액",
							border:false,
							tbar:[
								new Ext.Button({
									text:"공정관리",
									icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_package.png",
									handler:function() {
										WorkGroupSetup(wno);
									}
								}),
								'-',
								new Ext.Button({
									text:"저장",
									icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_disk.png",
									handler:function() {
										Ext.Msg.wait("처리중입니다.","Please Wait...");
										var data = GetGridData(Ext.getCmp("CostViewUnit"));
										Ext.Ajax.request({
											url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php",
											success:function() {
												Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
												Ext.getCmp("CostViewUnit").getStore().commitChanges();
											},
											failure:function() {
												Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 추가하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
											},
											headers:{},
											params:{"action":"workspace","do":"cost","mode":"unit","idx":idx,"data":data}
										});
									}
								})
							],
							bbar:new Ext.ux.StatusBar({
								enableOverflow:false,
								items:[
									new Ext.Toolbar.TextItem({
										cls:"x-status-text-panel",
										style:"margin-right:2px; padding:3px 5px 0px 5px;",
										height:22,
										text:'연면적:<span id="CostViewUnitTotalArea"></span>'
									}),
									new Ext.Toolbar.TextItem({
										cls:"x-status-text-panel",
										height:22,
										style:"margin-right:2px; padding:3px 5px 0px 5px;",
										text:'평면적:<span id="CostViewUnitTotalPyung"></span>'
									})
								]
							}),
							cm:new Ext.grid.ColumnModel([
								{
									dataIndex:"idx",
									hidden:true,
									hideable:false
								},{
									header:"정렬",
									dataIndex:"sort",
									width:60,
									sortable:false,
									menuDisabled:true,
									renderer:function(value,p,record) {
										var temp = record.data.workgroup.split(" ");
										var group = temp[0];
										var sort = group;
										if (value < 10) sort+= "0";
										sort+= value;

										return sort;
									},
									summaryType:"data",
									summaryRenderer:function(value,p,record) {
										var lastRow = Ext.getCmp("CostViewUnit").getStore().getAt(Ext.getCmp("CostViewUnit").getStore().getCount()-1);
										if (lastRow.get("worktype") == record.data.worktype && lastRow.get("workgroup") == record.data.workgroup) {
											return '<div>소계</div><div class="x-grid3-summary-double">계</div>';
										} else {
											return "소계";
										}
									}
								},{
									dataIndex:"workgroup",
									hidden:true,
									hideable:false
								},{
									header:"공종",
									dataIndex:"worktype",
									width:180
								},{
									header:"공사금액",
									dataIndex:"price",
									width:120,
									summaryType:"sum",
									renderer:GridNumberFormat,
									summaryRenderer:function(value,p,record) {
										var lastRow = Ext.getCmp("CostViewUnit").getStore().getAt(Ext.getCmp("CostViewUnit").getStore().getCount()-1);
										if (lastRow.get("worktype") == record.data.worktype && lastRow.get("workgroup") == record.data.workgroup) {
											return '<div>'+GridNumberFormat(value)+'</div><div class="x-grid3-summary-double">'+GridNumberFormat(Ext.getCmp("CostViewUnit").getStore().sum("price"))+'</div>';
										} else {
											return GridNumberFormat(value);
										}
									}
								},{
									header:"평당환산금액",
									dataIndex:"unit_price",
									width:120,
									summaryType:"sum",
									renderer:GridNumberFormat,
									summaryRenderer:function(value,p,record) {
										var lastRow = Ext.getCmp("CostViewUnit").getStore().getAt(Ext.getCmp("CostViewUnit").getStore().getCount()-1);
										if (lastRow.get("worktype") == record.data.worktype && lastRow.get("workgroup") == record.data.workgroup) {
											return '<div>'+GridNumberFormat(value)+'</div><div class="x-grid3-summary-double">'+GridNumberFormat(Ext.getCmp("CostViewUnit").getStore().sum("unit_price"))+'</div>';
										} else {
											return GridNumberFormat(value);
										}
									}
								},{
									header:"비고",
									dataIndex:"etc",
									width:400,
									editor:new Ext.form.TextField({selectOnFocus:true})
								}
							]),
							plugins:new Ext.ux.grid.GroupSummary(),
							trackMouseOver:true,
							store:new Ext.data.GroupingStore({
								proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
								reader:new Ext.data.JsonReader({
									root:"lists",
									totalProperty:"totalCount",
									fields:["idx","workgroup","worktype",{name:"price",type:"int"},{name:"unit_price",type:"int"},{name:"sort",type:"int"},"etc"]
								}),
								groupField:"workgroup",
								remoteSort:false,
								sortInfo:{field:"sort",direction:"ASC"},
								baseParams:{"action":"workspace","get":"cost","mode":"unit","idx":idx}
							}),
							view:new Ext.grid.GroupingView({
								enableGroupingMenu:false,
								hideGroupedColumn:true,
								showGroupName:false,
								enableNoGroups:false,
								headersDisabled:false
							})
						})
					],
					listeners:{tabchange:{fn:function(tabs,tab) {
						if (tab.getId() == "CostViewUnit") {
							if (document.getElementById("CostViewUnitTotalArea").innerHTML == "") {
								var workspace = Ext.getCmp("ListTab").getActiveTab().selModel.getSelections().pop();
								document.getElementById("CostViewUnitTotalArea").innerHTML = GetNumberFormat(workspace.get("totalarea"))+"㎡";
								document.getElementById("CostViewUnitTotalPyung").innerHTML = GetNumberFormat(Math.round(workspace.get("totalarea")/3.3058*100)/100)+"평";
							}
						}
					}}}
				})
			],
			listeners:{
				show:{fn:function() {
					Ext.getCmp("CostViewSheet").getStore().load();
					Ext.getCmp("CostViewGroup").getStore().load();
					Ext.getCmp("CostViewUnit").getStore().load();
				}},
				close:{fn:function() {
					if (Ext.getCmp("CostPanel")) Ext.getCmp("CostPanel").getActiveTab().getStore().reload();
				}}
			}
		}).show();
	}

	var WorkspaceListCm = new Ext.grid.ColumnModel([
		new Ext.grid.RowNumberer(),
		{
			dataIndex:"idx",
			hidden:true,
			hidable:false
		},{
			header:"현장명",
			dataIndex:"title",
			sortable:true,
			width:250
		},{
			header:"발주처",
			dataIndex:"orderer",
			sortable:true,
			width:100
		},{
			header:"공사기간",
			dataIndex:"workstart_date",
			sortable:true,
			width:150,
			renderer:function(value,p,record) {
				var data = "";
				if (record.data.workstart_date != "1970-01-01") {
					data+= record.data.workstart_date;
				}
				if (record.data.workend_date != "1970-01-01") {
					data+= " ~ "+record.data.workend_date;
				}

				return data;
			}
		},{
			header:"소장명",
			dataIndex:"master",
			sortable:false,
			width:100
		},{
			header:"근로자수",
			dataIndex:"worker",
			sortable:false,
			width:80,
			renderer:GridNumberFormat
		},{
			header:"현장연락처",
			dataIndex:"telephone",
			sortable:false,
			width:100
		},{
			header:"공정율",
			dataIndex:"workpercent",
			sortable:false,
			width:90,
			renderer:function(value) {
				var data = '<div style="font-family:tahoma; font-size:10px;">';
				data+= '<span style="font-weight:bold; letter-spacing:-3px;">';
				for (var i=10;i<=100;i=i+10) {
					if (i < value) data+= '<span style="color:#EF5600;">|</span>';
					else data+= '<span style="color:#CCCCCC;">|</span>';
				}
				data+= '</span>';

				data+= " "+value+"%";

				return data;
			}
		},{
			header:"현장상황",
			dataIndex:"type",
			sortable:true,
			width:80,
			renderer:function(value) {
				if (value == "WORKING") return "공사중";
				else if (value == "ESTIMATE") return "견적중";
				else return "준공완료";
			}
		}
	]);

	// 현장페이지
	function WorkspacePageFunction(grid,idx,e) {
		var data = grid.getStore().getAt(idx);

		new Ext.Window({
			title:"현장정보보기",
			modal:true,
			maximizable:true,
			layout:"fit",
			width:980,
			height:550,
			style:"background:#FFFFFF;",
			html:'<iframe src="<?php echo $_ENV['dir']; ?>/module/erp/workspace.php?wno='+data.get("idx")+'&mode=default" frameborder="0" style="width:100%; height:100%;"></iframe>'
		}).show();
	}

	// 현장 이미지 업로드
	function WorkspacePhotoFunction(idx) {
		new Ext.Window({
			title:"현장사진등록",
			modal:true,
			width:620,
			resizable:false,
			items:[
				new Ext.Panel({
					layout:"border",
					border:false,
					height:300,
					items:[
						new Ext.grid.GridPanel({
							id:"WorkspacePhotoList",
							title:"사진목록",
							margins:"5 5 5 5",
							region:"center",
							tbar:[
								new Ext.Toolbar.TextItem({
									id:"AzUploaderArea",
									style:"margin:-1px 0px 0px -1px; .margin:-2px 0px 0px -1px;",
									listeners:{render:{fn:function() {
										new AzUploader({
											id:"WorkspacePhotoUploader",
											autoRender:false,
											flashURL:"<?php echo $_ENV['dir']; ?>/module/uploader/flash/AzUploader.swf",
											uploadURL:"<?php echo $_ENV['dir']; ?>/module/erp/exec/FileUpload.do.php?action=workspace&wno="+idx,
											buttonURL:"<?php echo $_ENV['dir']; ?>/module/erp/images/admin/btn_fileupload.gif",
											width:82,
											height:22,
											moduleDir:"<?php echo $_ENV['dir']; ?>/module/erp",
											maxFileSize:0,
											maxTotalSize:100,
											onSelect:AzUploaderOnSelect,
											onProgress:AzUploaderOnProgress,
											onComplete:function() {
												Ext.getCmp("AzUploadProgress").hide();
												Ext.getCmp("WorkspacePhotoList").getStore().reload();
											}
										}).render("AzUploaderArea");
									}}}
								}),
								new Ext.Button({
									icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_picture_delete.png",
									text:"파일삭제",
									handler:function() {
										var checked = Ext.getCmp("WorkspacePhotoList").selModel.getSelections();
										if (checked.length == 0) {
											Ext.Msg.show({title:"에러",msg:"삭제할 사진을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
											return false;
										}

										var idxs = new Array();
										for (var i=0, loop=checked.length;i<loop;i++) {
											idxs[i] = checked[i].get("idx");
										}

										Ext.Msg.show({title:"안내",msg:"해당 사진을 정말 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.QUESTION,fn:function(button) {
											if (button == "ok") {
												Ext.Msg.wait("처리중입니다.","Please Wait...");
												Ext.Ajax.request({
													url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php",
													success:function(XML) {
														Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
														Ext.getCmp("WorkspacePhotoList").getStore().reload();
													},
													failure:function() {
														Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
													},
													headers:{},
													params:{"action":"workspace","do":"image","idx":idxs.join(",")}
												});
											}
										}});
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
									header:"파일이름",
									dataIndex:"filename",
									sortable:true,
									width:180
								},{
									header:"파일용량",
									dataIndex:"filesize",
									sortable:true,
									width:60,
									renderer:function(value) {
										return '<div style="font-family:arial; text-align:right;">'+GetFileSize(value)+'</div>';
									}
								}
							]),
							sm:new Ext.grid.CheckboxSelectionModel(),
							store:new Ext.data.Store({
								proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
								reader:new Ext.data.JsonReader({
									root:"lists",
									totalProperty:"totalCount",
									fields:[{name:"idx",type:"int"},"filename",{name:"filesize",type:"int"},"filepath"]
								}),
								remoteSort:true,
								sortInfo:{field:"title",direction:"ASC"},
								baseParams:{"action":"workspace","get":"image","wno":idx}
							}),
							listeners:{rowclick:{fn:function(grid,row,event) {
								var sHTML = '<img src="'+grid.getStore().getAt(row).get("filepath")+'" style="margin:5px; width:270px;" />';
								Ext.getCmp("WorkspacePhotoPreview").getLayoutTarget().dom.innerHTML = sHTML;
							}}}
						}),
						new Ext.Panel({
							id:"WorkspacePhotoPreview",
							title:"미리보기",
							margins:"5 5 5 0",
							region:"east",
							width:300,
							autoScroll:true,
							html:""
						})
					]
				})
			],
			listeners:{show:{fn:function() {
				Ext.getCmp("WorkspacePhotoList").getStore().load();
			}}}
		}).show();
	}

	// 미 진행현장
	function WorkspaceEmptyFunction() {
		Ext.Msg.show({title:"안내",msg:"해당현장은 진행중인 현장이 아니므로, 현장페이지를 열 수 없습니다.<br />먼저 현장상태를 진행현장으로 변경하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
	}

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"현장관리",
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
			new Ext.Toolbar.Spacer({
				id:"spacer1"
			}),
			new Ext.form.TextField({
				id:"keyword",
				width:120,
				emptyText:"검색어를 입력하세요"
			}),
			new Ext.Toolbar.Spacer({
				id:"spacer2"
			}),
			new Ext.Button({
				id:"btnSearch",
				text:"검색",
				icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_magnifier.png",
				handler:function() {
					Ext.getCmp("ListTab").getActiveTab().getStore().baseParams.keyword = Ext.getCmp("keyword").getValue();
					Ext.getCmp("ListTab").getActiveTab().getStore().reload();
				}
			}),
			new Ext.Button({
				id:"btnCreate",
				icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_building_add.png",
				text:"신규현장등록",
				handler:function() {
					WorkspaceFunction();
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
						title:"진행현장",
						border:false,
						autoScroll:true,
						cm:WorkspaceListCm,
						store:WorkspaceListstore1,
						trackMouseOver:true,
						loadMask:{msg:"데이터를 로딩중입니다."},
						viewConfig:{forceFit:false},
						listeners:{
							rowcontextmenu:{fn:MenuFunction},
							rowdblclick:{fn:WorkspacePageFunction}
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
						listeners:{
							rowcontextmenu:{fn:MenuFunction},
							rowdblclick:{fn:WorkspaceEmptyFunction}
						}
					}),
					new Ext.grid.GridPanel({
						id:"ListTab3",
						title:"준공현장",
						border:false,
						autoScroll:true,
						cm:WorkspaceListCm,
						store:WorkspaceListstore3,
						trackMouseOver:true,
						loadMask:{msg:"데이터를 로딩중입니다."},
						viewConfig:{forceFit:false},
						listeners:{
							rowcontextmenu:{fn:MenuFunction},
							rowdblclick:{fn:WorkspacePageFunction}
						}
					}),
					new Ext.grid.GridPanel({
						id:"ListTab4",
						title:"미계약현장",
						border:false,
						autoScroll:true,
						cm:WorkspaceListCm,
						store:WorkspaceListstore4,
						trackMouseOver:true,
						loadMask:{msg:"데이터를 로딩중입니다."},
						viewConfig:{forceFit:false},
						listeners:{
							rowcontextmenu:{fn:MenuFunction},
							rowdblclick:{fn:WorkspacePageFunction}
						}
					})
				],
				listeners:{tabchange:{fn:function(tabs,tab) {
					if (tab.getId() == "ListTab1" || tab.getId() == "ListTab2") {
						Ext.getCmp("year").hide();
						Ext.getCmp("keyword").hide();
						Ext.getCmp("btnSearch").hide();
						Ext.getCmp("spacer1").hide();
						Ext.getCmp("spacer2").hide();
						Ext.getCmp("btnCreate").show();

						Ext.getCmp(tab.getId()).getStore().load();
					} else {
						Ext.getCmp("year").show();
						Ext.getCmp("keyword").show();
						Ext.getCmp("btnSearch").show();
						Ext.getCmp("spacer1").show();
						Ext.getCmp("spacer2").show();
						Ext.getCmp("btnCreate").hide();
						Ext.getCmp("keyword").setValue(Ext.getCmp(tab.getId()).getStore().baseParams.keyword);
						Ext.getCmp("year").setValue(Ext.getCmp(tab.getId()).getStore().baseParams.year);

						Ext.getCmp(tab.getId()).getStore().load();
					}

				}}}
			})
		]
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>