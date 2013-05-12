<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	var MonthListStore = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:["date","display"]
		}),
		remoteSort:false,
		sortInfo:{field:"date",direction:"ASC"},
		baseParams:{"action":"month","wno":"<?php echo $this->wno; ?>"}
	});

	var OrderStore = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:[{name:"idx",type:"int"},"title","date",{name:"item",type:"int"},"type","file","is_confirm","is_consult","is_contract"]
		}),
		remoteSort:false,
		sortInfo:{field:"date",direction:"ASC"},
		baseParams:{"wno":"<?php echo $this->wno; ?>","action":"itemorder","get":"order","mode":"list","date":"<?php echo Request('iErpMonth','cookie') != null ? Request('iErpMonth','cookie') : GetTime('Y-m'); ?>"}
	});

	function FileUploadFunction(step,idx) {
		new Ext.Window({
			id:"FileWindow",
			title:"첨부파일 등록하기",
			width:400,
			modal:true,
			resizeable:false,
			items:[
				new Ext.form.FormPanel({
					id:"FileForm",
					border:false,
					style:"padding:5px 5px 2px 5px; background:#FFFFFF;",
					errorReader:new Ext.form.XmlErrorReader(),
					fileUpload:true,
					defaults:{hideLabel:true},
					items:[
						new Ext.ux.form.FileUploadField({
							name:"file",
							buttonText:"",
							buttonCfg:{iconCls:"upload-file"},
							allowBlank:false,
							width:375,
							listeners:{
								focus:{fn:function(form) {
									if (form.getValue()) {
										Ext.Msg.show({title:"초기화선택",msg:"첨부파일을 초기화 하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
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
						var file = FormSubmitReturnValue(action);
						if (!file) {
							Ext.Msg.show({title:"에러",msg:"서버에 이상이 있어 첨부파일을 등록하지 못하였습니다.",buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.ERROR});
							return false;
						}

						if (step == "order") {
							var temp = file.split("|");
							var menu = new Ext.menu.Menu();
							menu.add({
								text:"첨부파일 다운로드 ("+temp[1]+", "+GetFileSize(temp[2])+")",
								icon:"<?php echo $this->moduleDir; ?>/images/common/icon_drive_go.png",
								handler:function() {
									downloadFrame.location.href = "<?php echo $this->moduleDir; ?>/exec/Workspace.do.php?wno=<?php echo $this->wno; ?>&action=itemorder&do=file&mode=download&step=order&idx="+idx;
								}
							});
							menu.add({
								text:"첨부파일 삭제하기",
								icon:"<?php echo $this->moduleDir; ?>/images/common/icon_drive_delete.png",
								handler:function() {
									Ext.Msg.show({title:"안내",msg:"첨부파일을 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
										if (button == "ok") {
											Ext.Ajax.request({
												url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php",
												success:function() {
													Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
													var menu = new Ext.menu.Menu();
													menu.add({
														text:"첨부파일이 없습니다.",
														icon:"<?php echo $this->moduleDir; ?>/images/common/icon_drive_go.png"
													});
													menu.add('-');
													menu.add({
														text:"첨부파일 등록하기",
														icon:"<?php echo $this->moduleDir; ?>/images/common/icon_drive_disk.png",
														handler:function() {
															FileUploadFunction("order",idx);
														}
													});
													Ext.getCmp("OrderFile").menu = menu;
												},
												failure:function() {
													Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 삭제하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
												},
												headers:{},
												params:{"action":"itemorder","do":"file","mode":"delete","step":"order","wno":"<?php echo $wno; ?>","idx":idx}
											});
										}
									}});
								}
							});
							menu.add('-');
							menu.add({
								text:"첨부파일 등록하기",
								icon:"<?php echo $this->moduleDir; ?>/images/common/icon_drive_disk.png",
								handler:function() {
									FileUploadFunction("order",idx);
								}
							});
						}
						Ext.getCmp("OrderFile").menu = menu;
						Ext.Msg.show({title:"안내",msg:"첨부파일을 성공적으로 등록하였습니다.",buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.INFO});
						Ext.getCmp("FileWindow").close();
					}}}
				})
			],
			buttons:[
				new Ext.Button({
					text:"확인",
					icon:"<?php echo $this->moduleDir; ?>/images/common/icon_tick.png",
					handler:function() {
						Ext.Msg.show({title:"안내",msg:"기존의 첨부파일이 있다면, 현재의 파일로 대체됩니다.<br />계속 진행하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.QUESTION,fn:function(button) {
							if (button == "ok") {
								Ext.getCmp("FileForm").getForm().submit({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php?wno=<?php echo $this->wno; ?>&action=itemorder&do=file&mode=upload&step="+step+"&idx="+idx,waitMsg:"파일을 업로드중입니다."});
							}
						}});
					}
				}),
				new Ext.Button({
					text:"취소",
					icon:"<?php echo $this->moduleDir; ?>/images/common/icon_cross.png",
					handler:function() {
						Ext.getCmp("FileWindow").close();
					}
				})
			]
		}).show();
	}

	function OrderFunction(idx) {
		new Ext.Window({
			id:"OrderWindow",
			title:"발주요청서 보기",
			width:950,
			height:550,
			modal:true,
			maximizable:true,
			layout:"border",
			tbar:[
				new Ext.Button({
					id:"OrderCost1",
					icon:"<?php echo $this->moduleDir; ?>/images/common/icon_checkbox.png",
					text:"재료비",
					enableToggle:true,
					listeners:{toggle:{fn:function(button,pressed) {
						if (pressed == true) {
							button.setIcon("<?php echo $this->moduleDir; ?>/images/common/icon_checkbox_on.png");
						} else {
							button.setIcon("<?php echo $this->moduleDir; ?>/images/common/icon_checkbox.png");
						}
						Ext.getCmp("OrderListPanel").getStore().sort("sort","ASC");
					}}}
				}),
				' ',
				new Ext.Button({
					id:"OrderCost2",
					icon:"<?php echo $this->moduleDir; ?>/images/common/icon_checkbox.png",
					text:"노무비",
					enableToggle:true,
					listeners:{toggle:{fn:function(button,pressed) {
						if (pressed == true) {
							button.setIcon("<?php echo $this->moduleDir; ?>/images/common/icon_checkbox_on.png");
						} else {
							button.setIcon("<?php echo $this->moduleDir; ?>/images/common/icon_checkbox.png");
						}
						Ext.getCmp("OrderListPanel").getStore().sort("sort","ASC");
					}}}
				}),
				' ',
				new Ext.Button({
					id:"OrderCost3",
					icon:"<?php echo $this->moduleDir; ?>/images/common/icon_checkbox.png",
					text:"경비",
					enableToggle:true,
					listeners:{toggle:{fn:function(button,pressed) {
						if (pressed == true) {
							button.setIcon("<?php echo $this->moduleDir; ?>/images/common/icon_checkbox_on.png");
						} else {
							button.setIcon("<?php echo $this->moduleDir; ?>/images/common/icon_checkbox.png");
						}
						Ext.getCmp("OrderListPanel").getStore().sort("sort","ASC");
					}}}
				}),
				'-',
				new Ext.SplitButton({
					id:"OrderItemAdd",
					text:"품목추가",
					icon:"<?php echo $this->moduleDir; ?>/images/common/icon_table_row_insert.png",
					menu:[{
						text:"도급내역에서 추가하기",
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_table_row_insert.png",
						handler:function() {
							new Ext.Window({
								id:"OrderItemAddWindow",
								title:"도급내역에서 추가하기",
								width:950,
								height:400,
								modal:true,
								layout:"fit",
								items:[
									GridContractSearchList("workspace","<?php echo $this->wno; ?>","fit")
								],
								buttons:[
									new Ext.Button({
										text:"선택항목추가하기",
										icon:"<?php echo $this->moduleDir; ?>/images/common/icon_tick.png",
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
										icon:"<?php echo $this->moduleDir; ?>/images/common/icon_cross.png",
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
					icon:"<?php echo $this->moduleDir; ?>/images/common/icon_table_row_delete.png",
					handler:function() {
						GridDeleteRow(Ext.getCmp("OrderListPanel"));
					}
				}),
				'-',
				new Ext.Button({
					id:"OrderFile",
					text:"첨부파일",
					icon:"<?php echo $this->moduleDir; ?>/images/common/icon_disk.png",
					menu:[]
				}),
				'-',
				new Ext.Button({
					id:"OrderStatus",
					text:"본사확인",
					icon:"<?php echo $this->moduleDir; ?>/images/common/icon_lock_open.png",
					listeners:{toggle:{fn:function(button,pressed) {
						if (pressed == true) {
							button.setIcon("<?php echo $this->moduleDir; ?>/images/common/icon_lock.png");
						} else {
							button.setIcon("<?php echo $this->moduleDir; ?>/images/common/icon_lock_open.png");
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
							width:50,
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
							hideable:false,
							renderer:function(value,p,record,row) {
								record.data.sort = row;
								return record.data.sort;
							}
						},
						new Ext.ux.grid.CheckboxSelectionModel()
					]),
					store:new Ext.data.GroupingStore({
						proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php"}),
						reader:new Ext.data.JsonReader({
							root:"lists",
							totalProperty:"totalCount",
							fields:["is_new","group","itemcode","code","gno","tno","workgroup","worktype","title","size","unit",{name:"contract_ea",type:"float"},"order_ea",{name:"ea",type:"float"},{name:"cost1",type:"int"},{name:"cost2",type:"int"},{name:"cost3",type:"int"},{name:"sort",type:"int"}]
						}),
						remoteSort:false,
						groupField:"group",
						sortInfo:{field:"sort",direction:"ASC"},
						baseParams:{"wno":"<?php echo $this->wno; ?>","action":"itemorder","get":"order","mode":"item","idx":idx}
					}),
					sm:new Ext.ux.grid.CheckboxSelectionModel(),
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
						render:{fn:function() {
							GridEditorAutoMatchItem(Ext.getCmp("OrderListPanel"),"<?php echo $this->wno; ?>");
							GridEditorWorkgroupType(Ext.getCmp("OrderListPanel"),"<?php echo $this->wno; ?>");
						}},
						beforeedit:{fn:function(object) {
							if (Ext.getCmp("OrderForm").getForm().findField("status") != "NEW") return false;
							GridEditorBeforeWorkgroupType(object);
						}},
						afteredit:{fn:function(object) {
							GridAutoMatchItem(object,"<?php echo $this->wno; ?>");
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
								Ext.getCmp("OrderWindow").setTitle(form.findField("title").getValue()+" 발주요청서 보기");
								var temp = form.findField("type").getValue().split(",");
								if (temp[0] == "TRUE") Ext.getCmp("OrderCost1").toggle(true);
								if (temp[1] == "TRUE") Ext.getCmp("OrderCost2").toggle(true);
								if (temp[2] == "TRUE") Ext.getCmp("OrderCost3").toggle(true);

								var fileMenu = new Ext.menu.Menu();
								if (form.findField("file").getValue() != "") {
									var temp = form.findField("file").getValue().split("|");
									fileMenu.add({
										text:"첨부파일 다운로드 ("+temp[1]+", "+GetFileSize(temp[2])+")",
										icon:"<?php echo $this->moduleDir; ?>/images/common/icon_drive_go.png",
										handler:function() {
											downloadFrame.location.href = "<?php echo $this->moduleDir; ?>/exec/Workspace.do.php?wno=<?php echo $this->wno; ?>&action=itemorder&do=file&mode=download&step=order&idx="+idx;
										}
									});
								} else {
									fileMenu.add({
										text:"첨부파일이 없습니다.",
										icon:"<?php echo $this->moduleDir; ?>/images/common/icon_drive_go.png"
									});
								}

								if (form.findField("status").getValue() != "NEW") {
									Ext.getCmp("OrderStatus").toggle(true);
									Ext.getCmp("OrderCost1").disable(true);
									Ext.getCmp("OrderCost2").disable(true);
									Ext.getCmp("OrderCost3").disable(true);
									Ext.getCmp("OrderItemAdd").disable(true);
									Ext.getCmp("OrderItemDelete").disable(true);
								} else {
									if (form.findField("file").getValue() != "") {
										fileMenu.add({
											text:"첨부파일 삭제하기",
											icon:"<?php echo $this->moduleDir; ?>/images/common/icon_drive_delete.png",
											handler:function() {
												Ext.Msg.show({title:"안내",msg:"첨부파일을 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
													if (button == "ok") {
														Ext.Ajax.request({
															url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php",
															success:function() {
																Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
																var menu = new Ext.menu.Menu();
																menu.add({
																	text:"첨부파일이 없습니다.",
																	icon:"<?php echo $this->moduleDir; ?>/images/common/icon_drive_go.png"
																});
																menu.add('-');
																menu.add({
																	text:"첨부파일 등록하기",
																	icon:"<?php echo $this->moduleDir; ?>/images/common/icon_drive_disk.png",
																	handler:function() {
																		FileUploadFunction("order",idx);
																	}
																});
																Ext.getCmp("OrderFile").menu = menu;
															},
															failure:function() {
																Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 삭제하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
															},
															headers:{},
															params:{"action":"itemorder","do":"file","mode":"delete","step":"order","wno":"<?php echo $wno; ?>","idx":idx}
														});
													}
												}});
											}
										});
									}
									fileMenu.add('-');
									fileMenu.add({
										text:"첨부파일 등록하기",
										icon:"<?php echo $this->moduleDir; ?>/images/common/icon_drive_disk.png",
										handler:function() {
											FileUploadFunction("order",idx);
										}
									});
								}
								Ext.getCmp("OrderFile").menu = fileMenu;
								Ext.getCmp("OrderListPanel").getStore().load();
							}

							if (action.type == "submit") {
								var value = FormSubmitReturnValue(action);
								if (value) {
									var temp = value.split(",");
									var idx = temp[0];

									Ext.getCmp("OrderWindow").close();

									if (parseInt(temp[1]) > 0) {
										Ext.Msg.show({title:"안내",msg:"중복되는 품목 <b>"+temp[1]+"</b>개를 제외하였습니다.<br />발주요청서를 확인하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO,fn:function() { OrderFunction(idx); }});
									} else {
										OrderFunction(idx);
									}
								}
							}
						}}
					}
				})
			],
			buttons:[
				new Ext.Button({
					text:"엑셀파일로 변환",
					icon:"<?php echo $this->moduleDir; ?>/images/common/icon_page_white_excel.png",
					handler:function() {
						ExcelConvert("<?php echo $this->moduleDir; ?>/exec/GetExcel.do.php?action=workspace&get=order&idx="+grid.getStore().getAt(idx).get("idx"));
					}
				}),
				new Ext.Button({
					text:"수정",
					icon:"<?php echo $this->moduleDir; ?>/images/common/icon_tick.png",
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
							Ext.getCmp("OrderForm").getForm().submit({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php?action=itemorder&do=modify&wno=<?php echo $this->wno; ?>&idx="+idx,waitMsg:"데이터를 수정중입니다.",submitEmptyText:false});
						} else {
							Ext.Msg.show({title:"안내",msg:"본사에서 확인한 발주요청건은 수정할 수 없습니다.<br />본사와 협의후에 수정하여 주시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
						}
					}
				}),
				new Ext.Button({
					text:"닫기",
					icon:"<?php echo $this->moduleDir; ?>/images/common/icon_cross.png",
					handler:function() {
						Ext.getCmp("OrderWindow").close();
					}
				})
			],
			listeners:{show:{fn:function() {
				Ext.getCmp("OrderForm").getForm().load({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php?wno=<?php echo $this->wno; ?>&action=itemorder&get=order&mode=data&idx="+idx,waitMsg:"데이터를 로딩중입니다."});
			}}}
		}).show();
	}

	function WriteOrderFunction() {
		new Ext.Window({
			id:"OrderWindow",
			title:"자재발주요청서작성",
			width:950,
			height:550,
			modal:true,
			maximizable:true,
			layout:"fit",
			items:[
				new Ext.Panel({
					layout:"border",
					border:false,
					items:[
						GridContractSearchList("workspace","<?php echo $this->wno; ?>"),
						new Ext.grid.EditorGridPanel({
							id:"OrderListPanel",
							title:"자재발주요청서작성",
							margins:"0 5 0 5",
							region:"center",
							tbar:[
								new Ext.form.TextField({
									id:"OrderTitle",
									width:210,
									emptyText:"발주요청서 제목을 입력하여 주십시오."
								}),
								'-',
								new Ext.Button({
									text:"선택항목추가하기",
									icon:"<?php echo $this->moduleDir; ?>/images/common/icon_arrow_down.png",
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
									}
								}),
								'-',
								new Ext.Button({
									text:"새항목추가하기",
									icon:"<?php echo $this->moduleDir; ?>/images/common/icon_table_row_insert.png",
									handler:function() {
										GridInsertRow(Ext.getCmp("OrderListPanel"),{"is_new":"TRUE","group":" ","order_ea":"0,0,0,0"});
									}
								}),
								new Ext.Button({
									text:"선택항목삭제하기",
									icon:"<?php echo $this->moduleDir; ?>/images/common/icon_table_row_delete.png",
									handler:function() {
										GridDeleteRow(Ext.getCmp("OrderListPanel"));
									}
								}),
								'-',
								new Ext.Button({
									id:"OrderCost1",
									icon:"<?php echo $this->moduleDir; ?>/images/common/icon_checkbox.png",
									text:"재료비",
									enableToggle:true,
									listeners:{toggle:{fn:function(button,pressed) {
										if (pressed == true) {
											button.setIcon("<?php echo $this->moduleDir; ?>/images/common/icon_checkbox_on.png");
										} else {
											button.setIcon("<?php echo $this->moduleDir; ?>/images/common/icon_checkbox.png");
										}
										Ext.getCmp("OrderListPanel").getStore().sort("sort","ASC");
									}}}
								}),
								' ',
								new Ext.Button({
									id:"OrderCost2",
									icon:"<?php echo $this->moduleDir; ?>/images/common/icon_checkbox.png",
									text:"노무비",
									enableToggle:true,
									listeners:{toggle:{fn:function(button,pressed) {
										if (pressed == true) {
											button.setIcon("<?php echo $this->moduleDir; ?>/images/common/icon_checkbox_on.png");
										} else {
											button.setIcon("<?php echo $this->moduleDir; ?>/images/common/icon_checkbox.png");
										}
										Ext.getCmp("OrderListPanel").getStore().sort("sort","ASC");
									}}}
								}),
								' ',
								new Ext.Button({
									id:"OrderCost3",
									icon:"<?php echo $this->moduleDir; ?>/images/common/icon_checkbox.png",
									text:"경비",
									enableToggle:true,
									listeners:{toggle:{fn:function(button,pressed) {
										if (pressed == true) {
											button.setIcon("<?php echo $this->moduleDir; ?>/images/common/icon_checkbox_on.png");
										} else {
											button.setIcon("<?php echo $this->moduleDir; ?>/images/common/icon_checkbox.png");
										}
										Ext.getCmp("OrderListPanel").getStore().sort("sort","ASC");
									}}}
								})
							],
							cm:new Ext.grid.ColumnModel([
								new Ext.ux.grid.CheckboxSelectionModel(),
								{
									dataIndex:"group",
									hideable:false
								},{
									header:"공종그룹",
									dataIndex:"gno",
									width:80,
									sortable:false,
									renderer:function(value,p,record,row,col,store) {
										return GridWorkgroup(value,p,record,Ext.getCmp("OrderListPanel").getColumnModel().getCellEditor(col,row).field);
									}
								},{
									header:"공종명",
									dataIndex:"tno",
									width:120,
									sortable:false,
									renderer:function(value,p,record,row,col,store) {
										return GridWorktype(value,p,record,Ext.getCmp("OrderListPanel").getColumnModel().getCellEditor(col,row).field);
									}
								},{
									header:"품명",
									dataIndex:"title",
									width:340,
									sortable:false,
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
									width:60,
									sortable:false,
									renderer:GridContractItemNotFound,
									editor:new Ext.form.TextField({selectOnFocus:true})
								},{
									header:"요청수량",
									dataIndex:"ea",
									width:75,
									sortable:false,
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
									hideable:false,
									renderer:function(value,p,record,row) {
										record.data.sort = row;
										return record.data.sort;
									}
								}
							]),
							store:new Ext.data.GroupingStore({
								reader:new Ext.data.JsonReader({
									root:"lists",
									totalProperty:"totalCount",
									fields:["is_new","group","itemcode","code","workgroup","gno","worktype","tno","title","size","unit",{name:"cost1",type:"int"},{name:"cost2",type:"int"},{name:"cost3",type:"int"},{name:"ea",type:"float"},{name:"price",type:"int"},"order_ea",{name:"sort",type:"int"}]
								}),
								remoteSort:false,
								groupField:"group",
								sortInfo:{field:"sort",direction:"ASC"}
							}),
							sm:new Ext.ux.grid.CheckboxSelectionModel(),
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
								render:{fn:function() {
									GridEditorAutoMatchItem(Ext.getCmp("OrderListPanel"),"<?php echo $this->wno; ?>");
									GridEditorWorkgroupType(Ext.getCmp("OrderListPanel"),"<?php echo $this->wno; ?>");
								}},
								beforeedit:{fn:function(object) {
									GridEditorBeforeWorkgroupType(object);
								}},
								afteredit:{fn:function(object) {
									GridAutoMatchItem(object,"<?php echo $this->wno; ?>");
									GridEditorAfterWorkgroupType(object)

									if (object.field == "ea" && object.value == "") {
										object.grid.getStore().getAt(object.row).set(object.field,0);
									}

									if (object.field == "gno") {
										object.grid.getStore().getAt(object.row).set("tno","");
									}
								}}
							}
						}),
						new Ext.form.FormPanel({
							id:"OrderForm",
							title:"비고 및 첨부파일",
							region:"south",
							collapsible:true,
							split:true,
							margins:"0 5 5 5",
							errorReader:new Ext.form.XmlErrorReader(),
							fileUpload:true,
							height:100,
							minHeight:100,
							items:[
								new Ext.form.FieldSet({
									border:false,
									style:"padding:5px;",
									defaults:{hideLabel:true},
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
										new Ext.form.TextArea({
											name:"etc",
											allowBlank:true,
											emptyText:"본사에 전달할 비고사항이 있다면 입력하여 주십시오."
										}),
										new Ext.ux.form.FileUploadField({
											name:"file",
											buttonText:"",
											buttonCfg:{iconCls:"upload-file"},
											allowBlank:true,
											emptyText:"첨부파일이 있다면 등록하여 주십시오.",
											listeners:{
												focus:{fn:function(form) {
													if (form.getValue()) {
														Ext.Msg.show({title:"초기화선택",msg:"첨부파일을 초기화 하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
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
									]
								})

							],
							listeners:{
								resize:{fn:function() {
									Ext.getCmp("OrderForm").getForm().findField("etc").setWidth(Ext.getCmp("OrderForm").getInnerWidth()-12);
									Ext.getCmp("OrderForm").getForm().findField("etc").setHeight(Ext.getCmp("OrderForm").getInnerHeight()-36);
									Ext.getCmp("OrderForm").getForm().findField("file").setWidth(Ext.getCmp("OrderForm").getInnerWidth()-12);
								}},
								actioncomplete:{fn:function(form,action) {
									var value = FormSubmitReturnValue(action);
									if (value) {
										var temp = value.split(",");
										var idx = temp[0];

										Ext.getCmp("OrderWindow").close();

										if (parseInt(temp[1]) > 0) {
											Ext.Msg.show({title:"안내",msg:"중복되는 품목 <b>"+temp[1]+"</b>개를 제외하였습니다.<br />발주요청서를 확인하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO,fn:function() { OrderFunction(idx); }});
										} else {
											OrderFunction(idx);
										}
										Ext.getCmp("ListPanel").getStore().reload();
									}
								}}
							}
						})
					]
				})
			],
			buttons:[
				new Ext.Button({
					text:"확인",
					icon:"<?php echo $this->moduleDir; ?>/images/common/icon_tick.png",
					handler:function() {
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

						if (!Ext.getCmp("OrderTitle").getValue()) {
							Ext.getCmp("OrderForm").getForm().findField("title").setValue(Ext.getCmp("OrderListPanel").getStore().getAt(0).get("title")+(Ext.getCmp("OrderListPanel").getStore().getCount() > 1 ? "외 "+(Ext.getCmp("OrderListPanel").getStore().getCount()-1)+"개 품목" : ""));
						} else {
							Ext.getCmp("OrderForm").getForm().findField("title").setValue(Ext.getCmp("OrderTitle").getValue());
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
						Ext.getCmp("OrderForm").getForm().submit({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php?action=itemorder&do=add&wno=<?php echo $this->wno; ?>",waitMsg:"데이터를 전송중입니다.",submitEmptyText:false});
					}
				}),
				new Ext.Button({
					text:"취소",
					icon:"<?php echo $this->moduleDir; ?>/images/common/icon_cross.png",
					handler:function() {
						Ext.getCmp("OrderWindow").close();
					}
				})
			]
		}).show();
	}

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"자재발주관리",
		layout:"fit",
		items:[
			new Ext.grid.GridPanel({
				id:"ListPanel",
				border:false,
				autoScroll:true,
				tbar:[
					new Ext.Button({
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_control_left.png",
						text:"이전달",
						handler:function() {
							if (Ext.getCmp("month").selectedIndex == 0) {
								Ext.Msg.show({title:"에러",msg:"이전달 기록이 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
							} else {
								Ext.getCmp("ListPanel").getStore().baseParams.date = Ext.getCmp("month").getStore().getAt(Ext.getCmp("month").selectedIndex-1).get("date");
								Ext.getCmp("month").setValue(Ext.getCmp("ListPanel").getStore().baseParams.date);
								Ext.getCmp("month").selectedIndex = Ext.getCmp("month").selectedIndex - 1;
								Ext.getCmp("ListPanel").getStore().load();
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

									Ext.getCmp("ListPanel").getStore().baseParams.date = Ext.getCmp("month").getValue();
									Ext.getCmp("ListPanel").getStore().load({params:{start:0,limit:30}});
								});
							}},
							select:{fn:function(form) {
								Ext.getCmp("ListPanel").getStore().baseParams.date = form.getValue();
								Ext.getCmp("ListPanel").getStore().load({params:{start:0,limit:30}});
							}}
						}
					}),
					' ',
					new Ext.Button({
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_control_right.png",
						iconAlign:"right",
						text:"다음달",
						handler:function() {
							if (Ext.getCmp("month").selectedIndex+1 == Ext.getCmp("month").getStore().getCount()) {
								Ext.Msg.show({title:"에러",msg:"다음달 기록이 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
							} else {
								Ext.getCmp("ListPanel").getStore().baseParams.date = Ext.getCmp("month").getStore().getAt(Ext.getCmp("month").selectedIndex+1).get("date");
								Ext.getCmp("month").setValue(Ext.getCmp("ListPanel").getStore().baseParams.date);
								Ext.getCmp("month").selectedIndex = Ext.getCmp("month").selectedIndex + 1;
								Ext.getCmp("ListPanel").getStore().load();
							}
						}
					}),
					'-',
					new Ext.Button({
						text:"자재발주요청서작성",
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_paste_plain.png",
						handler:function() {
							WriteOrderFunction();
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
						header:"요청서명",
						dataIndex:"title",
						sortable:false,
						width:450,
						renderer:function(value,p,record) {
							if (record.data.file) {
								return value+'<img src="<?php echo $this->moduleDir; ?>/images/common/icon_bullet_disk.png" style="vertical-align:middle; margin-left:5px;" />';
							} else {
								return value;
							}
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

							sHTML+= '<img src="<?php echo $this->moduleDir; ?>/images/common/icon_cost1_';
							sHTML+= temp[0] == "TRUE" ? "on" : "off";
							sHTML+= '.gif" style="margin-right:1px;" />';

							sHTML+= '<img src="<?php echo $this->moduleDir; ?>/images/common/icon_cost2_';
							sHTML+= temp[1] == "TRUE" ? "on" : "off";
							sHTML+= '.gif" style="margin-right:1px;" />';

							sHTML+= '<img src="<?php echo $this->moduleDir; ?>/images/common/icon_cost3_';
							sHTML+= temp[2] == "TRUE" ? "on" : "off";
							sHTML+= '.gif" style="margin-right:1px;" />';

							return sHTML;
						}
					},{
						header:"상태",
						width:95,
						sortable:true,
						renderer:function(value,p,record) {
							var sHTML = '<div style="text-align:center; font:0/0 arial;">';
							sHTML+= '<img src="<?php echo $this->moduleDir; ?>/images/common/icon_confirm_';
							sHTML+= record.data.is_confirm == "TRUE" ? "on" : "off";
							sHTML+= '.gif" style="margin-right:1px;" />';
							sHTML+= '<img src="<?php echo $this->moduleDir; ?>/images/common/icon_consult_';
							sHTML+= record.data.is_consult == "TRUE" ? "on" : "off";
							sHTML+= '.gif" style="margin-right:1px;" />';
							sHTML+= '<img src="<?php echo $this->moduleDir; ?>/images/common/icon_contract_';
							sHTML+= record.data.is_contract == "TRUE" ? "on" : "off";
							sHTML+= '.gif" style="margin-right:1px;" />';
							sHTML+= '</div>';

							return sHTML;
						}
					},{
						header:"요청일",
						dataIndex:"date",
						sortable:true,
						width:110
					},
					new Ext.grid.CheckboxSelectionModel()
				]),
				sm:new Ext.grid.CheckboxSelectionModel(),
				store:OrderStore,
				loadMask:{msg:"데이터를 로딩중입니다."},
				bbar:new Ext.PagingToolbar({
					pageSize:30,
					store:OrderStore,
					displayInfo:true,
					displayMsg:'{0} - {1} of {2}',
					emptyMsg:"데이터없음"
				}),
				listeners:{
					rowdblclick:{fn:function(grid,idx,e) {
						OrderFunction(grid.getStore().getAt(idx).get("idx"));
					}},
					rowcontextmenu:{fn:function(grid,idx,e) {
						GridContextmenuSelect(grid,idx);

						var data = grid.getStore().getAt(idx);
						var menu = new Ext.menu.Menu();
						menu.add('<b class="menu-title">'+data.get("title")+'</b>');

						menu.add({
							text:"발주요청서명 변경",
							icon:"<?php echo $this->moduleDir; ?>/images/common/icon_table_edit.png",
							handler:function() {
								new Ext.Window({
									id:"TitleWindow",
									title:"발주요청서명 변경",
									width:400,
									modal:true,
									resizeable:false,
									items:[
										new Ext.form.FormPanel({
											id:"TitleForm",
											border:false,
											style:"padding:5px 5px 2px 5px; background:#FFFFFF;",
											errorReader:new Ext.form.XmlErrorReader(),
											fileUpload:true,
											defaults:{hideLabel:true},
											items:[
												new Ext.form.TextField({
													name:"title",
													allowBlank:false,
													width:375,
													value:data.get("title")
												})
											],
											listeners:{actioncomplete:{fn:function(form,action) {
												if (action.type == "submit") {
													Ext.Msg.show({title:"안내",msg:"발주요청서명을 성공적으로 변경하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
													Ext.getCmp("TitleWindow").close();
													Ext.getCmp("ListPanel").getStore().reload();
												}
											}}}
										})
									],
									buttons:[
										new Ext.Button({
											text:"확인",
											icon:"<?php echo $this->moduleDir; ?>/images/common/icon_tick.png",
											handler:function() {
												Ext.getCmp("TitleForm").getForm().submit({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php?wno=<?php echo $this->wno; ?>&action=itemorder&do=title&idx="+data.get("idx"),waitMsg:"발주요청서명을 수정중입니다."});
											}
										}),
										new Ext.Button({
											text:"닫기",
											icon:"<?php echo $this->moduleDir; ?>/images/common/icon_cross.png",
											handler:function() {
												Ext.getCmp("TitleWindow").close();
											}
										})
									],
									listeners:{show:{fn:function() {
										Ext.getCmp("TitleForm").getForm().findField("title").focus(true,100);
									}}}
								}).show();
							}
						});
						menu.add({
							text:"발주요청서 삭제",
							icon:"<?php echo $this->moduleDir; ?>/images/common/icon_table_delete.png",
							handler:function() {
								if (data.get("is_confirm") == "TRUE") {
									Ext.Msg.show({title:"에러",msg:"본사에서 확인한 발주요청은 삭제할 수 없습니다.<br />본사와 협의후에 삭제하여 주시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
									return false;
								}

								Ext.Msg.show({title:"안내",msg:"발주요청서명을 정말 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.QUESTION,fn:function(button) {
									if (button == "ok") {
										Ext.Ajax.request({
											url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php",
											success:function() {
												Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
												Ext.getCmp("ListPanel").getStore().reload();
											},
											failure:function() {
												Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
											},
											headers:{},
											params:{"action":"itemorder","do":"delete","wno":<?php echo $this->wno; ?>,"idx":data.get("idx")}
										});
									}
								}});

							}
						})
						e.stopEvent();
						menu.showAt(e.getXY());
					}}
				}
			})
		]
	});

	OrderStore.on("load",function() {
		SetCookie("iErpMonth",OrderStore.baseParams.date);
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>
<iframe name="downloadFrame" style="display:none;"></iframe>