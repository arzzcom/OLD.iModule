<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	var MonthListStore = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:["date","display"]
		}),
		remoteSort:false,
		sortInfo:{field:"date",direction:"ASC"},
		baseParams:{"action":"month"}
	});

	// 현장 발주요청서 보기
	function OrderFunction(idx,wno) {
		if (Ext.getCmp("OrderWindow")) return;

		new Ext.Window({
			id:"OrderWindow",
			title:"발주요청서 보기",
			width:980,
			height:550,
			modal:true,
			maximizable:true,
			layout:"border",
			tbar:[
				new Ext.Button({
					id:"OrderCost1",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_checkbox.png",
					text:"재료비",
					enableToggle:true,
					listeners:{toggle:{fn:function(button,pressed) {
						if (pressed == true) {
							button.setIcon("<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_checkbox_on.png");
						} else {
							button.setIcon("<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_checkbox.png");
						}
						Ext.getCmp("OrderListPanel").getStore().sort("sort","ASC");
					}}}
				}),
				' ',
				new Ext.Button({
					id:"OrderCost2",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_checkbox.png",
					text:"노무비",
					enableToggle:true,
					listeners:{toggle:{fn:function(button,pressed) {
						if (pressed == true) {
							button.setIcon("<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_checkbox_on.png");
						} else {
							button.setIcon("<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_checkbox.png");
						}
						Ext.getCmp("OrderListPanel").getStore().sort("sort","ASC");
					}}}
				}),
				' ',
				new Ext.Button({
					id:"OrderCost3",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_checkbox.png",
					text:"경비",
					enableToggle:true,
					listeners:{toggle:{fn:function(button,pressed) {
						if (pressed == true) {
							button.setIcon("<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_checkbox_on.png");
						} else {
							button.setIcon("<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_checkbox.png");
						}
						Ext.getCmp("OrderListPanel").getStore().sort("sort","ASC");
					}}}
				}),
				'-',
				new Ext.SplitButton({
					id:"OrderItemAdd",
					text:"품목추가",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_row_insert.png",
					menu:[{
						text:"도급내역에서 추가하기",
						icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_row_insert.png",
						handler:function() {
							new Ext.Window({
								id:"OrderItemAddWindow",
								title:"도급내역에서 추가하기",
								width:950,
								height:400,
								modal:true,
								layout:"fit",
								items:[
									GridContractSearchList("workspace",wno,"fit")
								],
								buttons:[
									new Ext.Button({
										text:"선택항목추가하기",
										icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_tick.png",
										handler:function() {
											var checked = Ext.getCmp("ContractSearchList").selModel.getSelections();

											if (checked.length == 0) {
												Ext.Msg.show({title:"에러",msg:"추가할 항목을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
												return false;
											}

											Ext.getCmp("OrderListPanel").stopEditing();
											var insert = new Array();
											for (var i=0, loop=checked.length;i<loop;i++) {
												var data = checked[i];
												if (Ext.getCmp("OrderListPanel").getStore().find("code",data.get("code"),false,false) == -1) {
													insert.push({"is_new":"TRUE","group":" ","workgroup":data.get("workgroup"),"worktype":data.get("worktype"),"gno":data.get("gno"),"tno":data.get("tno"),"code":data.get("code"),"itemcode":data.get("itemcode"),"title":data.get("title"),"size":data.get("size"),"unit":data.get("unit"),"ea":data.get("ea"),"cost1":data.get("cost1"),"cost2":data.get("cost2"),"cost3":data.get("cost3"),"order_ea":data.get("order_ea")});
												}
											}
											GridInsertRow(Ext.getCmp("OrderListPanel"),insert);
											Ext.getCmp("OrderListPanel").startEditing(0,0);
											Ext.getCmp("OrderItemAddWindow").close();
										}
									}),
									new Ext.Button({
										text:"취소",
										icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
										handler:function() {
											Ext.getCmp("OrderItemAddWindow").close();
										}
									})
								]
							}).show();
						}
					}],
					handler:function() {
						GridInsertRow(Ext.getCmp("OrderListPanel"),{"is_new":"TRUE","group":" ","order_ea":"0,0,0,0"});
					}
				}),
				new Ext.Button({
					id:"OrderItemDelete",
					text:"품목삭제",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_row_delete.png",
					handler:function() {
						GridDeleteRow(Ext.getCmp("OrderListPanel"));
					}
				}),
				'-',
				new Ext.Button({
					id:"OrderFile",
					text:"첨부파일",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_disk.png",
					menu:[]
				}),
				'-',
				new Ext.Button({
					id:"OrderStatus",
					text:"본사확인",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_lock_open.png",
					handler:function(button) {
						if (Ext.getCmp("OrderForm").getForm().findField("status").getValue() == "COMPLETE") {
							Ext.Msg.show({title:"에러",msg:"발주계약이 실행중이므로 본사확인을 취소할 수 없습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
						} else {
							Ext.Msg.show({title:"안내",msg:(button.pressed == true ? "본사확인처리를 하시겠습니까?<br />본사확인처리 후에는 현장 및 본사에서 발주요청서를 수정할 수 없습니다." : "본사확인처리를 취소하시겠습니까?<br />본사확인처리 취소시에 해당 발주요청건에 대한 품의내역, 계약내역이 초기화됩니다."),buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.QUESTION,fn:function(btn) {
								if (btn == "ok") {
									Ext.Msg.wait("처리중입니다.","Please Wait...");
									Ext.Ajax.request({
										url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php",
										success:function(XML) {
											var status = AjaxResult(XML,"status");
											if (status != "COMPLETE") {
												Ext.Msg.show({title:"안내",msg:"성공적으로 처리하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
												Ext.getCmp("OrderForm").getForm().findField("status").setValue(status);
												if (status != "NEW") {
													Ext.getCmp("OrderStatus").toggle(true);
													Ext.getCmp("OrderCost1").disable();
													Ext.getCmp("OrderCost2").disable();
													Ext.getCmp("OrderCost3").disable();
													Ext.getCmp("OrderItemAdd").disable();
													Ext.getCmp("OrderItemDelete").disable();
												} else {
													Ext.getCmp("OrderStatus").toggle(false);
													Ext.getCmp("OrderCost1").enable();
													Ext.getCmp("OrderCost2").enable();
													Ext.getCmp("OrderCost3").enable();
													Ext.getCmp("OrderItemAdd").enable();
													Ext.getCmp("OrderItemDelete").enable();
												}
												Ext.getCmp("ListTab1").getStore().reload();
												Ext.getCmp("ListTab2").getStore().reload();
												Ext.getCmp("ListTab3").getStore().reload();
											} else {
												Ext.Msg.show({title:"에러",msg:"발주계약이 실행중이므로 본사확인을 취소할 수 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
											}
										},
										failure:function() {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
										},
										headers:{},
										params:{"action":"order","do":"order","mode":"confirm","idx":idx}
									});
								}
							}});
						}
					},
					listeners:{toggle:{fn:function(button,pressed) {
						if (pressed == true) {
							button.setIcon("<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_lock.png");
						} else {
							button.setIcon("<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_lock_open.png");
						}
					}}}
				})
			],
			items:[
				new Ext.grid.EditorGridPanel({
					title:"품목보기",
					split:true,
					region:"center",
					id:"OrderListPanel",
					margins:"5 5 0 5",
					border:true,
					cm:new Ext.grid.ColumnModel([
						new Ext.grid.RowNumberer(),
						{
							dataIndex:"group",
							hideable:false
						},{
							header:"공종그룹",
							dataIndex:"gno",
							width:80,
							renderer:function(value,p,record,row,col,store) {
								return GridWorkgroup(value,p,record,Ext.getCmp("OrderListPanel").getColumnModel().getCellEditor(col,row).field);
							}
						},{
							header:"공종명",
							dataIndex:"tno",
							width:120,
							renderer:function(value,p,record,row,col,store) {
								return GridWorktype(value,p,record,Ext.getCmp("OrderListPanel").getColumnModel().getCellEditor(col,row).field);
							}
						},{
							header:"품명",
							dataIndex:"title",
							width:280,
							renderer:GridContractItemNotFound
						},{
							header:"규격",
							dataIndex:"size",
							width:100,
							renderer:GridContractItemNotFound,
							editor:new Ext.form.TextField({selectOnFocus:true})
						},{
							header:"단위",
							dataIndex:"unit",
							width:70,
							renderer:GridContractItemNotFound,
							editor:new Ext.form.TextField({selectOnFocus:true})
						},{
							header:"계약수량",
							dataIndex:"contract_ea",
							width:65,
							renderer:GridNumberFormat
						},{
							header:"요청수량",
							dataIndex:"ea",
							width:65,
							renderer:GridItemOrderEA,
							editor:new Ext.form.NumberField({selectOnFocus:true})
						},{
							header:"합계",
							dataIndex:"total",
							width:100,
							sortable:false,
							summaryType:"sum",
							renderer:function(value,p,record) {
								var cost = 0;
								if (Ext.getCmp("OrderCost1").pressed == true) cost+= record.data.cost1;
								if (Ext.getCmp("OrderCost2").pressed == true) cost+= record.data.cost2;
								if (Ext.getCmp("OrderCost3").pressed == true) cost+= record.data.cost3;

								record.data.total = Math.floor(cost*record.data.ea);
								return GridNumberFormat(record.data.total);
							},
							summaryRenderer:GridNumberFormat
						},{
							dataIndex:"sort",
							hidden:true,
							hideable:false
						},
						new Ext.ux.grid.CheckboxSelectionModel()
					]),
					store:new Ext.data.GroupingStore({
						proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
						reader:new Ext.data.JsonReader({
							root:"lists",
							totalProperty:"totalCount",
							fields:["is_new","group","itemcode","code","gno","tno","workgroup","worktype","title","size","unit",{name:"contract_ea",type:"float"},"order_ea",{name:"ea",type:"float"},{name:"cost1",type:"int"},{name:"cost2",type:"int"},{name:"cost3",type:"int"},{name:"sort",type:"int"}]
						}),
						remoteSort:false,
						groupField:"group",
						sortInfo:{field:"sort",direction:"ASC"},
						baseParams:{"action":"order","get":"order","mode":"item","idx":idx}
					}),
					sm:new Ext.ux.grid.CheckboxSelectionModel(),
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
						render:{fn:function() {
							GridEditorAutoMatchItem(Ext.getCmp("OrderListPanel"),wno);
							GridEditorWorkgroupType(Ext.getCmp("OrderListPanel"),wno);
						}},
						beforeedit:{fn:function(object) {
							if (Ext.getCmp("OrderForm").getForm().findField("status").getValue() != "NEW") return false;
							GridEditorBeforeWorkgroupType(object);
						}},
						afteredit:{fn:function(object) {
							GridAutoMatchItem(object,wno);
							GridEditorAfterWorkgroupType(object)

							if (object.field == "ea" && object.value == "") {
								object.grid.getStore().getAt(object.row).set(object.field,0);
							}
						}}
					}
				}),
				new Ext.form.FormPanel({
					id:"OrderForm",
					title:"비고",
					region:"south",
					split:true,
					margins:"0 5 5 5",
					height:90,
					defaults:{hideLabel:true},
					errorReader:new Ext.form.XmlErrorReader(),
					reader:new Ext.data.XmlReader(
						{record:"form",success:"@success",errormsg:"@errormsg"},
						["etc","file","status","title","type"]
					),
					items:[
						new Ext.form.Hidden({
							name:"title"
						}),
						new Ext.form.Hidden({
							name:"type"
						}),
						new Ext.form.Hidden({
							name:"data"
						}),
						new Ext.form.Hidden({
							name:"status"
						}),
						new Ext.form.Hidden({
							name:"file"
						}),
						new Ext.form.TextArea({
							name:"etc",
							style:"margin:5px;"
						})
					],
					listeners:{
						resize:{fn:function() {
							Ext.getCmp("OrderForm").getForm().findField("etc").setWidth(Ext.getCmp("OrderForm").getInnerWidth()-12);
							Ext.getCmp("OrderForm").getForm().findField("etc").setHeight(Ext.getCmp("OrderForm").getInnerHeight()-12);
						}},
						actioncomplete:{fn:function(form,action) {
							if (action.type == "load") {
								Ext.getCmp("OrderWindow").setTitle(form.findField("title").getValue());
								Ext.getCmp("OrderStatus").toggle(form.findField("status").getValue() != "NEW");
								var temp = form.findField("type").getValue().split(",");
								if (temp[0] == "TRUE") Ext.getCmp("OrderCost1").toggle(true);
								if (temp[1] == "TRUE") Ext.getCmp("OrderCost2").toggle(true);
								if (temp[2] == "TRUE") Ext.getCmp("OrderCost3").toggle(true);

								var fileMenu = new Ext.menu.Menu();
								if (form.findField("file").getValue() != "") {
									var temp = form.findField("file").getValue().split("|");
									fileMenu.add({
										text:"첨부파일 다운로드 ("+temp[1]+", "+GetFileSize(temp[2])+")",
										icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_drive_go.png",
										handler:function() {
											downloadFrame.location.href = "<?php echo $_ENV['dir']; ?>/module/erp/exec/Workspace.do.php?action=order&do=file&mode=download&step=order&idx="+idx;
										}
									});
								} else {
									fileMenu.add({
										text:"첨부파일이 없습니다.",
										icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_drive_go.png"
									});
								}
								Ext.getCmp("OrderFile").menu = fileMenu;

								if (form.findField("status").getValue() != "NEW") {
									Ext.getCmp("OrderStatus").toggle(true);
									Ext.getCmp("OrderCost1").disable();
									Ext.getCmp("OrderCost2").disable();
									Ext.getCmp("OrderCost3").disable();
									Ext.getCmp("OrderItemAdd").disable();
									Ext.getCmp("OrderItemDelete").disable();
								} else {
									Ext.getCmp("OrderStatus").toggle(false);
									Ext.getCmp("OrderCost1").enable();
									Ext.getCmp("OrderCost2").enable();
									Ext.getCmp("OrderCost3").enable();
									Ext.getCmp("OrderItemAdd").enable();
									Ext.getCmp("OrderItemDelete").enable();
								}
							}

							if (action.type == "submit") {
								var value = FormSubmitReturnValue(action);
								if (value) {
									var temp = value.split(",");
									if (parseInt(temp[1]) > 0) {
										var msg = "중복되는 품목 <b>"+temp[1]+"</b>개를 제외하였습니다.<br />발주요청서를 확인하여 주십시오.";
									} else {
										var msg = "성공적으로 수정하였습니다.";
									}

									Ext.Msg.show({title:"안내",msg:msg,buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
									Ext.getCmp("OrderListPanel").getStore().reload();
								}
							}
						}}
					}
				})
			],
			buttons:[
				new Ext.Button({
					text:"본사품의서작성",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_edit.png",
					hidden:(Ext.getCmp("ConsultWindow") || Ext.getCmp("ContractWindow") ? true : false),
					handler:function() {
						ConsultSelectFunction(idx,wno);
					}
				}),
				new Ext.Button({
					text:"엑셀파일로 변환",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_page_white_excel.png",
					handler:function() {
						ExcelConvert("<?php echo $_ENV['dir']; ?>/module/erp/exec/GetExcel.do.php?action=workspace&get=order&idx="+idx);
					}
				}),
				new Ext.Button({
					text:"수정",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_tick.png",
					hidden:(Ext.getCmp("ConsultWindow") || Ext.getCmp("ContractWindow") ? true : false),
					handler:function() {
						if (Ext.getCmp("OrderForm").getForm().findField("status").getValue() == "NEW") {
							if (Ext.getCmp("OrderListPanel").getStore().getCount() == 0) {
								Ext.Msg.show({title:"에러",msg:"발주요청할 품목을 입력하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
								return false;
							}

							for (var i=0, loop=Ext.getCmp("OrderListPanel").getStore().getCount();i<loop;i++) {
								var data = Ext.getCmp("OrderListPanel").getStore().getAt(i);
								if (!data.get("title") || !data.get("gno") || !data.get("tno")) {
									Ext.Msg.show({title:"에러",msg:"공종그룹, 공종명 및 품명을 입력하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
									return false;
								}
							}

							var type = "";
							if (Ext.getCmp("OrderCost1").pressed == true) type+= "TRUE";
							else type+= "FALSE";
							if (Ext.getCmp("OrderCost2").pressed == true) type+= ",TRUE";
							else type+= ",FALSE";
							if (Ext.getCmp("OrderCost3").pressed == true) type+= ",TRUE";
							else type+= ",FALSE";

							if (type == "FALSE,FALSE,FALSE") {
								Ext.Msg.show({title:"에러",msg:"재료비, 노무비, 경비 중 하나이상을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
								return false;
							} else {
								Ext.getCmp("OrderForm").getForm().findField("type").setValue(type);
							}

							Ext.getCmp("OrderForm").getForm().findField("data").setValue(GetGridData(Ext.getCmp("OrderListPanel")));
							Ext.getCmp("OrderForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php?action=order&do=order&mode=modify&idx="+idx,waitMsg:"데이터를 수정중입니다.",submitEmptyText:false});
						} else {
							Ext.Msg.show({title:"안내",msg:"본사확인처리된 발주요청건은 수정할 수 없습니다.<br />수정하시려면 먼저 본사확인을 취소하여 주시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
						}
					}
				}),
				new Ext.Button({
					text:"닫기",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
					handler:function() {
						Ext.getCmp("OrderWindow").close();
					}
				})
			],
			listeners:{show:{fn:function() {
				Ext.getCmp("OrderForm").getForm().load({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php?action=order&get=order&mode=data&idx="+idx,waitMsg:"데이터를 로딩중입니다."});
				Ext.getCmp("OrderListPanel").getStore().load();
			}}}
		}).show();
	}

	// 본사품의서 작성 선택
	function ConsultSelectFunction(idx,wno) {
		if (Ext.getCmp("ConsultSelectWindow")) return false;

		new Ext.Window({
			id:"ConsultSelectWindow",
			title:"본사품의서작성",
			modal:true,
			width:400,
			height:(!wno ? 160 : 135),
			layout:"fit",
			items:[
				new Ext.form.FormPanel({
					border:false,
					style:"padding:10px; background:#FFFFFF;",
					labelAlign:"right",
					labelWidth:80,
					autoWidth:true,
					errorReader:new Ext.form.XmlErrorReader(),
					items:[
						new Ext.form.TextField({
							fieldLabel:"품의서명",
							width:280,
							id:"ConsultSelectTitle",
							value:new Date().format("Y년 m월 d일")+" 품의서"
						}),
						new Ext.form.ComboBox({
							fieldLabel:"품의서선택",
							width:280,
							id:"ConsultSelectType",
							typeAhead:true,
							triggerAction:"all",
							lazyRender:true,
							store:new Ext.data.SimpleStore({
								fields:["value","display"],
								data:[["EACH","개별항목 품의서"],["EQUAL","동일항목 품의서"],["SERIES","회차별 품의서"]]
							}),
							editable:false,
							allowBlank:false,
							mode:"local",
							displayField:"display",
							valueField:"value",
							emptyText:"품의서종류를 선택하여 주십시오."
						}),
						new Ext.form.ComboBox({
							fieldLabel:"현장선택",
							width:280,
							id:"ConsultWorkspace",
							typeAhead:true,
							triggerAction:"all",
							hidden:(wno ? true : false),
							lazyRender:true,
							store:new Ext.data.Store({
								proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
								reader:new Ext.data.JsonReader({
									root:"lists",
									totalProperty:"totalCount",
									fields:[{name:"idx",type:"int"},"title"]
								}),
								remoteSort:true,
								sortInfo:{field:"title",direction:"ASC"},
								baseParams:{"action":"workspace","get":"list","category":"working"}
							}),
							editable:false,
							allowBlank:false,
							mode:"local",
							displayField:"title",
							valueField:"idx",
							emptyText:"품의서를 작성할 현장을 선택하여 주십시오.",
							listeners:{render:{fn:function(form) {
								form.getStore().load();
							}}}
						})
					]
				})
			],
			buttons:[
				new Ext.Button({
					text:"확인",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_tick.png",
					handler:function() {
						if (!Ext.getCmp("ConsultSelectType").getValue()) {
							Ext.Msg.show({title:"에러",msg:"품의서종류를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
							return false;
						} else if (!Ext.getCmp("ConsultSelectTitle").getValue()) {
							Ext.Msg.show({title:"에러",msg:"품의서명를 입력하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
							return false;
						} else {
							if (!wno) {
								if (!Ext.getCmp("ConsultWorkspace").getValue()) {
									Ext.Msg.show({title:"에러",msg:"품의서를 작성할 현장을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
									return false;
								} else {
									wno = Ext.getCmp("ConsultWorkspace").getValue();
								}
							}
							ConsultFunction(Ext.getCmp("ConsultSelectType").getValue(),0,wno,idx,Ext.getCmp("ConsultSelectTitle").getValue());
						}
						Ext.getCmp("ConsultSelectWindow").close();
					}
				}),
				new Ext.Button({
					text:"취소",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
					handler:function() {
						Ext.getCmp("ConsultSelectWindow").close();
					}
				})
			]
		}).show();
	}

	// 본사품의서 작성
	function ConsultFunction(type,idx,wno,repto,title) {
		if (Ext.getCmp("ConsultWindow")) return false;

		var ContractStore = new Ext.data.GroupingStore({
			proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
			reader:new Ext.data.JsonReader({
				root:"lists",
				totalProperty:"totalCount",
				fields:[{name:"sort",type:"int"},"group","itemcode","code","gno","tno","workgroup","worktype","title","size","unit",{name:"ea",type:"float"},"order_ea",{name:"cost1",type:"int"},{name:"cost2",type:"int"},{name:"cost3",type:"int"},{name:"exec_cost1",type:"int"},{name:"exec_cost2",type:"int"},{name:"exec_cost3",type:"int"},{name:"cost",type:"int"},{name:"exec_cost",type:"int"},{name:"price",type:"int"},{name:"exec_price",type:"int"},"avgcost1","avgcost2","avgcost3"]
			}),
			remoteSort:false,
			groupField:"group",
			sortInfo:{field:"sort",direction:"ASC"},
			baseParams:{"action":"order","get":"consult","mode":"contract","idx":idx,"repto":repto}
		});
		ContractStore.load();

		// 도급내역
		var ContractPanel = new Ext.grid.EditorGridPanel({
			id:"ContractPanel",
			title:"도급내역",
			region:"west",
			width:300,
			split:true,
			layout:"fit",
			margins:"0 0 0 5",
			collapsible:true,
			tbar:[
				new Ext.Button({
					id:"ConsultCost1",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_checkbox.png",
					text:"재료비",
					enableToggle:true,
					listeners:{toggle:{fn:function(button,pressed) {
						if (pressed == true) {
							button.setIcon("<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_checkbox_on.png");
						} else {
							button.setIcon("<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_checkbox.png");
						}

						var tab = Ext.getCmp("ConsultPanel").getActiveTab();
						for (var i=0, loop=Ext.getCmp("ConsultPanel").store.getCount();i<loop;i++) {
							if (tab.getId() == Ext.getCmp("ConsultPanel").get(i).getId()) {
								var tno = i;
								break;
							}
						}
						Ext.getCmp("ConsultPanel").store.getAt(tno).set("cost1",(pressed == true ? "TRUE" : "FALSE"));
						tab.getColumnModel().setHidden(9,!pressed);

						Ext.getCmp("ContractPanel").getStore().sort("sort","ASC");
						tab.getStore().sort("sort","ASC");
					}}}
				}),
				' ',
				new Ext.Button({
					id:"ConsultCost2",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_checkbox.png",
					text:"노무비",
					enableToggle:true,
					listeners:{toggle:{fn:function(button,pressed) {
						if (pressed == true) {
							button.setIcon("<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_checkbox_on.png");
						} else {
							button.setIcon("<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_checkbox.png");
						}

						var tab = Ext.getCmp("ConsultPanel").getActiveTab();
						for (var i=0, loop=Ext.getCmp("ConsultPanel").store.getCount();i<loop;i++) {
							if (tab.getId() == Ext.getCmp("ConsultPanel").get(i).getId()) {
								var tno = i;
								break;
							}
						}
						Ext.getCmp("ConsultPanel").store.getAt(tno).set("cost2",(pressed == true ? "TRUE" : "FALSE"));
						tab.getColumnModel().setHidden(10,!pressed);

						Ext.getCmp("ContractPanel").getStore().sort("sort","ASC");
						tab.getStore().sort("sort","ASC");
					}}}
				}),
				' ',
				new Ext.Button({
					id:"ConsultCost3",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_checkbox.png",
					text:"경비",
					enableToggle:true,
					listeners:{toggle:{fn:function(button,pressed) {
						if (pressed == true) {
							button.setIcon("<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_checkbox_on.png");
						} else {
							button.setIcon("<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_checkbox.png");
						}

						var tab = Ext.getCmp("ConsultPanel").getActiveTab();
						for (var i=0, loop=Ext.getCmp("ConsultPanel").store.getCount();i<loop;i++) {
							if (tab.getId() == Ext.getCmp("ConsultPanel").get(i).getId()) {
								var tno = i;
								break;
							}
						}
						Ext.getCmp("ConsultPanel").store.getAt(tno).set("cost3",(pressed == true ? "TRUE" : "FALSE"));
						tab.getColumnModel().setHidden(11,!pressed);

						Ext.getCmp("ContractPanel").getStore().sort("sort","ASC");
						tab.getStore().sort("sort","ASC");
					}}}
				}),
				'-',
				new Ext.Button({
					text:"추가",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_row_insert.png",
					handler:function() {
						new Ext.Window({
							id:"ConsultItemAddWindow",
							title:"도급내역에서 추가하기",
							width:950,
							height:400,
							modal:true,
							layout:"fit",
							items:[
								GridContractSearchList("workspace",wno,"fit")
							],
							buttons:[
								new Ext.Button({
									text:"선택항목추가하기",
									icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_tick.png",
									handler:function() {
										var checked = Ext.getCmp("ContractSearchList").selModel.getSelections();

										if (checked.length == 0) {
											Ext.Msg.show({title:"에러",msg:"추가할 항목을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
											return false;
										}

										var insert = new Array();
										for (var i=0, loop=checked.length;i<loop;i++) {
											var data = checked[i];
											if (Ext.getCmp("ContractPanel").getStore().find("code",data.get("code"),false,false) == -1) {
												insert.push({"is_new":"TRUE","group":" ","gno":data.get("gno"),"tno":data.get("tno"),"workgroup":data.get("workgroup"),"worktype":data.get("worktype"),"code":data.get("code"),"itemcode":data.get("itemcode"),"title":data.get("title"),"size":data.get("size"),"unit":data.get("unit"),"ea":data.get("ea"),"cost1":data.get("cost1"),"cost2":data.get("cost2"),"cost3":data.get("cost3"),"order_ea":data.get("order_ea"),"avgcost1":data.get("avgcost1"),"avgcost2":data.get("avgcost2"),"avgcost3":data.get("avgcost3")});
											}
										}
										GridInsertRow(Ext.getCmp("ContractPanel"),insert);
										Ext.getCmp("ConsultItemAddWindow").close();
									}
								}),
								new Ext.Button({
									text:"취소",
									icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
									handler:function() {
										Ext.getCmp("ConsultItemAddWindow").close();
									}
								})
							]
						}).show();
					}
				}),
				new Ext.Button({
					text:"삭제",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_row_delete.png",
					handler:function() {
						var checked = Ext.getCmp("ContractPanel").getActiveTab().selModel.getSelections();

						if (checked.length == 0) {
							Ext.Msg.show({title:"삭제오류",msg:"삭제할 항목을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
						} else {
							for (var i=0, loop=checked.length;i<loop;i++) {
								Ext.getCmp("ContractPanel").getActiveTab().getStore().remove(checked[i]);
							}
						}
					}
				})
			],
			cm:new Ext.grid.ColumnModel([
				new Ext.ux.grid.CheckboxSelectionModel(),
				{
					dataIndex:"group",
					hidden:true,
					hideable:false
				},{
					dataIndex:"gno",
					hidden:true,
					hideable:false
				},{
					dataIndex:"tno",
					hidden:true,
					hideable:false
				},{
					header:"품명",
					dataIndex:"title",
					width:180,
					summaryType:"count",
					renderer:GridContractItemNotFound,
					summaryRenderer:GridSummaryCount
				},{
					header:"규격",
					dataIndex:"size",
					width:80,
					renderer:GridContractItemNotFound
				},{
					header:"단위",
					dataIndex:"unit",
					width:40,
					hidden:true,
					renderer:GridContractItemNotFound
				},{
					header:"수량",
					dataIndex:"ea",
					width:50,
					sortable:false,
					renderer:GridItemOrderEA,
					editor:new Ext.form.NumberField({selectOnFocus:true})
				},{
					header:"단가",
					dataIndex:"cost",
					width:80,
					summaryType:"sum",
					renderer:function(value,p,record) {
						var contract = 0;
						var exec = 0;

						if (Ext.getCmp("ConsultCost1").pressed == true) {
							contract+= record.data.cost1;
							exec+= record.data.exec_cost1;
						}
						if (Ext.getCmp("ConsultCost2").pressed == true) {
							contract+= record.data.cost2;
							exec+= record.data.exec_cost2;
						}
						if (Ext.getCmp("ConsultCost3").pressed == true) {
							contract+= record.data.cost3;
							exec+= record.data.exec_cost3;
						}

						record.data.cost = contract;
						record.data.exec_cost = exec;
						return GridNumberFormat(record.data.cost);
					},
					summaryRenderer:GridNumberFormat
				},{
					header:"금액",
					dataIndex:"price",
					width:90,
					summaryType:"sum",
					renderer:function(value,p,record) {
						record.data.price = record.data.cost * record.data.ea;
						record.data.exec_price = record.data.exec_cost * record.data.ea;
						return GridNumberFormat(record.data.price);
					},
					summaryRenderer:GridNumberFormat
				},{
					dataIndex:"sort",
					hidden:true,
					hideable:false
				}
			]),
			sm:new Ext.ux.grid.CheckboxSelectionModel(),
			trackMouseOver:true,
			store:ContractStore,
			plugins:new Ext.ux.grid.GroupSummary(),
			bbar:new Ext.ux.StatusBar({
				enableOverflow:false,
				items:[
					new Ext.Toolbar.TextItem({
						cls:"x-status-text-panel",
						style:"margin-right:2px; padding:3px 5px 0px 5px;",
						height:22,
						text:'도급대비:<span id="ConsultContractPercent">0.00%</span>'
					}),
					new Ext.Toolbar.TextItem({
						cls:"x-status-text-panel",
						height:22,
						style:"margin-right:2px; padding:3px 5px 0px 5px;",
						text:'실행대비:<span id="ConsultExecPercent">0.00%</span>'
					})
				]
			}),
			view:new Ext.grid.GroupingView({
				enableGroupingMenu:false,
				hideGroupedColumn:true,
				showGroupName:false,
				enableNoGroups:false,
				headersDisabled:false,
				showGroupHeader:false
			})
		});

		// 업체별 품의
		var ConsultTabPanel = new Ext.Panel({
			title:"업체별 품의서",
			region:"center",
			layout:"fit",
			margins:"0 5 0 0",
			items:[
				new Ext.TabPanel({
					id:"ConsultPanel",
					tabPosition:"bottom",
					activeTab:0,
					enableTabScroll:true,
					border:false,
					store:new Ext.data.Store({
						proxy:new Ext.data.ScriptTagProxy({url:ENV.dir+"/module/erp/exec/Admin.get.php"}),
						reader:new Ext.data.JsonReader({
							root:'lists',
							totalProperty:'totalCount',
							fields:["cno","wno","title","cost1","cost2","cost3"]
						}),
						remoteSort:false,
						sortInfo:{field:"title", direction:"ASC"},
						baseParams:{"action":"order","get":"consult","mode":"tab","idx":idx},
						listeners:{
							add:{fn:function(store,data,tno) {
								ConsultCreateTabPanel(type,idx,tno);
							}},
							beforeload:{fn:function(store) {
								if (store.getCount() == 0) return;
								Ext.getCmp("ConsultPanel").removeAll(true);
							}},
							load:{fn:function(store) {
								if (store.getCount() == 0) return;
								for (var i=0, loop=store.getCount();i<loop;i++) {
									ConsultCreateTabPanel(type,idx,i);
								}
							}},
							remove:{fn:function(store) {
								if (store.getCount() == 0) {
									Ext.getCmp("ConsultPanel").add(
										new Ext.Panel({
											id:"LoadingTab",
											title:"협력업체선택",
											html:'<div style="width:80%; margin:0 auto; margin-top:100px; border:1px solid #98C0F4; background:#DEEDFA; padding:10px; color:#15428B;" class="dotum f11 center">업체를 선택하세요.</div>'
										})
									);
								}
							}}
						}
					}),
					tbar:[
						new Ext.form.ComboBox({
							id:"ConsultCooperationList",
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
								baseParams:{"action":"cooperation","get":"list","mode":"simple"}
							}),
							width:150,
							editable:false,
							mode:"local",
							displayField:"title",
							valueField:"idx",
							emptyText:"협력업체선택",
							listeners:{render:{fn:function(form) {
								form.getStore().load();
							}}}
						}),
						' ',
						new Ext.Button({
							text:"품의서작성",
							icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_paste_plain.png",
							handler:function() {
								if (!Ext.getCmp("ConsultCooperationList").getValue()) {
									Ext.Msg.show({title:"에러",msg:"업체를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
									return false;
								}
								var store = Ext.getCmp("ConsultPanel").store;
								var record = Ext.data.Record.create(["cno","wno","title","cost1","cost2","cost3"]);

								if (type == "SERIES") {
									for (var i=0, loop=store.getCount();i<loop;i++) {
										if (store.getAt(i).get("cno") != Ext.getCmp("ConsultCooperationList").getValue()) {
											Ext.Msg.show({title:"에러",msg:"회차별 품의서는 동일업체만 선택가능합니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
											return false;
										}
									}
									store.add(new record({"cno":Ext.getCmp("ConsultCooperationList").getValue(),"wno":wno,"title":(store.getCount()+1)+"회("+Ext.getCmp("ConsultCooperationList").getRawValue()+")","cost1":"FALSE","cost2":"FALSE","cost3":"FALSE"}));
								} else {
									store.add(new record({"cno":Ext.getCmp("ConsultCooperationList").getValue(),"wno":wno,"title":Ext.getCmp("ConsultCooperationList").getRawValue(),"cost1":"FALSE","cost2":"FALSE","cost3":"FALSE"}));
								}
								Ext.getCmp("ConsultCooperationList").setValue("");
							}
						}),
						new Ext.Button({
							text:"협력업체등록",
							icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_building_link.png",
							handler:function() {
								CooperationFunction();
							}
						}),
						'-',
						new Ext.Button({
							id:"ConsultLoad",
							disabled:true,
							text:"도급내역불러오기",
							icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_arrow_right.png",
							handler:function() {
								var checked = Ext.getCmp("ContractPanel").selModel.getSelections();
								if (checked.length == 0) {
									Ext.Msg.show({title:"에러",msg:"도급내역에서 불러올 품목을 선택하여 주십시오.",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.WARNING});
								} else {
									var insert = new Array();
									for (var i=0, loop=checked.length;i<loop;i++) {
										var data = checked[i];
										if (Ext.getCmp("ConsultPanel").getActiveTab().getStore().find("code",data.get("code")) == -1) {
											insert.push({"is_new":"FALSE","group":" ","code":data.get("code"),"gno":data.get("gno"),"tno":data.get("tno"),"title":data.get("title"),"size":data.get("size"),"unit":data.get("unit"),"ea":data.get("ea"),"order_ea":data.get("order_ea"),"cost1":data.get("cost1"),"cost2":data.get("cost2"),"cost3":data.get("cost3"),"avgcost1":data.get("avgcost1"),"avgcost2":data.get("avgcost2"),"avgcost3":data.get("avgcost3"),"sort":data.get("sort")});
										}
									}

									GridInsertRow(Ext.getCmp("ConsultPanel").getActiveTab(),insert);
								}
							}
						}),
						'-',
						new Ext.SplitButton({
							id:"ConsultAdd",
							disabled:true,
							text:"품목추가",
							icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_row_insert.png",
							menu:[{
								text:"도급내역에서 추가하기",
								icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_row_insert.png",
								handler:function() {
									new Ext.Window({
										id:"ConsultItemAddWindow",
										title:"도급내역에서 추가하기",
										width:950,
										height:400,
										modal:true,
										layout:"fit",
										items:[
											GridContractSearchList("workspace",wno,"fit")
										],
										buttons:[
											new Ext.Button({
												text:"선택항목추가하기",
												icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_tick.png",
												handler:function() {
													var checked = Ext.getCmp("ContractSearchList").selModel.getSelections();

													if (checked.length == 0) {
														Ext.Msg.show({title:"에러",msg:"추가할 항목을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
														return false;
													}

													Ext.getCmp("ConsultPanel").getActiveTab().stopEditing();
													var insert = new Array();
													for (var i=0, loop=checked.length;i<loop;i++) {
														var data = checked[i];
														if (Ext.getCmp("ConsultPanel").getActiveTab().getStore().find("code",data.get("code"),false,false) == -1) {
															insert.push({"is_new":"TRUE","group":" ","gno":data.get("gno"),"tno":data.get("tno"),"workgroup":data.get("workgroup"),"worktype":data.get("worktype"),"code":data.get("code"),"itemcode":data.get("itemcode"),"title":data.get("title"),"size":data.get("size"),"unit":data.get("unit"),"ea":data.get("ea"),"cost1":data.get("cost1"),"cost2":data.get("cost2"),"cost3":data.get("cost3"),"order_ea":data.get("order_ea"),"avgcost1":data.get("avgcost1"),"avgcost2":data.get("avgcost2"),"avgcost3":data.get("avgcost3")});
														}
													}
													GridInsertRow(Ext.getCmp("ConsultPanel").getActiveTab(),insert);
													Ext.getCmp("ConsultPanel").getActiveTab().startEditing(0,0);
													Ext.getCmp("ConsultItemAddWindow").close();
												}
											}),
											new Ext.Button({
												text:"취소",
												icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
												handler:function() {
													Ext.getCmp("ConsultItemAddWindow").close();
												}
											})
										]
									}).show();
								}
							}],
							handler:function() {
								GridInsertRow(Ext.getCmp("ConsultPanel").getActiveTab(),{"is_new":"TRUE","group":" ","order_ea":"0,0,0,0"});
							}
						}),
						new Ext.Button({
							id:"ConsultDelete",
							disabled:true,
							text:"품목삭제",
							icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_row_delete.png",
							handler:function() {
								var checked = Ext.getCmp("ConsultPanel").getActiveTab().selModel.getSelections();

								if (checked.length == 0) {
									Ext.Msg.show({title:"삭제오류",msg:"삭제할 항목을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
								} else {
									for (var i=0, loop=checked.length;i<loop;i++) {
										Ext.getCmp("ConsultPanel").getActiveTab().getStore().remove(checked[i]);
									}
								}
							}
						})
					],
					items:[
						new Ext.Panel({
							id:"LoadingTab",
							title:"협력업체선택",
							html:'<div style="width:80%; margin:0 auto; margin-top:100px; border:1px solid #98C0F4; background:#DEEDFA; padding:10px; color:#15428B;" class="dotum f11 center">협력업체를 선택하세요.</div>'
						})
					],
					listeners:{
						render:{fn:function() {
							Ext.getCmp("ConsultPanel").store.load();
						}},
						tabchange:{fn:function(tabs,tab) {
							if (!tab) return;
							if (tab.getId() == "LoadingTab") {
								Ext.getCmp("ConsultCost1").disable();
								Ext.getCmp("ConsultCost2").disable();
								Ext.getCmp("ConsultCost3").disable();
								Ext.getCmp("ConsultLoad").disable();
								Ext.getCmp("ConsultAdd").disable();
								Ext.getCmp("ConsultDelete").disable();
							} else {
								Ext.getCmp("ConsultCost1").enable();
								Ext.getCmp("ConsultCost2").enable();
								Ext.getCmp("ConsultCost3").enable();
								Ext.getCmp("ConsultLoad").enable();
								Ext.getCmp("ConsultAdd").enable();
								Ext.getCmp("ConsultDelete").enable();

								for (var i=0, loop=Ext.getCmp("ConsultPanel").store.getCount();i<loop;i++) {
									if (tab.getId() == Ext.getCmp("ConsultPanel").get(i).getId()) {
										var tno = i;
										break;
									}
								}

								var tabData = Ext.getCmp("ConsultPanel").store.getAt(tno);

								if (tabData.get("cost1") == "TRUE") Ext.getCmp("ConsultCost1").toggle(true);
								else Ext.getCmp("ConsultCost1").toggle(false);

								if (tabData.get("cost2") == "TRUE") Ext.getCmp("ConsultCost2").toggle(true);
								else Ext.getCmp("ConsultCost2").toggle(false);

								if (tabData.get("cost3") == "TRUE") Ext.getCmp("ConsultCost3").toggle(true);
								else Ext.getCmp("ConsultCost3").toggle(false);
							}
						}}
					}
				})
			]
		});

		var EtcPanel = new Ext.Panel({
			region:"south",
			height:80,
			title:"비고",
			split:true,
			margins:"0 5 0 5",
			layout:"fit",
			items:[
				new Ext.form.FormPanel({
					id:"ConsultForm",
					border:false,
					errorReader:new Ext.form.XmlErrorReader(),
					reader:new Ext.data.XmlReader(
						{record:"form",success:"@success",errormsg:"@errormsg"},
						["title","repto","etc"]
					),
					items:[
						new Ext.form.Hidden({
							name:"title"
						}),
						new Ext.form.Hidden({
							name:"repto",
							value:repto
						}),
						new Ext.form.Hidden({
							name:"wno",
							value:wno
						}),
						new Ext.form.Hidden({
							name:"contract"
						}),
						new Ext.form.Hidden({
							name:"consult"
						}),
						new Ext.form.Hidden({
							name:"type",
							value:type
						}),
						new Ext.form.TextArea({
							name:"etc",
							hideLabel:true,
							style:"margin:5px;",
							width:944,
							height:43
						})
					],
					listeners:{
						render:{fn:function() {
							Ext.getCmp("ConsultForm").getForm().load({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php?action=order&get=consult&mode=etc&idx="+idx+"&repto="+repto,waitMsg:"정보를 로딩중입니다."});
						}},
						resize:{fn:function() {
							Ext.getCmp("ConsultForm").getForm().findField("etc").setWidth(Ext.getCmp("ConsultForm").getInnerWidth()-10);
							Ext.getCmp("ConsultForm").getForm().findField("etc").setHeight(Ext.getCmp("ConsultForm").getInnerHeight()-10);
						}},
						actioncomplete:{fn:function(form,action) {
							if (action.type == "load") {
								if (form.findField("title").getValue()) Ext.getCmp("ConsultWindow").setTitle(form.findField("title").getValue());
								else if (title) form.findField("title").setValue(title);
								if (form.findField("repto").getValue() != 0) Ext.getCmp("ConsultBtnOrder").show();
							}

							if (action.type == "submit") {
								var value = FormSubmitReturnValue(action);

								if (value) {
									var temp = value.split(",");

									var msg = "";
									for (var i=1, loop=temp.length;i<loop;i++) {
										if (parseInt(temp[i]) > 0) {
											msg+= "<br />"+Ext.getCmp("ConsultPanel").store.getAt(i-1).get("title")+" 품의서 : <b>"+temp[i]+"</b>개 항목";
										}
									}

									if (msg != "") {
										msg = "중복되는 품목이 있어 아래의 품의서에서 일부 품목이 제외되었습니다.<br />품의서를 확인하여 주시기 바랍니다.<br />"+msg;
									} else {
										msg = "성공적으로 저장하였습니다.";
									}
								}

								Ext.getCmp("ListTab1").getStore().reload();
								Ext.getCmp("ListTab2").getStore().reload();
								Ext.getCmp("ListTab").activate("ListTab2");

								if (!idx) Ext.getCmp("ConsultWindow").close();

								Ext.Msg.show({title:"안내",msg:msg,buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO,fn:function(button) {
									if (idx) Ext.getCmp("ConsultPanel").store.reload();
									else ConsultFunction(type,temp[0],wno);
								}});
							}
						}}
					}
				})
			]
		});

		new Ext.Window({
			id:"ConsultWindow",
			title:(idx != 0 ? "본사품의서 보기" : title+" 작성"),
			modal:true,
			width:980,
			height:550,
			maximizable:true,
			layout:"fit",
			items:[
				new Ext.Panel({
					border:false,
					layout:"border",
					style:"padding:5px 0px 5px 0px;",
					items:[
						ContractPanel,
						ConsultTabPanel,
						EtcPanel
					]
				})
			],
			buttons:[
				new Ext.Button({
					id:"ConsultBtnOrder",
					text:"현장발주요청서보기",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_edit.png",
					hidden:true,
					handler:function() {
						var repto = Ext.getCmp("ConsultForm").getForm().findField("repto").getValue();
						if (repto) OrderFunction(repto,wno);
					}
				}),
				new Ext.Button({
					text:"발주계약서작성",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_edit.png",
					hidden:(idx != 0 && !Ext.getCmp("ContractWindow") ? false : true),
					handler:function() {
						ContractSelectFunction(idx,wno);
					}
				}),
				new Ext.Button({
					text:"엑셀파일로 변환",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_page_white_excel.png",
					hidden:(idx != 0 ? false : true),
					handler:function() {
						new Ext.Window({
							id:"ExcelWindow",
							title:"엑셀파일로 변환",
							width:500,
							height:300,
							modal:true,
							layout:"fit",
							items:[
								new Ext.grid.GridPanel({
									border:false,
									id:"ExcelList",
									cm:new Ext.grid.ColumnModel([
										new Ext.grid.CheckboxSelectionModel(),
										{
											header:"업체명/회차",
											dataIndex:"title",
											width:310
										},{
											header:"금액",
											dataIndex:"price",
											width:120,
											renderer:GridNumberFormat
										}
									]),
									sm:new Ext.grid.CheckboxSelectionModel(),
									store:new Ext.data.Store({
										proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
										reader:new Ext.data.JsonReader({
											root:"lists",
											totalProperty:"totalCount",
											fields:[{name:"tno",type:"int"},"title",{name:"price",type:"int"}]
										}),
										remoteSort:false,
										sortInfo:{field:"tno",direction:"ASC"},
										baseParams:{"action":"order","get":"consult","mode":"excel","idx":idx}
									}),
									listeners:{
										beforeselect:{fn:function(grid,idx) {
											return false;
										}}
									}
								})
							],
							buttons:[
								new Ext.Button({
									text:"확인",
									icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_tick.png",
									handler:function() {
										var checked = Ext.getCmp("ExcelList").selModel.getSelections();

										if (checked.length == 0) {
											Ext.Msg.show({title:"에러",msg:"엑셀파일로 변환할 업체명/회차를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
											return false;
										} else if (checked.length >= 4) {
											Ext.Msg.show({title:"에러",msg:"동시에 4개까지만 출력할 수 있습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
											return false;
										}

										var tnos = new Array();
										for (var i=0, loop=checked.length;i<loop;i++) {
											tnos[i] = checked[i].get("tno");
										}
										var tno = tnos.join(",");
										Ext.getCmp("ExcelWindow").close();
										ExcelConvert("<?php echo $_ENV['dir']; ?>/module/erp/exec/GetExcel.do.php?action=commander&get=order&idx="+idx+"&tno="+tno);
									}
								}),
								new Ext.Button({
									text:"확인",
									icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
									handler:function() {
										Ext.getCmp("ExcelWindow").close();
									}
								})
							],
							listeners:{
								show:{fn:function() {
									Ext.getCmp("ExcelList").getStore().load();
								}}
							}
						}).show();
					}
				}),
				new Ext.Button({
					text:(idx == 0 ? "확인" : "수정"),
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_tick.png",
					hidden:(Ext.getCmp("ContractWindow") ? true : false),
					handler:function() {
						if (Ext.getCmp("ConsultPanel").store.getCount() == 0) {
							Ext.Msg.show({title:"에러",msg:"품의내역을 작성하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
							return false;
						}

						var datas = new Array();
						var tabs = Ext.getCmp("ConsultPanel").store;

						for (var i=0, loop=tabs.getCount();i<loop;i++) {
							var checked = Ext.getCmp("ConsultPanel").get(i).getStore();
							for (var j=0, loopj=checked.getCount();j<loopj;j++) {
								if (!checked.getAt(j).get("gno") || !checked.getAt(j).get("tno")) {
									Ext.Msg.show({title:"에러",msg:"품의내역에 공종그룹과 공종명이 빠진 품목이 있습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
									return false;
								}
								if (!checked.getAt(j).get("title")) {
									Ext.Msg.show({title:"에러",msg:"품의내역에 품명이 빠진 품목이 있습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
									return false;
								}
							}
							datas[i] = tabs.getAt(i).get("cno")+"\t"+tabs.getAt(i).get("cost1")+"\t"+tabs.getAt(i).get("cost2")+"\t"+tabs.getAt(i).get("cost3")+"\t"+GetGridData(Ext.getCmp("ConsultPanel").get(i));
						}

						Ext.getCmp("ConsultForm").getForm().findField("contract").setValue(GetGridData(Ext.getCmp("ContractPanel")));
						Ext.getCmp("ConsultForm").getForm().findField("consult").setValue(datas.join("\n"));

						if (idx == 0) {
							Ext.getCmp("ConsultForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php?action=order&do=consult&mode=add",waitMsg:"데이터를 저장중입니다."});
						} else {
							Ext.getCmp("ConsultForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php?action=order&do=consult&mode=modify&idx="+idx,waitMsg:"데이터를 수정중입니다."});
						}
					}
				}),
				new Ext.Button({
					text:(idx == 0 ? "취소" : "닫기"),
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
					handler:function() {
						Ext.getCmp("ConsultWindow").close();
					}
				})
			],
			listeners:{
				show:{fn:function() {
					if (Ext.getCmp("OrderWindow")) Ext.getCmp("OrderWindow").close();
				}}
			}
		}).show();

		this.ConsultCreateTabPanel = function(type,idx,tno) {
			if (Ext.getCmp("LoadingTab")) Ext.getCmp("ConsultPanel").remove(Ext.getCmp("LoadingTab"));

			var CreatePanel = new Ext.grid.EditorGridPanel({
				title:Ext.getCmp("ConsultPanel").store.getAt(tno).get("title"),
				layout:"fit",
				closable:true,
				cm:new Ext.grid.ColumnModel({
					defaults:{menuDisabled:true},
					columns:[
						new Ext.ux.grid.CheckboxSelectionModel(),
						{
							dataIndex:"group",
							hideable:false
						},{
							dataIndex:"sort",
							hidden:true,
							hideable:false,
							renderer:function(value,p,record,row) { return record.data.sort = row; }
						},{
							dataIndex:"gno",
							hidden:true,
							hideable:false
						},{
							dataIndex:"tno",
							hidden:true,
							hideable:false
						},{
							header:"품명",
							dataIndex:"title",
							width:130,
							sortable:false,
							summaryType:"count",
							renderer:GridContractItemNotFound,
							summaryRenderer:GridSummaryCount
						},{
							header:"규격",
							dataIndex:"size",
							width:80,
							renderer:GridContractItemNotFound,
							editor:new Ext.form.TextField({selectOnFocus:true})
						},{
							header:"단위",
							dataIndex:"unit",
							width:40,
							renderer:GridContractItemNotFound,
							editor:new Ext.form.TextField({selectOnFocus:true})
						},{
							header:"수량",
							dataIndex:"ea",
							width:50,
							renderer:GridItemOrderEA,
							editor:new Ext.form.TextField({selectOnFocus:true})
						},{
							header:"재료단가",
							dataIndex:"cost1",
							hidden:true,
							hideable:false,
							width:80,
							renderer:function(value,p,record) {
								return GridItemAvgCost(value,record.data.avgcost1);
							},
							editor:new Ext.form.NumberField({selectOnFocus:true}),
							summaryType:"sum",
							summaryRenderer:GridNumberFormat
						},{
							header:"노무단가",
							dataIndex:"cost2",
							hidden:true,
							hideable:false,
							width:80,
							renderer:function(value,p,record) {
								return GridItemAvgCost(value,record.data.avgcost2);
							},
							editor:new Ext.form.NumberField({selectOnFocus:true}),
							summaryType:"sum",
							summaryRenderer:GridNumberFormat
						},{
							header:"경비단가",
							dataIndex:"cost3",
							hidden:true,
							hideable:false,
							width:80,
							renderer:function(value,p,record) {
								return GridItemAvgCost(value,record.data.avgcost3);
							},
							editor:new Ext.form.NumberField({selectOnFocus:true}),
							summaryType:"sum",
							summaryRenderer:GridNumberFormat
						},{
							header:"합계",
							dataIndex:"price",
							width:80,
							renderer:function(value,p,record) {
								var cost = 0;
								if (Ext.getCmp("ConsultCost1").pressed == true) cost+= record.data.cost1;
								if (Ext.getCmp("ConsultCost2").pressed == true) cost+= record.data.cost2;
								if (Ext.getCmp("ConsultCost3").pressed == true) cost+= record.data.cost3;

								record.data.price = cost * record.data.ea;
								return GridNumberFormat(record.data.price);
							},
							summaryType:"sum",
							summaryRenderer:function(value) {
								if (Ext.getCmp("ContractPanel").getStore().sum("price") > 0) document.getElementById("ConsultContractPercent").innerHTML = (value/Ext.getCmp("ContractPanel").getStore().sum("price")*100).toFixed(2)+"%";
								if (Ext.getCmp("ContractPanel").getStore().sum("exec_price") > 0)  document.getElementById("ConsultExecPercent").innerHTML = (value/Ext.getCmp("ContractPanel").getStore().sum("exec_price")*100).toFixed(2)+"%";
								return GridNumberFormat(value);
							}
						}
					]
				}),
				sm:new Ext.ux.grid.CheckboxSelectionModel(),
				store:new Ext.data.GroupingStore({
					proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
					reader:new Ext.data.JsonReader({
						root:"lists",
						totalProperty:"totalCount",
						fields:["is_new",{name:"sort",type:"int"},"group","gno","tno","workgroup","worktype","code","title","size","unit",{name:"ea",type:"float"},"order_ea",{name:"cost1",type:"int"},{name:"cost2",type:"int"},{name:"cost3",type:"int"},"avgcost1","avgcost2","avgcost3"]
					}),
					remoteSort:false,
					groupField:"group",
					sortInfo:{field:"sort",direction:"ASC"},
					baseParams:{"action":"order","get":"consult","mode":"tabdata","idx":idx,"tno":tno},
					listeners:{
						update:{fn:function(store) {
							Ext.getCmp("ConsultPanel").getActiveTab().getView().getRowClass = function(record,index) {
								if (!store.getAt(index).get("gno") || !store.getAt(index).get("tno")) return "x-grid3-td-row-error";
								return "";
							}
						}},
						add:{fn:function(store) {
							Ext.getCmp("ConsultPanel").getActiveTab().getView().getRowClass = function(record,index) {
								if (!store.getAt(index).get("gno") || !store.getAt(index).get("tno")) return "x-grid3-td-row-error";
								return "";
							}
						}}
					}
				}),
				trackMouseOver:true,
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
					render:{fn:function(object) {
						GridEditorAutoMatchItem(Ext.getCmp("ConsultPanel").get(tno),Ext.getCmp("ConsultPanel").store.getAt(tno).get("wno"));
						Ext.getCmp("ConsultPanel").get(tno).getStore().load();
					}},
					beforeedit:{fn:function(object) {
						GridEditorAutoMatchPrice(object);
					}},
					afteredit:{fn:function(object) {
						GridAutoMatchItem(object,Ext.getCmp("ConsultPanel").store.getAt(tno).get("wno"));

						if (object.field == "ea" || object.field == "cost1" || object.field == "cost2" || object.field == "cost3") {
							if (!object.value) object.grid.getStore().getAt(object.row).set(object.field,0);
						}
					}},
					rowcontextmenu:{fn:function(grid,idx,e) {
						var data = grid.getStore().getAt(idx);
						var title = data.get("title") ? data.get("title") : "품명없음";
						var wno = Ext.getCmp("ConsultPanel").store.getAt(tno).get("wno");
						var menu = new Ext.menu.Menu();
						menu.add('<b class="menu-title">'+title+'</b>');

						var record = Ext.data.Record.create([{name:"value",type:"int"},{name:"display",type:"string"}]);
						var workgroups = {};
						for (var i=0, loop=Ext.getCmp("ContractPanel").getStore().getCount();i<loop;i++) {
							var temp = Ext.getCmp("ContractPanel").getStore().getAt(i);
							if (workgroups[temp.get("gno")+","+temp.get("tno")] === undefined) {
								workgroups[temp.get("gno")+","+temp.get("tno")] = temp.get("workgroup")+">"+temp.get("worktype");
							}
						}

						for (var i=0, loop=Ext.getCmp("ConsultPanel").getActiveTab().getStore().getCount();i<loop;i++) {
							var temp = Ext.getCmp("ConsultPanel").getActiveTab().getStore().getAt(i);
							if (workgroups[temp.get("gno")+","+temp.get("tno")] === undefined) {
								workgroups[temp.get("gno")+","+temp.get("tno")] = temp.get("workgroup")+">"+temp.get("worktype");
							}
						}

						for (workgroup in workgroups) {
							var temp = workgroup.split(",");
							menu.add({
								text:workgroups[workgroup],
								checked:(data.get("gno") == temp[0] && data.get("tno") == temp[1]),
								gno:temp[0],
								tno:temp[1],
								group:"workgroup",
								handler:function(item) {
									data.set("gno",item.gno);
									data.set("tno",item.tno);

									Ext.Ajax.request({
										url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Workspace.get.php",
										success:function(XML) {
											if (AjaxResult(XML,"itemcode")) {
												data.set("code",AjaxResult(XML,"code"));
											} else {
												data.set("code","");
											}
										},
										failure:function() {
											data.set("code","");
										},
										headers:{},
										params:{"action":"item","get":"check","wno":wno,"gno":item.gno,"tno":item.tno,"title":data.get("title"),"size":data.get("size"),"unit":data.get("unit")}
									});
								}
							});
						}

						e.stopEvent();
						menu.showAt(e.getXY());
					}},
					close:{fn:function(tab) {
						for (var i=0, loop=Ext.getCmp("ConsultPanel").store.getCount();i<loop;i++) {
							if (tab.getId() == Ext.getCmp("ConsultPanel").get(i).getId()) {
								Ext.getCmp("ConsultPanel").store.removeAt(i);
								break;
							}
						}
					}}
				}
			});

			Ext.getCmp("ConsultPanel").add(
				CreatePanel
			).show();
		}
	}

	// 업체별 품의서 탭 생성


	// 협력업체등록
	function CooperationFunction() {
		new Ext.Window({
			title:"협력업체등록",
			id:"CooperationWindow",
			width:600,
			height:400,
			layout:"fit",
			modal:true,
			items:[
				new Ext.form.FormPanel({
					id:"CooperationForm",
					style:"background:#FFFFFF;",
					labelAlign:"right",
					labelWidth:100,
					autoScroll:true,
					border:false,
					autoWidth:true,
					errorReader:new Ext.form.XmlErrorReader(),
					items:[
						new Ext.form.FieldSet({
							title:"기본정보",
							msgTarget:"side",
							style:"margin:10px;",
							autoWidth:true,
							autoHeight:true,
							items:[
								new Ext.form.TextField({
									name:"title",
									fieldLabel:"업체명",
									width:200,
									allowBlank:false
								}),
								new Ext.form.TextField({
									name:"company_number",
									fieldLabel:"사업자등록번호",
									width:200,
									emptyText:"'-' 는 제외하고 입력하세요.",
									allowBlank:false,
									validator:CheckCompanyNumber,
									listeners:{
										focus:{fn:FocusNumberOnly},
										blur:{fn:BlurCompanyNumberFormat}
									}
								}),
								new Ext.form.TextField({
									name:"type",
									fieldLabel:"업태/업종",
									width:200
								}),
								new Ext.form.TextField({
									name:"master",
									fieldLabel:"대표자",
									width:200,
									allowBlank:false
								}),
								new Ext.form.TextField({
									name:"telephone",
									fieldLabel:"대표번호",
									width:200,
									allowBlank:false,
									emptyText:"'-' 는 제외하고 입력하세요.",
									listeners:{
										blur:{fn:BlurTelephoneFormat},
										focus:{fn:FocusNumberOnly}
									}
								})
							]
						}),
						FormAddressFieldSet("CooperationForm")
					],
					listeners:{actioncomplete:{fn:function(form,action) {
						if (action.type == "submit") {
							Ext.Msg.show({title:"안내",msg:"성공적으로 등록하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
							Ext.getCmp("ConsultCooperationList").getStore().load();
							Ext.getCmp("CooperationWindow").close();
						}
					}}}
				})
			],
			buttons:[
				new Ext.Button({
					text:"확인",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_tick.png",
					handler:function() {
						Ext.getCmp("CooperationForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php?action=cooperation&do=add",waitMsg:"협력업체를 추가중입니다."});
					}
				}),
				new Ext.Button({
					text:"취소",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
					handler:function() {
						Ext.getCmp("CooperationWindow").close();
					}
				})
			]
		}).show();
	}

	// 발주계약서 선택
	function ContractSelectFunction(idx,wno) {
		new Ext.Window({
			id:"ContractSelectWindow",
			title:"발주계약서작성",
			modal:true,
			width:400,
			height:140,
			layout:"fit",
			items:[
				new Ext.form.FormPanel({
					border:false,
					style:"padding:10px; background:#FFFFFF;",
					labelAlign:"right",
					labelWidth:80,
					autoWidth:true,
					errorReader:new Ext.form.XmlErrorReader(),
					items:[
						new Ext.form.TextField({
							fieldLabel:"계약서명",
							width:280,
							id:"ContractSelectTitle",
							value:new Date().format("Y년 m월 d일")+" 계약서"
						}),
						new Ext.form.ComboBox({
							fieldLabel:"품의서선택",
							width:280,
							id:"ContractSelectConsult",
							typeAhead:true,
							triggerAction:"all",
							lazyRender:true,
							store:new Ext.data.Store({
								proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
								reader:new Ext.data.JsonReader({
									root:"lists",
									totalProperty:"totalCount",
									fields:["tno","title"]
								}),
								remoteSort:false,
								sortInfo:{field:"tno",direction:"ASC"},
								baseParams:{"action":"order","get":"consult","mode":"tab","idx":idx}
							}),
							editable:false,
							allowBlank:false,
							mode:"local",
							displayField:"title",
							valueField:"tno",
							emptyText:"품의서를 선택하여 주십시오.",
							listeners:{
								render:{fn:function() {
									Ext.getCmp("ContractSelectConsult").getStore().load();
								}}
							}
						})
					]
				})
			],
			buttons:[
				new Ext.Button({
					text:"확인",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_tick.png",
					handler:function() {
						if (!Ext.getCmp("ContractSelectConsult").getValue()) {
							Ext.Msg.show({title:"에러",msg:"품의서를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
							return false;
						} else if (!Ext.getCmp("ContractSelectTitle").getValue()) {
							Ext.Msg.show({title:"에러",msg:"계약서명 입력하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
							return false;
						} else {
							ContractFunction(0,wno,idx,Ext.getCmp("ContractSelectConsult").getValue(),Ext.getCmp("ContractSelectTitle").getValue());
						}
						Ext.getCmp("ContractSelectWindow").close();
					}
				}),
				new Ext.Button({
					text:"취소",
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
					handler:function() {
						Ext.getCmp("ContractSelectWindow").close();
					}
				})
			]
		}).show();
	}

	// 발주계약서
	function ContractFunction(idx,wno,parent,tno,title) {
		if (!parent) parent = 0;
		if (!tno) tno = 0;
		if (!title) title = "";

		new Ext.Window({
			id:"ContractWindow",
			layout:"fit",
			width:980,
			height:550,
			modal:true,
			title:title,
			items:[
				new Ext.Panel({
					layout:"border",
					border:false,
					tbar:[
						new Ext.SplitButton({
							id:"ContractItemAdd",
							text:"품목추가",
							icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_row_insert.png",
							menu:[{
								text:"도급내역에서 추가하기",
								icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_row_insert.png",
								handler:function() {
									new Ext.Window({
										id:"ContractItemAddWindow",
										title:"도급내역에서 추가하기",
										width:950,
										height:400,
										modal:true,
										layout:"fit",
										items:[
											GridContractSearchList("workspace",wno,"fit")
										],
										buttons:[
											new Ext.Button({
												text:"선택항목추가하기",
												icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_tick.png",
												handler:function() {
													var checked = Ext.getCmp("ContractSearchList").selModel.getSelections();

													if (checked.length == 0) {
														Ext.Msg.show({title:"에러",msg:"추가할 항목을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
														return false;
													}

													Ext.getCmp("ContractItemList").stopEditing();
													var insert = new Array();
													for (var i=0, loop=checked.length;i<loop;i++) {
														var data = checked[i];
														if (Ext.getCmp("ContractItemList").getStore().find("code",data.get("code"),false,false) == -1) {
															insert.push({"is_new":"TRUE","group":" ","gno":data.get("gno"),"tno":data.get("tno"),"workgroup":data.get("workgroup"),"worktype":data.get("worktype"),"code":data.get("code"),"itemcode":data.get("itemcode"),"title":data.get("title"),"size":data.get("size"),"unit":data.get("unit"),"ea":data.get("ea"),"cost1":data.get("cost1"),"cost2":data.get("cost2"),"cost3":data.get("cost3"),"order_ea":data.get("order_ea"),"avgcost1":data.get("avgcost1"),"avgcost2":data.get("avgcost2"),"avgcost3":data.get("avgcost3")});
														}
													}
													GridInsertRow(Ext.getCmp("ContractItemList"),insert);
													Ext.getCmp("ContractItemList").startEditing(0,0);
													Ext.getCmp("ContractItemAddWindow").close();
												}
											}),
											new Ext.Button({
												text:"취소",
												icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
												handler:function() {
													Ext.getCmp("ContractItemAddWindow").close();
												}
											})
										]
									}).show();
								}
							}],
							handler:function() {
								GridInsertRow(Ext.getCmp("ContractItemList"),{"is_new":"TRUE","group":" ","order_ea":"0,0,0,0"});
							}
						}),
						new Ext.Button({
							id:"ContractItemDelete",
							text:"품목삭제",
							icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_row_delete.png",
							handler:function() {
								GridDeleteRow(Ext.getCmp("ContractItemList"),"삭제할 품목을 선택하여 주십시오.");
							}
						}),
						'-',
						new Ext.Button({
							id:"ContractCalc",
							text:"금액일괄수정",
							icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_calculator.png",
							handler:function() {
								new Ext.Window({
									id:"ContractCalculatorWindow",
									title:"금액일괄수정",
									width:300,
									layout:"fit",
									modal:true,
									resizable:false,
									items:[
										new Ext.form.FormPanel({
											id:"ContractCalculatorForm",
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
												Ext.Msg.show({title:"확인",msg:"현재단가대비 자동으로 계약금액을 계산하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
													if (button == "ok") {
														for (var i=0, loop=Ext.getCmp("ContractItemList").getStore().getCount();i<loop;i++) {
															Ext.getCmp("ContractItemList").getStore().getAt(i).set("cost1",Math.floor(Ext.getCmp("ContractItemList").getStore().getAt(i).get("cost1")*Ext.getCmp("ContractCalculatorForm").getForm().findField("percent").getValue()/100));
															Ext.getCmp("ContractItemList").getStore().getAt(i).set("cost2",Math.floor(Ext.getCmp("ContractItemList").getStore().getAt(i).get("cost2")*Ext.getCmp("ContractCalculatorForm").getForm().findField("percent").getValue()/100));
															Ext.getCmp("ContractItemList").getStore().getAt(i).set("cost3",Math.floor(Ext.getCmp("ContractItemList").getStore().getAt(i).get("cost3")*Ext.getCmp("ContractCalculatorForm").getForm().findField("percent").getValue()/100));
														}

														Ext.getCmp("ContractCalculatorWindow").close();
													}
												}});
											}
										}),
										new Ext.Button({
											text:"취소",
											icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
											handler:function() {
												Ext.getCmp("ContractCalculatorWindow").close();
											}
										})
									]
								}).show();
							}
						}),
						'-',
						new Ext.Button({
							id:"ContractStatusOutsourcing",
							text:"하도급계약실행",
							icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_lightning.png",
							handler:function(button) {
								if (Ext.getCmp("ContractItemList").getStore().getModifiedRecords().length > 0) {
									Ext.Msg.show({title:"안내",msg:"수정사항이 있습니다.<br />먼저 수정내용을 저장해주시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
									return false;
								}

								if (Ext.getCmp("ContractForm").getForm().findField("status").getValue() != "NEW") {
									var msg = "이미 하도급계약 또는 자재발주가 실행중입니다.<br />실행내역을 변경할 경우 기존의 기성청구 및 자재입고내역이 모두 초기화됩니다.<br />실행내역을 변경하시겠습니까?";
								} else {
									var msg = "하도급계약을 실행하면, 해당 발주건은 완료됩니다.<br />하도급계약실행시 현장 및 본사에서 기성청구가 가능해집니다.<br />하도급계약을 실행하시겠습니까?";
								}

								Ext.Msg.show({title:"안내",msg:msg,buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.QUESTION,fn:function(btn) {
									if (btn == "ok") {
										Ext.Msg.wait("처리중입니다.","Please Wait...");
										Ext.Ajax.request({
											url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php",
											success:function(XML) {
												var status = AjaxResult(XML,"status");
												if (status == "OUTSOURCING") {
													Ext.getCmp("ContractStatusOutsourcing").toggle(true);
													Ext.getCmp("ContractStatusItemorder").toggle(false);
												} else if (status == "ITEMORDER") {
													Ext.getCmp("ContractStatusOutsourcing").toggle(false);
													Ext.getCmp("ContractStatusItemorder").toggle(true);
												} else {
													Ext.getCmp("ContractStatusOutsourcing").toggle(false);
													Ext.getCmp("ContractStatusItemorder").toggle(false);
												}

												if (status != "NEW") {
													Ext.getCmp("ContractItemAdd").disable();
													Ext.getCmp("ContractItemDelete").disable();
													Ext.getCmp("ContractCalc").disable();
												} else {
													Ext.getCmp("ContractItemAdd").enable();
													Ext.getCmp("ContractItemDelete").enable();
													Ext.getCmp("ContractCalc").enable();
												}
												Ext.getCmp("ContractForm").getForm().findField("status").setValue(status);
												Ext.Msg.show({title:"안내",msg:"성공적으로 처리하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
												Ext.getCmp("ListTab1").getStore().reload();
												Ext.getCmp("ListTab2").getStore().reload();
												Ext.getCmp("ListTab3").getStore().reload();
											},
											failure:function() {
												Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
											},
											headers:{},
											params:{"action":"order","do":"contract","mode":"status","status":"outsourcing","idx":idx}
										});
									}
								}});
							}
						}),
						' ',
						new Ext.Button({
							id:"ContractStatusItemorder",
							text:"자재발주실행",
							icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_lorry.png",
							handler:function(button) {
								if (Ext.getCmp("ContractItemList").getStore().getModifiedRecords().length > 0) {
									Ext.Msg.show({title:"안내",msg:"수정사항이 있습니다.<br />먼저 수정내용을 저장해주시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
									return false;
								}

								if (Ext.getCmp("ContractItemList").getStore().getModifiedRecords().length > 0) {
									Ext.Msg.show({title:"안내",msg:"수정사항이 있습니다.<br />먼저 수정내용을 저장해주시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
									return false;
								}

								if (Ext.getCmp("ContractForm").getForm().findField("status").getValue() != "NEW") {
									var msg = "이미 하도급계약 또는 자재발주가 실행중입니다.<br />실행내역을 변경할 경우 기존의 기성청구 및 자재입고내역이 모두 초기화됩니다.<br />실행내역을 변경하시겠습니까?";
								} else {
									var msg = "자재발주를 실행하면, 해당 발주건은 완료됩니다.<br />자재발주실행시 현장 및 본사에서 자재입고관리가 가능해집니다.<br />자재발주를 실행하시겠습니까?";
								}

								Ext.Msg.show({title:"안내",msg:msg,buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.QUESTION,fn:function(btn) {
									if (btn == "ok") {
										Ext.Msg.wait("처리중입니다.","Please Wait...");
										Ext.Ajax.request({
											url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php",
											success:function(XML) {
												var status = AjaxResult(XML,"status");
												if (status == "OUTSOURCING") {
													Ext.getCmp("ContractStatusOutsourcing").toggle(true);
													Ext.getCmp("ContractStatusItemorder").toggle(false);
												} else if (status == "ITEMORDER") {
													Ext.getCmp("ContractStatusOutsourcing").toggle(false);
													Ext.getCmp("ContractStatusItemorder").toggle(true);
												} else {
													Ext.getCmp("ContractStatusOutsourcing").toggle(false);
													Ext.getCmp("ContractStatusItemorder").toggle(false);
												}

												if (status != "NEW") {
													Ext.getCmp("ContractItemAdd").disable();
													Ext.getCmp("ContractItemDelete").disable();
													Ext.getCmp("ContractCalc").disable();
												} else {
													Ext.getCmp("ContractItemAdd").enable();
													Ext.getCmp("ContractItemDelete").enable();
													Ext.getCmp("ContractCalc").enable();
												}
												Ext.getCmp("ContractForm").getForm().findField("status").setValue(status);
												Ext.Msg.show({title:"안내",msg:"성공적으로 처리하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
												Ext.getCmp("ListTab1").getStore().reload();
												Ext.getCmp("ListTab2").getStore().reload();
												Ext.getCmp("ListTab3").getStore().reload();
											},
											failure:function() {
												Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
											},
											headers:{},
											params:{"action":"order","do":"contract","mode":"status","status":"itemorder","idx":idx}
										});
									}
								}});
							}
						})
					],
					items:[
						new Ext.grid.EditorGridPanel({
							id:"ContractItemList",
							margins:"5 5 0 5",
							region:"center",
							layout:"fit",
							title:"계약품목",
							cm:new Ext.grid.ColumnModel([
								new Ext.ux.grid.CheckboxSelectionModel(),
								{
									dataIndex:"group",
									hidden:true,
									hideable:false
								},{
									header:"공종그룹",
									dataIndex:"gno",
									width:80,
									renderer:function(value,p,record,row,col,store) {
										return GridWorkgroup(value,p,record,Ext.getCmp("ContractItemList").getColumnModel().getCellEditor(col,row).field);
									}
								},{
									header:"공종명",
									dataIndex:"tno",
									width:110,
									renderer:function(value,p,record,row,col,store) {
										return GridWorktype(value,p,record,Ext.getCmp("ContractItemList").getColumnModel().getCellEditor(col,row).field);
									}
								},{
									header:"품명",
									dataIndex:"title",
									width:220,
									renderer:GridContractItemNotFound
								},{
									header:"규격",
									dataIndex:"size",
									width:80,
									renderer:GridContractItemNotFound,
									editor:new Ext.form.TextField({selectOnFocus:true})
								},{
									header:"단위",
									dataIndex:"unit",
									width:40,
									hidden:false,
									renderer:GridContractItemNotFound,
									editor:new Ext.form.TextField({selectOnFocus:true})
								},{
									header:"수량",
									dataIndex:"ea",
									width:50,
									sortable:false,
									renderer:GridItemOrderEA,
									editor:new Ext.form.NumberField({selectOnFocus:true})
								},{
									header:"재료단가",
									dataIndex:"cost1",
									width:80,
									sortable:false,
									summaryType:"sum",
									renderer:function(value,p,record) {
										return GridItemAvgCost(value,record.data.avgcost1);
									},
									editor:new Ext.form.NumberField({selectOnFocus:true})
								},{
									header:"노무단가",
									dataIndex:"cost2",
									width:80,
									sortable:false,
									summaryType:"sum",
									renderer:function(value,p,record) {
										return GridItemAvgCost(value,record.data.avgcost1);
									},
									editor:new Ext.form.NumberField({selectOnFocus:true})
								},{
									header:"경비단가",
									dataIndex:"cost3",
									width:80,
									sortable:false,
									summaryType:"sum",
									renderer:function(value,p,record) {
										return GridItemAvgCost(value,record.data.avgcost1);
									},
									editor:new Ext.form.NumberField({selectOnFocus:true})
								},{
									header:"금액",
									dataIndex:"price",
									width:90,
									sortable:false,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.price = (record.data.cost1 + record.data.cost2 + record.data.cost3) * record.data.ea;
										return GridNumberFormat(record.data.price);
									},
									summaryRenderer:GridNumberFormat
								}
							]),
							store:new Ext.data.GroupingStore({
								proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
								reader:new Ext.data.JsonReader({
									root:"lists",
									totalProperty:"totalCount",
									fields:["is_new","group","gno","tno","workgroup","worktype","code","title","size","unit",{name:"ea",type:"float"},"order_ea",{name:"cost1",type:"int"},{name:"cost2",type:"int"},{name:"cost3",type:"int"},{name:"price",type:"int"},{name:"sort",type:"int"},"avgcost1","avgcost2","avgcost3"]
								}),
								remoteSort:false,
								groupField:"group",
								sortInfo:{field:"sort",direction:"ASC"},
								baseParams:{"action":"order","get":"contract","mode":"item","idx":idx,"parent":parent,"tno":tno}
							}),
							sm:new Ext.ux.grid.CheckboxSelectionModel(),
							trackMouseOver:true,
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
								render:{fn:function() {
									GridEditorAutoMatchItem(Ext.getCmp("ContractItemList"),wno);
									GridEditorWorkgroupType(Ext.getCmp("ContractItemList"),wno);
								}},
								beforeedit:{fn:function(object) {
									if (Ext.getCmp("ContractForm").getForm().findField("status").getValue() && Ext.getCmp("ContractForm").getForm().findField("status").getValue() != "NEW") return false;
									GridEditorBeforeWorkgroupType(object);
									GridEditorAutoMatchPrice(object);
								}},
								afteredit:{fn:function(object) {
									GridAutoMatchItem(object,wno);
									GridEditorAfterWorkgroupType(object)
									if ((object.field == "ea" || object.field == "cost1" || object.field == "cost2" || object.field == "cost3") && !object.value) object.grid.getStore().getAt(object.row).set(object.field,0);
								}}
							}
						}),
						new Ext.Panel({
							region:"south",
							height:80,
							title:"비고",
							split:true,
							margins:"0 5 5 5",
							layout:"fit",
							items:[
								new Ext.form.FormPanel({
									id:"ContractForm",
									border:false,
									errorReader:new Ext.form.XmlErrorReader(),
									reader:new Ext.data.XmlReader(
										{record:"form",success:"@success",errormsg:"@errormsg"},
										["cno","repto","status","title","etc","parent","parent_type"]
									),
									items:[
										new Ext.form.Hidden({
											name:"tno",
											value:tno
										}),
										new Ext.form.Hidden({
											name:"cno"
										}),
										new Ext.form.Hidden({
											name:"repto"
										}),
										new Ext.form.Hidden({
											name:"parent",
											value:parent
										}),
										new Ext.form.Hidden({
											name:"parent_type"
										}),
										new Ext.form.Hidden({
											name:"title"
										}),
										new Ext.form.Hidden({
											name:"data"
										}),
										new Ext.form.Hidden({
											name:"status"
										}),
										new Ext.form.TextArea({
											name:"etc",
											hideLabel:true,
											style:"margin:5px;",
											width:944,
											height:43
										})
									],
									listeners:{
										render:{fn:function() {
											Ext.getCmp("ContractForm").getForm().load({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php?action=order&get=contract&mode=data&idx="+idx+"&parent="+parent,waitMsg:"정보를 로딩중입니다."});
										}},
										resize:{fn:function() {
											Ext.getCmp("ContractForm").getForm().findField("etc").setWidth(Ext.getCmp("ContractForm").getInnerWidth()-10);
											Ext.getCmp("ContractForm").getForm().findField("etc").setHeight(Ext.getCmp("ContractForm").getInnerHeight()-10);
										}},
										actioncomplete:{fn:function(form,action) {
											if (action.type == "load") {
												if (form.findField("title").getValue()) Ext.getCmp("ContractWindow").setTitle(form.findField("title").getValue());
												else if (title) form.findField("title").setValue(title);
												if (form.findField("repto").getValue() != 0) Ext.getCmp("ContractBtnOrder").show();
												if (form.findField("parent").getValue() && form.findField("parent_type").getValue()) Ext.getCmp("ContractBtnConsult").show();
												if (form.findField("status").getValue() == "OUTSOURCING") {
													Ext.getCmp("ContractStatusOutsourcing").toggle(true);
													Ext.getCmp("ContractStatusItemorder").toggle(false);
												} else if (form.findField("status").getValue() == "ITEMORDER") {
													Ext.getCmp("ContractStatusOutsourcing").toggle(false);
													Ext.getCmp("ContractStatusItemorder").toggle(true);
												}

												if (form.findField("status").getValue() == "") {
													Ext.getCmp("ContractStatusItemorder").disable();
													Ext.getCmp("ContractStatusOutsourcing").disable();
												} else if (form.findField("status").getValue() != "NEW") {
													Ext.getCmp("ContractItemAdd").disable();
													Ext.getCmp("ContractItemDelete").disable();
													Ext.getCmp("ContractCalc").disable();
												}
											}

											if (action.type == "submit") {
												var value = FormSubmitReturnValue(action);

												if (value) {
													var temp = value.split(",");
													if (parseInt(temp[1]) > 0) {
														msg = "중복되는 품목 <b>"+temp[1]+"</b>개를 제외하였습니다.<br />발주계약서를 확인하여 주십시오.<br />"+msg;
													} else {
														msg = "성공적으로 저장하였습니다.";
													}
												}
												Ext.getCmp("ListTab1").getStore().reload();
												Ext.getCmp("ListTab2").getStore().reload();
												Ext.getCmp("ListTab3").getStore().reload();
												Ext.getCmp("ListTab").activate("ListTab3");

												if (!idx) Ext.getCmp("ContractWindow").close();
												Ext.Msg.show({title:"안내",msg:msg,buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO,fn:function(button) {
													if (idx) Ext.getCmp("ContractItemList").getStore().reload();
													else ContractFunction(temp[0],wno);
												}});
											}
										}}
									}
								})
							]
						})
					]
				})
			],
			buttons:[
				new Ext.Button({
					id:"ContractBtnOrder",
					text:"현장발주요청서보기",
					hidden:true,
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_edit.png",
					handler:function() {
						var repto = Ext.getCmp("ContractForm").getForm().findField("repto").getValue();
						if (repto) OrderFunction(repto,wno);
					}
				}),
				new Ext.Button({
					id:"ContractBtnConsult",
					text:"본사품의서보기",
					hidden:true,
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_edit.png",
					handler:function() {
						var type = Ext.getCmp("ContractForm").getForm().findField("parent_type").getValue();
						var parent = Ext.getCmp("ContractForm").getForm().findField("parent").getValue();
						if (type && parent) ConsultFunction(type,parent,wno);
					}
				}),
				new Ext.Button({
					text:(idx == 0 ? "확인" : "수정"),
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_tick.png",
					handler:function() {
						if (Ext.getCmp("ContractForm").getForm().findField("status").getValue() == "OUTSOURCING") {
							Ext.Msg.show({title:"에러",msg:"하도급계약이 실행중이므로 수정할 수 없습니다.<br />하도급계약을 수정하시려면, 하도급계약관리에서 수정하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
							return false;
						}

						if (Ext.getCmp("ContractForm").getForm().findField("status").getValue() == "ITEMORDER") {
							Ext.Msg.show({title:"에러",msg:"자재발주계약이 실행중이므로 수정할 수 없습니다.<br />자재발주계약을 수정하시려면, 자재입고관리에서 수정하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
							return false;
						}

						if (Ext.getCmp("ContractItemList").getStore().getCount() == 0) {
							Ext.Msg.show({title:"에러",msg:"계약품목이 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
							return false;
						}

						for (var i=0, loop=Ext.getCmp("ContractItemList").getStore().getCount();i<loop;i++) {
							var data = Ext.getCmp("ContractItemList").getStore().getAt(i);
							if (!data.get("title") || !data.get("gno") || !data.get("tno")) {
								Ext.Msg.show({title:"에러",msg:"공종그룹, 공종명 및 품명을 입력하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
								return false;
							}
						}

						Ext.getCmp("ContractForm").getForm().findField("data").setValue(GetGridData(Ext.getCmp("ContractItemList")));

						if (idx == 0) {
							Ext.getCmp("ContractForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php?action=order&do=contract&mode=add",waitMsg:"데이터를 저장중입니다."});
						} else {
							Ext.getCmp("ContractForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php?action=order&do=contract&mode=modify&idx="+idx,waitMsg:"데이터를 수정중입니다."});
						}
					}
				}),
				new Ext.Button({
					text:(idx == 0 ? "취소" : "닫기"),
					icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
					handler:function() {
						Ext.getCmp("ContractWindow").close();
					}
				})
			],
			listeners:{show:{fn:function() {
				Ext.getCmp("ContractItemList").getStore().load();
				if (Ext.getCmp("ConsultWindow")) Ext.getCmp("ConsultWindow").close();
			}}}
		}).show();
	}

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"자재/하도급 발주관리",
		layout:"fit",
		tbar:[
			new Ext.Button({
				icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_control_left.png",
				text:"이전달",
				handler:function() {
					if (Ext.getCmp("month").selectedIndex == 0) {
						Ext.Msg.show({title:"에러",msg:"이전달 기록이 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
					} else {
						Ext.getCmp("ListTab1").getStore().baseParams.date = Ext.getCmp("ListTab2").getStore().baseParams.date = Ext.getCmp("ListTab3").getStore().baseParams.date = Ext.getCmp("month").getStore().getAt(Ext.getCmp("month").selectedIndex-1).get("date");
						Ext.getCmp("month").setValue(Ext.getCmp("ListTab1").getStore().baseParams.date);
						Ext.getCmp("month").selectedIndex = Ext.getCmp("month").selectedIndex - 1;
						Ext.getCmp("ListTab1").getStore().reload();
						Ext.getCmp("ListTab2").getStore().reload();
						Ext.getCmp("ListTab3").getStore().reload();
					}
				}
			}),
			' ',
			new Ext.form.ComboBox({
				id:"month",
				store:MonthListStore,
				displayField:"display",
				valueField:"date",
				typeAhead:true,
				mode:"local",
				triggerAction:"all",
				width:90,
				editable:false,
				listeners:{
					render:{fn:function(form) {
						form.getStore().load();
						form.getStore().on("load",function() {
							form.setValue("<?php echo Request('iErpMonth','cookie') != null ? Request('iErpMonth','cookie') : GetTime('Y-m'); ?>");
							for (var i=0, loop=form.getStore().getCount();i<loop;i++) {
								if (form.getStore().getAt(i).get("date") == form.getValue()) {
									form.selectedIndex = i;
									break;
								}
							}

							if (form.selectedIndex == -1) {
								form.selectedIndex = form.getStore().getCount()-1;
								form.setValue(form.getStore().getAt(form.getStore().getCount()-1).get("date"));
								SetCookie("iErpMonth",form.getValue());
							}

							Ext.getCmp("ListTab1").getStore().baseParams.date = Ext.getCmp("month").getValue();
							Ext.getCmp("ListTab1").getStore().load();

							Ext.getCmp("ListTab2").getStore().baseParams.date = Ext.getCmp("month").getValue();
							Ext.getCmp("ListTab2").getStore().load();

							Ext.getCmp("ListTab3").getStore().baseParams.date = Ext.getCmp("month").getValue();
							Ext.getCmp("ListTab3").getStore().load();
						});
					}},
					select:{fn:function(form) {
						Ext.getCmp("ListTab1").getStore().baseParams.date = form.getValue();
						Ext.getCmp("ListTab1").getStore().reload();

						Ext.getCmp("ListTab2").getStore().baseParams.date = form.getValue();
						Ext.getCmp("ListTab2").getStore().reload();

						Ext.getCmp("ListTab3").getStore().baseParams.date = form.getValue();
						Ext.getCmp("ListTab3").getStore().reload();
					}}
				}
			}),
			' ',
			new Ext.Button({
				icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_control_right.png",
				iconAlign:"right",
				text:"다음달",
				handler:function() {
					if (Ext.getCmp("month").selectedIndex+1 == Ext.getCmp("month").getStore().getCount()) {
						Ext.Msg.show({title:"에러",msg:"다음달 기록이 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
					} else {
						Ext.getCmp("ListTab1").getStore().baseParams.date = Ext.getCmp("ListTab2").getStore().baseParams.date = Ext.getCmp("ListTab3").getStore().baseParams.date = Ext.getCmp("month").getStore().getAt(Ext.getCmp("month").selectedIndex+1).get("date");
						Ext.getCmp("month").setValue(Ext.getCmp("ListTab1").getStore().baseParams.date);
						Ext.getCmp("month").selectedIndex = Ext.getCmp("month").selectedIndex + 1;
						Ext.getCmp("ListTab1").getStore().reload();
						Ext.getCmp("ListTab2").getStore().reload();
						Ext.getCmp("ListTab3").getStore().reload();
					}
				}
			}),
			'-',
			new Ext.Button({
				id:"GroupByDate",
				text:"일자별",
				icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_checkbox_on.png",
				enableToggle:false,
				pressed:true,
				handler:function(button) {
					button.setIcon("<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_checkbox_on.png");
					Ext.getCmp("ListTab1").getStore().clearGrouping();
					Ext.getCmp("ListTab2").getStore().clearGrouping();
					Ext.getCmp("ListTab3").getStore().clearGrouping();
					Ext.getCmp("ListTab1").getStore().groupBy("date");
					Ext.getCmp("ListTab2").getStore().groupBy("date");
					Ext.getCmp("ListTab3").getStore().groupBy("date");

					Ext.getCmp("ListTab1").getStore().sort("date","DESC");
					Ext.getCmp("ListTab2").getStore().sort("date","DESC");
					Ext.getCmp("ListTab3").getStore().sort("date","DESC");
					Ext.getCmp("GroupByWorkspace").setIcon("<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_checkbox.png");
					Ext.getCmp("GroupByWorkspace").toggle(false);
					Ext.getCmp("GroupByDate").toggle(true);
				}
			}),
			' ',
			new Ext.Button({
				id:"GroupByWorkspace",
				text:"현장별",
				icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_checkbox.png",
				enableToggle:false,
				handler:function(button) {
					button.setIcon("<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_checkbox_on.png");
					Ext.getCmp("ListTab1").getStore().clearGrouping();
					Ext.getCmp("ListTab2").getStore().clearGrouping();
					Ext.getCmp("ListTab3").getStore().clearGrouping();
					Ext.getCmp("ListTab1").getStore().groupBy("workspace");
					Ext.getCmp("ListTab2").getStore().groupBy("workspace");
					Ext.getCmp("ListTab3").getStore().groupBy("workspace");

					Ext.getCmp("ListTab1").getStore().sort("date","DESC");
					Ext.getCmp("ListTab2").getStore().sort("date","DESC");
					Ext.getCmp("ListTab3").getStore().sort("date","DESC");
					Ext.getCmp("GroupByDate").setIcon("<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_checkbox.png");
					Ext.getCmp("GroupByDate").toggle(false);
					Ext.getCmp("GroupByWorkspace").toggle(true);
				}
			}),
			'-',
			new Ext.Button({
				text:"본사품의서작성",
				icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table.png",
				handler:function(button) {
					ConsultSelectFunction(0);
				}
			}),
		],
		items:[
			new Ext.TabPanel({
				id:"ListTab",
				tabPosition:"bottom",
				activeTab:0,
				border:false,
				items:[
					new Ext.grid.GridPanel({
						title:"현장발주요청서",
						id:"ListTab1",
						layout:"fit",
						border:false,
						cm:new Ext.grid.ColumnModel({
							defaults:{menuDisabled:true},
							columns:[
								new Ext.grid.RowNumberer(),
								{
									dataIndex:"idx",
									hidden:true,
									hideable:false
								},{
									header:"요청현장명",
									dataIndex:"workspace",
									sortable:false,
									width:150
								},{
									header:"발주요청서명",
									dataIndex:"title",
									sortable:false,
									width:400,
									renderer:function(value,p,record) {
										var sHTML = "";
										if (record.data.order_type == "OUTSOURCING") {
											sHTML+= '<span style="color:#EF5600;">[하도급]</span> '+value;
										} else {
											sHTML+= '<span style="color:#3764A0;">[자재]</span> '+value;
										}

										if (record.data.file) {
											sHTML+='<img src="<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_bullet_disk.png" style="vertical-align:middle; margin-left:5px;" />';
										}

										return sHTML;
									}
								},{
									header:"품목수",
									dataIndex:"item",
									width:80,
									sortable:false,
									renderer:GridNumberFormat
								},{
									header:"발주종류",
									dataIndex:"type",
									sortable:false,
									width:100,
									renderer:function(value,p,record) {
										var temp = value.split(",");
										var sHTML = '<div style="text-align:center; font:0/0 arial;">';

										sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cost1_';
										sHTML+= temp[0] == "TRUE" ? "on" : "off";
										sHTML+= '.gif" style="margin-right:1px;" />';

										sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cost2_';
										sHTML+= temp[1] == "TRUE" ? "on" : "off";
										sHTML+= '.gif" style="margin-right:1px;" />';

										sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cost3_';
										sHTML+= temp[2] == "TRUE" ? "on" : "off";
										sHTML+= '.gif" style="margin-right:1px;" />';

										return sHTML;
									}
								},{
									header:"상태",
									width:150,
									sortable:true,
									renderer:function(value,p,record) {
										var sHTML = '<div style="text-align:center; font:0/0 arial;">';
										sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_confirm_';
										sHTML+= record.data.is_confirm == "TRUE" ? "on" : "off";
										sHTML+= '.gif" style="margin-right:1px;" />';
										//sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_estimate_';
										//sHTML+= record.data.is_estimate == "TRUE" ? "on" : "off";
										sHTML+= '.gif" style="margin-right:1px;" />';
										sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_consult_';
										sHTML+= record.data.is_consult == "TRUE" ? "on" : "off";
										sHTML+= '.gif" style="margin-right:1px;" />';
										sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_contract_';
										sHTML+= record.data.is_contract == "TRUE" ? "on" : "off";
										sHTML+= '.gif" style="margin-right:1px;" />';
										sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_complete_';
										sHTML+= record.data.is_complete == "TRUE" ? "on" : "off";
										sHTML+= '.gif" />';
										sHTML+= '</div>';

										return sHTML;
									}
								},{
									header:"요청일",
									dataIndex:"datetime",
									sortable:true,
									width:160
								},{
									dataIndex:"date",
									hidden:true,
									hideable:false
								},
								new Ext.grid.CheckboxSelectionModel()
							]
						}),
						sm:new Ext.grid.CheckboxSelectionModel(),
						store:new Ext.data.GroupingStore({
							proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
							reader:new Ext.data.JsonReader({
								root:"lists",
								totalProperty:"totalCount",
								fields:[{name:"idx",type:"int"},{name:"wno",type:"int"},"order_type","workspace","title",{name:"item",type:"int"},"type","file","date","datetime","is_confirm","is_estimate","is_consult","is_contract","is_complete","consult","contract","estimate"]
							}),
							remoteSort:true,
							sortInfo:{field:"date",direction:"DESC"},
							groupField:"date",
							baseParams:{"action":"order","get":"order","mode":"list","date":"<?php echo Request('iErpMonth','cookie') != null ? Request('iErpMonth','cookie') : GetTime('Y-m'); ?>"},
							listeners:{load:{fn:function(store) {
								SetCookie("iErpMonth",store.baseParams.date);
							}}}
						}),
						loadMask:{msg:"데이터를 로딩중입니다."},
						view:new Ext.grid.GroupingView({
							enableGroupingMenu:false,
							hideGroupedColumn:false,
							showGroupName:false,
							enableNoGroups:false,
							headersDisabled:false
						}),
						listeners:{
							rowdblclick:{fn:function(grid,idx,e) {
								var data = grid.getStore().getAt(idx);

								OrderFunction(data.get("idx"),data.get("wno"));
							}},
							rowcontextmenu:{fn:function(grid,idx,e) {
								GridContextmenuSelect(grid,idx);
								var data = grid.getStore().getAt(idx);

								var menu = new Ext.menu.Menu();
								menu.add('<b class="menu-title">'+data.get("title")+'</b>');

								menu.add({
									text:(data.get("is_confirm") == "FALSE" ? "본사확인처리" : "본사확인취소"),
									icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_lock"+(data.get("is_confirm") == "FALSE" ? "" : "_open")+".png",
									handler:function(item) {
										Ext.Msg.show({title:"안내",msg:(data.get("is_confirm") == "FALSE" ? "본사확인처리를 하시겠습니까?<br />본사확인처리 후에는 현장에서 발주요청서를 수정할 수 없습니다." : "본사확인처리를 취소하시겠습니까?<br />본사확인처리 취소시에 해당 발주요청건에 대한 품의내역, 계약내역이 초기화됩니다."),buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.QUESTION,fn:function(btn) {
											if (btn == "ok") {
												Ext.Msg.wait("처리중입니다.","Please Wait...");
												Ext.Ajax.request({
													url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php",
													success:function(XML) {
														var status = AjaxResult(XML,"status");
														if (status != "COMPLETE") {
															Ext.Msg.show({title:"안내",msg:"성공적으로 처리하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
															Ext.getCmp("ListTab1").getStore().reload();
															Ext.getCmp("ListTab2").getStore().reload();
															Ext.getCmp("ListTab3").getStore().reload();
														} else {
															Ext.Msg.show({title:"에러",msg:"발주계약이 실행중이므로 본사확인을 취소할 수 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
														}
													},
													failure:function() {
														Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
													},
													headers:{},
													params:{"action":"order","do":"order","mode":"confirm","idx":data.get("idx")}
												});
											}
										}});
									}
								});
								var consultMenu = new Ext.menu.Menu();
								consultMenu.add('<b class="menu-sub-title">본사품의서</b>');
								consultMenu.add({
									text:"본사품의서 작성",
									icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_edit.png",
									handler:function(item) {
										ConsultSelectFunction(data.get("idx"),data.get("wno"));
									}
								});

								if (data.get("consult")) {
									consultMenu.add('-');
									var temp = data.get("consult").split("\t");
									for (var i=0, loop=temp.length;i<loop;i++) {
										var consult = temp[i].split("||");

										var contractMenu = new Ext.menu.Menu();
										contractMenu.add('<b class="menu-sub-title">발주계약서</b>');
										contractMenu.add({
											text:"발주계약서 작성",
											parent:consult[0],
											icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_edit.png",
											handler:function(item) {
												ContractSelectFunction(item.parent,data.get("wno"));
											}
										});
										if (consult.length > 3) {
											contractMenu.add('-');
											for (var j=3, loopj=consult.length;j<loopj;j++) {
												var contract = consult[j].split('##');
												contractMenu.add({
													text:contract[1],
													idx:contract[0],
													handler:function(item) {
														ContractFunction(item.idx,data.get("wno"));
													}
												});
											}
										}
										consultMenu.add({
											text:consult[2],
											type:consult[1],
											idx:consult[0],
											menu:contractMenu,
											handler:function(item) {
												ConsultFunction(item.type,item.idx,data.get("idx"));
											}
										});
									}
								}
								menu.add({
									text:"본사품의서 및 발주계약서",
									icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_edit.png",
									menu:consultMenu
								});
								menu.add({
									text:"현장발주요청서삭제",
									icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
									handler:function(item) {
										Ext.Msg.show({title:"안내",msg:"현장발주요청서를 정말 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.QUESTION,fn:function(btn) {
											if (btn == "ok") {
												Ext.Msg.wait("처리중입니다.","Please Wait...");
												Ext.Ajax.request({
													url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.do.php",
													success:function(XML) {
														var result = AjaxResult(XML,"result");
														if (result == "TRUE") {
															Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
															Ext.getCmp("ListTab1").getStore().reload();
														} else {
															Ext.Msg.show({title:"에러",msg:"품의서가 작성된 요청서는 삭제할 수 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.ERROR});
														}
													},
													failure:function() {
														Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
													},
													headers:{},
													params:{"action":"order","do":"order","mode":"delete","idx":data.get("idx")}
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
						title:"본사품의서",
						id:"ListTab2",
						layout:"fit",
						border:false,
						cm:new Ext.grid.ColumnModel({
							defaults:{menuDisabled:true},
							columns:[
								new Ext.grid.RowNumberer(),
								{
									dataIndex:"idx",
									hidden:true,
									hidable:false
								},{
									header:"현장명",
									dataIndex:"workspace",
									sortable:false,
									width:150
								},{
									header:"품의서명",
									dataIndex:"title",
									sortable:false,
									width:300
								},{
									header:"품의업체",
									dataIndex:"cooperation",
									sortable:false,
									width:150
								},{
									header:"품목수",
									dataIndex:"item",
									width:80,
									sortable:false,
									renderer:GridNumberFormat
								},{
									header:"상태",
									width:70,
									sortable:true,
									renderer:function(value,p,record) {
										var sHTML = '<div style="text-align:center; font:0/0 arial;">';
										sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_contract_';
										sHTML+= record.data.is_contract == "TRUE" ? "on" : "off";
										sHTML+= '.gif" style="margin-right:1px;" />';
										sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_complete_';
										sHTML+= record.data.is_complete == "TRUE" ? "on" : "off";
										sHTML+= '.gif" />';
										sHTML+= '</div>';

										return sHTML;
									}
								},{
									header:"품의일",
									dataIndex:"datetime",
									sortable:true,
									width:160
								},{
									dataIndex:"date",
									hidden:true,
									hideable:false
								}
							]
						}),
						store:new Ext.data.GroupingStore({
							proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
							reader:new Ext.data.JsonReader({
								root:"lists",
								totalProperty:"totalCount",
								fields:[{name:"idx",type:"int"},{name:"wno",type:"int"},{name:"repto",type:"int"},"type","workspace","title","cooperation",{name:"item",type:"int"},"date","datetime","is_complete","is_contract","contract"]
							}),
							remoteSort:true,
							sortInfo:{field:"date",direction:"DESC"},
							groupField:"date",
							baseParams:{"action":"order","get":"consult","mode":"list","date":"<?php echo Request('iErpMonth','cookie') != null ? Request('iErpMonth','cookie') : GetTime('Y-m'); ?>"}
						}),
						loadMask:{msg:"데이터를 로딩중입니다."},
						view:new Ext.grid.GroupingView({
							enableGroupingMenu:false,
							hideGroupedColumn:false,
							showGroupName:false,
							enableNoGroups:false,
							headersDisabled:false
						}),
						listeners:{
							rowdblclick:{fn:function(grid,row,event) {
								ConsultFunction(grid.getStore().getAt(row).get("type"),grid.getStore().getAt(row).get("idx"),grid.getStore().getAt(row).get("wno"));
							}},
							rowcontextmenu:{fn:function(grid,idx,event) {
								GridContextmenuSelect(grid,idx);
								var data = grid.getStore().getAt(idx);

								var menu = new Ext.menu.Menu();
								menu.add('<b class="menu-title">'+data.get("title")+'</b>');

								if (data.get("repto") > 0) {
									menu.add({
										text:"현장발주요청서",
										icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_edit.png",
										handler:function(item) {
											OrderFunction(data.get("idx"),data.get("wno"));
										}
									});
								}

								var contractMenu = new Ext.menu.Menu();
								contractMenu.add('<b class="menu-sub-title">발주계약서</b>');
								contractMenu.add({
									text:"발주계약서작성",
									icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_edit.png",
									handler:function(item) {
										ContractSelectFunction(data.get("idx"),data.get("wno"));
									}
								});

								if (data.get("contract")) {
									contractMenu.add('-');
									var temp = data.get("contract").split("\t");
									for (var i=0, loop=temp.length;i<loop;i++) {
										var info = temp[i].split("||");
										contractMenu.add({
											text:info[1],
											idx:info[0],
											handler:function(item) {
												ContractFunction(item.idx,data.get("wno"));
											}
										});
									}
								}

								menu.add({
									text:"발주계약서",
									icon:(Ext.isIE6 ? "" : "<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_paste_plain.png"),
									menu:contractMenu
								});

								event.stopEvent();
								menu.showAt(event.getXY());
							}}
						}
					}),
					new Ext.grid.GridPanel({
						title:"발주계약서",
						id:"ListTab3",
						layout:"fit",
						border:false,
						cm:new Ext.grid.ColumnModel({
							defaults:{menuDisabled:true},
							columns:[
								new Ext.grid.RowNumberer(),
								{
									dataIndex:"idx",
									hidden:true,
									hideable:false
								},{
									header:"현장명",
									dataIndex:"workspace",
									sortable:false,
									width:150
								},{
									header:"업체명",
									dataIndex:"cooperation",
									sortable:false,
									width:150
								},{
									header:"발주계약서명",
									dataIndex:"title",
									sortable:false,
									width:350
								},{
									header:"품목수",
									dataIndex:"item",
									width:60,
									sortable:false,
									renderer:GridNumberFormat
								},{
									header:"금액",
									dataIndex:"price",
									width:100,
									sortable:true,
									renderer:GridNumberFormat
								},{
									header:"상태",
									dataIndex:"status",
									width:85,
									sortable:true,
									renderer:function(value,p,record) {
										var sHTML = '<div style="text-align:center; font:0/0 arial;">';
										sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_outsourcing_';
										sHTML+= value == "OUTSOURCING" ? "on" : "off";
										sHTML+= '.gif" />';
										sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_item_';
										sHTML+= value == "ITEMORDER" ? "on" : "off";
										sHTML+= '.gif" style="margin-left:1px;" />';
										sHTML+= '</div>';

										return sHTML;
									}
								},{
									header:"작성일",
									dataIndex:"datetime",
									sortable:true,
									width:160
								},{
									dataIndex:"date",
									hidden:true,
									hideable:false
								},
								new Ext.grid.CheckboxSelectionModel()
							]
						}),
						sm:new Ext.grid.CheckboxSelectionModel(),
						store:new Ext.data.GroupingStore({
							proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
							reader:new Ext.data.JsonReader({
								root:"lists",
								totalProperty:"totalCount",
								fields:[{name:"idx",type:"int"},{name:"wno",type:"int"},{name:"ono",type:"int"},{name:"repto",type:"int"},"workspace","cooperation","title",{name:"item",type:"int"},"status","date","datetime",{name:"price",type:"int"}]
							}),
							remoteSort:true,
							sortInfo:{field:"date",direction:"DESC"},
							groupField:"date",
							baseParams:{"action":"order","get":"contract","mode":"list","date":"<?php echo Request('iErpMonth','cookie') != null ? Request('iErpMonth','cookie') : GetTime('Y-m'); ?>"}
						}),
						loadMask:{msg:"데이터를 로딩중입니다."},
						view:new Ext.grid.GroupingView({
							enableGroupingMenu:false,
							hideGroupedColumn:false,
							showGroupName:false,
							enableNoGroups:false,
							headersDisabled:false
						}),
						listeners:{
							rowdblclick:{fn:function(grid,idx,e) {
								var data = grid.getStore().getAt(idx);

								ContractFunction(data.get("idx"),data.get("wno"));
							}},
							rowcontextmenu:{fn:function(grid,idx,e) {
								GridContextmenuSelect(grid,idx);
								var data = grid.getStore().getAt(idx);

								var menu = new Ext.menu.Menu();
								menu.add('<b class="menu-title">'+data.get("title")+'</b>');

								if (data.get("repto") > 0) {
									menu.add({
										text:"현장발주요청서",
										icon:(Ext.isIE6 ? "" : "<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_edit.png"),
										handler:function(item) {
											OrderFunction(data.get("idx"),data.get("wno"));
										}
									});
								}

								if (data.get("ono") > 0) {
									menu.add({
										text:"본사품의서",
										icon:(Ext.isIE6 ? "" : "<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_edit.png"),
										handler:function(item) {
											ConsultFunction(data.get("ono"),data.get("repto"));
										}
									});
								}
								e.stopEvent();
								menu.showAt(e.getXY());
							}}
						}
					})
				]
			})
		]
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>