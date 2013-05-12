<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"매물등록기초자료관리",
		layout:"fit",
		items:[
			new Ext.TabPanel({
				tabPosition:"bottom",
				activeTab:0,
				border:false,
				items:[
					new Ext.Panel({
						title:"대학정보관리",
						layout:"hbox",
						border:false,
						layoutConfig:{align:"stretch"},
						items:[
							new Ext.grid.EditorGridPanel({
								id:"University1",
								title:"지역구분",
								margins:"5 5 5 5",
								tbar:[
									new Ext.Button({
										text:"지역구분추가",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_map_add.png",
										handler:function() {
											new Ext.Window({
												id:"UniversityAddWindow",
												title:"지역구분추가",
												width:350,
												height:110,
												modal:true,
												resizable:false,
												layout:"fit",
												items:[
													new Ext.form.FormPanel({
														id:"UniversityAddForm",
														labelAlign:"right",
														labelWidth:85,
														border:false,
														errorReader:new Ext.form.XmlErrorReader(),
														style:"background:#FFFFFF; padding:10px;",
														items:[
															new Ext.form.TextField({
																fieldLabel:"지역구분",
																name:"title",
																width:200,
																allowBlank:false
															})
														],
														listeners:{actioncomplete:{fn:function(form,action) {
															if (action.type == "submit") {
																Ext.Msg.show({title:"안내",msg:"성공적으로 추가하였습니다.<br />계속해서 지역구분을 추가하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button){
																	Ext.getCmp("University1").getStore().reload();
																	if (button == "ok") {
																		Ext.getCmp("UniversityAddForm").getForm().findField("title").setValue();
																		Ext.getCmp("UniversityAddForm").getForm().findField("title").clearInvalid();
																		Ext.getCmp("UniversityAddForm").getForm().findField("title").focus();
																	} else {
																		Ext.getCmp("UniversityAddWindow").close();
																	}
																}});
			
															}
														}}}
													})
												],
												buttons:[
													new Ext.Button({
														text:"확인",
														icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_tick.png",
														handler:function() {
															Ext.getCmp("UniversityAddForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php?action=database&subaction=university&do=add&parent=0",waitMsg:"데이터를 전송중입니다."});
														}
													}),
													new Ext.Button({
														text:"취소",
														icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_cross.png",
														handler:function() {
															Ext.getCmp("UniversityAddWindow").close();
														}
													})
												]
											}).show();
										}
									}),
									new Ext.Button({
										text:"지역구분삭제",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_map_delete.png",
										handler:function() {
											var checked = Ext.getCmp("University1").selModel.getSelections();
											if (checked.length == 0) {
												Ext.Msg.show({title:"에러",msg:"삭제할 지역구분을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
												return false;
											}
			
											var idxs = new Array();
											for (var i=0, loop=checked.length;i<loop;i++) {
												idxs.push(checked[i].get("idx"));
											}
											var idx = idxs.join(",");
											
											Ext.Msg.show({title:"안내",msg:"선택지역구분을 삭제하게 되면 해당 지역구분의 대학교도 함께 삭제됩니다.<br />정말 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.QUESTION,fn:function(button) {
												if (button == "ok") {
													Ext.Msg.wait("처리중입니다.","Please Wait...");
													Ext.Ajax.request({
														url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php",
														success:function() {
															Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
															Ext.getCmp("University1").getStore().reload();
														},
														failure:function() {
															Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
														},
														headers:{},
														params:{"action":"database","subaction":"university","do":"delete","idx":idx}
													});
												}
											}});
										}
									}),
									'->',
									new Ext.Button({
										text:"변경사항저장",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_disk.png",
										handler:function() {
											Ext.Msg.wait("처리중입니다.","Please Wait...");
											Ext.Ajax.request({
												url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php",
												success:function() {
													Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
													Ext.getCmp("University1").getStore().reload();
												},
												failure:function() {
													Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
												},
												headers:{},
												params:{"action":"database","subaction":"university","do":"modify","data":GetGridData(Ext.getCmp("University1"))}
											});
										}
									})
								],
								bbar:[
									new Ext.Button({
										text:"위로이동",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_arrow_up.png",
										handler:function() {
											var checked = Ext.getCmp("University1").selModel.getSelections();
		
											if (checked.length == 0) {
												Ext.Msg.show({title:"에러",msg:"이동할 지역구분을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
												return false;
											}
			
											var selecter = new Array();
											for (var i=0, loop=checked.length;i<loop;i++) {
												var sort = checked[i].get("sort");
												if (sort != 0) {
													Ext.getCmp("University1").getStore().getAt(sort).set("sort",sort-1);
													Ext.getCmp("University1").getStore().getAt(sort-1).set("sort",sort);
			
													selecter.push(sort-1);
													Ext.getCmp("University1").getStore().sort("sort","ASC");
												} else {
													return false;
												}
											}
			
											for (var i=0, loop=selecter.length;i<loop;i++) {
												Ext.getCmp("University1").selModel.selectRow(selecter[i]);
											}
										}
									}),
									new Ext.Button({
										text:"아래로이동",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_arrow_down.png",
										handler:function() {
											var checked = Ext.getCmp("University1").selModel.getSelections();
		
											if (checked.length == 0) {
												Ext.Msg.show({title:"에러",msg:"이동할 지역구분을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
												return false;
											}
			
											var selecter = new Array();
											for (var i=checked.length-1;i>=0;i--) {
												var sort = checked[i].get("sort");
												if (sort != Ext.getCmp("University1").getStore().getCount()-1) {
													Ext.getCmp("University1").getStore().getAt(sort).set("sort",sort+1);
													Ext.getCmp("University1").getStore().getAt(sort+1).set("sort",sort);
			
													selecter.push(sort+1);
													Ext.getCmp("University1").getStore().sort("sort","ASC");
												} else {
													return false;
												}
											}
			
											for (var i=0, loop=selecter.length;i<loop;i++) {
												Ext.getCmp("University1").selModel.selectRow(selecter[i]);
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
										header:"지역구분명",
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
									proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php"}),
									reader:new Ext.data.JsonReader({
										root:"lists",
										totalProperty:"totalCount",
										fields:["idx","title",{name:"sort",type:"int"}]
									}),
									remoteSort:false,
									sortInfo:{field:"sort",direction:"ASC"},
									baseParams:{action:"database","subaction":"university",parent:"0"},
									listeners:{load:{fn:function() {
										Ext.getCmp("University2").getStore().baseParams.parent = "-1";
										Ext.getCmp("University2").getStore().load();
									}}}
								}),
								autoExpandColumn:"title1",
								flex:1,
								listeners:{rowclick:{fn:function(grid,idx,e) {
									Ext.getCmp("University2").getStore().baseParams.parent = grid.getStore().getAt(idx).get("idx");
									Ext.getCmp("University2").getStore().load();
								}}}
							}),
							new Ext.grid.EditorGridPanel({
								id:"University2",
								title:"대학교",
								margins:"5 5 5 0",
								tbar:[
									new Ext.Button({
										text:"대학교추가",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_report_add.png",
										handler:function() {
											if (Ext.getCmp("University2").getStore().baseParams.parent == "-1") {
												Ext.Msg.show({title:"에러",msg:"지역을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
												return false;
											}
											
											var parent = Ext.getCmp("University1").getStore().find("idx",Ext.getCmp("University2").getStore().baseParams.parent,false,false);
											
											new Ext.Window({
												id:"UniversityAddWindow",
												title:"대학교추가 ("+Ext.getCmp("University1").getStore().getAt(parent).get("title")+")",
												width:350,
												height:110,
												modal:true,
												resizable:false,
												layout:"fit",
												items:[
													new Ext.form.FormPanel({
														id:"UniversityAddForm",
														labelAlign:"right",
														labelWidth:85,
														border:false,
														errorReader:new Ext.form.XmlErrorReader(),
														style:"background:#FFFFFF; padding:10px;",
														items:[
															new Ext.form.TextField({
																fieldLabel:"대학교명",
																name:"title",
																width:200,
																allowBlank:false
															})
														],
														listeners:{actioncomplete:{fn:function(form,action) {
															if (action.type == "submit") {
																Ext.Msg.show({title:"안내",msg:"성공적으로 추가하였습니다.<br />계속해서 대학교를 추가하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button){
																	Ext.getCmp("University2").getStore().reload();
																	if (button == "ok") {
																		Ext.getCmp("UniversityAddForm").getForm().findField("title").setValue();
																		Ext.getCmp("UniversityAddForm").getForm().findField("title").clearInvalid();
																		Ext.getCmp("UniversityAddForm").getForm().findField("title").focus();
																	} else {
																		Ext.getCmp("UniversityAddWindow").close();
																	}
																}});
			
															}
														}}}
													})
												],
												buttons:[
													new Ext.Button({
														text:"확인",
														icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_tick.png",
														handler:function() {
															Ext.getCmp("UniversityAddForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php?action=database&subaction=university&do=add&parent="+Ext.getCmp("University2").getStore().baseParams.parent,waitMsg:"데이터를 전송중입니다."});
														}
													}),
													new Ext.Button({
														text:"취소",
														icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_cross.png",
														handler:function() {
															Ext.getCmp("UniversityAddWindow").close();
														}
													})
												]
											}).show();
										}
									}),
									new Ext.Button({
										text:"대학교삭제",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_report_delete.png",
										handler:function() {
											var checked = Ext.getCmp("University2").selModel.getSelections();
											if (checked.length == 0) {
												Ext.Msg.show({title:"에러",msg:"삭제할 대학교명을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
												return false;
											}
			
											var idxs = new Array();
											for (var i=0, loop=checked.length;i<loop;i++) {
												idxs.push(checked[i].get("idx"));
											}
											var idx = idxs.join(",");
											
											Ext.Msg.show({title:"안내",msg:"정말 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.QUESTION,fn:function(button) {
												if (button == "ok") {
													Ext.Msg.wait("처리중입니다.","Please Wait...");
													Ext.Ajax.request({
														url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php",
														success:function() {
															Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
															Ext.getCmp("University1").getStore().reload();
														},
														failure:function() {
															Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
														},
														headers:{},
														params:{"action":"database","subaction":"university","do":"delete","idx":idx}
													});
												}
											}});
										}
									}),
									'->',
									new Ext.Button({
										text:"변경사항저장",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_disk.png",
										handler:function() {
											Ext.Msg.wait("처리중입니다.","Please Wait...");
											Ext.Ajax.request({
												url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php",
												success:function() {
													Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
													Ext.getCmp("University2").getStore().reload();
												},
												failure:function() {
													Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
												},
												headers:{},
												params:{"action":"database","subaction":"university","do":"modify","data":GetGridData(Ext.getCmp("University2"))}
											});
										}
									})
								],
								bbar:[
									new Ext.Button({
										text:"위로이동",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_arrow_up.png",
										handler:function() {
											var checked = Ext.getCmp("University2").selModel.getSelections();
		
											if (checked.length == 0) {
												Ext.Msg.show({title:"에러",msg:"이동할 대학교를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
												return false;
											}
			
											var selecter = new Array();
											for (var i=0, loop=checked.length;i<loop;i++) {
												var sort = checked[i].get("sort");
												if (sort != 0) {
													Ext.getCmp("University2").getStore().getAt(sort).set("sort",sort-1);
													Ext.getCmp("University2").getStore().getAt(sort-1).set("sort",sort);
			
													selecter.push(sort-1);
													Ext.getCmp("University2").getStore().sort("sort","ASC");
												} else {
													return false;
												}
											}
			
											for (var i=0, loop=selecter.length;i<loop;i++) {
												Ext.getCmp("University2").selModel.selectRow(selecter[i]);
											}
										}
									}),
									new Ext.Button({
										text:"아래로이동",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_arrow_down.png",
										handler:function() {
											var checked = Ext.getCmp("University2").selModel.getSelections();
		
											if (checked.length == 0) {
												Ext.Msg.show({title:"에러",msg:"이동할 대학교를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
												return false;
											}
			
											var selecter = new Array();
											for (var i=checked.length-1;i>=0;i--) {
												var sort = checked[i].get("sort");
												if (sort != Ext.getCmp("University2").getStore().getCount()-1) {
													Ext.getCmp("University2").getStore().getAt(sort).set("sort",sort+1);
													Ext.getCmp("University2").getStore().getAt(sort+1).set("sort",sort);
			
													selecter.push(sort+1);
													Ext.getCmp("University2").getStore().sort("sort","ASC");
												} else {
													return false;
												}
											}
			
											for (var i=0, loop=selecter.length;i<loop;i++) {
												Ext.getCmp("University2").selModel.selectRow(selecter[i]);
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
										header:"대학명",
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
									proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php"}),
									reader:new Ext.data.JsonReader({
										root:"lists",
										totalProperty:"totalCount",
										fields:["idx","title",{name:"sort",type:"int"}]
									}),
									remoteSort:false,
									sortInfo:{field:"sort",direction:"ASC"},
									baseParams:{action:"database","subaction":"university",parent:"-1"},
									listeners:{load:{fn:function(store) {
										var parent = Ext.getCmp("University1").getStore().find("idx",store.baseParams.parent,false,false);
										if (parent != -1) {
											Ext.getCmp("University2").setTitle("대학교 ("+Ext.getCmp("University1").getStore().getAt(parent).get("title")+")");
										}
									}}}
								}),
								autoExpandColumn:"title2",
								flex:1
							})
						]
					}),
					new Ext.Panel({
						title:"지하철정보관리",
						layout:"hbox",
						border:false,
						layoutConfig:{align:"stretch"},
						items:[
							new Ext.grid.EditorGridPanel({
								id:"Subway1",
								title:"노선구분",
								margins:"5 5 5 5",
								tbar:[
									new Ext.Button({
										text:"노선구분추가",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_map_add.png",
										handler:function() {
											new Ext.Window({
												id:"SubwayAddWindow",
												title:"노선구분추가",
												width:350,
												height:110,
												modal:true,
												resizable:false,
												layout:"fit",
												items:[
													new Ext.form.FormPanel({
														id:"SubwayAddForm",
														labelAlign:"right",
														labelWidth:85,
														border:false,
														errorReader:new Ext.form.XmlErrorReader(),
														style:"background:#FFFFFF; padding:10px;",
														items:[
															new Ext.form.TextField({
																fieldLabel:"노선구분",
																name:"title",
																width:200,
																allowBlank:false
															})
														],
														listeners:{actioncomplete:{fn:function(form,action) {
															if (action.type == "submit") {
																Ext.Msg.show({title:"안내",msg:"성공적으로 추가하였습니다.<br />계속해서 노선구분을 추가하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button){
																	Ext.getCmp("Subway1").getStore().reload();
																	if (button == "ok") {
																		Ext.getCmp("SubwayAddForm").getForm().findField("title").setValue();
																		Ext.getCmp("SubwayAddForm").getForm().findField("title").clearInvalid();
																		Ext.getCmp("SubwayAddForm").getForm().findField("title").focus();
																	} else {
																		Ext.getCmp("SubwayAddWindow").close();
																	}
																}});
			
															}
														}}}
													})
												],
												buttons:[
													new Ext.Button({
														text:"확인",
														icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_tick.png",
														handler:function() {
															Ext.getCmp("SubwayAddForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php?action=database&subaction=subway&do=add&parent=0",waitMsg:"데이터를 전송중입니다."});
														}
													}),
													new Ext.Button({
														text:"취소",
														icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_cross.png",
														handler:function() {
															Ext.getCmp("SubwayAddWindow").close();
														}
													})
												]
											}).show();
										}
									}),
									new Ext.Button({
										text:"노선구분삭제",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_map_delete.png",
										handler:function() {
											var checked = Ext.getCmp("Subway1").selModel.getSelections();
											if (checked.length == 0) {
												Ext.Msg.show({title:"에러",msg:"삭제할 노선구분을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
												return false;
											}
			
											var idxs = new Array();
											for (var i=0, loop=checked.length;i<loop;i++) {
												idxs.push(checked[i].get("idx"));
											}
											var idx = idxs.join(",");
											
											Ext.Msg.show({title:"안내",msg:"선택노선구분을 삭제하게 되면 해당 노선구분의 지하철역도 함께 삭제됩니다.<br />정말 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.QUESTION,fn:function(button) {
												if (button == "ok") {
													Ext.Msg.wait("처리중입니다.","Please Wait...");
													Ext.Ajax.request({
														url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php",
														success:function() {
															Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
															Ext.getCmp("Subway1").getStore().reload();
														},
														failure:function() {
															Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
														},
														headers:{},
														params:{"action":"database","subaction":"subway","do":"delete","idx":idx}
													});
												}
											}});
										}
									}),
									'->',
									new Ext.Button({
										text:"변경사항저장",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_disk.png",
										handler:function() {
											Ext.Msg.wait("처리중입니다.","Please Wait...");
											Ext.Ajax.request({
												url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php",
												success:function() {
													Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
													Ext.getCmp("Subway1").getStore().reload();
												},
												failure:function() {
													Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
												},
												headers:{},
												params:{"action":"database","subaction":"subway","do":"modify","data":GetGridData(Ext.getCmp("Subway1"))}
											});
										}
									})
								],
								bbar:[
									new Ext.Button({
										text:"위로이동",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_arrow_up.png",
										handler:function() {
											var checked = Ext.getCmp("Subway1").selModel.getSelections();
		
											if (checked.length == 0) {
												Ext.Msg.show({title:"에러",msg:"이동할 노선구분을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
												return false;
											}
			
											var selecter = new Array();
											for (var i=0, loop=checked.length;i<loop;i++) {
												var sort = checked[i].get("sort");
												if (sort != 0) {
													Ext.getCmp("Subway1").getStore().getAt(sort).set("sort",sort-1);
													Ext.getCmp("Subway1").getStore().getAt(sort-1).set("sort",sort);
			
													selecter.push(sort-1);
													Ext.getCmp("Subway1").getStore().sort("sort","ASC");
												} else {
													return false;
												}
											}
			
											for (var i=0, loop=selecter.length;i<loop;i++) {
												Ext.getCmp("Subway1").selModel.selectRow(selecter[i]);
											}
										}
									}),
									new Ext.Button({
										text:"아래로이동",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_arrow_down.png",
										handler:function() {
											var checked = Ext.getCmp("Subway1").selModel.getSelections();
		
											if (checked.length == 0) {
												Ext.Msg.show({title:"에러",msg:"이동할 노선구분을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
												return false;
											}
			
											var selecter = new Array();
											for (var i=checked.length-1;i>=0;i--) {
												var sort = checked[i].get("sort");
												if (sort != Ext.getCmp("Subway1").getStore().getCount()-1) {
													Ext.getCmp("Subway1").getStore().getAt(sort).set("sort",sort+1);
													Ext.getCmp("Subway1").getStore().getAt(sort+1).set("sort",sort);
			
													selecter.push(sort+1);
													Ext.getCmp("Subway1").getStore().sort("sort","ASC");
												} else {
													return false;
												}
											}
			
											for (var i=0, loop=selecter.length;i<loop;i++) {
												Ext.getCmp("Subway1").selModel.selectRow(selecter[i]);
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
										header:"노선구분명",
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
									proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php"}),
									reader:new Ext.data.JsonReader({
										root:"lists",
										totalProperty:"totalCount",
										fields:["idx","title",{name:"sort",type:"int"}]
									}),
									remoteSort:false,
									sortInfo:{field:"sort",direction:"ASC"},
									baseParams:{action:"database","subaction":"subway",parent:"0"},
									listeners:{load:{fn:function() {
										Ext.getCmp("Subway2").getStore().baseParams.parent = "-1";
										Ext.getCmp("Subway2").getStore().load();
									}}}
								}),
								autoExpandColumn:"title1",
								flex:1,
								listeners:{rowclick:{fn:function(grid,idx,e) {
									Ext.getCmp("Subway2").getStore().baseParams.parent = grid.getStore().getAt(idx).get("idx");
									Ext.getCmp("Subway2").getStore().load();
								}}}
							}),
							new Ext.grid.EditorGridPanel({
								id:"Subway2",
								title:"지하철역",
								margins:"5 5 5 0",
								tbar:[
									new Ext.Button({
										text:"지하철역추가",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_report_add.png",
										handler:function() {
											if (Ext.getCmp("Subway2").getStore().baseParams.parent == "-1") {
												Ext.Msg.show({title:"에러",msg:"노선구분을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
												return false;
											}
											
											var parent = Ext.getCmp("Subway1").getStore().find("idx",Ext.getCmp("Subway2").getStore().baseParams.parent,false,false);
											
											new Ext.Window({
												id:"SubwayAddWindow",
												title:"지하철역추가 ("+Ext.getCmp("Subway1").getStore().getAt(parent).get("title")+")",
												width:350,
												height:110,
												modal:true,
												resizable:false,
												layout:"fit",
												items:[
													new Ext.form.FormPanel({
														id:"SubwayAddForm",
														labelAlign:"right",
														labelWidth:85,
														border:false,
														errorReader:new Ext.form.XmlErrorReader(),
														style:"background:#FFFFFF; padding:10px;",
														items:[
															new Ext.form.TextField({
																fieldLabel:"지하철역명",
																name:"title",
																width:200,
																allowBlank:false
															})
														],
														listeners:{actioncomplete:{fn:function(form,action) {
															if (action.type == "submit") {
																Ext.Msg.show({title:"안내",msg:"성공적으로 추가하였습니다.<br />계속해서 지하철역을 추가하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button){
																	Ext.getCmp("Subway2").getStore().reload();
																	if (button == "ok") {
																		Ext.getCmp("SubwayAddForm").getForm().findField("title").setValue();
																		Ext.getCmp("SubwayAddForm").getForm().findField("title").clearInvalid();
																		Ext.getCmp("SubwayAddForm").getForm().findField("title").focus();
																	} else {
																		Ext.getCmp("SubwayAddWindow").close();
																	}
																}});
			
															}
														}}}
													})
												],
												buttons:[
													new Ext.Button({
														text:"확인",
														icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_tick.png",
														handler:function() {
															Ext.getCmp("SubwayAddForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php?action=database&subaction=subway&do=add&parent="+Ext.getCmp("Subway2").getStore().baseParams.parent,waitMsg:"데이터를 전송중입니다."});
														}
													}),
													new Ext.Button({
														text:"취소",
														icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_cross.png",
														handler:function() {
															Ext.getCmp("SubwayAddWindow").close();
														}
													})
												]
											}).show();
										}
									}),
									new Ext.Button({
										text:"지하철역삭제",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_report_delete.png",
										handler:function() {
											var checked = Ext.getCmp("Subway2").selModel.getSelections();
											if (checked.length == 0) {
												Ext.Msg.show({title:"에러",msg:"삭제할 지하철역명을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
												return false;
											}
			
											var idxs = new Array();
											for (var i=0, loop=checked.length;i<loop;i++) {
												idxs.push(checked[i].get("idx"));
											}
											var idx = idxs.join(",");
											
											Ext.Msg.show({title:"안내",msg:"정말 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.QUESTION,fn:function(button) {
												if (button == "ok") {
													Ext.Msg.wait("처리중입니다.","Please Wait...");
													Ext.Ajax.request({
														url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php",
														success:function() {
															Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
															Ext.getCmp("Subway1").getStore().reload();
														},
														failure:function() {
															Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
														},
														headers:{},
														params:{"action":"database","subaction":"subway","do":"delete","idx":idx}
													});
												}
											}});
										}
									}),
									'->',
									new Ext.Button({
										text:"변경사항저장",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_disk.png",
										handler:function() {
											Ext.Msg.wait("처리중입니다.","Please Wait...");
											Ext.Ajax.request({
												url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php",
												success:function() {
													Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
													Ext.getCmp("Subway2").getStore().reload();
												},
												failure:function() {
													Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
												},
												headers:{},
												params:{"action":"database","subaction":"subway","do":"modify","data":GetGridData(Ext.getCmp("Subway2"))}
											});
										}
									})
								],
								bbar:[
									new Ext.Button({
										text:"위로이동",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_arrow_up.png",
										handler:function() {
											var checked = Ext.getCmp("Subway2").selModel.getSelections();
		
											if (checked.length == 0) {
												Ext.Msg.show({title:"에러",msg:"이동할 지하철역을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
												return false;
											}
			
											var selecter = new Array();
											for (var i=0, loop=checked.length;i<loop;i++) {
												var sort = checked[i].get("sort");
												if (sort != 0) {
													Ext.getCmp("Subway2").getStore().getAt(sort).set("sort",sort-1);
													Ext.getCmp("Subway2").getStore().getAt(sort-1).set("sort",sort);
			
													selecter.push(sort-1);
													Ext.getCmp("Subway2").getStore().sort("sort","ASC");
												} else {
													return false;
												}
											}
			
											for (var i=0, loop=selecter.length;i<loop;i++) {
												Ext.getCmp("Subway2").selModel.selectRow(selecter[i]);
											}
										}
									}),
									new Ext.Button({
										text:"아래로이동",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_arrow_down.png",
										handler:function() {
											var checked = Ext.getCmp("Subway2").selModel.getSelections();
		
											if (checked.length == 0) {
												Ext.Msg.show({title:"에러",msg:"이동할 지하철역을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
												return false;
											}
			
											var selecter = new Array();
											for (var i=checked.length-1;i>=0;i--) {
												var sort = checked[i].get("sort");
												if (sort != Ext.getCmp("Subway2").getStore().getCount()-1) {
													Ext.getCmp("Subway2").getStore().getAt(sort).set("sort",sort+1);
													Ext.getCmp("Subway2").getStore().getAt(sort+1).set("sort",sort);
			
													selecter.push(sort+1);
													Ext.getCmp("Subway2").getStore().sort("sort","ASC");
												} else {
													return false;
												}
											}
			
											for (var i=0, loop=selecter.length;i<loop;i++) {
												Ext.getCmp("Subway2").selModel.selectRow(selecter[i]);
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
										header:"지하철역명",
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
									proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php"}),
									reader:new Ext.data.JsonReader({
										root:"lists",
										totalProperty:"totalCount",
										fields:["idx","title",{name:"sort",type:"int"}]
									}),
									remoteSort:false,
									sortInfo:{field:"sort",direction:"ASC"},
									baseParams:{action:"database","subaction":"subway",parent:"-1"},
									listeners:{load:{fn:function(store) {
										var parent = Ext.getCmp("Subway1").getStore().find("idx",store.baseParams.parent,false,false);
										if (parent != -1) {
											Ext.getCmp("Subway2").setTitle("지하철역 ("+Ext.getCmp("Subway1").getStore().getAt(parent).get("title")+")");
										}
									}}}
								}),
								autoExpandColumn:"title2",
								flex:1
							})
						]
					})
				]
			})
			
		],
		listeners:{render:{fn:function() {
			Ext.getCmp("University1").getStore().load();
			Ext.getCmp("Subway1").getStore().load();
		}}}
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>