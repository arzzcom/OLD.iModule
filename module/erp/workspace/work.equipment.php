<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"장비입고관리",
		layout:"fit",
		items:[
			new Ext.grid.EditorGridPanel({
				id:"PaymentList",
				border:false,
				tbar:[
					new Ext.Button({
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_control_left.png",
						text:"이전일",
						handler:function() {
							var today = new Date(Ext.getCmp("today").getValue()).add("d",-1).format("Y-m-d");
							Ext.getCmp("today").setValue(today);
		
							SetCookie("iErpDate",today);
		
							Ext.getCmp("PaymentList").getStore().baseParams.date = today;
							Ext.getCmp("PaymentList").getStore().load();
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
		
							Ext.getCmp("PaymentList").getStore().baseParams.date = today;
							Ext.getCmp("PaymentList").getStore().load();
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
							
							Ext.getCmp("PaymentList").getStore().baseParams.date = today;
							Ext.getCmp("PaymentList").getStore().load();
						}
					}),
					'-',
					new Ext.Button({
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_table_row_insert.png",
						text:"추가",
						handler:function() {
							GridInsertRow(Ext.getCmp("PaymentList"),{"idx":"0","is_new":"TRUE","group":" ","date":Ext.getCmp("today").getValue(),"order_ea":"0,0,0,0","payment":"FALSE"});
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
									params:{"action":"work","do":"equipment","wno":"<?php echo $this->wno; ?>","mode":"delete","idx":idxs.join(",")}
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
								if (!data.get("gno") || !data.get("tno") || !data.get("title") || !data.get("ea") || !data.get("cost")) {
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
								params:{"action":"work","do":"equipment","wno":"<?php echo $this->wno; ?>","mode":"modify","data":data,"date":new Date(Ext.getCmp("today").getValue()).format("Y-m-d")}
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
						header:"공종그룹",
						dataIndex:"gno",
						width:80,
						renderer:function(value,p,record,row,col,store) {
							return GridWorkgroup(value,p,record,Ext.getCmp("PaymentList").getColumnModel().getCellEditor(col,row).field);
						}
					},{
						header:"공종명",
						dataIndex:"tno",
						width:100,
						renderer:function(value,p,record,row,col,store) {
							return GridWorktype(value,p,record,Ext.getCmp("PaymentList").getColumnModel().getCellEditor(col,row).field);
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
						sortable:false,
						width:40,
						renderer:function() {
							return "장비";
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
					baseParams:{"wno":"<?php echo $this->wno; ?>","action":"work","get":"equipment","date":new Date(Ext.getCmp("today").getValue()).format("Y-m-d")}
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
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>