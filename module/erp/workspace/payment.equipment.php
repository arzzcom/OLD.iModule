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

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"장비비관리",
		layout:"fit",
		items:[
			new Ext.grid.EditorGridPanel({
				id:"PaymentList",
				border:false,
				tbar:[
					new Ext.Button({
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_control_left.png",
						text:"이전달",
						handler:function() {
							if (Ext.getCmp("month").selectedIndex == 0) {
								Ext.Msg.show({title:"에러",msg:"이전달 기록이 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
							} else {
								Ext.getCmp("PaymentList").getStore().baseParams.date = Ext.getCmp("month").getStore().getAt(Ext.getCmp("month").selectedIndex-1).get("date");
								Ext.getCmp("month").setValue(Ext.getCmp("PaymentList").getStore().baseParams.date);
								Ext.getCmp("month").selectedIndex = Ext.getCmp("month").selectedIndex - 1;
								Ext.getCmp("PaymentList").getStore().reload();
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
									}
								});
							}},
							select:{fn:function(form) {
								Ext.getCmp("PaymentList").getStore().baseParams.date = form.getValue();
								Ext.getCmp("PaymentList").getStore().reload();
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
								Ext.getCmp("PaymentList").getStore().baseParams.date = Ext.getCmp("month").getStore().getAt(Ext.getCmp("month").selectedIndex+1).get("date");
								Ext.getCmp("month").setValue(Ext.getCmp("PaymentList").getStore().baseParams.date);
								Ext.getCmp("month").selectedIndex = Ext.getCmp("month").selectedIndex + 1;
								Ext.getCmp("PaymentList").getStore().reload();
							}
						}
					}),
					'-',
					new Ext.Button({
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_table_row_insert.png",
						text:"추가",
						handler:function() {
							if (Ext.getCmp("month").getValue() == new Date().format("Y-m")) {
								var date = new Date().format("Y-m-d");
							} else {
								var date = Ext.getCmp("month").getValue()+"-"+new Date(Ext.getCmp("month").getValue()+"-01").format("t");
							}
							GridInsertRow(Ext.getCmp("PaymentList"),{"idx":"0","is_new":"TRUE","group":" ","date":date,"order_ea":"0,0,0,0","payment":"FALSE"});
						}
					}),
					new Ext.Button({
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_table_row_delete.png",
						text:"삭제",
						handler:function() {
							var idxs = new Array();
							var checked = Ext.getCmp("PaymentList").selModel.getSelections();
							if (checked.length == 0) {
								Ext.Msg.show({title:"안내",msg:"삭제할 품목이 없습니다. 삭제할 품목을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								return false;
							}
							
							Ext.Msg.wait("처리중입니다.","Please Wait...");
							for (var i=0, loop=checked.length;i<loop;i++) {
								if (checked[i].get("idx") != "0") {
									idxs.push(checked[i].get("idx"));
								}
								Ext.getCmp("PaymentList").getStore().remove(checked[i]);
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
									params:{"action":"payment","do":"equipment","wno":"<?php echo $this->wno; ?>","mode":"delete","idx":idxs.join(",")}
								});
							} else {
								Ext.Msg.show({title:"안내",msg:"성공적으로 삭제되었습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
							}
						}
					}),
					'-',
					new Ext.Button({
						text:"변경사항 저장하기",
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_report_disk.png",
						handler:function() {
							for (var i=0, loop=Ext.getCmp("PaymentList").getStore().getCount();i<loop;i++) {
								var data = Ext.getCmp("PaymentList").getStore().getAt(i);
								if (!data.get("date") || !data.get("gno") || !data.get("tno") || !data.get("title") || !data.get("ea") || !data.get("cost")) {
									Ext.Msg.show({title:"에러",msg:"필수항목중 빠진 항목이 있습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
									return false;
								}
							}
							var data = GetGridData(Ext.getCmp("PaymentList"));

							Ext.Msg.wait("처리중입니다.","Please Wait...");
							Ext.Ajax.request({
								url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php",
								success:function() {
									Ext.Msg.show({title:"안내",msg:"장비비내역이 성공적으로 저장되었습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO,animEl:"SaveButton"});
									Ext.getCmp("PaymentList").getStore().reload();
								},
								failure:function() {
									Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 저장하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
								},
								headers:{},
								params:{"action":"payment","do":"equipment","wno":"<?php echo $this->wno; ?>","mode":"modify","data":data}
							});
						}
					})
				],
				cm:new Ext.grid.ColumnModel([
					new Ext.grid.RowNumberer(),
					{
						dataIndex:"group",
						hideable:false
					},{
						dataIndex:"row",
						hidden:true,
						hideable:false,
						renderer:function(value,p,record,row) {
							return record.data.row = row;
						}
					},{
						dataIndex:"idx",
						hidden:true,
						hideable:false
					},{
						header:"날짜",
						dataIndex:"date",
						width:100,
						renderer:GridDateFormat
					},{
						header:"공종그룹",
						dataIndex:"gno",
						width:100,
						renderer:function(value,p,record,row,col,store) {
							return GridWorkgroup(value,p,record,Ext.getCmp("PaymentList").getColumnModel().getCellEditor(col,row).field);
						}
					},{
						header:"공종명",
						dataIndex:"tno",
						width:120,
						renderer:function(value,p,record,row,col,store) {
							return GridWorktype(value,p,record,Ext.getCmp("PaymentList").getColumnModel().getCellEditor(col,row).field);
						}
					},{
						header:"품명",
						dataIndex:"title",
						width:250,
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
						renderer:GridContractItemNotFound,
						editor:new Ext.form.TextField({selectOnFocus:true})
					},{
						header:"적요",
						dataIndex:"etc",
						width:120,
						editor:new Ext.form.TextField({selectOnFocus:true})
					},{
						header:"수량",
						dataIndex:"ea",
						width:60,
						sortable:true,
						renderer:GridItemOrderEA,
						editor:new Ext.form.NumberField({selectOnFocus:true})
					},{
						header:"장비단가",
						dataIndex:"cost",
						width:80,
						sortable:true,
						summaryType:"sum",
						renderer:function(value,p,record) {
							return GridItemAvgCost(value,record.data.avgcost);
						},
						editor:new Ext.form.NumberField({selectOnFocus:true})
					},{
						header:"장비비",
						dataIndex:"price",
						width:80,
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
						header:"발주업체",
						dataIndex:"cooperation",
						width:150,
						editor:new Ext.form.TextField({selectOnFocus:true})
					},
					new Ext.ux.grid.CheckboxSelectionModel()
				]),
				sm:new Ext.ux.grid.CheckboxSelectionModel(),
				store:new Ext.data.GroupingStore({
					proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php"}),
					reader:new Ext.data.JsonReader({
						root:"lists",
						totalProperty:"totalCount",
						fields:["idx","is_new","group","date","gno","tno","workgroup","worktype","itemcode","code","subcode","title","size","unit",{name:"ea",type:"float"},"order_ea",{name:"cost",type:"int"},{name:"price",type:"int"},"payment","cooperation","avgcost","etc"]
					}),
					remoteSort:false,
					groupField:"group",
					sortInfo:{field:"date",direction:"ASC"},
					baseParams:{"wno":"<?php echo $this->wno; ?>","action":"payment","get":"equipment","date":"<?php echo Request('iErpMonth','cookie') != null ? Request('iErpMonth','cookie') : GetTime('Y-m'); ?>"}
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
						GridEditorAutoMatchItem(Ext.getCmp("PaymentList"),<?php echo $this->wno; ?>);
						GridEditorWorkgroupType(Ext.getCmp("PaymentList"),<?php echo $this->wno; ?>);
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
			})
		]
	});

	Ext.getCmp("PaymentList").getStore().load();

	Ext.getCmp("PaymentList").getStore().on("load",function() {
	var thisMonth = new Date(Ext.getCmp("month").getValue()+"-01");
		Ext.getCmp("PaymentList").getColumnModel().setEditor(Ext.getCmp("PaymentList").getColumnModel().findColumnIndex("date"),new Ext.grid.GridEditor(
			new Ext.form.DateField({
				minValue:thisMonth.format("Y-m-d"),
				maxValue:new Date(thisMonth.format("Y-m")+"-"+thisMonth.format("t")).format("Y-m-d"),
				value:new Date().format("Y-m-d"),
				format:"Y-m-d"
			})
		));
		SetCookie("iErpMonth",Ext.getCmp("PaymentList").getStore().baseParams.date);
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>