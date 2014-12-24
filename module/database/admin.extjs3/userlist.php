<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	function TableFunction(data) {
		new Ext.Window({
			id:"TableWindow",
			title:(data ? "테이블수정" : "테이블추가"),
			width:800,
			height:500,
			modal:true,
			resizable:false,
			layout:"fit",
			items:[
				new Ext.form.FormPanel({
					id:"TableForm",
					labelAlign:"right",
					labelWidth:85,
					border:false,
					autoWidth:true,
					autoScroll:true,
					errorReader:new Ext.form.XmlErrorReader(),
					items:[
						new Ext.form.FieldSet({
							defaults:{msgTarget:"side"},
							title:"기본정보",
							autoWidth:true,
							autoHeight:true,
							style:"margin:10px",
							items:[
								new Ext.form.TextField({
									fieldLabel:"테이블명",
									name:"name",
									width:600,
									emptyText:"영문과 숫자, 언더바(_)만을 이용하여 입력하세요.",
									allowBlank:false
								}),
								new Ext.form.TextField({
									fieldLabel:"테이블설명",
									name:"info",
									width:600,
									allowBlank:false
								}),
								new Ext.ux.form.FileUploadField({
									fieldLabel:"액션 PHP파일",
									name:"actionfile",
									width:600,
									buttonText:"",
									buttonCfg:{iconCls:"upload-file"},
									emptyText:"이 테이블에 자료를 등록할 때, 별도로 수행할 작업이 있다면, 해당하는 PHP파일을 첨부하세요.",
									allowBlank:<?php echo $field[$i]['option'] == 'NOT NULL' ? 'false' : 'true'; ?>,
									listeners:{
										focus:{fn:function(form) {
											if (form.getValue()) {
												Ext.Msg.show({title:"초기화선택",msg:"액션 PHP파일을 초기화 하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
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
								new Ext.form.Hidden({
									name:"idx"
								}),
								new Ext.form.Hidden({
									name:"field",
									allowBlank:false
								})
							]
						}),
						new Ext.grid.EditorGridPanel({
							id:"TableGrid",
							title:"필드설정",
							height:300,
							style:"padding:0px 10px 10px 10px;",
							tbar:[
								new Ext.Button({
									text:"필드추가",
									icon:"<?php echo $_ENV['dir']; ?>/module/database/images/admin/icon_textfield_add.png",
									handler:function() {
										new Ext.Window({
											title:"필드추가",
											id:"FieldAddWindow",
											width:400,
											modal:true,
											resizable:false,
											layout:"fit",
											items:[
												new Ext.form.FormPanel({
													id:"FieldAddForm",
													labelAlign:"right",
													labelWidth:85,
													border:false,
													autoWidth:true,
													autoScroll:true,
													autoHeight:true,
													style:"background:#FFFFFF; padding:10px;",
													errorReader:new Ext.form.XmlErrorReader(),
													items:[
														new Ext.form.TextField({
															fieldLabel:"필드명",
															name:"name",
															width:250,
															enableKeyEvents:true,
															listeners:{keydown:{fn:function(form,e) {
																if (e.keyCode == 13) {
																	if (!Ext.getCmp("FieldAddForm").getForm().findField("name").getValue()) {
																		Ext.Msg.show({title:"에러",msg:"필드명을 입력하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
																		return false;
																	}

																	if (Ext.getCmp("FieldAddForm").getForm().findField("name").getValue().match(/^[a-z0-9_]+$/gi) == null) {
																		Ext.Msg.show({title:"에러",msg:"영문과 숫자, 언더바(_)만 입력하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
																		Ext.getCmp("FieldAddForm").getForm().findField("name").setValue();
																		return false;
																	}

																	for (var i=0, loop=Ext.getCmp("TableGrid").getStore().getCount();i<loop;i++) {
																		if (Ext.getCmp("FieldAddForm").getForm().findField("name").getValue() == Ext.getCmp("TableGrid").getStore().getAt(i).get("newname")) {
																			Ext.Msg.show({title:"에러",msg:"이미 사용중인 필드명입니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
																			Ext.getCmp("FieldAddForm").getForm().findField("name").setValue();
																			return false;
																		}
																	}

																	GridInsertRow(Ext.getCmp("TableGrid"),{"sort":Ext.getCmp("TableGrid").getStore().getCount(),"name":Ext.getCmp("FieldAddForm").getForm().findField("name").getValue(),"newname":Ext.getCmp("FieldAddForm").getForm().findField("name").getValue()});
																	Ext.getCmp("FieldAddWindow").close();
																}
															}}},
															emptyText:"영문 및 숫자, 언더바(_)만 입력하세요."
														})
													]
												})
											],
											buttons:[
												new Ext.Button({
													text:"확인",
													icon:"<?php echo $_ENV['dir']; ?>/module/database/images/admin/icon_tick.png",
													handler:function() {
														if (!Ext.getCmp("FieldAddForm").getForm().findField("name").getValue()) {
															Ext.Msg.show({title:"에러",msg:"필드명을 입력하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
															return false;
														}

														if (Ext.getCmp("FieldAddForm").getForm().findField("name").getValue().match(/^[a-z0-9_]+$/gi) == null) {
															Ext.Msg.show({title:"에러",msg:"영문과 숫자, 언더바(_)만 입력하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
															Ext.getCmp("FieldAddForm").getForm().findField("name").setValue();
															return false;
														}

														for (var i=0, loop=Ext.getCmp("TableGrid").getStore().getCount();i<loop;i++) {
															if (Ext.getCmp("FieldAddForm").getForm().findField("name").getValue() == Ext.getCmp("TableGrid").getStore().getAt(i).get("newname")) {
																Ext.Msg.show({title:"에러",msg:"이미 사용중인 필드명입니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
																Ext.getCmp("FieldAddForm").getForm().findField("name").setValue();
																return false;
															}
														}

														GridInsertRow(Ext.getCmp("TableGrid"),{"sort":Ext.getCmp("TableGrid").getStore().getCount(),"name":Ext.getCmp("FieldAddForm").getForm().findField("name").getValue(),"newname":Ext.getCmp("FieldAddForm").getForm().findField("name").getValue()});
														Ext.getCmp("FieldAddWindow").close();
													}
												}),
												new Ext.Button({
													text:"취소",
													icon:"<?php echo $_ENV['dir']; ?>/module/database/images/admin/icon_cross.png",
													handler:function() {
														Ext.getCmp("FieldAddWindow").close();
													}
												})
											]
										}).show();
									}
								}),
								new Ext.Button({
									text:"필드삭제",
									icon:"<?php echo $_ENV['dir']; ?>/module/database/images/admin/icon_textfield_delete.png",
									handler:function() {
										var checked = Ext.getCmp("TableGrid").selModel.getSelections();

										if (checked.length == 0) {
											Ext.Msg.show({title:"에러",msg:"삭제할 필드를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
											return false;
										}

										if (data) {
											Ext.Msg.show({title:"안내",msg:"필드를 삭제할경우, 해당 필드에 있는 모든 데이터가 유실됩니다.<br />정말 필드를 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
												if (button == "ok") {
													for (var i=0, loop=checked.length;i<loop;i++) {
														Ext.getCmp("TableGrid").getStore().remove(checked[i]);
													}
												}
											}});
										} else {
											for (var i=0, loop=checked.length;i<loop;i++) {
												Ext.getCmp("TableGrid").getStore().remove(checked[i]);
											}
										}
										Ext.getCmp("TableGrid").selModel.deselectAll();
										for (var i=0, loop=Ext.getCmp("TableGrid").getStore().getCount();i<loop;i++) {
											Ext.getCmp("TableGrid").getStore().getAt(i).set("sort",i);
										}
									}
								}),
								'-',
								new Ext.Button({
									text:"위로 이동",
									icon:"<?php echo $_ENV['dir']; ?>/module/database/images/admin/icon_arrow_up.png",
									handler:function() {
										var checked = Ext.getCmp("TableGrid").selModel.getSelections();

										if (checked.length == 0) {
											Ext.Msg.show({title:"에러",msg:"이동할 필드를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
											return false;
										}

										var selecter = new Array();
										for (var i=0, loop=checked.length;i<loop;i++) {
											var sort = checked[i].get("sort");
											if (sort != 0) {
												Ext.getCmp("TableGrid").getStore().getAt(sort).set("sort",sort-1);
												Ext.getCmp("TableGrid").getStore().getAt(sort-1).set("sort",sort);

												selecter.push(sort-1);
												Ext.getCmp("TableGrid").getStore().sort("sort","ASC");
											} else {
												return false;
											}
										}

										for (var i=0, loop=selecter.length;i<loop;i++) {
											Ext.getCmp("TableGrid").selModel.selectRow(selecter[i]);
										}
									}
								}),
								new Ext.Button({
									text:"아래로 이동",
									icon:"<?php echo $_ENV['dir']; ?>/module/database/images/admin/icon_arrow_down.png",
									handler:function() {
										var checked = Ext.getCmp("TableGrid").selModel.getSelections();

										if (checked.length == 0) {
											Ext.Msg.show({title:"에러",msg:"이동할 필드를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
											return false;
										}

										var selecter = new Array();
										for (var i=checked.length-1;i>=0;i--) {
											var sort = checked[i].get("sort");
											if (sort != Ext.getCmp("TableGrid").getStore().getCount()-1) {
												Ext.getCmp("TableGrid").getStore().getAt(sort).set("sort",sort+1);
												Ext.getCmp("TableGrid").getStore().getAt(sort+1).set("sort",sort);

												selecter.push(sort+1);
												Ext.getCmp("TableGrid").getStore().sort("sort","ASC");
											} else {
												return false;
											}
										}

										for (var i=0, loop=selecter.length;i<loop;i++) {
											Ext.getCmp("TableGrid").selModel.selectRow(selecter[i]);
										}
									}
								})
							],
							cm:new Ext.grid.ColumnModel([
								new Ext.grid.RowNumberer(),
								{
									header:"필드명",
									dataIndex:"name",
									width:100,
									hideable:false,
									editor:new Ext.form.TextField({selectOnFocus:true})
								},{
									header:"필드명",
									dataIndex:"newname",
									width:100,
									hidden:true,
									hideable:false,
									editor:new Ext.form.TextField({selectOnFocus:true})
								},{
									header:"필드설명",
									dataIndex:"info",
									width:150,
									editor:new Ext.form.TextField({selectOnFocus:true})
								},{
									header:"종류",
									dataIndex:"type",
									width:100,
									editor:new Ext.form.ComboBox({
										typeAhead:true,
										triggerAction:"all",
										lazyRender:true,
										listClass:"x-combo-list-small",
										store:new Ext.data.SimpleStore({
											fields:["type"],
											data:[["INT"],["VARCHAR"],["TEXT"],["HTML"],["DATE"],["SELECT"],["FILE"]]
										}),
										editable:false,
										mode:"local",
										displayField:"type",
										valueField:"type"
									})
								},{
									header:"길이/값",
									dataIndex:"length",
									width:95,
									renderer:function(value) {
										if (value == parseInt(value)) return GridNumberFormat(value);
										else return value;
									},
									editor:new Ext.form.TextField({selectOnFocus:true})
								},{
									header:"옵션",
									dataIndex:"option",
									width:90,
									editor:new Ext.form.ComboBox({
										typeAhead:true,
										triggerAction:"all",
										lazyRender:true,
										listClass:"x-combo-list-small",
										store:new Ext.data.SimpleStore({
											fields:["option","value"],
											data:[["NONE",""],["AUTO_INCREMENT","AUTO_INCREMENT"],["NOT NULL","NOT NULL"]]
										}),
										editable:false,
										mode:"local",
										displayField:"option",
										valueField:"value"
									})
								},{
									header:"기본값",
									dataIndex:"default",
									width:80,
									editor:new Ext.form.TextField({selectOnFocus:true})
								},{
									header:"인덱스",
									dataIndex:"index",
									width:80,
									editor:new Ext.form.ComboBox({
										typeAhead:true,
										triggerAction:"all",
										lazyRender:true,
										listClass:"x-combo-list-small",
										store:new Ext.data.SimpleStore({
											fields:["index","value"],
											data:[["NONE",""],["PRIMARY","PRIMARY"],["UNIQUE","UNIQUE"],["BTREE","BTREE"]]
										}),
										editable:false,
										mode:"local",
										displayField:"index",
										valueField:"value"
									})
								},
								new Ext.ux.grid.CheckboxSelectionModel()
							]),
							store:new Ext.data.Store({
								proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/database/exec/Admin.get.php"}),
								reader:new Ext.data.JsonReader({
									root:"lists",
									totalProperty:"totalCount",
									fields:["name","newname","info","type","length","option","default","index",{name:"sort",type:"int"}]
								}),
								remoteSort:false,
								sortInfo:{field:"sort",direction:"ASC"},
								baseParams:{"action":"field","idx":""}
							}),
							sm:new Ext.ux.grid.CheckboxSelectionModel(),
							trackMouseOver:true,
							listeners:{
								beforeedit:{fn:function(object) {
									if (data) {
										if (object.field == "name") return false;
									} else {
										if (object.field == "newname") return false;
									}

									if (object.field == "length" && (object.record.data.type == "FILE" || object.record.data.type == "TEXT" || object.record.data.type == "HTML" || object.record.data.type == "DATE")) return false;
								}},
								afteredit:{fn:function(object) {
									if (object.field == "index" && object.value != "PRIMARY") {
										if (object.record.data.option == "AUTO_INCREMENT") Ext.getCmp("TableGrid").getStore().getAt(object.row).set("option","");
									}

									if (object.field == "option" && object.value == "AUTO_INCREMENT") {
										Ext.getCmp("TableGrid").getStore().getAt(object.row).set("type","INT");
										Ext.getCmp("TableGrid").getStore().getAt(object.row).set("length","11");
										Ext.getCmp("TableGrid").getStore().getAt(object.row).set("index","PRIMARY");
									}

									if (object.field == "type" && (object.value == "FILE" || object.value == "TEXT" || object.value == "HTML" || object.value == "DATE")) {
										Ext.getCmp("TableGrid").getStore().getAt(object.row).set("length","");
									}

									if (object.field == "name" || object.field == "newname") {
										for (var i=0, loop=Ext.getCmp("TableGrid").getStore().getCount();i<loop;i++) {
											if (i != object.row && Ext.getCmp("TableGrid").getStore().getAt(i).get("newname") == object.value) {
												Ext.Msg.show({title:"에러",msg:"이미 사용중인 필드명입니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
												Ext.getCmp("TableGrid").getStore().getAt(object.row).set(object.field,object.originalValue);
												object.value = object.originalValue;
												object.record.commit();
												break;
											}
										}
									}

									if (object.field == "name") {
										Ext.getCmp("TableGrid").getStore().getAt(object.row).set("newname",object.value);
									}
								}}
							}
						})
					],
					listeners:{
						render:{fn:function() {
							if (data) {
								Ext.getCmp("TableForm").getForm().findField("name").setValue(data.get("name"));
								Ext.getCmp("TableForm").getForm().findField("info").setValue(data.get("info"));
								Ext.getCmp("TableForm").getForm().findField("idx").setValue(data.get("idx"));
								Ext.getCmp("TableGrid").getStore().baseParams.idx = data.get("idx");
								Ext.getCmp("TableGrid").getStore().load();
								Ext.getCmp("TableGrid").colModel.setHidden(1,true);
								Ext.getCmp("TableGrid").colModel.setHidden(2,false);
							} else {
								Ext.getCmp("TableGrid").colModel.setHidden(1,false);
								Ext.getCmp("TableGrid").colModel.setHidden(2,true);
							}
						}},
						actioncomplete:{fn:function(form,action) {
						if (action.type == "submit") {
							if (data) {
								Ext.Msg.show({title:"안내",msg:"테이블을 수정하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
							} else {
								Ext.Msg.show({title:"안내",msg:"테이블을 추가하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
							}
							//Ext.getCmp("TableWindow").close();
							Ext.getCmp("ListPanel").getStore().reload();
						}
					}}}
				})
			],
			buttons:[
				new Ext.Button({
					text:(data ? "수정" : "확인"),
					icon:"<?php echo $_ENV['dir']; ?>/module/database/images/admin/icon_tick.png",
					handler:function() {
						var isPrimary = false;
						for (var i=0, loop=Ext.getCmp("TableGrid").getStore().getCount();i<loop;i++) {
							var field = Ext.getCmp("TableGrid").getStore().getAt(i);
							if (!field.get("info") || !field.get("type") || ((field.get("type") == "INT" || field.get("type") == "VARCHAR") && !field.get("length"))) {
								Ext.Msg.show({title:"에러",msg:"빠진 항목이 있습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
								return false;
							}

							if (field.get("type") == "SELECT" && (field.get("length").indexOf(",") == -1 || field.get("length").indexOf("'") == -1)) {
								Ext.Msg.show({title:"에러",msg:"SELECT 형태는 길이/값 항목에 '선택사항1','선택사항2' 방식으로 값을 입력하여야 합니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
								return false;
							}

							if (field.get("index") == "PRIMARY") {
								if (isPrimary == true) {
									Ext.Msg.show({title:"에러",msg:"PRIMARY인덱스는 한개의 필드만 적용가능 합니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
									return false;
								}

								field.set("type","INT");
								field.set("length","11");
								isPrimary = true;
							}

							if (field.get("type") == "FILE" || field.get("type") == "TEXT" || field.get("type") == "HTML" || field.get("type") == "DATE") {
								field.set("length","");
							}

							if (field.get("type") == "FILE") {
								field.set("default","");
							}
						}

						if (isPrimary == false) {
							Ext.Msg.show({title:"에러",msg:"PRIMARY인덱스는 반드시 한개이상 설정하여야 합니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
							return false;
						}

						Ext.getCmp("TableForm").getForm().findField("field").setValue(GetGridData(Ext.getCmp("TableGrid")));
						if (data) {
							Ext.getCmp("TableForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/database/exec/Admin.do.php?action=table&do=modify",waitMsg:"테이블을 수정중입니다."});
						} else {
							Ext.getCmp("TableForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/database/exec/Admin.do.php?action=table&do=add",waitMsg:"테이블을 추가중입니다."});
						}
					}
				}),
				new Ext.Button({
					text:"취소",
					icon:"<?php echo $_ENV['dir']; ?>/module/database/images/admin/icon_cross.png",
					handler:function() {
						Ext.getCmp("TableWindow").close();
					}
				})
			]
		}).show();
	}

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"사용자테이블관리",
		layout:"fit",
		items:[
			new Ext.grid.GridPanel({
				id:"ListPanel",
				border:false,
				tbar:[
					new Ext.Button({
						text:"테이블추가",
						icon:"<?php echo $_ENV['dir']; ?>/module/database/images/admin/icon_database_add.png",
						handler:function() {
							TableFunction();
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
						dataIndex:"group",
						hideable:false
					},{
						header:"테이블명",
						dataIndex:"name",
						sortable:true,
						summaryType:"count",
						width:150,
						summaryRenderer:GridSummaryCount
					},{
						header:"테이블설명",
						dataIndex:"info",
						sortable:true,
						width:350
					},{
						header:"자료수",
						dataIndex:"record",
						sortable:true,
						width:60,
						summaryType:"sum",
						renderer:GridNumberFormat
					},{
						header:"DB용량",
						dataIndex:"dbsize",
						sortable:true,
						width:80,
						summaryType:"sum",
						renderer:function(value) {
							return '<div style="font-family:arial; text-align:right;">'+GetFileSize(value)+'</div>';
						}
					},{
						header:"첨부용량",
						dataIndex:"filesize",
						sortable:true,
						width:80,
						summaryType:"sum",
						renderer:function(value) {
							return '<div style="font-family:arial; text-align:right;">'+GetFileSize(value)+'</div>';
						}
					},
					new Ext.grid.CheckboxSelectionModel()
				]),
				store:new Ext.data.GroupingStore({
					proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/database/exec/Admin.get.php"}),
					reader:new Ext.data.JsonReader({
						root:"lists",
						totalProperty:"totalCount",
						fields:[{name:"idx",type:"int"},"group","name","info",{name:"record",type:"int"},{name:"dbsize",type:"int"},{name:"filesize",type:"int"}]
					}),
					remoteSort:false,
					groupField:"group",
					sortInfo:{field:"name",direction:"ASC"},
					baseParams:{action:"list"}
				}),
				sm:new Ext.grid.CheckboxSelectionModel(),
				loadMask:{msg:"데이터를 로딩중입니다."},
				plugins:new Ext.ux.grid.GroupSummary(),
				view:new Ext.grid.GroupingView({
					enableGroupingMenu:false,
					hideGroupedColumn:true,
					showGroupName:false,
					enableNoGroups:false,
					headersDisabled:false,
					showGroupHeader:false
				}),
				listeners:{
					rowdblclick:{fn:function(grid,idx,event) {
						new Ext.Window({
							title:"테이블보기",
							modal:true,
							maximizable:true,
							width:900,
							height:550,
							style:"background:#FFFFFF;",
							html:'<iframe src="<?php echo $_ENV['dir']; ?>/module/database/admin/table.php?idx='+grid.getStore().getAt(idx).get("idx")+'" frameborder="0" style="width:100%; height:100%;" scrolling="0"></iframe>'
						}).show();
					}},
					rowcontextmenu:{fn:function(grid,idx,e) {
						GridContextmenuSelect(grid,idx);

						var data = grid.getStore().getAt(idx);

						var menu = new Ext.menu.Menu();
						menu.add('<b class="menu-title">'+data.get("name")+'</b>');
						menu.add({
							text:"테이블수정",
							icon:"<?php echo $_ENV['dir']; ?>/module/database/images/admin/icon_database_edit.png",
							handler:function(item) {
								TableFunction(data);
							}
						});
						menu.add({
							text:"테이블비우기",
							icon:"<?php echo $_ENV['dir']; ?>/module/database/images/admin/icon_database_error.png",
							handler:function(item) {
								Ext.Msg.show({title:"안내",msg:"테이블을 비우면, 모든 데이터 및 첨부파일이 삭제됩니다.<br />테이블을 비우시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
									if (button == "ok") {
										Ext.Ajax.request({
											url:"<?php echo $_ENV['dir']; ?>/module/database/exec/Admin.do.php",
											success:function() {
												Ext.Msg.show({title:"안내",msg:"성공적으로 비웠습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
												Ext.getCmp("ListPanel").getStore().reload();
											},
											failure:function() {
												Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
											},
											headers:{},
											params:{"action":"table","do":"truncate","idx":data.get("idx")}
										});
									}
								}});
							}
						});
						menu.add({
							text:"테이블삭제",
							icon:"<?php echo $_ENV['dir']; ?>/module/database/images/admin/icon_database_delete.png",
							handler:function(item) {

							}
						});

						e.stopEvent();
						menu.showAt(e.getXY());
					}}
				}
			})
		]
	});

	Ext.getCmp("ListPanel").getStore().load();
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>