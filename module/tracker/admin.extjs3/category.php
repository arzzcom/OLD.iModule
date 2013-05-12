<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;
	
	var FormLayoutStore = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/tracker/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:["idx","title"]
		}),
		remoteSort:false,
		autoLoad:true,
		sortInfo:{field:"idx",direction:"ASC"},
		baseParams:{"action":"layout","type":"form"},
		listeners:{load:{fn:function() { ViewLayoutStore.load(); }}}
	});
	
	var ViewLayoutStore = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/tracker/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:["idx","title"]
		}),
		remoteSort:false,
		autoLoad:true,
		sortInfo:{field:"idx",direction:"ASC"},
		baseParams:{"action":"layout","type":"view"},
		listeners:{load:{fn:function() { SearchLayoutStore.load(); }}}
	});
	
	var SearchLayoutStore = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/tracker/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:["idx","title"]
		}),
		remoteSort:false,
		autoLoad:true,
		sortInfo:{field:"idx",direction:"ASC"},
		baseParams:{"action":"layout","type":"search"},
		listeners:{load:{fn:function() { ArtistLayoutStore.load(); }}}
	});
	
	var ArtistLayoutStore = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/tracker/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:["idx","title"]
		}),
		remoteSort:false,
		autoLoad:true,
		sortInfo:{field:"idx",direction:"ASC"},
		baseParams:{"action":"layout","type":"artist"},
		listeners:{load:{fn:function() { Ext.getCmp("Category1").getStore().load(); }}}
	});

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"카테고리관리",
		layout:"fit",
		items:[
			new Ext.Panel({
				layout:"hbox",
				border:false,
				layoutConfig:{align:"stretch"},
				items:[
					new Ext.grid.EditorGridPanel({
						id:"Category1",
						title:"1차카테고리",
						margins:"5 5 5 5",
						tbar:[
							new Ext.Button({
								text:"카테고리추가",
								icon:"<?php echo $_ENV['dir']; ?>/module/tracker/images/admin/icon_category_add.png",
								handler:function() {
									new Ext.Window({
										id:"CategoryAddWindow",
										title:"1차카테고리추가",
										width:350,
										height:190,
										modal:true,
										resizable:false,
										layout:"fit",
										items:[
											new Ext.form.FormPanel({
												id:"CategoryAddForm",
												labelAlign:"right",
												labelWidth:85,
												border:false,
												errorReader:new Ext.form.XmlErrorReader(),
												style:"background:#FFFFFF; padding:10px;",
												items:[
													new Ext.form.TextField({
														fieldLabel:"카테고리명",
														name:"title",
														width:200,
														allowBlank:false
													}),
													new Ext.form.CompositeField({
														fieldLabel:"등록레이아웃",
														width:200,
														items:[
															new Ext.form.ComboBox({
																id:"formLayout",
																typeAhead:true,
																triggerAction:"all",
																lazyRender:true,
																hiddenName:"form_layout",
																store:FormLayoutStore,
																width:110,
																editable:false,
																mode:"local",
																displayField:"title",
																valueField:"idx",
																emptyText:"레이아웃선택"
															}),
															new Ext.Button({
																text:"레이아웃추가",
																width:85
															})
														]
													}),
													new Ext.form.CompositeField({
														fieldLabel:"보기레이아웃",
														width:200,
														items:[
															new Ext.form.ComboBox({
																id:"viewLayout",
																typeAhead:true,
																triggerAction:"all",
																lazyRender:true,
																hiddenName:"view_layout",
																store:ViewLayoutStore,
																width:110,
																editable:false,
																mode:"local",
																displayField:"title",
																valueField:"idx",
																emptyText:"레이아웃선택"
															}),
															new Ext.Button({
																text:"레이아웃추가",
																width:85
															})
														]
													}),
													new Ext.form.CompositeField({
														fieldLabel:"검색레이아웃",
														width:200,
														items:[
															new Ext.form.ComboBox({
																id:"searchLayout",
																typeAhead:true,
																triggerAction:"all",
																lazyRender:true,
																hiddenName:"search_layout",
																store:SearchLayoutStore,
																width:110,
																editable:false,
																mode:"local",
																displayField:"title",
																valueField:"idx",
																emptyText:"레이아웃선택"
															}),
															new Ext.Button({
																text:"레이아웃추가",
																width:85
															})
														]
													}),
													new Ext.form.CompositeField({
														fieldLabel:"배우레이아웃",
														width:200,
														items:[
															new Ext.form.ComboBox({
																id:"searchLayout",
																typeAhead:true,
																triggerAction:"all",
																lazyRender:true,
																hiddenName:"artist_layout",
																store:ArtistLayoutStore,
																width:110,
																editable:false,
																mode:"local",
																displayField:"title",
																valueField:"idx",
																emptyText:"레이아웃선택"
															}),
															new Ext.Button({
																text:"레이아웃추가",
																width:85
															})
														]
													})
												],
												listeners:{actioncomplete:{fn:function(form,action) {
													if (action.type == "submit") {
														Ext.Msg.show({title:"안내",msg:"성공적으로 추가하였습니다.<br />계속해서 카테고리를 추가하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button){
															Ext.getCmp("Category1").getStore().reload();
															if (button == "ok") {
																Ext.getCmp("CategoryAddForm").getForm().findField("title").setValue();
																Ext.getCmp("CategoryAddForm").getForm().findField("title").clearInvalid();
																Ext.getCmp("CategoryAddForm").getForm().findField("title").focus();
															} else {
																Ext.getCmp("CategoryAddWindow").close();
															}
														}});
													}
												}}}
											})
										],
										buttons:[
											new Ext.Button({
												text:"확인",
												icon:"<?php echo $_ENV['dir']; ?>/module/tracker/images/admin/icon_tick.png",
												handler:function() {
													Ext.getCmp("CategoryAddForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/tracker/exec/Admin.do.php?action=category&do=add&parent=0",waitMsg:"데이터를 전송중입니다."});
												}
											}),
											new Ext.Button({
												text:"취소",
												icon:"<?php echo $_ENV['dir']; ?>/module/tracker/images/admin/icon_cross.png",
												handler:function() {
													Ext.getCmp("CategoryAddWindow").close();
												}
											})
										]
									}).show();
								}
							}),
							new Ext.Button({
								text:"카테고리삭제",
								icon:"<?php echo $_ENV['dir']; ?>/module/tracker/images/admin/icon_category_delete.png",
								handler:function() {
									var checked = Ext.getCmp("Category1").selModel.getSelections();
									if (checked.length == 0) {
										Ext.Msg.show({title:"에러",msg:"삭제할 카테고리를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
										return false;
									}
	
									var idxs = new Array();
									for (var i=0, loop=checked.length;i<loop;i++) {
										idxs.push(checked[i].get("idx"));
									}
									var idx = idxs.join(",");
									
									Ext.Msg.show({title:"안내",msg:"선택카테고리를 삭제하게 되면 해당카테고리의 하위카테고리 및 토렌트도 함께 삭제됩니다.<br />정말 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.QUESTION,fn:function(button) {
										if (button == "ok") {
											Ext.Msg.wait("처리중입니다.","Please Wait...");
											Ext.Ajax.request({
												url:"<?php echo $_ENV['dir']; ?>/module/tracker/exec/Admin.do.php",
												success:function() {
													Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
													Ext.getCmp("Category1").getStore().reload();
												},
												failure:function() {
													Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
												},
												headers:{},
												params:{"action":"category","do":"delete","idx":idx}
											});
										}
									}});
								}
							}),
							'->',
							new Ext.Button({
								text:"변경사항저장",
								icon:"<?php echo $_ENV['dir']; ?>/module/tracker/images/admin/icon_disk.png",
								handler:function() {
									Ext.Msg.wait("처리중입니다.","Please Wait...");
									Ext.Ajax.request({
										url:"<?php echo $_ENV['dir']; ?>/module/tracker/exec/Admin.do.php",
										success:function() {
											Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
											Ext.getCmp("Category1").getStore().reload();
										},
										failure:function() {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
										},
										headers:{},
										params:{"action":"category","do":"modify","data":GetGridData(Ext.getCmp("Category1"))}
									});
								}
							})
						],
						bbar:[
							new Ext.Button({
								text:"위로이동",
								icon:"<?php echo $_ENV['dir']; ?>/module/tracker/images/admin/icon_arrow_up.png",
								handler:function() {
									var checked = Ext.getCmp("Category1").selModel.getSelections();

									if (checked.length == 0) {
										Ext.Msg.show({title:"에러",msg:"이동할 카테고리를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
										return false;
									}
	
									var selecter = new Array();
									for (var i=0, loop=checked.length;i<loop;i++) {
										var sort = checked[i].get("sort");
										if (sort != 0) {
											Ext.getCmp("Category1").getStore().getAt(sort).set("sort",sort-1);
											Ext.getCmp("Category1").getStore().getAt(sort-1).set("sort",sort);
	
											selecter.push(sort-1);
											Ext.getCmp("Category1").getStore().sort("sort","ASC");
										} else {
											return false;
										}
									}
	
									for (var i=0, loop=selecter.length;i<loop;i++) {
										Ext.getCmp("Category1").selModel.selectRow(selecter[i]);
									}
								}
							}),
							new Ext.Button({
								text:"아래로이동",
								icon:"<?php echo $_ENV['dir']; ?>/module/tracker/images/admin/icon_arrow_down.png",
								handler:function() {
									var checked = Ext.getCmp("Category1").selModel.getSelections();

									if (checked.length == 0) {
										Ext.Msg.show({title:"에러",msg:"이동할 카테고리를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
										return false;
									}
	
									var selecter = new Array();
									for (var i=checked.length-1;i>=0;i--) {
										var sort = checked[i].get("sort");
										if (sort != Ext.getCmp("Category1").getStore().getCount()-1) {
											Ext.getCmp("Category1").getStore().getAt(sort).set("sort",sort+1);
											Ext.getCmp("Category1").getStore().getAt(sort+1).set("sort",sort);
	
											selecter.push(sort+1);
											Ext.getCmp("Category1").getStore().sort("sort","ASC");
										} else {
											return false;
										}
									}
	
									for (var i=0, loop=selecter.length;i<loop;i++) {
										Ext.getCmp("Category1").selModel.selectRow(selecter[i]);
									}
								}
							})
						],
						cm:new Ext.grid.ColumnModel([
							new Ext.ux.grid.CheckboxSelectionModel(),
							{
								dataIndex:"idx",
								hidden:true,
								hideable:false
							},{
								id:"title1",
								header:"카테고리명",
								dataIndex:"title",
								sortable:false,
								menuDisabled:true,
								resizable:false,
								editor:new Ext.form.TextField({selectOnFocus:true})
							},{
								header:"등록레이아웃",
								dataIndex:"form_layout",
								sortable:false,
								menuDisabled:true,
								width:80,
								editor:new Ext.form.ComboBox({
									typeAhead:true,
									triggerAction:"all",
									lazyRender:true,
									store:FormLayoutStore,
									editable:false,
									mode:"local",
									displayField:"title",
									valueField:"idx",
									emptyText:"레이아웃선택"
								}),
								renderer:function(value,p,record) {
									var idx = FormLayoutStore.find("idx",new RegExp(value+"$"),false,false);
									return FormLayoutStore.getAt(idx).get("title");
								}
							},{
								header:"보기레이아웃",
								dataIndex:"view_layout",
								sortable:false,
								menuDisabled:true,
								width:80,
								editor:new Ext.form.ComboBox({
									typeAhead:true,
									triggerAction:"all",
									lazyRender:true,
									store:ViewLayoutStore,
									editable:false,
									mode:"local",
									displayField:"title",
									valueField:"idx",
									emptyText:"레이아웃선택"
								}),
								renderer:function(value,p,record) {
									var idx = ViewLayoutStore.find("idx",new RegExp(value+"$"),false,false);
									return ViewLayoutStore.getAt(idx).get("title");
								}
							},{
								header:"검색레이아웃",
								dataIndex:"search_layout",
								sortable:false,
								menuDisabled:true,
								width:80,
								editor:new Ext.form.ComboBox({
									typeAhead:true,
									triggerAction:"all",
									lazyRender:true,
									store:SearchLayoutStore,
									editable:false,
									mode:"local",
									displayField:"title",
									valueField:"idx",
									emptyText:"레이아웃선택"
								}),
								renderer:function(value,p,record) {
									var idx = SearchLayoutStore.find("idx",new RegExp(value+"$"),false,false);
									return SearchLayoutStore.getAt(idx).get("title");
								}
							},{
								header:"배우레이아웃",
								dataIndex:"artist_layout",
								sortable:false,
								menuDisabled:true,
								width:80,
								editor:new Ext.form.ComboBox({
									typeAhead:true,
									triggerAction:"all",
									lazyRender:true,
									store:ArtistLayoutStore,
									editable:false,
									mode:"local",
									displayField:"title",
									valueField:"idx",
									emptyText:"레이아웃선택"
								}),
								renderer:function(value,p,record) {
									var idx = ArtistLayoutStore.find("idx",new RegExp(value+"$"),false,false);
									return ArtistLayoutStore.getAt(idx).get("title");
								}
							},{
								dataIndex:"sort",
								hidden:true,
								hideable:false
							}
						]),
						sm:new Ext.ux.grid.CheckboxSelectionModel(),
						store:new Ext.data.Store({
							proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/tracker/exec/Admin.get.php"}),
							reader:new Ext.data.JsonReader({
								root:"lists",
								totalProperty:"totalCount",
								fields:["idx","title","form_layout","view_layout","search_layout","artist_layout",{name:"sort",type:"int"}]
							}),
							remoteSort:false,
							sortInfo:{field:"sort",direction:"ASC"},
							baseParams:{action:"category",parent:"0"},
							listeners:{load:{fn:function() {
								Ext.getCmp("Category2").getStore().baseParams.parent = "-1";
								Ext.getCmp("Category2").getStore().load();
							}}}
						}),
						autoExpandColumn:"title1",
						flex:5,
						listeners:{rowclick:{fn:function(grid,idx,e) {
							Ext.getCmp("Category2").getStore().baseParams.parent = grid.getStore().getAt(idx).get("idx");
							Ext.getCmp("Category2").getStore().load();
						}}}
					}),
					new Ext.grid.EditorGridPanel({
						id:"Category2",
						title:"2차카테고리",
						margins:"5 5 5 0",
						tbar:[
							new Ext.Button({
								text:"카테고리추가",
								icon:"<?php echo $_ENV['dir']; ?>/module/tracker/images/admin/icon_category_add.png",
								handler:function() {
									if (Ext.getCmp("Category2").getStore().baseParams.parent == "-1") {
										Ext.Msg.show({title:"에러",msg:"1차카테고리를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
										return false;
									}
									
									var parent = Ext.getCmp("Category1").getStore().find("idx",Ext.getCmp("Category2").getStore().baseParams.parent,false,false);
									
									new Ext.Window({
										id:"CategoryAddWindow",
										title:"2차카테고리추가 ("+Ext.getCmp("Category1").getStore().getAt(parent).get("title")+")",
										width:350,
										height:110,
										modal:true,
										resizable:false,
										layout:"fit",
										items:[
											new Ext.form.FormPanel({
												id:"CategoryAddForm",
												labelAlign:"right",
												labelWidth:85,
												border:false,
												errorReader:new Ext.form.XmlErrorReader(),
												style:"background:#FFFFFF; padding:10px;",
												items:[
													new Ext.form.TextField({
														fieldLabel:"카테고리명",
														name:"title",
														width:200,
														allowBlank:false
													})
												],
												listeners:{actioncomplete:{fn:function(form,action) {
													if (action.type == "submit") {
														Ext.Msg.show({title:"안내",msg:"성공적으로 추가하였습니다.<br />계속해서 카테고리를 추가하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button){
															Ext.getCmp("Category2").getStore().reload();
															if (button == "ok") {
																Ext.getCmp("CategoryAddForm").getForm().findField("title").setValue();
																Ext.getCmp("CategoryAddForm").getForm().findField("title").clearInvalid();
																Ext.getCmp("CategoryAddForm").getForm().findField("title").focus();
															} else {
																Ext.getCmp("CategoryAddWindow").close();
															}
														}});
	
													}
												}}}
											})
										],
										buttons:[
											new Ext.Button({
												text:"확인",
												icon:"<?php echo $_ENV['dir']; ?>/module/tracker/images/admin/icon_tick.png",
												handler:function() {
													Ext.getCmp("CategoryAddForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/tracker/exec/Admin.do.php?action=category&do=add&parent="+Ext.getCmp("Category2").getStore().baseParams.parent,waitMsg:"데이터를 전송중입니다."});
												}
											}),
											new Ext.Button({
												text:"취소",
												icon:"<?php echo $_ENV['dir']; ?>/module/tracker/images/admin/icon_cross.png",
												handler:function() {
													Ext.getCmp("CategoryAddWindow").close();
												}
											})
										]
									}).show();
								}
							}),
							new Ext.Button({
								text:"카테고리삭제",
								icon:"<?php echo $_ENV['dir']; ?>/module/tracker/images/admin/icon_category_delete.png",
								handler:function() {
									var checked = Ext.getCmp("Category2").selModel.getSelections();
									if (checked.length == 0) {
										Ext.Msg.show({title:"에러",msg:"삭제할 카테고리를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
										return false;
									}
	
									var idxs = new Array();
									for (var i=0, loop=checked.length;i<loop;i++) {
										idxs.push(checked[i].get("idx"));
									}
									var idx = idxs.join(",");
									
									Ext.Msg.show({title:"안내",msg:"선택카테고리를 삭제하게 되면 해당카테고리의 하위카테고리 및 토렌트도 함께 삭제됩니다.<br />정말 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.QUESTION,fn:function(button) {
										if (button == "ok") {
											Ext.Msg.wait("처리중입니다.","Please Wait...");
											Ext.Ajax.request({
												url:"<?php echo $_ENV['dir']; ?>/module/tracker/exec/Admin.do.php",
												success:function() {
													Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
													Ext.getCmp("Category2").getStore().reload();
												},
												failure:function() {
													Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
												},
												headers:{},
												params:{"action":"category","do":"delete","idx":idx}
											});
										}
									}});
								}
							}),
							'->',
							new Ext.Button({
								text:"변경사항저장",
								icon:"<?php echo $_ENV['dir']; ?>/module/tracker/images/admin/icon_disk.png",
								handler:function() {
									Ext.Msg.wait("처리중입니다.","Please Wait...");
									Ext.Ajax.request({
										url:"<?php echo $_ENV['dir']; ?>/module/tracker/exec/Admin.do.php",
										success:function() {
											Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
											Ext.getCmp("Category2").getStore().reload();
										},
										failure:function() {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
										},
										headers:{},
										params:{"action":"category","do":"modify","data":GetGridData(Ext.getCmp("Category2"))}
									});
								}
							})
						],
						bbar:[
							new Ext.Button({
								text:"위로이동",
								icon:"<?php echo $_ENV['dir']; ?>/module/tracker/images/admin/icon_arrow_up.png",
								handler:function() {
									var checked = Ext.getCmp("Category2").selModel.getSelections();

									if (checked.length == 0) {
										Ext.Msg.show({title:"에러",msg:"이동할 카테고리를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
										return false;
									}
	
									var selecter = new Array();
									for (var i=0, loop=checked.length;i<loop;i++) {
										var sort = checked[i].get("sort");
										if (sort != 0) {
											Ext.getCmp("Category2").getStore().getAt(sort).set("sort",sort-1);
											Ext.getCmp("Category2").getStore().getAt(sort-1).set("sort",sort);
	
											selecter.push(sort-1);
											Ext.getCmp("Category2").getStore().sort("sort","ASC");
										} else {
											return false;
										}
									}
	
									for (var i=0, loop=selecter.length;i<loop;i++) {
										Ext.getCmp("Category2").selModel.selectRow(selecter[i]);
									}
								}
							}),
							new Ext.Button({
								text:"아래로이동",
								icon:"<?php echo $_ENV['dir']; ?>/module/tracker/images/admin/icon_arrow_down.png",
								handler:function() {
									var checked = Ext.getCmp("Category2").selModel.getSelections();

									if (checked.length == 0) {
										Ext.Msg.show({title:"에러",msg:"이동할 카테고리를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
										return false;
									}
	
									var selecter = new Array();
									for (var i=checked.length-1;i>=0;i--) {
										var sort = checked[i].get("sort");
										if (sort != Ext.getCmp("Category2").getStore().getCount()-1) {
											Ext.getCmp("Category2").getStore().getAt(sort).set("sort",sort+1);
											Ext.getCmp("Category2").getStore().getAt(sort+1).set("sort",sort);
	
											selecter.push(sort+1);
											Ext.getCmp("Category2").getStore().sort("sort","ASC");
										} else {
											return false;
										}
									}
	
									for (var i=0, loop=selecter.length;i<loop;i++) {
										Ext.getCmp("Category2").selModel.selectRow(selecter[i]);
									}
								}
							})
						],
						cm:new Ext.grid.ColumnModel([
							new Ext.ux.grid.CheckboxSelectionModel(),
							{
								dataIndex:"idx",
								hidden:true,
								hideable:false
							},{
								id:"title2",
								header:"카테고리명",
								dataIndex:"title",
								sortable:false,
								menuDisabled:true,
								resizable:false,
								editor:new Ext.form.TextField({selectOnFocus:true})
							},{
								dataIndex:"sort",
								hidden:true,
								hideable:false
							}
						]),
						sm:new Ext.ux.grid.CheckboxSelectionModel(),
						store:new Ext.data.Store({
							proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/tracker/exec/Admin.get.php"}),
							reader:new Ext.data.JsonReader({
								root:"lists",
								totalProperty:"totalCount",
								fields:["idx","title",{name:"sort",type:"int"}]
							}),
							remoteSort:false,
							sortInfo:{field:"sort",direction:"ASC"},
							baseParams:{action:"category",parent:"-1"},
							listeners:{load:{fn:function(store) {
								var parent = Ext.getCmp("Category1").getStore().find("idx",store.baseParams.parent,false,false);
								if (parent != -1) {
									Ext.getCmp("Category2").setTitle("2차카테고리 ("+Ext.getCmp("Category1").getStore().getAt(parent).get("title")+")");
								} else {
									Ext.getCmp("Category2").setTitle("2차카테고리");
								}
								
								Ext.getCmp("Category3").getStore().baseParams.parent = "-1";
								Ext.getCmp("Category3").getStore().load();
							}}}
						}),
						autoExpandColumn:"title2",
						flex:3,
						listeners:{rowclick:{fn:function(grid,idx,e) {
							Ext.getCmp("Category3").getStore().baseParams.parent = grid.getStore().getAt(idx).get("idx");
							Ext.getCmp("Category3").getStore().load();
						}}}
					}),
					new Ext.grid.EditorGridPanel({
						id:"Category3",
						title:"3차카테고리",
						margins:"5 5 5 0",
						tbar:[
							new Ext.Button({
								text:"카테고리추가",
								icon:"<?php echo $_ENV['dir']; ?>/module/tracker/images/admin/icon_category_add.png",
								handler:function() {
									if (Ext.getCmp("Category3").getStore().baseParams.parent == "-1") {
										Ext.Msg.show({title:"에러",msg:"2차카테고리를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
										return false;
									}
									
									var parent = Ext.getCmp("Category2").getStore().find("idx",Ext.getCmp("Category3").getStore().baseParams.parent,false,false);
									
									new Ext.Window({
										id:"CategoryAddWindow",
										title:"3차카테고리추가 ("+Ext.getCmp("Category2").getStore().getAt(parent).get("title")+")",
										width:350,
										height:110,
										modal:true,
										resizable:false,
										layout:"fit",
										items:[
											new Ext.form.FormPanel({
												id:"CategoryAddForm",
												labelAlign:"right",
												labelWidth:85,
												border:false,
												errorReader:new Ext.form.XmlErrorReader(),
												style:"background:#FFFFFF; padding:10px;",
												items:[
													new Ext.form.TextField({
														fieldLabel:"카테고리명",
														name:"title",
														width:200,
														allowBlank:false
													})
												],
												listeners:{actioncomplete:{fn:function(form,action) {
													if (action.type == "submit") {
														Ext.Msg.show({title:"안내",msg:"성공적으로 추가하였습니다.<br />계속해서 카테고리를 추가하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button){
															Ext.getCmp("Category3").getStore().reload();
															if (button == "ok") {
																Ext.getCmp("CategoryAddForm").getForm().findField("title").setValue();
																Ext.getCmp("CategoryAddForm").getForm().findField("title").clearInvalid();
																Ext.getCmp("CategoryAddForm").getForm().findField("title").focus();
															} else {
																Ext.getCmp("CategoryAddWindow").close();
															}
														}});
	
													}
												}}}
											})
										],
										buttons:[
											new Ext.Button({
												text:"확인",
												icon:"<?php echo $_ENV['dir']; ?>/module/tracker/images/admin/icon_tick.png",
												handler:function() {
													Ext.getCmp("CategoryAddForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/tracker/exec/Admin.do.php?action=category&do=add&parent="+Ext.getCmp("Category3").getStore().baseParams.parent,waitMsg:"데이터를 전송중입니다."});
												}
											}),
											new Ext.Button({
												text:"취소",
												icon:"<?php echo $_ENV['dir']; ?>/module/tracker/images/admin/icon_cross.png",
												handler:function() {
													Ext.getCmp("CategoryAddWindow").close();
												}
											})
										]
									}).show();
								}
							}),
							new Ext.Button({
								text:"카테고리삭제",
								icon:"<?php echo $_ENV['dir']; ?>/module/tracker/images/admin/icon_category_delete.png",
								handler:function() {
									var checked = Ext.getCmp("Category3").selModel.getSelections();
									if (checked.length == 0) {
										Ext.Msg.show({title:"에러",msg:"삭제할 카테고리를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
										return false;
									}
	
									var idxs = new Array();
									for (var i=0, loop=checked.length;i<loop;i++) {
										idxs.push(checked[i].get("idx"));
									}
									var idx = idxs.join(",");
									
									Ext.Msg.show({title:"안내",msg:"선택카테고리를 삭제하게 되면 해당카테고리의 하위카테고리 및 토렌트도 함께 삭제됩니다.<br />정말 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.QUESTION,fn:function(button) {
										if (button == "ok") {
											Ext.Msg.wait("처리중입니다.","Please Wait...");
											Ext.Ajax.request({
												url:"<?php echo $_ENV['dir']; ?>/module/tracker/exec/Admin.do.php",
												success:function() {
													Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
													Ext.getCmp("Category3").getStore().reload();
												},
												failure:function() {
													Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
												},
												headers:{},
												params:{"action":"category","do":"delete","idx":idx}
											});
										}
									}});
								}
							}),
							'->',
							new Ext.Button({
								text:"변경사항저장",
								icon:"<?php echo $_ENV['dir']; ?>/module/tracker/images/admin/icon_disk.png",
								handler:function() {
									Ext.Msg.wait("처리중입니다.","Please Wait...");
									Ext.Ajax.request({
										url:"<?php echo $_ENV['dir']; ?>/module/tracker/exec/Admin.do.php",
										success:function() {
											Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
											Ext.getCmp("Category3").getStore().reload();
										},
										failure:function() {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
										},
										headers:{},
										params:{"action":"category","do":"modify","data":GetGridData(Ext.getCmp("Category3"))}
									});
								}
							})
						],
						bbar:[
							new Ext.Button({
								text:"위로이동",
								icon:"<?php echo $_ENV['dir']; ?>/module/tracker/images/admin/icon_arrow_up.png",
								handler:function() {
									var checked = Ext.getCmp("Category3").selModel.getSelections();

									if (checked.length == 0) {
										Ext.Msg.show({title:"에러",msg:"이동할 카테고리를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
										return false;
									}
	
									var selecter = new Array();
									for (var i=0, loop=checked.length;i<loop;i++) {
										var sort = checked[i].get("sort");
										if (sort != 0) {
											Ext.getCmp("Category3").getStore().getAt(sort).set("sort",sort-1);
											Ext.getCmp("Category3").getStore().getAt(sort-1).set("sort",sort);
	
											selecter.push(sort-1);
											Ext.getCmp("Category3").getStore().sort("sort","ASC");
										} else {
											return false;
										}
									}
	
									for (var i=0, loop=selecter.length;i<loop;i++) {
										Ext.getCmp("Category3").selModel.selectRow(selecter[i]);
									}
								}
							}),
							new Ext.Button({
								text:"아래로이동",
								icon:"<?php echo $_ENV['dir']; ?>/module/tracker/images/admin/icon_arrow_down.png",
								handler:function() {
									var checked = Ext.getCmp("Category3").selModel.getSelections();

									if (checked.length == 0) {
										Ext.Msg.show({title:"에러",msg:"이동할 카테고리를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
										return false;
									}
	
									var selecter = new Array();
									for (var i=checked.length-1;i>=0;i--) {
										var sort = checked[i].get("sort");
										if (sort != Ext.getCmp("Category3").getStore().getCount()-1) {
											Ext.getCmp("Category3").getStore().getAt(sort).set("sort",sort+1);
											Ext.getCmp("Category3").getStore().getAt(sort+1).set("sort",sort);
	
											selecter.push(sort+1);
											Ext.getCmp("Category3").getStore().sort("sort","ASC");
										} else {
											return false;
										}
									}
	
									for (var i=0, loop=selecter.length;i<loop;i++) {
										Ext.getCmp("Category3").selModel.selectRow(selecter[i]);
									}
								}
							})
						],
						cm:new Ext.grid.ColumnModel([
							new Ext.ux.grid.CheckboxSelectionModel(),
							{
								dataIndex:"idx",
								hidden:true,
								hideable:false
							},{
								id:"title3",
								header:"카테고리명",
								dataIndex:"title",
								sortable:false,
								menuDisabled:true,
								resizable:false,
								editor:new Ext.form.TextField({selectOnFocus:true})
							},{
								dataIndex:"sort",
								hidden:true,
								hideable:false
							}
						]),
						sm:new Ext.ux.grid.CheckboxSelectionModel(),
						store:new Ext.data.Store({
							proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/tracker/exec/Admin.get.php"}),
							reader:new Ext.data.JsonReader({
								root:"lists",
								totalProperty:"totalCount",
								fields:["idx","title",{name:"sort",type:"int"}]
							}),
							remoteSort:false,
							sortInfo:{field:"sort",direction:"ASC"},
							baseParams:{action:"category",parent:"0"},
							listeners:{load:{fn:function(store) {
								var parent = Ext.getCmp("Category2").getStore().find("idx",store.baseParams.parent,false,false);
								if (parent != -1) {
									Ext.getCmp("Category3").setTitle("3차카테고리 ("+Ext.getCmp("Category2").getStore().getAt(parent).get("title")+")");
								} else {
									Ext.getCmp("Category3").setTitle("3차카테고리");
								}
							}}}
						}),
						autoExpandColumn:"title3",
						flex:3
					})
				]
			})
		],
		listeners:{render:{fn:function() {
			
		}}}
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>