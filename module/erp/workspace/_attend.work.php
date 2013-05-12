<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"작업일보",
		layout:"fit",
		tbar:[
			new Ext.Button({
				icon:"<?php echo $this->moduleDir; ?>/images/common/icon_control_left.png",
				text:"이전일",
				handler:function() {
					var today = new Date(Ext.getCmp("today").getValue()).add("d",-1).format("Y-m-d");
					Ext.getCmp("today").setValue(today);

					SetCookie("iErpDate",today);
					Ext.getCmp("WorkList").getStore().baseParams.date = today;
					Ext.getCmp("WorkList").getStore().load();

					Ext.getCmp("OutsourcingList").getStore().baseParams.date = today;
					Ext.getCmp("OutsourcingList").getStore().load();

					Ext.getCmp("DayworkerList").getStore().baseParams.date = today;
					Ext.getCmp("DayworkerList").getStore().load();
				}
			}),
			' ',
			new Ext.form.DateField({
				id:"today",
				format:"Y-m-d",
				width:90,
				value:"<?php echo Request('iErpDate','cookie') != null ? Request('iErpDate','cookie') : GetTime('Y-m-d'); ?>",
				listeners:{select:{fn:function(form,date) {
					var today = new Date(date).format("Y-m-d");

					SetCookie("iErpDate",today);
					Ext.getCmp("WorkList").getStore().baseParams.date = today;
					Ext.getCmp("WorkList").getStore().load();

					Ext.getCmp("OutsourcingList").getStore().baseParams.date = today;
					Ext.getCmp("OutsourcingList").getStore().load();

					Ext.getCmp("DayworkerList").getStore().baseParams.date = today;
					Ext.getCmp("DayworkerList").getStore().load();
				}}}
			}),
			' ',
			new Ext.Button({
				icon:"<?php echo $this->moduleDir; ?>/images/common/icon_control_right.png",
				iconAlign:"right",
				text:"다음일",
				handler:function() {
					var today = new Date(Ext.getCmp("today").getValue()).add("d",1).format("Y-m-d");
					Ext.getCmp("today").setValue(today);

					SetCookie("iErpDate",today);
					Ext.getCmp("WorkList").getStore().baseParams.date = today;
					Ext.getCmp("WorkList").getStore().load();

					Ext.getCmp("OutsourcingList").getStore().baseParams.date = today;
					Ext.getCmp("OutsourcingList").getStore().load();

					Ext.getCmp("DayworkerList").getStore().baseParams.date = today;
					Ext.getCmp("DayworkerList").getStore().load();
				}
			}),
			'-',
			new Ext.Button({
				id:"InsertButton",
				text:"작업등록 및 수정",
				icon:"<?php echo $this->moduleDir; ?>/images/common/icon_report_edit.png",
				handler:function() {
					if (Ext.getCmp("ListTab").getActiveTab().getId() == "WorkList") {
						var checked = Ext.getCmp("WorkList").selModel.getSelections();
						if (checked.length == 0) {
							Ext.Msg.show({title:"에러",msg:"작업을 등록/수정할 근로자를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
							return false;
						}

						new Ext.Window({
							id:"AddWindow",
							title:"작업등록 및 수정",
							width:400,
							height:300,
							modal:true,
							resizable:false,
							layout:"fit",
							items:[
								new Ext.Panel({
									border:false,
									layout:"border",
									items:[
										new Ext.TabPanel({
											id:"WorkForm",
											region:"north",
											activeTab:0,
											border:false,
											height:100,
											items:[
												new Ext.form.FormPanel({
													title:"신규작업",
													labelAlign:"right",
													labelWidth:70,
													border:false,
													autoWidth:true,
													autoHeight:true,
													errorReader:new Ext.form.XmlErrorReader(),
													style:"padding:10px;",
													items:[
														new Ext.form.ComboBox({
															fieldLabel:"전일작업",
															typeAhead:true,
															triggerAction:"all",
															lazyRender:true,
															store:new Ext.data.Store({
																proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Workspace.get.php"}),
																reader:new Ext.data.JsonReader({
																	root:"lists",
																	totalProperty:"totalCount",
																	fields:["idx","display","work"]
																}),
																remoteSort:false,
																sortInfo:{field:"idx",direction:"ASC"},
																baseParams:{"action":"attend","get":"yesterday_work","wno":<?php echo $this->wno; ?>},
															}),
															width:260,
															editable:false,
															mode:"local",
															displayField:"display",
															valueField:"work",
															listeners:{
																render:{fn:function(form) {
																	form.getStore().load();
																	form.getStore().on("load",function(store) {
																		form.setValue(store.getAt(0).get("work"));
																		Ext.getCmp("WorkForm").getActiveTab().getForm().findField("work").enable();
																	});
																}},
																select:{fn:function(form) {
																	if (form.getValue() == "") {
																		Ext.getCmp("WorkForm").getActiveTab().getForm().findField("work").enable();
																		Ext.getCmp("WorkForm").getActiveTab().getForm().findField("work").focus();
																	} else {
																		Ext.getCmp("WorkForm").getActiveTab().getForm().findField("work").disable();
																		Ext.getCmp("WorkForm").getActiveTab().getForm().findField("work").setValue(form.getValue());
																	}
																}}
															}
														}),
														new Ext.form.TextField({
															fieldLabel:"작업명",
															disabled:true,
															name:"work",
															width:260,
															emptyText:"신규작업명을 입력하세요."
														})
													]
												})
											]
										}),
										new Ext.Panel({
											region:"center",
											margins:"0 -1 -1 -1",
											title:"공수 및 세부설정",
											items:[
												new Ext.form.FormPanel({
													id:"DetailForm",
													region:"south",
													height:180,
													labelAlign:"right",
													labelWidth:70,
													border:false,
													autoWidth:true,
													autoHeight:true,
													errorReader:new Ext.form.XmlErrorReader(),
													style:"padding:10px;",
													items:[
														new Ext.form.ComboBox({
															fieldLabel:"공수설정",
															name:"working",
															typeAhead:true,
															triggerAction:"all",
															lazyRender:true,
															store:new Ext.data.SimpleStore({
																fields:["value","working"],
																data:[["0","0.0"],["5","0.5"],["10","1.0"],["15","1.5"],["20","2.0"],["25","2.5"],["30","3.0"]]
															}),
															editable:false,
															mode:"local",
															displayField:"working",
															valueField:"value",
															emptyText:"변경하지 않음"
														}),
														new Ext.form.ComboBox({
															fieldLabel:"야근여부",
															name:"is_overwork",
															typeAhead:true,
															triggerAction:"all",
															lazyRender:true,
															listClass:'x-combo-list-small',
															store:new Ext.data.SimpleStore({
																fields:["is_overwork","value"],
																data:[["야근","TRUE"],["정상근무","FALSE"]]
															}),
															editable:false,
															mode:"local",
															displayField:"is_overwork",
															valueField:"value",
															emptyText:"변경하지 않음"
														}),
														new Ext.form.TextField({
															fieldLabel:"검토사항",
															name:"etc",
															width:260,
															emptyText:"검토사항을 변경하실려면 입력하세요."
														})
													]
												})
											]
										})
									]
								})
							],
							buttons:[
								new Ext.Button({
									text:"확인",
									icon:"<?php echo $this->moduleDir; ?>/images/common/icon_tick.png",
									handler:function() {
										if (!Ext.getCmp("WorkForm").getActiveTab().getForm().findField("work").getValue()) {
											Ext.Msg.show({title:"에러",msg:"작업명을 입력하거나 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
											return false;
										}

										var checked = Ext.getCmp("WorkList").selModel.getSelections();

										for (var i=0, loop=checked.length;i<loop;i++) {
											checked[i].set("work",Ext.getCmp("WorkForm").getActiveTab().getForm().findField("work").getValue());
											if (Ext.getCmp("DetailForm").getForm().findField("working").getValue()) {
												checked[i].set("working",Ext.getCmp("DetailForm").getForm().findField("working").getValue());
											}
											if (Ext.getCmp("DetailForm").getForm().findField("is_overwork").getValue()) {
												checked[i].set("is_overwork",Ext.getCmp("DetailForm").getForm().findField("is_overwork").getValue());
											}
											if (Ext.getCmp("DetailForm").getForm().findField("etc").getValue()) {
												checked[i].set("etc",Ext.getCmp("DetailForm").getForm().findField("etc").getValue());
											}
										}

										Ext.getCmp("WorkList").getStore().clearGrouping();
										Ext.getCmp("WorkList").getStore().groupBy("work");
										Ext.getCmp("WorkList").selModel.clearSelections();
										Ext.getCmp("AddWindow").close();
									}
								}),
								new Ext.Button({
									text:"취소",
									icon:"<?php echo $this->moduleDir; ?>/images/common/icon_cross.png",
									handler:function() {
										Ext.getCmp("AddWindow").close();
									}
								})
							]
						}).show();
					} else if (Ext.getCmp("ListTab").getActiveTab().getId() == "OutsourcingList") {
						new Ext.Window({
							id:"AddWindow",
							title:"외주노무 작업등록",
							width:400,
							height:270,
							modal:true,
							resizable:false,
							layout:"fit",
							items:[
								new Ext.form.FormPanel({
									id:"AddForm",
									labelAlign:"right",
									labelWidth:80,
									border:false,
									autoWidth:true,
									errorReader:new Ext.form.XmlErrorReader(),
									style:"padding:10px; background:#FFFFFF;",
									items:[
										new Ext.form.ComboBox({
											hiddenName:"cno",
											fieldLabel:"외주업체명",
											typeAhead:true,
											triggerAction:"all",
											lazyRender:true,
											store:new Ext.data.Store({
												proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Workspace.get.php"}),
												reader:new Ext.data.JsonReader({
													root:"lists",
													totalProperty:"totalCount",
													fields:["idx","title"]
												}),
												remoteSort:false,
												sortInfo:{field:"title",direction:"ASC"},
												baseParams:{"action":"attend","get":"outsourcing","wno":<?php echo $this->wno; ?>},
											}),
											width:260,
											editable:false,
											mode:"local",
											displayField:"title",
											valueField:"idx",
											emptyText:"외주업체를 선택하여 주십시오.",
											allowBlank:false,
											listeners:{
												render:{fn:function(form) {
													form.getStore().load();
												}}
											}
										}),
										new Ext.form.TextField({
											fieldLabel:"작업 및 검토",
											name:"work",
											width:260,
											allowBlank:false,
											emptyText:"작업 및 검토사항을 입력하세요."
										}),
										new Ext.form.TextField({
											fieldLabel:"파견인원",
											name:"worker",
											width:100,
											value:"0",
											allowBlank:false,
											style:"text-align:right;",
											enableKeyEvents:true,
											listeners:{
												keydown:{fn:PressNumberOnly},
												blur:{fn:BlurNumberFormat},
												focus:{fn:FocusNumberOnly}
											}
										}),
										new Ext.form.TextField({
											fieldLabel:"일일노임",
											name:"payment",
											width:100,
											value:"0",
											allowBlank:false,
											style:"text-align:right;",
											enableKeyEvents:true,
											listeners:{
												keydown:{fn:PressNumberOnly},
												blur:{fn:BlurNumberFormat},
												focus:{fn:FocusNumberOnly}
											}
										}),
										new Ext.form.TimeField({
											fieldLabel:"출근시간",
											name:"intime",
											width:80,
											increment:30,
											typeAhead:true,
											lazyRender:false,
											editable:true,
											allowBlank:false,
											format:"H:i"
										}),
										new Ext.form.TimeField({
											fieldLabel:"퇴근시간",
											name:"outtime",
											width:80,
											increment:30,
											typeAhead:true,
											lazyRender:false,
											editable:true,
											format:"H:i"
										}),
										new Ext.form.ComboBox({
											fieldLabel:"야근여부",
											hiddenName:"is_overwork",
											width:80,
											typeAhead:true,
											triggerAction:"all",
											lazyRender:true,
											listClass:'x-combo-list-small',
											store:new Ext.data.SimpleStore({
												fields:["is_overwork","value"],
												data:[["야근","TRUE"],["정상근무","FALSE"]]
											}),
											editable:false,
											mode:"local",
											value:"FALSE",
											displayField:"is_overwork",
											valueField:"value"
										})
									],
									listeners:{actioncomplete:{fn:function(form,action) {
										if (action.type == "submit") {
											Ext.getCmp("OutsourcingList").getStore().reload();
											Ext.getCmp("AddWindow").close();
										}
									}}}
								})
							],
							buttons:[
								new Ext.Button({
									text:"확인",
									icon:"<?php echo $this->moduleDir; ?>/images/common/icon_tick.png",
									handler:function() {
										Ext.getCmp("AddForm").getForm().submit({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php?action=attend&do=attend_outsourcing_write&date="+Ext.getCmp("today").getValue().format("Y-m-d")+"&wno=<?php echo $this->wno; ?>",waitMsg:"외주노무 작업을 추가중입니다."});
									}
								}),
								new Ext.Button({
									text:"취소",
									icon:"<?php echo $this->moduleDir; ?>/images/common/icon_cross.png",
									handler:function() {
										Ext.getCmp("AddWindow").close();
									}
								})
							]
						}).show();
					} else {
						new Ext.Window({
							id:"AddWindow",
							title:"일용직노무 작업등록",
							width:400,
							height:380,
							modal:true,
							resizable:false,
							layout:"fit",
							items:[
								new Ext.form.FormPanel({
									id:"AddForm",
									labelAlign:"right",
									labelWidth:80,
									border:false,
									autoWidth:true,
									errorReader:new Ext.form.XmlErrorReader(),
									style:"padding:10px; background:#FFFFFF;",
									items:[
										new Ext.form.ComboBox({
											hiddenName:"dno",
											fieldLabel:"기존근로자",
											typeAhead:true,
											triggerAction:"all",
											lazyRender:true,
											store:new Ext.data.Store({
												proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Workspace.get.php"}),
												reader:new Ext.data.JsonReader({
													root:"lists",
													totalProperty:"totalCount",
													fields:["idx","name","value"]
												}),
												remoteSort:false,
												sortInfo:{field:"name",direction:"ASC"},
												baseParams:{"action":"attend","get":"dayworker","wno":<?php echo $this->wno; ?>},
											}),
											width:260,
											editable:false,
											mode:"local",
											displayField:"name",
											valueField:"idx",
											emptyText:"기존근로자를 선택하여 주십시오.",
											listeners:{
												render:{fn:function(form) {
													form.getStore().load();
												}},
												select:{fn:function(form,record,idx) {
													if (form.getValue()) {
														var temp = record.data.value.split("@");
														Ext.getCmp("AddForm").getForm().findField("name").setValue(temp[0]);
														Ext.getCmp("AddForm").getForm().findField("jumin").setValue(temp[1]);
														Ext.getCmp("AddForm").getForm().findField("payment").setValue(temp[2]);
														Ext.getCmp("AddForm").getForm().findField("account_name").setValue(temp[3]);
														Ext.getCmp("AddForm").getForm().findField("account_bank").setValue(temp[4]);
														Ext.getCmp("AddForm").getForm().findField("account_number").setValue(temp[5]);

														Ext.getCmp("AddForm").getForm().findField("name").disable();
														Ext.getCmp("AddForm").getForm().findField("jumin").disable();
													} else {
														Ext.getCmp("AddForm").getForm().findField("name").setValue("");
														Ext.getCmp("AddForm").getForm().findField("jumin").setValue("");
														Ext.getCmp("AddForm").getForm().findField("payment").setValue("");
														Ext.getCmp("AddForm").getForm().findField("account_name").setValue("");
														Ext.getCmp("AddForm").getForm().findField("account_bank").setValue("");
														Ext.getCmp("AddForm").getForm().findField("account_number").setValue("");

														Ext.getCmp("AddForm").getForm().findField("name").enable();
														Ext.getCmp("AddForm").getForm().findField("jumin").enable();
													}
												}}
											}
										}),
										new Ext.form.TextField({
											fieldLabel:"이름",
											name:"name",
											width:100
										}),
										new Ext.form.TextField({
											fieldLabel:"주민등록번호",
											name:"jumin",
											width:200,
											validator:CheckJumin,
											listeners:{blur:{fn:BlurJumin}}
										}),
										new Ext.form.TextField({
											fieldLabel:"작업 및 검토",
											name:"work",
											width:260,
											allowBlank:false,
											emptyText:"작업 및 검토사항을 입력하세요."
										}),
										new Ext.form.TextField({
											fieldLabel:"단가",
											name:"payment",
											width:100,
											value:"0",
											allowBlank:false,
											style:"text-align:right;",
											enableKeyEvents:true,
											listeners:{
												keydown:{fn:PressNumberOnly},
												blur:{fn:BlurNumberFormat},
												focus:{fn:FocusNumberOnly}
											}
										}),
										new Ext.form.TextField({
											fieldLabel:"예금주",
											name:"account_name",
											width:100
										}),
										new Ext.form.ComboBox({
											fieldLabel:"은행명",
											hiddenName:"account_bank",
											store:BankSimpleStore,
											displayField:"bank",
											valueField:"bank",
											typeAhead:true,
											mode:"local",
											triggerAction:"all",
											emptyText:"은행을 선택하세요.",
											width:190,
											editable:false
										}),
										new Ext.form.TextField({
											fieldLabel:"계좌번호",
											name:"account_number",
											width:190
										}),
										new Ext.form.TimeField({
											fieldLabel:"출근시간",
											name:"intime",
											width:80,
											increment:30,
											typeAhead:true,
											lazyRender:false,
											editable:true,
											allowBlank:false,
											format:"H:i"
										}),
										new Ext.form.TimeField({
											fieldLabel:"퇴근시간",
											name:"outtime",
											width:80,
											increment:30,
											typeAhead:true,
											lazyRender:false,
											editable:true,
											format:"H:i"
										}),
										new Ext.form.ComboBox({
											fieldLabel:"야근여부",
											hiddenName:"is_overwork",
											width:80,
											typeAhead:true,
											triggerAction:"all",
											lazyRender:true,
											listClass:'x-combo-list-small',
											store:new Ext.data.SimpleStore({
												fields:["is_overwork","value"],
												data:[["야근","TRUE"],["정상근무","FALSE"]]
											}),
											editable:false,
											mode:"local",
											value:"FALSE",
											displayField:"is_overwork",
											valueField:"value"
										})
									],
									listeners:{actioncomplete:{fn:function(form,action) {
										if (action.type == "submit") {
											Ext.getCmp("DayworkerList").getStore().reload();
											Ext.getCmp("AddWindow").close();
										}
									}}}
								})
							],
							buttons:[
								new Ext.Button({
									text:"확인",
									icon:"<?php echo $this->moduleDir; ?>/images/common/icon_tick.png",
									handler:function() {
										Ext.getCmp("AddForm").getForm().submit({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php?action=attend&do=attend_dayworker_write&date="+Ext.getCmp("today").getValue().format("Y-m-d")+"&wno=<?php echo $this->wno; ?>",waitMsg:"일용직노무 작업을 추가중입니다."});
									}
								}),
								new Ext.Button({
									text:"취소",
									icon:"<?php echo $this->moduleDir; ?>/images/common/icon_cross.png",
									handler:function() {
										Ext.getCmp("AddWindow").close();
									}
								})
							]
						}).show();
					}
				}
			}),
			new Ext.Button({
				text:"작업삭제",
				icon:"<?php echo $this->moduleDir; ?>/images/common/icon_report_delete.png",
				handler:function() {
					Ext.Msg.show({title:"안내",msg:"정말 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
						if (button == "ok") {
							if (Ext.getCmp("ListTab").getActiveTab().getId() == "WorkList") {
								var checked = Ext.getCmp("WorkList").selModel.getSelections();
								if (checked.length == 0) {
									Ext.Msg.show({title:"에러",msg:"작업을 삭제할 근로자를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
									return false;
								}

								for (var i=0, loop=checked.length;i<loop;i++) {
									checked[i].set("work","");
								}
							} else if (Ext.getCmp("ListTab").getActiveTab().getId() == "OutsourcingList") {
								var checked = Ext.getCmp("OutsourcingList").selModel.getSelections();
								if (checked.length == 0) {
									Ext.Msg.show({title:"에러",msg:"작업을 삭제할 외주업체를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
									return false;
								}

								var idxs = new Array();
								for (var i=0, loop=checked.length;i<loop;i++) {
									idxs[i] = checked[i].get("idx","");
								}

								var idx = idxs.join(",");
								Ext.Ajax.request({
									url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php",
									success:function() {
										Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO,animEl:"SaveButton"});
										Ext.getCmp("OutsourcingList").getStore().reload();
									},
									failure:function() {
										Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 삭제하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
									},
									headers:{},
									params:{"action":"attend","do":"work_outsourcing_delete","wno":"<?php echo $this->wno; ?>","idx":idx}
								});
							} else if (Ext.getCmp("ListTab").getActiveTab().getId() == "DayworkerList") {
								var checked = Ext.getCmp("DayworkerList").selModel.getSelections();
								if (checked.length == 0) {
									Ext.Msg.show({title:"에러",msg:"작업을 삭제할 일용직근로자를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
									return false;
								}

								var idxs = new Array();
								for (var i=0, loop=checked.length;i<loop;i++) {
									idxs[i] = checked[i].get("idx","");
								}

								var idx = idxs.join(",");
								Ext.Ajax.request({
									url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php",
									success:function() {
										Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO,animEl:"SaveButton"});
										Ext.getCmp("DayworkerList").getStore().reload();
									},
									failure:function() {
										Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 삭제하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
									},
									headers:{},
									params:{"action":"attend","do":"work_dayworker_delete","wno":"<?php echo $this->wno; ?>","idx":idx}
								});
							}
						}
					}});
				}
			}),
			new Ext.Button({
				text:"변경사항 저장하기",
				icon:"<?php echo $this->moduleDir; ?>/images/common/icon_report_disk.png",
				handler:function() {
					if (Ext.getCmp("ListTab").getActiveTab().getId() == "WorkList") {
						for (var i=0, loop=Ext.getCmp("WorkList").getStore().getCount();i<loop;i++) {
							if (!Ext.getCmp("WorkList").getStore().getAt(i).get("work")) {
								Ext.Msg.show({title:"에러",msg:"작업명이 등록되지 않은 근로자가 있습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
								return false;
							}
						}

						var data = GetGridData(Ext.getCmp("WorkList"));

						Ext.Ajax.request({
							url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php",
							success:function() {
								Ext.Msg.show({title:"안내",msg:"작업일보가 성공적으로 저장되었습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO,animEl:"SaveButton"});
								Ext.getCmp("WorkList").getStore().commitChanges();
							},
							failure:function() {
								Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 저장하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
							},
							headers:{},
							params:{"action":"attend","do":"work","wno":"<?php echo $this->wno; ?>","date":new Date(Ext.getCmp("today").getValue()).format("Y-m-d"),"data":data}
						});
					} else if (Ext.getCmp("ListTab").getActiveTab().getId() == "OutsourcingList") {
						var data = GetGridData(Ext.getCmp("OutsourcingList"));

						Ext.Ajax.request({
							url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php",
							success:function() {
								Ext.Msg.show({title:"안내",msg:"작업일보가 성공적으로 저장되었습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO,animEl:"SaveButton"});
								Ext.getCmp("OutsourcingList").getStore().commitChanges();
							},
							failure:function() {
								Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 저장하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
							},
							headers:{},
							params:{"action":"attend","do":"work_outsourcing","wno":"<?php echo $this->wno; ?>","date":new Date(Ext.getCmp("today").getValue()).format("Y-m-d"),"data":data}
						});
					} else if (Ext.getCmp("ListTab").getActiveTab().getId() == "DayworkerList") {
						var data = GetGridData(Ext.getCmp("DayworkerList"));

						Ext.Ajax.request({
							url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php",
							success:function() {
								Ext.Msg.show({title:"안내",msg:"작업일보가 성공적으로 저장되었습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO,animEl:"SaveButton"});
								Ext.getCmp("DayworkerList").getStore().commitChanges();
							},
							failure:function() {
								Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 저장하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
							},
							headers:{},
							params:{"action":"attend","do":"work_dayworker","wno":"<?php echo $this->wno; ?>","date":new Date(Ext.getCmp("today").getValue()).format("Y-m-d"),"data":data}
						});
					}
				}
			}),
			'-',
			new Ext.Button({
				text:"전일작업 불러오기",
				icon:"<?php echo $this->moduleDir; ?>/images/common/icon_table_relationship.png",
				handler:function() {
					Ext.Msg.show({title:"안내",msg:"전일작업을 불러오면, 금일 작성된 작업일보는 초기화 됩니다.<br />전일작업을 불러오시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
						if (button == "ok") {
							if (Ext.getCmp("ListTab").getActiveTab().getId() == "WorkList") {
								var mode = "member";
							} else if (Ext.getCmp("ListTab").getActiveTab().getId() == "OutsourcingList") {
								var mode = "outsourcing";
							} else {
								var mode = "dayworker";
							}

							Ext.Ajax.request({
								url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php",
								success:function() {
									Ext.Msg.show({title:"안내",msg:"전일작업을 성공적으로 로딩하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
									Ext.getCmp("ListTab").getActiveTab().getStore().load();
								},
								failure:function() {
									Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 저장하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
								},
								headers:{},
								params:{"action":"attend","do":"yesterday_load","wno":"<?php echo $this->wno; ?>","date":new Date(Ext.getCmp("today").getValue()).format("Y-m-d"),"mode":mode}
							});
						}
					}});
				}
			})
		],
		items:[
			new Ext.TabPanel({
				id:"ListTab",
				border:false,
				tabPosition:"bottom",
				activeTab:0,
				items:[
					new Ext.grid.EditorGridPanel({
						title:"직원관리",
						id:"WorkList",
						border:false,
						cm:new Ext.grid.ColumnModel([
							new Ext.grid.RowNumberer(),
							{
								dataIndex:"idx",
								hidden:true,
								hideable:false
							},{
								header:"작업명",
								dataIndex:"work",
								width:200,
								renderer:function(value) {
									return value ? value : "(미등록)";
								}
							},{
								header:"이름",
								dataIndex:"name",
								sortable:false,
								locked:true,
								width:60
							},{
								header:"직위",
								dataIndex:"grade",
								width:80,
								renderer:function(value) {
									var temp = value.split("||");
									var sHTML = temp[0];

									if (temp.length > 1 && temp[1]) {
										sHTML+= " ("+temp[1]+")";
									}

									return sHTML;
								}
							},{
								header:"직원번호",
								dataIndex:"workernum",
								sortable:false,
								width:110,
								renderer:function(value,p,record) {
									var sHTML = value;
									if (record.data.is_support == "TRUE") {
										sHTML+= '<img src="<?php echo $this->moduleDir; ?>/images/common/icon_support.png" class="grid-bullet-text" onmouseover="Tip(true,\'['+record.data.oworkspace+']에서 파견근무\',event);" onmouseout="Tip(false)" />';
									}

									return sHTML;
								}
							},{
								header:"주민등록번호",
								dataIndex:"jumin",
								sortable:false,
								width:110
							},{
								header:"출근시간",
								dataIndex:"intime",
								sortable:false,
								width:100,
								renderer:function(value,p,record) {
									var sHTML = '<span style="font-family:arial;">';
									if (record.data.is_delay == "TRUE") {
										sHTML+= '<span style="color:#FF0000;">'+value+'</span>';
									} else {
										sHTML+= value;
									}

									if (record.data.write_intime) {
										sHTML+= ' / ';
										if (record.data.is_delay == "TRUE") sHTML+= '<span style="color:#FF0000;">';
										sHTML+= '<span style="font-weight:bold;" onmouseover="Tip(true,\''+record.data.write_memo+'\',event);" onmouseout="Tip(false)">'+record.data.write_intime+'</span>';
										if (record.data.is_delay == "TRUE") sHTML+= '</span>';
									}

									if (record.data.is_write == "TRUE") {
										sHTML+= '<img src="<?php echo $this->moduleDir; ?>/images/common/icon_handwrite.png" class="grid-bullet" onmouseover="Tip(true,\''+record.data.write_memo+'\',event);" onmouseout="Tip(false)" />';
									}

									if (record.data.inphoto) {
										sHTML+= '<img src="<?php echo $this->moduleDir; ?>/images/common/icon_photo.png" class="grid-bullet" onmouseover="Tip(true,\'<img src='+record.data.inphoto+' />\',event);" onmouseout="Tip(false)" />';
									}

									return sHTML;
								}
							},{
								header:"퇴근시간",
								dataIndex:"outtime",
								sortable:false,
								width:110,
								renderer:function(value,p,record) {
									var sHTML = '<span style="font-family:arial;">';
									if (record.data.is_early == "TRUE") {
										sHTML+= '<span style="color:#FF0000;">'+value+'</span>';
									} else {
										sHTML+= value;
									}

									if (record.data.write_outtime) {
										sHTML+= ' / ';
										if (record.data.is_early == "TRUE") sHTML+= '<span style="color:#FF0000;">';
										sHTML+= '<span style="font-weight:bold;"onmouseover="Tip(true,\''+record.data.write_memo+'\',event);" onmouseout="Tip(false)">'+record.data.write_outtime+'</span>';
										if (record.data.is_early == "TRUE") sHTML+= '</span>';
									}

									if (record.data.is_write == "TRUE") {
										sHTML+= '<img src="<?php echo $this->moduleDir; ?>/images/common/icon_handwrite.png" class="grid-bullet" onmouseover="Tip(true,\''+record.data.write_memo+'\',event);" onmouseout="Tip(false)" />';
									}

									if (record.data.outphoto) {
										sHTML+= '<img src="<?php echo $this->moduleDir; ?>/images/common/icon_photo.png" class="grid-bullet" onmouseover="Tip(true,\'<img src='+record.data.outphoto+' />\',event);" onmouseout="Tip(false)" />';
									}

									return sHTML;
								}
							},{
								header:"야근여부",
								dataIndex:"is_overwork",
								sortable:false,
								width:80,
								renderer:function(value) {
									if (value == "TRUE") {
										return "야근";
									} else {
										return "정상근무";
									}
								},
								editor:new Ext.form.ComboBox({
									typeAhead:true,
									triggerAction:"all",
									lazyRender:true,
									store:new Ext.data.SimpleStore({
										fields:["value","is_overwork"],
										data:[["TRUE","야근"],["FALSE","정상근무"]]
									}),
									editable:false,
									mode:"local",
									displayField:"is_overwork",
									valueField:"value"
								})
							},{
								header:"공수",
								dataIndex:"working",
								sortable:false,
								width:80,
								renderer:function(value) {
									return '<div style="text-align:right; font-family:arial;">'+(value/10).toFixed(1)+'</div>';
								},
								editor:new Ext.form.ComboBox({
									typeAhead:true,
									triggerAction:"all",
									lazyRender:true,
									store:new Ext.data.SimpleStore({
										fields:["value","working"],
										data:[["0","0.0"],["5","0.5"],["10","1.0"],["15","1.5"],["20","2.0"],["25","2.5"],["30","3.0"]]
									}),
									editable:false,
									mode:"local",
									displayField:"working",
									valueField:"value"
								})
							},{
								header:"검토사항",
								dataIndex:"etc",
								sortable:false,
								width:250,
								editor:new Ext.form.TextField({selectOnFocus:true})
							},
							new Ext.grid.CheckboxSelectionModel()
						]),
						sm:new Ext.grid.CheckboxSelectionModel(),
						clicksToEdit:1,
						stripeRows:true,
						store:new Ext.data.GroupingStore({
							proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php"}),
							reader:new Ext.data.JsonReader({
								root:"lists",
								totalProperty:"totalCount",
								fields:[{name:"idx",type:"int"},"work","pno","name","grade","photo","workernum","jumin","intime","outtime","write_intime","write_outtime","inphoto","outphoto","is_overwork","is_support","oworkspace","is_write",{name:"working",type:"int"},"write_memo","etc"]
							}),
							remoteSort:false,
							sortInfo:{field:"name",direction:"ASC"},
							groupField:"work",
							baseParams:{"wno":"<?php echo $this->wno; ?>","action":"attend","get":"work","date":"<?php echo Request('iErpDate','cookie') != null ? Request('iErpDate','cookie') : GetTime('Y-m-d'); ?>"}
						}),
						view:new Ext.grid.GroupingView({
							enableGroupingMenu:false,
							hideGroupedColumn:true,
							forceFit:false,
							groupTextTpl:"<span class=\"attend-work-workspace-target\">{text} ({[values.rs.length]}명) </span>"
						}),
						trackMouseOver:true,
						loadMask:{msg:"데이터를 로딩중입니다."}
					}),
					new Ext.grid.EditorGridPanel({
						title:"하도급근로자관리",
						id:"OutsourcingList",
						border:false,
						cm:new Ext.grid.ColumnModel([
							new Ext.grid.RowNumberer(),
							{
								dataIndex:"idx",
								hidden:true,
								hideable:false
							},{
								header:"외주업체명",
								dataIndex:"company",
								width:200
							},{
								header:"근로자수",
								dataIndex:"worker",
								sortable:false,
								locked:true,
								width:60,
								renderer:GridNumberFormat,
								editor:new Ext.form.NumberField({selectOnFocus:true})
							},{
								header:"일일노임",
								dataIndex:"payment",
								sortable:false,
								locked:true,
								width:100,
								renderer:GridNumberFormat,
								editor:new Ext.form.NumberField({selectOnFocus:true})
							},{
								header:"금월노임",
								dataIndex:"monthly_payment",
								width:80,
								renderer:function(value,p,record) {
									return GridNumberFormat(value+record.data.payment);
								}
							},{
								header:"출근시간",
								dataIndex:"intime",
								sortable:false,
								width:70,
								editor:new Ext.form.TimeField({
									increment:30,
									typeAhead:true,
									lazyRender:false,
									editable:true,
									format:"H:i"
								})
							},{
								header:"퇴근시간",
								dataIndex:"outtime",
								sortable:false,
								width:70,
								editor:new Ext.form.TimeField({
									increment:30,
									typeAhead:true,
									lazyRender:false,
									editable:true,
									format:"H:i"
								})
							},{
								header:"야근여부",
								dataIndex:"is_overwork",
								sortable:false,
								width:80,
								renderer:function(value) {
									if (value == "TRUE") {
										return "야근";
									} else {
										return "정상근무";
									}
								},
								editor:new Ext.form.ComboBox({
									typeAhead:true,
									triggerAction:"all",
									lazyRender:true,
									store:new Ext.data.SimpleStore({
										fields:["value","is_overwork"],
										data:[["TRUE","야근"],["FALSE","정상근무"]]
									}),
									editable:false,
									mode:"local",
									displayField:"is_overwork",
									valueField:"value"
								})
							},{
								header:"작업 및 검토사항",
								dataIndex:"work",
								sortable:false,
								width:250,
								editor:new Ext.form.TextField({selectOnFocus:true})
							},
							new Ext.grid.CheckboxSelectionModel()
						]),
						sm:new Ext.grid.CheckboxSelectionModel(),
						clicksToEdit:1,
						trackMouseOver:true,
						stripeRows:true,
						store:new Ext.data.Store({
							proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php"}),
							reader:new Ext.data.JsonReader({
								root:"lists",
								totalProperty:"totalCount",
								fields:[{name:"idx",type:"int"},"company",{name:"payment",type:"int"},{name:"monthly_payment",type:"int"},"worker","intime","outtime","is_overwork","work"]
							}),
							remoteSort:false,
							sortInfo:{field:"company",direction:"ASC"},
							baseParams:{"wno":"<?php echo $this->wno; ?>","action":"attend","get":"work_outsourcing","date":"<?php echo Request('iErpDate','cookie') != null ? Request('iErpDate','cookie') : GetTime('Y-m-d'); ?>"}
						})
					}),
					new Ext.grid.EditorGridPanel({
						title:"일용직근로자관리",
						id:"DayworkerList",
						cm:new Ext.grid.ColumnModel([
							new Ext.grid.RowNumberer(),
							{
								dataIndex:"idx",
								hidden:true,
								hideable:false
							},{
								header:"이름",
								dataIndex:"name",
								width:80
							},{
								header:"주민등록번호",
								dataIndex:"jumin",
								width:120
							},{
								header:"단가",
								dataIndex:"payment",
								width:70,
								renderer:GridNumberFormat,
								editor:new Ext.form.NumberField({selectOnFocus:true})
							},{
								header:"금월단가",
								dataIndex:"monthly_payment",
								width:80,
								renderer:function(value,p,record) {
									return GridNumberFormat(value+record.data.payment);
								}
							},{
								header:"출근시간",
								dataIndex:"intime",
								width:70,
								editor:new Ext.form.TimeField({
									increment:30,
									typeAhead:true,
									lazyRender:false,
									editable:true,
									format:"H:i"
								})
							},{
								header:"퇴근시간",
								dataIndex:"outtime",
								width:70,
								editor:new Ext.form.TimeField({
									increment:30,
									typeAhead:true,
									lazyRender:false,
									editable:true,
									format:"H:i"
								})
							},{
								header:"야근여부",
								dataIndex:"is_overwork",
								sortable:false,
								width:80,
								renderer:function(value) {
									if (value == "TRUE") {
										return "야근";
									} else {
										return "정상근무";
									}
								},
								editor:new Ext.form.ComboBox({
									typeAhead:true,
									triggerAction:"all",
									lazyRender:true,
									store:new Ext.data.SimpleStore({
										fields:["value","is_overwork"],
										data:[["TRUE","야근"],["FALSE","정상근무"]]
									}),
									editable:false,
									mode:"local",
									displayField:"is_overwork",
									valueField:"value"
								})
							},{
								header:"작업 및 검토사항",
								dataIndex:"work",
								sortable:false,
								width:250,
								editor:new Ext.form.TextField({selectOnFocus:true})
							},
							new Ext.grid.CheckboxSelectionModel()
						]),
						sm:new Ext.grid.CheckboxSelectionModel(),
						clicksToEdit:1,
						trackMouseOver:true,
						stripeRows:true,
						store:new Ext.data.Store({
							proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php"}),
							reader:new Ext.data.JsonReader({
								root:"lists",
								totalProperty:"totalCount",
								fields:[{name:"idx",type:"int"},"name","jumin",{name:"payment",type:"int"},{name:"monthly_payment",type:"int"},"intime","outtime","is_overwork","work"]
							}),
							remoteSort:false,
							sortInfo:{field:"name",direction:"ASC"},
							baseParams:{"wno":"<?php echo $this->wno; ?>","action":"attend","get":"work_dayworker","date":"<?php echo Request('iErpDate','cookie') != null ? Request('iErpDate','cookie') : GetTime('Y-m-d'); ?>"}
						})
					})
				],
				listeners:{tabchange:{fn:function(tabs,tab) {
					if (tab.getId() == "WorkList") {
						Ext.getCmp("InsertButton").setText("작업등록 및 수정");
					} else if (tab.getId() == "OutsourcingList") {
						Ext.getCmp("InsertButton").setText("외주노무 작업등록");
					} else {
						Ext.getCmp("InsertButton").setText("일용직노무 작업등록");
					}
				}}}
			})
		]
	});

	Ext.getCmp("WorkList").getStore().load();
	Ext.getCmp("OutsourcingList").getStore().load();
	Ext.getCmp("DayworkerList").getStore().load();
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>