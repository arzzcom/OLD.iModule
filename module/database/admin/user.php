<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/module/database/script/AzUploader.js"></script>
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/module/wysiwyg/script/wysiwyg.js"></script>
<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	function AddTable(idx) {
		new Ext.Window({
			id:"AddTableWindow",
			title:(idx ? "테이블수정" : "테이블추가"),
			width:850,
			modal:true,
			resizable:false,
			items:[
				new Ext.form.FormPanel({
					id:"AddTableForm",
					border:false,
					fieldDefaults:{labelAlign:"right",labelWidth:85,anchor:"100%",allowBlank:false},
					bodyPadding:"10 10 5 10",
					items:[
						new Ext.form.FieldSet({
							title:"기본정보",
							items:[
								new Ext.form.TextField({
									fieldLabel:"테이블명",
									name:"name",
									emptyText:"영문과 숫자, 언더바(_)만을 이용하여 입력하세요."
								}),
								new Ext.form.TextField({
									fieldLabel:"테이블설명",
									name:"info"
								}),
								new Ext.form.Hidden({
									name:"field"
								}),
								new Ext.form.Hidden({
									name:"update",
									allowBlank:true
								}),
								new Ext.form.Hidden({
									name:"delete",
									value:"[]",
									allowBlank:true
								})
							]
						})
					]
				}),
				new Ext.Panel({
					border:false,
					style:{background:"#FFFFFF"},
					items:[
						new Ext.grid.GridPanel({
							id:"AddFieldList",
							title:"필드설정",
							height:300,
							margin:"0 10 10 10",
							tbar:[
								new Ext.Button({
									text:"필드추가",
									icon:"<?php echo $_ENV['dir']; ?>/module/database/images/admin/icon_textfield_add.png",
									handler:function() {
										new Ext.Window({
											title:"필드추가",
											id:"AddFieldWindow",
											width:400,
											modal:true,
											resizable:false,
											layout:"fit",
											items:[
												new Ext.form.FormPanel({
													id:"AddFieldForm",
													border:false,
													fieldDefaults:{labelAlign:"right",labelWidth:85,anchor:"100%",allowBlank:false},
													bodyPadding:"5 5 0 5",
													items:[
														new Ext.form.TextArea({
															fieldLabel:"필드명",
															name:"name",
															height:100,
															emptyText:"영문소문자 및 숫자, 언더바(_)만 입력하세요. 여러개의 필드를 추가할 경우, 한줄에 하나의 필드명을 입력하시면 됩니다.",
															validator:function(value) {
																var temp = value.split("\n");
																for (var i=0, loop=temp.length;i<loop;i++) {
																	if (temp[i].search(/^[a-z_0-9]+$/) == -1) {
																		return "필드명은 영문소문자 및 숫자, 언더바(_)만 가능합니다.";
																	} else if (Ext.getCmp("AddFieldList").getStore().find("name",temp[i],0,false,true,true) > -1) {
																		return "필드명이 중복됩니다.";
																	} else {
																		return true;
																	}
																}
															}
														})
													]
												})
											],
											buttons:[
												new Ext.Button({
													text:"확인",
													handler:function() {
														if (Ext.getCmp("AddFieldForm").isValid() == true) {
															var temp = Ext.getCmp("AddFieldForm").getForm().findField("name").getValue().split("\n");
															for (var i=0, loop=temp.length;i<loop;i++) {
																Ext.getCmp("AddFieldList").getStore().add({name:temp[i],sort:Ext.getCmp("AddFieldList").getStore().getCount()});
															}
														} else {
															Ext.Msg.show({title:"에러",msg:"필드명이 잘못입력되었습니다.<br />에러메세지를 확인하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
														}
														Ext.getCmp("AddFieldWindow").close();
													}
												}),
												new Ext.Button({
													text:"취소",
													handler:function() {
														Ext.getCmp("AddFieldWindow").close();
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
										var checked = Ext.getCmp("AddFieldList").getSelectionModel().getSelection();
		
										if (checked.length == 0) {
											Ext.Msg.show({title:"에러",msg:"삭제할 필드를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
											return false;
										}
										
										var deleteField = Ext.getCmp("AddTableForm").getForm().findField("delete").getValue() ? Ext.JSON.decode(Ext.getCmp("AddTableForm").getForm().findField("delete").getValue()) : new Array();

										for (var i=0, loop=checked.length;i<loop;i++) {
											Ext.getCmp("AddFieldList").getStore().remove(checked[i]);
											deleteField.push(checked[i].get("name"));
										}
										
										for (var i=0, loop=Ext.getCmp("AddFieldList").getStore().getCount();i<loop;i++) {
											Ext.getCmp("AddFieldList").getStore().getAt(i).set("sort",i);
										}
										
										Ext.getCmp("AddTableForm").getForm().findField("delete").setValue(Ext.JSON.encode(deleteField));
									}
								}),
								'-',
								{xtype:"tbtext",text:"순서변경"},
								new Ext.Button({
									text:"위로 이동",
									icon:"<?php echo $_ENV['dir']; ?>/module/database/images/admin/icon_arrow_up.png",
									handler:function() {
										var checked = Ext.getCmp("AddFieldList").getSelectionModel().getSelection();

										if (checked.length == 0) {
											Ext.Msg.show({title:"에러",msg:"이동할 필드를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
											return false;
										}
		
										var selecter = new Array();
										for (var i=0, loop=checked.length;i<loop;i++) {
											selecter.push(checked[i].get("sort")-1);
										}
										for (var i=0, loop=checked.length;i<loop;i++) {
											var sort = checked[i].get("sort");
											if (sort != 0) {
												Ext.getCmp("AddFieldList").getStore().getAt(sort).set("sort",sort-1);
												Ext.getCmp("AddFieldList").getStore().getAt(sort-1).set("sort",sort);
												Ext.getCmp("AddFieldList").getStore().sort("sort","ASC");
											} else {
												return false;
											}
										}
										
										for (var i=0, loop=selecter.length;i<loop;i++) {
											Ext.getCmp("AddFieldList").getSelectionModel().select(selecter[i],i!=0);
										}
									}
								}),
								new Ext.Button({
									text:"아래로 이동",
									icon:"<?php echo $_ENV['dir']; ?>/module/database/images/admin/icon_arrow_down.png",
									handler:function() {
										var checked = Ext.getCmp("AddFieldList").getSelectionModel().getSelection();

										if (checked.length == 0) {
											Ext.Msg.show({title:"에러",msg:"이동할 필드를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
											return false;
										}
		
										var selecter = new Array();
										for (var i=0, loop=checked.length;i<loop;i++) {
											selecter.push(checked[i].get("sort")+1);
										}
										for (var i=checked.length-1;i>=0;i--) {
											var sort = checked[i].get("sort");
											if (sort != Ext.getCmp("AddFieldList").getStore().getCount()-1) {
												Ext.getCmp("AddFieldList").getStore().getAt(sort).set("sort",sort+1);
												Ext.getCmp("AddFieldList").getStore().getAt(sort+1).set("sort",sort);
												Ext.getCmp("AddFieldList").getStore().sort("sort","ASC");
											} else {
												return false;
											}
										}
										
										for (var i=0, loop=selecter.length;i<loop;i++) {
											Ext.getCmp("AddFieldList").getSelectionModel().select(selecter[i],i!=0);
										}
									}
								}),
								'->',
								{xtype:"tbtext",text:"더블클릭 : 수정"}
							],
							columns:[
								new Ext.grid.RowNumberer(),
								{
									header:"필드명",
									dataIndex:"name",
									width:100,
									editor:new Ext.form.TextField({
										selectOnFocus:true,
										allowBlank:false,
										validator:function(value) {
											if (value.search(/^[a-z_0-9]+$/) == -1) {
												return "필드명은 영문소문자 및 숫자, 언더바(_)만 가능합니다.";
											} else {
												return true;
											}
										}
									})
								},{
									header:"필드설명",
									dataIndex:"info",
									minWidth:100,
									flex:1,
									editor:new Ext.form.TextField({selectOnFocus:true,allowBlank:false})
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
									width:110,
									renderer:function(value,p,record) {
										if (record.data.type.search(/^(FILE|TEXT|HTML|DATE)$/) == 0) {
											return '<span style="color:#666666;">설정불가</span>';
										} else if (value == "") {
											if (record.data.type == "SELECT") {
												return '<span style="color:#666666;">\'선택값1\',\'선택값2\'</span>';
											}
										} else {
											if (value.search(/^[0-9]+$/) == 0) return GridNumberFormat(value);
											else return value;
										}
									},
									editor:new Ext.form.TextField({
										selectOnFocus:true,
										validator:function(value) {
											if (value.search(/^[0-9]+$/) == 0) {
												return true;
											} else {
												var temp = value.split(",");
												for (var i=0, loop=temp.length;i<loop;i++) {
													if (temp[i].search(/^'[^']+'$/) == -1) {
														return "길이값이 숫자이거나, '선택값1','선택값2' 등의 형식이 아닙니다.";
													}
												}
											}
											return true;
										}
									})
								},{
									header:"옵션",
									dataIndex:"option",
									width:130,
									renderer:function(value,p,record) {
										if (record.data.type.search(/^(SELECT|FILE|TEXT|HTML|DATE)$/) == 0) {
											return '<span style="color:#666666;">설정불가</span>';
										} else {
											return value;
										}
									},
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
									renderer:function(value,p,record) {
										if (record.data.type.search(/^(SELECT|FILE|DATE|TEXT|HTML)$/) == 0) {
											return '<span style="color:#666666;">설정불가</span>';
										} else {
											return value;
										}
									},
									editor:new Ext.form.TextField({selectOnFocus:true})
								},{
									header:"인덱스",
									dataIndex:"index",
									width:80,
									renderer:function(value,p,record) {
										if (record.data.type.search(/^(FILE|TEXT|HTML)$/) == 0) {
											return '<span style="color:#666666;">설정불가</span>';
										} else {
											return value;
										}
									},
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
								}
							],
							store:new Ext.data.JsonStore({
								proxy:{
									type:"ajax",
									simpleSortMode:true,
									url:"<?php echo $_ENV['dir']; ?>/module/database/exec/Admin.get.php",
									reader:{type:"json",root:"lists",totalProperty:"totalCount"},
									extraParams:{action:"user",get:"fieldlist",idx:(idx ? idx : "0")}
								},
								remoteSort:false,
								sorters:[{property:"sort",direction:"ASC"}],
								autoLoad:true,
								pageSize:50,
								fields:["name","info","type","length","option","default","index",{name:"sort",type:"int"}]
							}),
							columnLines:true,
							plugins:[
								new Ext.grid.plugin.CellEditing({
									pluginId:"AddFieldEdit",
									clicksToEdit:2,
									listeners:{
										beforeedit:{fn:function(edit,object) {
											if (object.field == "length") {
												if (object.record.data.type.search(/^(FILE|TEXT|HTML|DATE)$/) == 0) {
													return false;
												}
											}
											if (object.field == "option") {
												if (object.record.data.type.search(/^(SELECT|FILE|TEXT|HTML|DATE)$/) == 0) {
													return false;
												}
											}
											if (object.field == "default") {
												if (object.record.data.type.search(/^(SELECT|FILE|DATE|TEXT|HTML)$/) == 0) {
													return false;
												}
											}
											if (object.field == "index") {
												if (object.record.data.type.search(/^(FILE|TEXT|HTML)$/) == 0) {
													return false;
												}
											}
											return true;
										}},
										afteredit:{fn:function(edit,object) {
											if (object.field == "type") {
												if (object.value == "SELECT") {
													var temp = object.value.split(",");
													for (var i=0, loop=temp.length;i<loop;i++) {
														if (temp[i].search(/^'[^']+'$/) == -1) {
															object.grid.getStore().getAt(object.rowIdx).set("length","");
															break;
														}
													}
													
													if (object.record.data.index == "PRIMARY" || object.record.data.index == "UNIQUE") {
														object.grid.getStore().getAt(object.rowIdx).set("index","");
													}
													
													object.grid.getStore().getAt(object.rowIdx).set("default","");
												}
												
												if (object.value != "INT") {
													if (object.record.data.option == "AUTO_INCREMENT" || object.record.data.option == "NOT NULL") {
														object.grid.getStore().getAt(object.rowIdx).set("option","");
													}
												}
												
												if (object.value.search(/^(FILE|TEXT|HTML|DATE)$/) == 0) {
													object.grid.getStore().getAt(object.rowIdx).set("option","");
													object.grid.getStore().getAt(object.rowIdx).set("index","");
												}
											}
											
											if (object.field == "option") {
												if (object.value == "AUTO_INCREMENT") {
													object.grid.getStore().getAt(object.rowIdx).set("type","INT");
													object.grid.getStore().getAt(object.rowIdx).set("info","고유값");
													object.grid.getStore().getAt(object.rowIdx).set("length","11");
													object.grid.getStore().getAt(object.rowIdx).set("default","");
													object.grid.getStore().getAt(object.rowIdx).set("index","PRIMARY");
												}
											}
											
											if (object.field == "index") {
												if (object.value != "PRIMARY" && object.record.data.option == "AUTO_INCREMENT") {
													object.grid.getStore().getAt(object.rowIdx).set("option","");
												}
												if (objec.value == "PRIMARY") {
													object.grid.getStore().getAt(object.rowIdx).set("type","INT");
												}
											}
										}}
									}
								})
							],
							selModel:new Ext.ux.selection.CheckboxModel({checkOnly:true,injectCheckbox:"last"})
						})
					]
				})
			],
			buttons:[
				new Ext.Button({
					text:(idx ? "수정" : "확인"),
					handler:function() {
						Ext.getCmp("AddFieldList").getSelectionModel().deselectAll();
						var isPrimary = false;
						var fields = new Array();
						for (var i=0, loop=Ext.getCmp("AddFieldList").getStore().getCount();i<loop;i++) {
							fields.push(Ext.getCmp("AddFieldList").getStore().getAt(i).data);
							
							var field = Ext.getCmp("AddFieldList").getStore().getAt(i);
							
							if (Ext.getCmp("AddFieldList").getStore().find("name",field.get("name"),i+1,false,true,true) > -1) {
								Ext.Msg.show({title:"에러",msg:"필드설정에 중복되는 필드명이 있습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING,fn:function() {
									Ext.getCmp("AddFieldList").getPlugin("AddFieldEdit").startEdit(Ext.getCmp("AddFieldList").getStore().find("name",field.get("name"),i+1,false,true,true),1);
								}});
								return false;
							}
							
							if (Ext.getCmp("AddFieldList").getStore().getAt(i).get("option") == "AUTO_INCREMENT" && Ext.getCmp("AddFieldList").getStore().find("option","AUTO_INCREMENT",i+1,false,true,true) > -1) {
								Ext.Msg.show({title:"에러",msg:"필드설정에 AUTO_INCREMENT 가 중복설정되었습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING,fn:function() {
									Ext.getCmp("AddFieldList").getPlugin("AddFieldEdit").startEdit(Ext.getCmp("AddFieldList").getStore().find("option","AUTO_INCREMENT",i+1,false,true,true),5);
								}});
								return false;
							}
							
							if (!field.get("info") || !field.get("type") || ((field.get("type") == "INT" || field.get("type") == "VARCHAR") && !field.get("length"))) {
								Ext.Msg.show({title:"에러",msg:"필드설정에 빠진 항목이 있습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
								return false;
							}

							if (field.get("type") == "SELECT") {
								var temp = field.get("length").split(",");
								for (var j=0, loopj=temp.length;j<loopj;j++) {
									if (temp[j].search(/^'[^']+'$/) == -1) {
										Ext.Msg.show({title:"에러",msg:"필드설정의 SELECT 형태는 길이/값 항목에 '선택사항1','선택사항2' 방식으로 값을 입력하여야 합니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING,fn:function() {
											Ext.getCmp("AddFieldList").getPlugin("AddFieldEdit").startEdit(i,4);
										}});
										return false;
									}
								}
								
								if (field.get("index") == "PRIMARY" || field.get("index") == "UNIQUE") {
									Ext.Msg.show({title:"에러",msg:"필드설정의 SELECT 형태는 인덱스종류로 PRIMARY와 UNIQUE를 가질 수 없습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING,fn:function() {
										Ext.getCmp("AddFieldList").getPlugin("AddFieldEdit").startEdit(i,7);
									}});
									return false;
								}
							}

							if (field.get("index") == "PRIMARY") {
								if (isPrimary == true) {
									Ext.Msg.show({title:"에러",msg:"필드설정 중 PRIMARY인덱스는 한개의 필드만 적용가능 합니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING,fn:function() {
										Ext.getCmp("AddFieldList").getPlugin("AddFieldEdit").startEdit(i,7);
									}});
									return false;
								}
								if (field.get("type") == "INT") {
									isPrimary = true;
								} else {
									Ext.Msg.show({title:"에러",msg:"필드설정 중 PRIMARY인덱스는 필드종류가 INT일 경우에만 가능합니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING,fn:function() {
										Ext.getCmp("AddFieldList").getPlugin("AddFieldEdit").startEdit(i,7);
									}});
									return false;
								}
							}

							if (field.get("type").search(/^(FILE,TEXT,HTML,DATE)$/) == 0) {
								field.set("length","");
								field.set("default","");
								field.set("index","");
							}
						}

						if (isPrimary == false) {
							Ext.Msg.show({title:"에러",msg:"필드설정에 PRIMARY인덱스는 반드시 한개이상 설정하여야 합니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
							return false;
						}
						
						Ext.getCmp("AddTableForm").getForm().findField("field").setValue(Ext.JSON.encode(fields));
						
						if (idx) {
							var modify = new Array();
							var update = Ext.getCmp("AddFieldList").getStore().getUpdatedRecords();
							for (var i=0, loop=update.length;i<loop;i++) {
								modify.push({update:update[i].data,origin:update[i].raw});
							}
							
							Ext.getCmp("AddTableForm").getForm().findField("update").setValue(Ext.JSON.encode(modify));
						}
						
						Ext.getCmp("AddTableForm").getForm().submit({
							url:"<?php echo $_ENV['dir']; ?>/module/database/exec/Admin.do.php?action=user&do="+(idx ? "modify&idx="+idx : "add"),
							submitEmptyText:false,
							waitTitle:"잠시만 기다려주십시오.",
							waitMsg:"테이블을 "+(idx ? "수정" : "생성")+"중입니다.",
							success:function(form,action) {
								Ext.Msg.show({title:"안내",msg:"성공적으로 "+(idx ? "수정" : "생성")+"하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
									Ext.getCmp("ListPanel").getStore().loadPage(1);
									Ext.getCmp("AddTableWindow").close();
								}});
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
						Ext.getCmp("AddTableWindow").close();
					}
				})
			],
			listeners:{
				show:{fn:function() {
					if (idx) {
						Ext.getCmp("AddTableForm").getForm().load({
							url:"<?php echo $_ENV['dir']; ?>/module/database/exec/Admin.get.php?action=user&get=info&idx="+idx,
							waitTitle:"잠시만 기다려주십시오.",
							waitMsg:"데이터를 로딩중입니다.",
							failure:function(form,action) {
								Ext.Msg.show({title:"에러",msg:"서버에 이상이 있어 데이터를 불러오지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
							}
						});
					}
				}}
			}
		}).show();
	}
	
	function AddRecord(tno,field,idx) {
		var uniqueID = tno.toString()+(idx ? idx.toString() : "0");
		if (Ext.getCmp("AddRecordWindow"+uniqueID)) return;
		
		var forms = new Array();
		var fields = new Array();
		var wysiwygs = new Array();
		var primary = {};
		
		for (var i=0, loop=field.length;i<loop;i++) {
			if (field[i].index == "PRIMARY") {
				primary = field[i];
			}
			
			if (field[i].option == "AUTO_INCREMENT") {
				forms.push(
					new Ext.form.NumberField({
						fieldLabel:field[i].info,
						name:field[i].name,
						disabled:true,
						emptyText:"자동증가값 (값이 자동으로 설정됩니다.)"
					})
				);
			} else if (field[i].type == "INT") {
				forms.push(
					new Ext.form.NumberField({
						fieldLabel:field[i].info,
						name:field[i].name
					})
				);
			} else if (field[i].type == "VARCHAR") {
				forms.push(
					new Ext.form.TextField({
						fieldLabel:field[i].info,
						name:field[i].name,
						maxLength:field[i].length
					})
				);
			} else if (field[i].type == "DATE") {
				forms.push(
					new Ext.form.DateField({
						fieldLabel:field[i].info,
						name:field[i].name,
						format:"Y-m-d"
					})
				);
			} else if (field[i].type == "TEXT") {
				forms.push(
					new Ext.form.TextArea({
						fieldLabel:field[i].info,
						name:field[i].name,
						height:200
					})
				);
			} else if (field[i].type == "HTML") {
				wysiwygs.push(uniqueID+"-"+field[i].name);
				forms.push(
					new Ext.form.TextArea({
						id:uniqueID+"-"+field[i].name+"-Wysiwyg",
						fieldLabel:field[i].info,
						name:field[i].name,
						height:250
					}),
					new Ext.Panel({
						id:uniqueID+"-"+field[i].name+"-Uploader-Panel",
						border:false,
						padding:"0 0 5 105",
						html:'<div id="'+uniqueID+'-'+field[i].name+'-Uploader-area"></div><div id="'+uniqueID+'-'+field[i].name+'-Uploader-image"></div><div id="'+field[i].name+'-Uploader-file"></div>',
						listeners:{render:{fn:function(panel) {
							var uploaderID = panel.getId().replace("-Panel","");
							
							new AzUploader({
								id:uploaderID,
								autoRender:false,
								autoLoad:(idx ? true : false),
								flashURL:"<?php echo $_ENV['dir']; ?>/module/database/flash/AzUploader.swf",
								uploadURL:"<?php echo $_ENV['dir']; ?>/module/database/exec/FileUpload.do.php?type=HTML&tno="+tno+"&wysiwyg="+panel.getId().split("-").shift(),
								loadURL:"<?php echo $_ENV['dir']; ?>/module/database/exec/FileLoad.do.php?type=HTML&wysiwyg="+panel.getId().split("-").shift()+"&repto="+idx,
								buttonURL:"<?php echo $_ENV['dir']; ?>/module/database/images/admin/icon_file_button.gif",
								width:75,
								height:20,
								moduleDir:"<?php echo $_ENV['dir']; ?>/module/database",
								formElement:"AddRecordForm",
								wysiwygElement:uniqueID+"-"+panel.getId().split("-")[1]+"-Wysiwyg-inputEl",
								panelElement:panel.getId(),
								maxFileSize:0,
								listeners:{
									beforeLoad:AzUploaderBeforeLoad,
									onSelect:AzUploaderOnSelect,
									onProgress:AzUploaderOnProgress,
									onComplete:AzUploaderOnComplete,
									onLoad:AzUploaderOnLoad,
									onUpload:AzUploaderOnUpload,
									onError:AzUploaderOnError
								}
							}).render(uploaderID+"-area");
							panel.doLayout();
						}}}
					})
				);
			} else if (field[i].type == "SELECT") {
				var selects = field[i].length.split(",");
				for (var j=0, loopj=selects.length;j<loopj;j++) {
					selects[j] = [selects[j].replace(/'/g,''),selects[j].replace(/'/g,'')];
				}
				selects.push(["선택안함",""]);
				forms.push(
					new Ext.form.ComboBox({
						fieldLabel:field[i].info,
						name:field[i].name,
						typeAhead:true,
						triggerAction:"all",
						lazyRender:true,
						store:new Ext.data.ArrayStore({
							fields:["display","value"],
							data:selects
						}),
						width:80,
						editable:false,
						mode:"local",
						displayField:"display",
						valueField:"value"
					})
				);
			} else if (field[i].type == "FILE") {
				forms.push(
					new Ext.form.FileUploadField({
						fieldLabel:field[i].info,
						name:field[i].name,
						buttonText:"",
						buttonConfig:{icon:"<?php echo $_ENV['dir']; ?>/images/common/icon_disk.png"}
					}),
					new Ext.form.Checkbox({
						name:field[i].name+"-delete",
						style:{margin:"0px 0px 5px 105px"},
						boxLabel:"첨부된 파일을 삭제합니다.",
						hidden:true
					})
				)
			}
			fields.push(field[i].name);
		}
		
		new Ext.Window({
			id:"AddRecordWindow"+uniqueID,
			title:idx ? "레코드수정" : "레코드추가",
			width:800,
			maxHeight:500,
			layout:"fit",
			items:[
				new Ext.form.FormPanel({
					id:"AddRecordForm"+uniqueID,
					bodyPadding:"5 5 0 5",
					border:false,
					autoScroll:true,
					fieldDefaults:{labelAlign:"right",labelWidth:100,anchor:"100%"},
					items:forms
				})
			],
			buttons:[
				new Ext.Button({
					text:"확인",
					handler:function() {
						for (var i=0, loop=wysiwygs.length;i<loop;i++) {
							oEditors.getById[wysiwygs[i]+"-Wysiwyg-inputEl"].exec("UPDATE_IR_FIELD",[]);
						}
						Ext.getCmp("AddRecordForm"+uniqueID).getForm().submit({
							url:"<?php echo $_ENV['dir']; ?>/module/database/exec/Admin.do.php?action=user&do=record&tno="+tno+"&primary="+primary.name+"&mode="+(idx ? "modify&idx="+idx : "add"),
							submitEmptyText:false,
							waitTitle:"잠시만 기다려주십시오.",
							waitMsg:"레코드를 "+(idx ? "수정" : "추가")+"중입니다.",
							success:function(form,action) {
								Ext.Msg.show({title:"안내",msg:"성공적으로 "+(idx ? "수정" : "추가")+"하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
									if (Ext.getCmp("RecordPanel"+tno)) Ext.getCmp("RecordPanel"+tno).getStore().loadPage(1);
									Ext.getCmp("AddRecordWindow"+uniqueID).close();
								}});
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
						Ext.getCmp("AddRecordWindow"+uniqueID).close();
					}
				})
			],
			listeners:{
				show:{fn:function() {
					if (idx) {
						Ext.getCmp("AddRecordForm"+uniqueID).getForm().load({
							url:"<?php echo $_ENV['dir']; ?>/module/database/exec/Admin.get.php?action=user&get=record&tno="+tno+"&mode=data&primary="+primary.name+"&idx="+idx,
							waitTitle:"잠시만 기다려주십시오.",
							waitMsg:"데이터를 로딩중입니다.",
							success:function(form,action) {
								for (var i=0, loop=wysiwygs.length;i<loop;i++) {
									try { oEditors.getById[wysiwygs[i]+"-Wysiwyg-inputEl"].exec("SET_IR",[form.findField(wysiwygs[i]).getValue()]); } catch(e) {};
								}
								
								for (field in action.result.data) {
									if (action.result.data[field] && Ext.getCmp("AddRecordForm"+uniqueID).getForm().findField(field+"-delete")) {
										Ext.getCmp("AddRecordForm"+uniqueID).getForm().findField(field+"-delete").setBoxLabel("첨부파일삭제 : "+action.result.data[field]);
										Ext.getCmp("AddRecordForm"+uniqueID).getForm().findField(field+"-delete").show();
									}
								}
								
								for (var i=0, loop=wysiwygs.length;i<loop;i++) {
									nhn.husky.EZCreator.createInIFrame({oAppRef:oEditors,elPlaceHolder:wysiwygs[i]+"-Wysiwyg-inputEl",sSkinURI:"<?php echo $_ENV['dir']; ?>/module/wysiwyg/wysiwyg.php?resize=false",fCreator:"createSEditorInIFrame"});
								}
							},
							failure:function(form,action) {
								Ext.Msg.show({title:"에러",msg:"서버에 이상이 있어 데이터를 불러오지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
							}
						});
					} else {
						for (var i=0, loop=wysiwygs.length;i<loop;i++) {
							nhn.husky.EZCreator.createInIFrame({oAppRef:oEditors,elPlaceHolder:wysiwygs[i]+"-Wysiwyg-inputEl",sSkinURI:"<?php echo $_ENV['dir']; ?>/module/wysiwyg/wysiwyg.php?resize=false",fCreator:"createSEditorInIFrame"});
						}
					}
				}}
			}
		}).show();
	}

	function ShowRecord(idx,title,field) {
		if (Ext.getCmp("ShowRecordWindow"+idx)) return;
		
		var columns = new Array();
		var fields = new Array();
		var searchs = new Array();
		var primary = {};
		
		for (var i=0, loop=field.length;i<loop;i++) {
			if (field[i].index == "PRIMARY") {
				primary = field[i];
			}
			
			if (field[i].option == "AUTO_INCREMENT") {
				columns.push(
					new Ext.grid.RowNumberer({
						header:field[i].info,
						dataIndex:field[i].name,
						width:60,
						align:"left",
						renderer:function(value,p,record) {
							p.tdCls = Ext.baseCSSPrefix + 'grid-cell-special';
							return GridNumberFormat(value);
						}
					})
				);
			} else if (field[i].type == "INT") {
				columns.push({
					header:field[i].info,
					dataIndex:field[i].name,
					width:80,
					renderer:GridNumberFormat
				});
			} else if (field[i].type == "VARCHAR") {
				columns.push({
					header:field[i].info,
					dataIndex:field[i].name,
					minWidth:parseInt(field[i].length)*3 > 200 ? 200 : (parseInt(field[i].length)*3 < 50 ? 50 : parseInt(field[i].length)*3),
					flex:1
				});
			} else if (field[i].type == "DATE") {
				columns.push({
					header:field[i].info,
					dataIndex:field[i].name,
					width:80
				});
			} else if (field[i].type == "HTML" || field[i].type == "TEXT") {
				columns.push({
					header:field[i].info,
					dataIndex:field[i].name,
					width:200,
					renderer:function(value) {
						return '<div style="height:14px; line-height:14px; white-space:nowrap; text-overflow:ellipsis; overflow:hidden;">'+value+'</div>';
					}
				});
			} else if (field[i].type == "SELECT") {
				columns.push({
					header:field[i].info,
					dataIndex:field[i].name,
					width:80
				});
			} else if (field[i].type == "FILE") {
				columns.push({
					header:field[i].info,
					dataIndex:field[i].name,
					width:200,
					renderer:function(value) {
						if (value) {
							return '<div style="background:url(<?php echo $_ENV['dir']; ?>/module/database/images/admin/icon_disk.png) no-repeat 0 50%; padding-left:20px; white-space:nowrap; text-overflow:ellipsis; overflow:hidden; font-family:tahoma;">'+value+'</div>';
						}
					}
				});
			}
			fields.push(field[i].name);
			if (field[i].type != "FILE") searchs.push([field[i].name,field[i].info]);
		}
		
		var store = new Ext.data.JsonStore({
			proxy:{
				type:"ajax",
				simpleSortMode:true,
				url:"<?php echo $_ENV['dir']; ?>/module/database/exec/Admin.get.php",
				reader:{type:"json",root:"lists",totalProperty:"totalCount"},
				extraParams:{action:"user",get:"record",mode:"list",key:"",keyword:"",idx:idx}
			},
			remoteSort:true,
			autoLoad:true,
			pageSize:50,
			fields:fields
		});
		
		new Ext.Window({
			id:"ShowRecordWindow"+idx,
			title:title+" 레코드",
			maximizable:true,
			width:900,
			height:550,
			layout:"fit",
			items:[
				new Ext.grid.GridPanel({
					id:"RecordPanel"+idx,
					border:false,
					tbar:[
						new Ext.Button({
							text:"레코드추가",
							icon:"<?php echo $_ENV['dir']; ?>/module/database/images/admin/icon_textfield_add.png",
							handler:function() {
								AddRecord(idx,field);
							}
						}),
						'-',
						new Ext.form.ComboBox({
							id:"ShowRecordKey"+idx,
							typeAhead:true,
							triggerAction:"all",
							lazyRender:true,
							listClass:"x-combo-list-small",
							store:new Ext.data.SimpleStore({
								fields:["value","display"],
								data:searchs
							}),
							width:80,
							emptyText:"검색조건",
							editable:false,
							mode:"local",
							displayField:"display",
							valueField:"value"
						}),
						new Ext.form.TextField({
							id:"ShowRecordKeyword"+idx,
							width:150,
							emptyText:"검색어 입력"
						}),
						new Ext.Button({
							text:"검색",
							icon:"<?php echo $_ENV['dir']; ?>/module/database/images/admin/icon_magnifier.png",
							handler:function() {
								if (Ext.getCmp("ShowRecordKeyword"+idx).getValue() != "" && !Ext.getCmp("ShowRecordKey"+idx).getValue()) {
									Ext.Msg.show({title:"안내",msg:"검색조건을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
									return;
								}
								Ext.getCmp("RecordPanel"+idx).getStore().getProxy().setExtraParam("key",Ext.getCmp("ShowRecordKey"+idx).getValue());
								Ext.getCmp("RecordPanel"+idx).getStore().getProxy().setExtraParam("keyword",Ext.getCmp("ShowRecordKeyword"+idx).getValue());
								Ext.getCmp("RecordPanel"+idx).getStore().loadPage(1);
							}
						}),
						new Ext.Button({
							text:"선택한 레코드를&nbsp;",
							icon:"<?php echo $_ENV['dir']; ?>/module/database/images/admin/icon_tick.png",
							menu:new Ext.menu.Menu({
								items:[{
									text:"레코드 삭제",
									handler:function() {
										var checked = Ext.getCmp("RecordPanel"+idx).getSelectionModel().getSelection();
										if (checked.length == 0) {
											Ext.Msg.show({title:"에러",msg:"삭제할 레코드를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
											return false;
										}
										
										var idxs = new Array();
										for (var i=0, loop=checked.length;i<loop;i++) {
											idxs[i] = checked[i].get(primary.name);
										}
										
										Ext.Msg.show({title:"안내",msg:"정말 선택한 레코드를 삭제하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
											if (button == "yes") {
												Ext.Msg.wait("레코드를 삭제중입니다.","잠시만 기다려주십시오.");
												Ext.Ajax.request({
													url:"<?php echo $_ENV['dir']; ?>/module/database/exec/Admin.do.php",
													success:function(response) {
														var data = Ext.JSON.decode(response.responseText);
														if (data.success == true) {
															Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
																Ext.getCmp("RecordPanel"+idx).getStore().loadPage(1);
															}});
														} else {
															Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
														}
													},
													failure:function() {
														Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
													},
													params:{"action":"user","do":"record",mode:"delete","tno":idx,primary:primary.name,idx:idxs.join(",")}
												});
											}
										}});
									}
								}]
							})
						}),
						'->',
						{xtype:"tbtext",text:"더블클릭 : 수정 / 우클릭 : 상세메뉴"}
					],
					columns:columns,
					store:store,
					columnLines:true,
					selModel:new Ext.selection.CheckboxModel({injectCheckbox:"last"}),
					bbar:new Ext.PagingToolbar({
						store:store,
						displayInfo:true
					}),
					listeners:{
						itemcontextmenu:{fn:function(grid,record,row,index,e) {
							var menu = new Ext.menu.Menu();
		
							menu.add('<b class="menu-title">'+primary.info+" : "+record.data[primary.name]+'</b>');
							
							menu.add({
								text:"레코드 수정",
								handler:function() {
									AddRecord(idx,field,record.data[primary.name]);
								}
							});
							
							menu.add({
								text:"레코드 삭제",
								handler:function() {
									Ext.Msg.show({title:"안내",msg:"정말 해당 레코드를 삭제하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
										if (button == "yes") {
											Ext.Msg.wait("레코드를 삭제중입니다.","잠시만 기다려주십시오.");
											Ext.Ajax.request({
												url:"<?php echo $_ENV['dir']; ?>/module/database/exec/Admin.do.php",
												success:function(response) {
													var data = Ext.JSON.decode(response.responseText);
													if (data.success == true) {
														Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
															Ext.getCmp("RecordPanel"+idx).getStore().loadPage(1);
														}});
													} else {
														Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
													}
												},
												failure:function() {
													Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
												},
												params:{"action":"user","do":"record",mode:"delete","tno":idx,primary:primary.name,idx:record.data[primary.name]}
											});
										}
									}});
								}
							});
							e.stopEvent();
							menu.showAt(e.getXY());
						}},
						itemdblclick:{fn:function(grid,record) {
							AddRecord(idx,field,record.data[primary.name]);
						}}
					}
				})
			],
			listeners:{
				close:{fn:function(panel) {
					Ext.getCmp("ListPanel").getStore().reload();
				}}
			}
		}).show();
	}
	
	function ItemContextMenu(grid,record,row,index,e) {
		var menu = new Ext.menu.Menu();
		
		menu.add('<b class="menu-title">'+record.data.info+'</b>');
		
		menu.add({
			text:"테이블 구조변경",
			handler:function() {
				AddTable(record.data.idx);
			}
		});
		menu.add('-');
		
		menu.add({
			text:"테이블 비우기",
			handler:function() {
				Ext.Msg.show({title:"확인",msg:"테이블을 비우게되면 해당테이블의 모든 레코드 및 첨부파일이 삭제됩니다.<br />선택한 테이블을 정말 비우시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
					if (button == "yes") {
						Ext.Msg.wait("선택한 테이블을 비우고 있습니다.","잠시만 기다려주십시오.");
						Ext.Ajax.request({
							url:"<?php echo $_ENV['dir']; ?>/module/database/exec/Admin.do.php",
							success:function(response) {
								var data = Ext.JSON.decode(response.responseText);
								if (data.success == true) {
									Ext.Msg.show({title:"안내",msg:"성공적으로 비웠습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
										Ext.getCmp("ListPanel").getStore().reload();
									}});
								} else {
									Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
								}
							},
							failure:function() {
								Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
							},
							params:{"action":"user","do":"truncate","idx":record.data.idx}
						});
					}
				}});
			}
		});
		
		menu.add({
			text:"테이블 삭제",
			handler:function() {
				Ext.Msg.show({title:"확인",msg:"테이블을 삭제하면 해당테이블의 모든 레코드 및 첨부파일이 삭제됩니다.<br />선택한 테이블을 정말 삭제하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
					if (button == "yes") {
						Ext.Msg.wait("선택한 테이블을 삭제하고 있습니다.","잠시만 기다려주십시오.");
						Ext.Ajax.request({
							url:"<?php echo $_ENV['dir']; ?>/module/database/exec/Admin.do.php",
							success:function(response) {
								var data = Ext.JSON.decode(response.responseText);
								if (data.success == true) {
									Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
										Ext.getCmp("ListPanel").getStore().reload();
									}});
								} else {
									Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
								}
							},
							failure:function() {
								Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
							},
							params:{"action":"user","do":"delete","idx":record.data.idx}
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
							AddTable();
						}
					}),
					'-',
					new Ext.form.TextField({
						id:"Keyword",
						width:150,
						emptyText:"테이블명, 테이블설명"
					}),
					new Ext.Button({
						text:"검색",
						icon:"<?php echo $_ENV['dir']; ?>/module/database/images/admin/icon_magnifier.png",
						handler:function() {
							Ext.getCmp("ListPanel").getStore().getProxy().setExtraParam("keyword",Ext.getCmp("Keyword").getValue());
							Ext.getCmp("ListPanel").getStore().reload();
						}
					}),
					'-',
					new Ext.Button({
						text:"선택한 테이블을&nbsp;",
						icon:"<?php echo $_ENV['dir']; ?>/module/database/images/admin/icon_tick.png",
						menu:new Ext.menu.Menu({
							items:[{
								text:"테이블 비우기",
								handler:function() {
									var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();
									if (checked.length == 0) {
										Ext.Msg.show({title:"에러",msg:"비울 테이블을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										return false;
									}
									
									var idxs = new Array();
									for (var i=0, loop=checked.length;i<loop;i++) {
										idxs[i] = checked[i].get("idx");
									}
									
									Ext.Msg.show({title:"확인",msg:"테이블을 비우게되면 해당테이블의 모든 레코드 및 첨부파일이 삭제됩니다.<br />선택한 테이블을 정말 비우시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
										if (button == "yes") {
											Ext.Msg.wait("선택한 테이블을 비우고 있습니다.","잠시만 기다려주십시오.");
											Ext.Ajax.request({
												url:"<?php echo $_ENV['dir']; ?>/module/database/exec/Admin.do.php",
												success:function(response) {
													var data = Ext.JSON.decode(response.responseText);
													if (data.success == true) {
														Ext.Msg.show({title:"안내",msg:"성공적으로 비웠습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
															Ext.getCmp("ListPanel").getStore().reload();
														}});
													} else {
														Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
													}
												},
												failure:function() {
													Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
												},
												params:{"action":"user","do":"truncate","idx":idxs.join(",")}
											});
										}
									}});
								}
							},{
								text:"테이블 삭제",
								handler:function() {
									var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();
									if (checked.length == 0) {
										Ext.Msg.show({title:"에러",msg:"삭제할 테이블을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										return false;
									}
									
									var idxs = new Array();
									for (var i=0, loop=checked.length;i<loop;i++) {
										idxs[i] = checked[i].get("idx");
									}
									
									Ext.Msg.show({title:"확인",msg:"테이블을 삭제하게되면 해당테이블의 모든 레코드 및 첨부파일이 삭제됩니다.<br />선택한 테이블을 정말 삭제하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
										if (button == "yes") {
											Ext.Msg.wait("선택한 테이블을 삭제하고 있습니다.","잠시만 기다려주십시오.");
											Ext.Ajax.request({
												url:"<?php echo $_ENV['dir']; ?>/module/database/exec/Admin.do.php",
												success:function(response) {
													var data = Ext.JSON.decode(response.responseText);
													if (data.success == true) {
														Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
															Ext.getCmp("ListPanel").getStore().reload();
														}});
													} else {
														Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
													}
												},
												failure:function() {
													Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
												},
												params:{"action":"user","do":"delete","idx":idxs.join(",")}
											});
										}
									}});
								}
							}]
						})
					}),
					'->',
					{xtype:"tbtext",text:"더블클릭 : 레코드보기 / 우클릭 : 상세메뉴"}
				],
				columns:[
					new Ext.grid.RowNumberer(),
					{
						header:"테이블명",
						dataIndex:"name",
						width:250,
						summaryType:"count",
						summaryRenderer:function(value) {
							return "총 "+value+"개 테이블";
						}
					},{
						header:"테이블설명",
						dataIndex:"info",
						minWidth:250,
						flex:1
					},{
						header:"레코드수",
						dataIndex:"record",
						width:100,
						summaryType:"sum",
						renderer:GridNumberFormat
					},{
						header:"DB용량",
						dataIndex:"dbsize",
						width:100,
						summaryType:"sum",
						renderer:function(value) {
							return '<div style="font-family:tahoma; text-align:right;">'+GetFileSize(value)+'</div>';
						}
					},{
						header:"첨부용량",
						dataIndex:"filesize",
						width:100,
						summaryType:"sum",
						renderer:function(value) {
							return '<div style="font-family:tahoma; text-align:right;">'+GetFileSize(value)+'</div>';
						}
					}
				],
				store:new Ext.data.JsonStore({
					proxy:{
						type:"ajax",
						simpleSortMode:true,
						url:"<?php echo $_ENV['dir']; ?>/module/database/exec/Admin.get.php",
						reader:{type:"json",root:"lists",totalProperty:"totalCount"},
						extraParams:{action:"user",get:"list",keyword:""}
					},
					remoteSort:false,
					sorters:[{property:"name",direction:"ASC"}],
					autoLoad:true,
					pageSize:50,
					fields:[{name:"idx",type:"int"},"group","name","info",{name:"record",type:"int"},{name:"dbsize",type:"int"},{name:"filesize",type:"int"}]
				}),
				columnLines:true,
				selModel:new Ext.selection.CheckboxModel({injectCheckbox:"last"}),
				features:[{
					ftype:"summary"
				}],
				listeners:{
					itemdblclick:{fn:function(grid,record) {
						Ext.Msg.wait("테이블 구조를 파악중입니다.","잠시만 기다려주십시오.");
						Ext.Ajax.request({
							url:"<?php echo $_ENV['dir']; ?>/module/database/exec/Admin.get.php",
							success:function(response) {
								var data = Ext.JSON.decode(response.responseText);
								if (data.success == true) {
									Ext.Msg.hide();
									ShowRecord(record.data.idx,record.data.info,data.field);
								} else {
									Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
								}
							},
							failure:function() {
								Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
							},
							params:{action:"user","get":"field","idx":record.data.idx}
						});
					}},
					itemcontextmenu:ItemContextMenu
				}
			})
		]
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>