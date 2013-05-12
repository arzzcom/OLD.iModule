<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"노무관리",
		layout:"fit",
		tbar:[
			new Ext.Button({
				icon:"<?php echo $this->moduleDir; ?>/images/common/icon_control_left.png",
				text:"이전일",
				handler:function() {
					var today = new Date(Ext.getCmp("today").getValue()).add("d",-1).format("Y-m-d");
					Ext.getCmp("today").setValue(today);

					SetCookie("iErpDate",today);

					Ext.getCmp("AttendList").getStore().baseParams.date = today;
					Ext.getCmp("AttendList").getStore().load();
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

					Ext.getCmp("AttendList").getStore().baseParams.date = today;
					Ext.getCmp("AttendList").getStore().load();
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

					Ext.getCmp("AttendList").getStore().baseParams.date = today;
					Ext.getCmp("AttendList").getStore().load();
				}
			}),
			'-',
			new Ext.Button({
				id:"InsertButton",
				text:"작업등록 및 수정",
				icon:"<?php echo $this->moduleDir; ?>/images/common/icon_report_edit.png",
				handler:function() {
					new Ext.Window({
						id:"AddWindow",
						title:"일용직노무 작업등록",
						width:420,
						height:425,
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
								items:[
									new Ext.form.FieldSet({
										title:"일용직근로자선택 및 작업등록",
										style:"margin:10px;",
										autoWidth:true,
										items:[
											new Ext.form.ComboBox({
												hiddenName:"dno",
												fieldLabel:"근로자명",
												typeAhead:true,
												triggerAction:"all",
												lazyRender:true,
												store:new Ext.data.Store({
													proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Workspace.get.php"}),
													reader:new Ext.data.JsonReader({
														root:"lists",
														totalProperty:"totalCount",
														fields:["idx","name","type","payment","job"]
													}),
													remoteSort:false,
													sortInfo:{field:"name",direction:"ASC"},
													baseParams:{"action":"work","get":"dayworker","mode":"worker","wno":<?php echo $this->wno; ?>,"date":new Date(Ext.getCmp("today").getValue()).format("Y-m-d")},
												}),
												width:260,
												editable:false,
												mode:"local",
												displayField:"name",
												valueField:"idx",
												allowBlank:false,
												emptyText:"근로자를 선택하여 주십시오.",
												listeners:{
													render:{fn:function(form) {
														form.getStore().load();
													}},
													select:{fn:function(form,record,idx) {
														Ext.getCmp("AddForm").getForm().findField("payment").setValue(record.data.payment);
														if (record.data.type == "개인") {
															Ext.getCmp("AddForm").getForm().findField("job").setValue(record.data.job);
															Ext.getCmp("AddForm").getForm().findField("job").disable();
															Ext.getCmp("AddForm").getForm().findField("worker").setValue(1);
															Ext.getCmp("AddForm").getForm().findField("worker").disable();
														} else {
															Ext.getCmp("AddForm").getForm().findField("worker").setValue(0);
															Ext.getCmp("AddForm").getForm().findField("job").enable();
															Ext.getCmp("AddForm").getForm().findField("worker").enable();
														}
													}}
												}
											}),
											new Ext.form.ComboBox({
												fieldLabel:"직공",
												hiddenName:"job",
												width:100,
												typeAhead:true,
												triggerAction:"all",
												lazyRender:true,
												listClass:'x-combo-list-small',
												store:JobSimpleStore,
												editable:false,
												mode:"local",
												value:"일반공",
												allowBlank:false,
												displayField:"job",
												valueField:"job"
											}),
											new Ext.form.TextField({
												fieldLabel:"인원",
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
												fieldLabel:"작업 및 적요",
												name:"work",
												width:260,
												allowBlank:false,
												emptyText:"작업 및 검토사항을 입력하세요."
											}),
											new Ext.form.ComboBox({
												fieldLabel:"공종그룹",
												hiddenName:"gno",
												typeAhead:true,
												triggerAction:"all",
												lazyRender:true,
												store:new Ext.data.Store({
													proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php"}),
													reader:new Ext.data.JsonReader({
														root:"lists",
														totalProperty:"totalCount",
														fields:["idx","workgroup","sort"]
													}),
													remoteSort:false,
													sortInfo:{field:"sort",direction:"ASC"},
													baseParams:{"action":"workspace","get":"workgroup","wno":"<?php echo $this->wno; ?>","is_all":"false"},
												}),
												width:260,
												editable:false,
												allowBlank:false,
												mode:"local",
												displayField:"workgroup",
												valueField:"idx",
												emptyText:"공종그룹을 선택하세요.",
												listeners:{
													render:{fn:function(form) {
														form.getStore().load();
													}},
													select:{fn:function(form) {
														if (form.getValue() == "") {
															Ext.getCmp("AddForm").getForm().findField("tno").disable();
														} else {
															Ext.getCmp("AddForm").getForm().findField("tno").enable();
															Ext.getCmp("AddForm").getForm().findField("tno").getStore().baseParams.gno = form.getValue();
															Ext.getCmp("AddForm").getForm().findField("tno").getStore().load();
														}
													}}
												}
											}),
											new Ext.form.ComboBox({
												fieldLabel:"공종명",
												hiddenName:"tno",
												typeAhead:true,
												triggerAction:"all",
												lazyRender:true,
												disabled:true,
												store:new Ext.data.Store({
													proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php"}),
													reader:new Ext.data.JsonReader({
														root:"lists",
														totalProperty:"totalCount",
														fields:["idx","worktype","sort"]
													}),
													remoteSort:false,
													sortInfo:{field:"sort",direction:"ASC"},
													baseParams:{"action":"workspace","get":"worktype","wno":"<?php echo $this->wno; ?>","is_all":"false","gno":""},
												}),
												width:260,
												editable:false,
												allowBlank:false,
												mode:"local",
												displayField:"worktype",
												valueField:"idx",
												emptyText:"공종명을 선택하세요."
											})
										]
									}),
									new Ext.form.FieldSet({
										title:"세부작업내역",
										style:"margin:10px;",
										autoWidth:true,
										items:[
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
										]
									})
								],
								listeners:{actioncomplete:{fn:function(form,action) {
									if (action.type == "submit") {
										Ext.getCmp("AttendList").getStore().reload();
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
									Ext.getCmp("AddForm").getForm().submit({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php?action=work&do=dayworker&mode=add&date="+Ext.getCmp("today").getValue().format("Y-m-d")+"&wno=<?php echo $this->wno; ?>",waitMsg:"일용직노무 작업을 추가중입니다."});
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
			}),
			new Ext.Button({
				text:"작업삭제",
				icon:"<?php echo $this->moduleDir; ?>/images/common/icon_report_delete.png",
				handler:function() {
					Ext.Msg.show({title:"안내",msg:"정말 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
						if (button == "ok") {
							var checked = Ext.getCmp("AttendList").selModel.getSelections();
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
									Ext.getCmp("AttendList").getStore().reload();
								},
								failure:function() {
									Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 삭제하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
								},
								headers:{},
								params:{"action":"work","do":"dayworker","mode":"delete","wno":"<?php echo $this->wno; ?>","idx":idx}
							});
						}
					}});
				}
			}),
			new Ext.Button({
				text:"변경사항 저장하기",
				icon:"<?php echo $this->moduleDir; ?>/images/common/icon_report_disk.png",
				handler:function() {
					var data = GetGridData(Ext.getCmp("AttendList"));

					Ext.Msg.wait("처리중입니다.","Please Wait...");
					Ext.Ajax.request({
						url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php",
						success:function() {
							Ext.Msg.show({title:"안내",msg:"작업일보가 성공적으로 저장되었습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO,animEl:"SaveButton"});
							Ext.getCmp("AttendList").getStore().commitChanges();
						},
						failure:function() {
							Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 저장하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
						},
						headers:{},
						params:{"action":"work","do":"dayworker","mode":"modify","wno":"<?php echo $this->wno; ?>","data":data}
					});
				}
			}),
			'-',
			new Ext.Button({
				text:"전일작업 불러오기",
				icon:"<?php echo $this->moduleDir; ?>/images/common/icon_table_relationship.png",
				handler:function() {
					Ext.Msg.show({title:"안내",msg:"전일작업을 불러오면, 금일 작성된 작업일보는 초기화 됩니다.<br />전일작업을 불러오시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
						if (button == "ok") {
							var dotype = "dayworker";
							
							Ext.Msg.wait("처리중입니다.","Please Wait...");
							Ext.Ajax.request({
								url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php",
								success:function() {
									Ext.Msg.show({title:"안내",msg:"전일작업을 성공적으로 로딩하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
									Ext.getCmp("ListTab").getActiveTab().getStore().reload();
								},
								failure:function() {
									Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 저장하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
								},
								headers:{},
								params:{"action":"work","do":dotype,"mode":"load","wno":"<?php echo $this->wno; ?>","date":new Date(Ext.getCmp("today").getValue()).format("Y-m-d")}
							});
						}
					}});
				}
			})
		],
		items:[
			new Ext.grid.EditorGridPanel({
				border:false,
				id:"AttendList",
				cm:new Ext.grid.ColumnModel([
					new Ext.grid.RowNumberer(),
					{
						dataIndex:"idx",
						hidden:true,
						hideable:false
					},{
						header:"공종그룹",
						dataIndex:"gno",
						width:80,
						renderer:function(value,p,record,row,col,store) {
							return GridWorkgroup(value,p,record,Ext.getCmp("AttendList").getColumnModel().getCellEditor(col,row).field);
						}
					},{
						header:"공종명",
						dataIndex:"tno",
						width:100,
						renderer:function(value,p,record,row,col,store) {
							return GridWorktype(value,p,record,Ext.getCmp("AttendList").getColumnModel().getCellEditor(col,row).field);
						}
					},{
						header:"품명",
						dataIndex:"name",
						width:180
					},{
						header:"규격",
						dataIndex:"job",
						width:100
					},{
						header:"적요",
						dataIndex:"work",
						sortable:false,
						width:200,
						editor:new Ext.form.TextField({selectOnFocus:true})
					},{
						header:"비목",
						sortable:false,
						width:40,
						renderer:function() {
							return "노무";
						}
					},{
						header:"지불처",
						dataIndex:"name",
						width:100
					},{
						header:"수량",
						dataIndex:"worker",
						width:60,
						renderer:GridNumberFormat,
						editor:new Ext.form.NumberField({selectOnFocus:true})
					},{
						header:"단위",
						sortable:false,
						width:40,
						renderer:function() {
							return "인";
						}
					},{
						header:"단가",
						sortable:false,
						locked:true,
						width:80,
						renderer:function(value,p,record) {
							return GridNumberFormat(record.data.payment/record.data.worker);
						}
					},{
						header:"금액",
						dataIndex:"payment",
						width:90,
						renderer:GridNumberFormat,
						editor:new Ext.form.NumberField({selectOnFocus:true})
					},{
						header:"지불여부",
						dataIndex:"payment",
						width:80,
						sortable:true,
						renderer:function(value) {
							return "미불";
						}
					},{
						hidden:true,
						header:"금월노임",
						dataIndex:"monthly_payment",
						width:80,
						renderer:function(value,p,record) {
							return GridNumberFormat(value+record.data.payment);
						}
					},{
						hidden:true,
						header:"주민(사업자)번호",
						dataIndex:"jumin",
						width:120
					},{
						hidden:true,
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
						hidden:true,
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
						hidden:true,
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
					},
					new Ext.ux.grid.CheckboxSelectionModel()
				]),
				sm:new Ext.ux.grid.CheckboxSelectionModel(),
				trackMouseOver:true,
				stripeRows:true,
				store:new Ext.data.Store({
					proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php"}),
					reader:new Ext.data.JsonReader({
						root:"lists",
						totalProperty:"totalCount",
						fields:[{name:"idx",type:"int"},"name","jumin",{name:"payment",type:"int"},{name:"monthly_payment",type:"int"},"intime","outtime","is_overwork","gno","tno","workgroup","worktype","work"]
					}),
					remoteSort:false,
					sortInfo:{field:"name",direction:"ASC"},
					baseParams:{"wno":"<?php echo $this->wno; ?>","action":"work","get":"dayworker","mode":"list","date":"<?php echo Request('iErpDate','cookie') != null ? Request('iErpDate','cookie') : GetTime('Y-m-d'); ?>"}
				}),
				listeners:{
					render:{fn:function() {
						GridEditorWorkgroupType(Ext.getCmp("AttendList"),<?php echo $this->wno; ?>);
					}},
					beforeedit:{fn:function(object) {
						GridEditorBeforeWorkgroupType(object);
					}},
					afteredit:{fn:function(object) {
						GridEditorAfterWorkgroupType(object);
					}}
				}
			})
		]
	});

	Ext.getCmp("AttendList").getStore().load();
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>