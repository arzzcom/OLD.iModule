<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"자재입고관리",
		layout:"fit",
		items:[
			new Ext.TabPanel({
				tabPosition:"bottom",
				activeTab:0,
				border:false,
				tbar:[
					new Ext.Button({
						id:"PrevButton",
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_control_left.png",
						text:"이전일",
						handler:function() {
							var today = new Date(Ext.getCmp("today").getValue()).add("d",-1).format("Y-m-d");
							Ext.getCmp("today").setValue(today);

							SetCookie("iErpDate",today);

							Ext.getCmp("WorkspaceList").getStore().baseParams.date = today;
							Ext.getCmp("WorkspaceList").getStore().load();
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

							Ext.getCmp("WorkspaceList").getStore().baseParams.date = today;
							Ext.getCmp("WorkspaceList").getStore().load();
						}}}
					}),
					' ',
					new Ext.Button({
						id:"NextButton",
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_control_right.png",
						iconAlign:"right",
						text:"다음일",
						handler:function() {
							var today = new Date(Ext.getCmp("today").getValue()).add("d",1).format("Y-m-d");
							Ext.getCmp("today").setValue(today);

							SetCookie("iErpDate",today);

							Ext.getCmp("WorkspaceList").getStore().baseParams.date = today;
							Ext.getCmp("WorkspaceList").getStore().load();
						}
					}),
					'-',
					new Ext.Button({
						id:"AddButton",
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_table_row_insert.png",
						text:"추가",
						handler:function() {
							GridInsertRow(Ext.getCmp("WorkspaceList"),{"idx":"0","is_new":"TRUE","group":" ","date":Ext.getCmp("today").getValue(),"order_ea":"0,0,0,0","type":"WORKSPACE","payment":"FALSE"});
						}
					}),
					new Ext.Button({
						id:"DeleteButton",
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_table_row_delete.png",
						text:"삭제",
						handler:function() {
							var checked = Ext.getCmp("WorkspaceList").selModel.getSelections();
							var isMsg = false;
							for (var i=0, loop=checked.length;i<loop;i++) {
								if (checked[i].get("type") == "ITEMORDER") {
									isMsg = true;
									break;
								}
							}
							if (isMsg == true) {
								Ext.Msg.show({title:"안내",msg:"본사발주품목은 삭제할 수 없습니다.<br />해당 항목을 제외하고 삭제하시겠습니까?",buttons:Ext.Msg.OK,icon:Ext.MessageBox.QUESTION,fn:function(button) {
									if (button == "ok") {
										for (var i=0, loop=checked.length;i<loop;i++) {
											if (checked[i].get("type") == "ITEMORDER") {
												Ext.getCmp("WorkspaceList").selModel.deselectRow(checked[i].get("row"));
											}
										}
									}
								}});
							}
							var idxs = new Array();
							var checked = Ext.getCmp("WorkspaceList").selModel.getSelections();
							if (checked.length == 0) {
								Ext.Msg.show({title:"안내",msg:"삭제할 품목이 없습니다. 삭제할 품목을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								return false;
							}

							Ext.Msg.wait("처리중입니다.","Please Wait...");
							for (var i=0, loop=checked.length;i<loop;i++) {
								if (checked[i].get("idx") != "0") {
									idxs.push(checked[i].get("idx"));
								}
								Ext.getCmp("WorkspaceList").getStore().remove(checked[i]);
							}
							if (idxs.length > 0) {
								Ext.Ajax.request({
									url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php",
									success:function() {
										Ext.Msg.show({title:"안내",msg:"성공적으로 삭제되었습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
									},
									failure:function() {
										Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 삭제하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
									},
									headers:{},
									params:{"action":"work","do":"item","wno":"<?php echo $this->wno; ?>","mode":"delete","idx":idxs.join(",")}
								});
							} else {
								Ext.Msg.show({title:"안내",msg:"성공적으로 삭제되었습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
							}
						}
					}),
					'-',
					new Ext.Button({
						id:"SaveButton",
						text:"변경사항 저장하기",
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_report_disk.png",
						handler:function() {
							for (var i=0, loop=Ext.getCmp("WorkspaceList").getStore().getCount();i<loop;i++) {
								var data = Ext.getCmp("WorkspaceList").getStore().getAt(i);
								if (!data.get("gno") || !data.get("tno") || !data.get("title") || !data.get("ea") || !data.get("cost") || !data.get("cooperation")) {
									Ext.Msg.show({title:"에러",msg:"필수항목중 빠진 항목이 있습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
									return false;
								}
							}
							var data = GetGridData(Ext.getCmp("WorkspaceList"));

							Ext.Msg.wait("처리중입니다.","Please Wait...");
							Ext.Ajax.request({
								url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php",
								success:function() {
									Ext.Msg.show({title:"안내",msg:"자재비내역이 성공적으로 저장되었습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO,animEl:"SaveButton"});
									Ext.getCmp("WorkspaceList").getStore().reload();
								},
								failure:function() {
									Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 저장하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
								},
								headers:{},
								params:{"action":"work","do":"item","wno":"<?php echo $this->wno; ?>","mode":"modify","data":data,"date":new Date(Ext.getCmp("today").getValue()).format("Y-m-d")}
							});
						}
					}),
					new Ext.Button({
						id:"IncomeButton",
						text:"입고 및 반출처리",
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_lorry.png",
						handler:function() {
							for (var i=0, loop=Ext.getCmp("OrderList").getStore().getCount();i<loop;i++) {
								if (Ext.getCmp("OrderList").getStore().getAt(i).get("remain_ea") < Ext.getCmp("OrderList").getStore().getAt(i).get("income")-Ext.getCmp("OrderList").getStore().getAt(i).get("outcome")) {
									Ext.Msg.show({title:"에러",msg:"입고처리할 수량이 잔여수량을 초과하는 품목이 있습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
									return false;
								}

								if (Ext.getCmp("OrderList").getStore().getAt(i).get("income_ea") < Ext.getCmp("OrderList").getStore().getAt(i).get("outcome")-Ext.getCmp("OrderList").getStore().getAt(i).get("income")) {
									Ext.Msg.show({title:"안내",msg:"반출처리할 수량이 입고수량을 초과하는 품목이 있습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
									return false;
								}
							}
							var data = GetGridData(Ext.getCmp("OrderList"));

							Ext.Msg.wait("처리중입니다.","Please Wait...");
							Ext.Ajax.request({
								url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php",
								success:function(XML) {
									Ext.Msg.show({title:"안내",msg:"성공적으로 처리하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
									Ext.getCmp("WorkspaceList").getStore().load();
									Ext.getCmp("OrderList").getStore().load();
								},
								failure:function() {
									Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
								},
								headers:{},
								params:{"action":"work","do":"item","mode":"income","data":data,"date":new Date(Ext.getCmp("today").getValue()).format("Y-m-d")}
							});
						}
					})
				],
				items:[
					new Ext.grid.EditorGridPanel({
						id:"WorkspaceList",
						title:"입고내역서",
						cm:new Ext.grid.ColumnModel([
							new Ext.grid.RowNumberer(),
							{
								dataIndex:"group",
								hideable:false
							},{
								dataIndex:"idx",
								hidden:true,
								hideable:false
							},{
								header:"공종그룹",
								dataIndex:"gno",
								width:80,
								renderer:function(value,p,record,row,col,store) {
									return GridWorkgroup(value,p,record,Ext.getCmp("WorkspaceList").getColumnModel().getCellEditor(col,row).field);
								}
							},{
								header:"공종명",
								dataIndex:"tno",
								width:100,
								renderer:function(value,p,record,row,col,store) {
									return GridWorktype(value,p,record,Ext.getCmp("WorkspaceList").getColumnModel().getCellEditor(col,row).field);
								}
							},{
								header:"품명",
								dataIndex:"title",
								width:180,
								renderer:GridContractItemNotFound
							},{
								header:"규격",
								dataIndex:"size",
								width:100,
								renderer:GridContractItemNotFound,
								editor:new Ext.form.TextField({selectOnFocus:true})
							},{
								header:"적요",
								dataIndex:"etc",
								width:200,
								editor:new Ext.form.TextField({selectOnFocus:true})
							},{
								header:"비목",
								width:40,
								renderer:function(value) {
									return "자재";
								}
							},{
								header:"지불처",
								dataIndex:"cooperation",
								width:100,
								editor:new Ext.form.TextField({selectOnFocus:true})
							},{
								header:"수량",
								dataIndex:"ea",
								width:60,
								sortable:true,
								renderer:GridItemOrderEA,
								editor:new Ext.form.NumberField({selectOnFocus:true})
							},{
								header:"단위",
								dataIndex:"unit",
								width:40,
								renderer:GridContractItemNotFound,
								editor:new Ext.form.TextField({selectOnFocus:true})
							},{
								header:"단가",
								dataIndex:"cost",
								width:80,
								sortable:true,
								summaryType:"sum",
								renderer:function(value,p,record) {
									return GridItemAvgCost(value,record.data.avgcost);
								},
								editor:new Ext.form.NumberField({selectOnFocus:true})
							},{
								header:"금액",
								dataIndex:"price",
								width:90,
								sortable:true,
								renderer:function(value,p,record) {
									record.data.price = Math.floor(record.data.ea*record.data.cost);
									return GridNumberFormat(record.data.price);
								},
								summaryType:"sum",
								summaryRenderer:GridNumberFormat
							},{
								header:"지불여부",
								dataIndex:"payment",
								width:80,
								sortable:true,
								renderer:function(value) {
									if (value == "TRUE") return "지불";
									else return "미불";
								},
								editor:new Ext.form.ComboBox({
									typeAhead:true,
									triggerAction:"all",
									lazyRender:true,
									listClass:"x-combo-list-small",
									store:new Ext.data.SimpleStore({
										fields:["payment","display"],
										data:[["TRUE","지불"],["FALSE","미불"]]
									}),
									editable:false,
									mode:"local",
									displayField:"display",
									valueField:"payment"
								})
							},{
								header:"형태",
								dataIndex:"type",
								width:50,
								renderer:function(value) {
									if (value == "WORKSPACE") return "현장";
									else return "본사";
								}
							},
							new Ext.ux.grid.CheckboxSelectionModel()
						]),
						sm:new Ext.ux.grid.CheckboxSelectionModel(),
						store:new Ext.data.GroupingStore({
							proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php"}),
							reader:new Ext.data.JsonReader({
								root:"lists",
								totalProperty:"totalCount",
								fields:["idx","is_new","group","type","gno","tno","workgroup","worktype","itemcode","code","subcode","title","size","unit",{name:"ea",type:"float"},"order_ea",{name:"cost",type:"int"},{name:"price",type:"int"},"payment","cooperation","avgcost","etc"]
							}),
							remoteSort:false,
							groupField:"group",
							sortInfo:{field:"title",direction:"ASC"},
							baseParams:{"wno":"<?php echo $this->wno; ?>","action":"work","get":"item","mode":"workspace","date":new Date(Ext.getCmp("today").getValue()).format("Y-m-d")}
						}),
						trackMouseOver:true,
						plugins:new Ext.grid.GroupSummary(),
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
								GridEditorAutoMatchItem(Ext.getCmp("WorkspaceList"),<?php echo $this->wno; ?>);
								GridEditorWorkgroupType(Ext.getCmp("WorkspaceList"),<?php echo $this->wno; ?>);
							}},
							beforeedit:{fn:function(object) {
								if (object.record.data.type == "ITEMORDER") return false;
								GridEditorBeforeWorkgroupType(object);
							}},
							afteredit:{fn:function(object) {
								GridAutoMatchItem(object,<?php echo $this->wno; ?>);
								GridEditorAfterWorkgroupType(object);

								if (object.field == "ea" || object.field == "cost") {
									if (!object.value) object.grid.getStore().getAt(object.row).set(object.field,0);
								}
							}}
						}
					}),
					new Ext.grid.EditorGridPanel({
						id:"OrderList",
						title:"본사발주입고처리",
						border:false,
						autoScroll:true,
						cm:new Ext.grid.ColumnModel([
							new Ext.grid.RowNumberer(),
							{
								dataIndex:"idx",
								hidden:true,
								hideable:false
							},{
								header:"요청서명",
								dataIndex:"group",
								sortable:false,
								width:450
							},{
								header:"공종그룹",
								dataIndex:"workgroup",
								width:80,
								summaryType:"count",
								summaryRenderer:function(value) {
									return '<span style="font-family:tahoma; font-size:10px;">Total '+GetNumberFormat(value)+' Item'+(value > 1 ? 's' : '')+'</span>';
								}
							},{
								header:"공종명",
								dataIndex:"worktype",
								width:100,
								sortable:false
							},{
								header:"품명",
								dataIndex:"title",
								width:180,
								renderer:GridContractItemNotFound
							},{
								header:"규격",
								dataIndex:"size",
								width:100,
								renderer:GridContractItemNotFound
							},{
								header:"단위",
								dataIndex:"unit",
								width:60,
								renderer:GridContractItemNotFound
							},{
								header:"발주수량",
								dataIndex:"order_ea",
								width:60,
								sortable:true,
								summaryType:"sum",
								renderer:GridNumberFormat
							},{
								header:"입고수량",
								dataIndex:"income_ea",
								width:60,
								sortable:true,
								summaryType:"sum",
								renderer:GridNumberFormat
							},{
								header:"잔여수량",
								dataIndex:"remain_ea",
								width:60,
								sortable:true,
								summaryType:"sum",
								renderer:function(value,p,record) {
									record.data.remain_ea = record.data.order_ea - record.data.income_ea;
									return GridNumberFormat(record.data.remain_ea);
								}
							},{
								header:"입고처리",
								dataIndex:"income",
								width:60,
								sortable:true,
								summaryType:"sum",
								renderer:function(value,p,record) {
									if (record.data.remain_ea < value-record.data.outcome) {
										return '<div style="color:#FF0000; text-align:right; font-family:arial;">'+GetNumberFormat(value)+'</div>';
									} else {
										return GridNumberFormat(value);
									}
								},
								editor:new Ext.form.NumberField({selectOnFocus:true})
							},{
								header:"반출처리",
								dataIndex:"outcome",
								width:60,
								sortable:true,
								summaryType:"sum",
								renderer:function(value,p,record) {
									if (record.data.income_ea < value-record.data.income) {
										return '<div style="color:#FF0000; text-align:right; font-family:arial;">'+GetNumberFormat(value)+'</div>';
									} else {
										return GridNumberFormat(value);
									}
								},
								editor:new Ext.form.NumberField({selectOnFocus:true})
							}
						]),
						plugins:new Ext.ux.grid.GroupSummary(),
						store:new Ext.data.GroupingStore({
							proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php"}),
							reader:new Ext.data.JsonReader({
								root:"lists",
								totalProperty:"totalCount",
								fields:[{name:"idx",type:"int"},"repto","group","code","workgroup","worktype","code","title","size","unit",{name:"order_ea",type:"float"},{name:"income_ea",type:"float"},{name:"income",type:"float"},{name:"outcome",type:"float"},{name:"cost1",type:"int"},{name:"cost2",type:"int"},{name:"cost3",type:"int"},"date"]
							}),
							remoteSort:false,
							sortInfo:{field:"date",direction:"ASC"},
							groupField:"group",
							baseParams:{"wno":"<?php echo $this->wno; ?>","action":"work","get":"item","mode":"order"}
						}),
						trackMouseOver:true,
						view:new Ext.grid.GroupingView({
							enableGroupingMenu:false,
							hideGroupedColumn:true,
							showGroupName:false,
							enableNoGroups:false,
							headersDisabled:false
						})
					})
				],
				listeners:{
					tabchange:{fn:function(tabs,tab) {
						if (tab.getId() == "WorkspaceList") {
							Ext.getCmp("SaveButton").show();
							Ext.getCmp("IncomeButton").hide();

							Ext.getCmp("PrevButton").enable();
							Ext.getCmp("today").enable();
							Ext.getCmp("NextButton").enable();
							Ext.getCmp("AddButton").enable();
							Ext.getCmp("DeleteButton").enable();
						} else {
							Ext.getCmp("SaveButton").hide();
							Ext.getCmp("IncomeButton").show();

							Ext.getCmp("PrevButton").disable();
							Ext.getCmp("today").disable();
							Ext.getCmp("NextButton").disable();
							Ext.getCmp("AddButton").disable();
							Ext.getCmp("DeleteButton").disable();
						}
					}}
				}
			})
		]
	});

	Ext.getCmp("WorkspaceList").getStore().load();
	Ext.getCmp("OrderList").getStore().load();
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>