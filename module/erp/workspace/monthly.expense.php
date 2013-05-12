<script type="text/javascript">
var  test = 1000;
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

	var CheckStore = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:["is_confirm"]
		}),
		baseParams:{"action":"monthly","get":"check","wno":"<?php echo $this->wno; ?>",date:""}
	});

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"경비청구서",
		layout:"fit",
		items:[
			new Ext.grid.EditorGridPanel({
				id:"MonthlyList",
				border:false,
				tbar:[
					new Ext.Button({
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_control_left.png",
						text:"이전달",
						handler:function() {
							if (Ext.getCmp("month").selectedIndex == 0) {
								Ext.Msg.show({title:"에러",msg:"이전달 기록이 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
							} else {
								Ext.getCmp("MonthlyList").getStore().baseParams.date = Ext.getCmp("month").getStore().getAt(Ext.getCmp("month").selectedIndex-1).get("date");
								Ext.getCmp("month").setValue(Ext.getCmp("MonthlyList").getStore().baseParams.date);
								Ext.getCmp("month").selectedIndex = Ext.getCmp("month").selectedIndex - 1;
								Ext.getCmp("MonthlyList").getStore().reload();
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
								Ext.getCmp("MonthlyList").getStore().baseParams.date = form.getValue();
								Ext.getCmp("MonthlyList").getStore().reload();
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
								Ext.getCmp("MonthlyList").getStore().baseParams.date = Ext.getCmp("month").getStore().getAt(Ext.getCmp("month").selectedIndex+1).get("date");
								Ext.getCmp("month").setValue(Ext.getCmp("MonthlyList").getStore().baseParams.date);
								Ext.getCmp("month").selectedIndex = Ext.getCmp("month").selectedIndex + 1;
								Ext.getCmp("MonthlyList").getStore().reload();
							}
						}
					}),
					'-',
					new Ext.Button({
						id:"BtnLoad",
						text:"지출내역불러오기",
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_table_row_insert.png",
						disabled:true,
						handler:function() {
							new Ext.Window({
								id:"LoadItemWindow",
								title:"경비내역불러오기",
								width:980,
								height:550,
								modal:true,
								layout:"fit",
								tbar:[
									new Ext.Button({
										icon:"<?php echo $this->moduleDir; ?>/images/common/icon_control_left.png",
										text:"이전달",
										handler:function() {
											if (Ext.getCmp("load").selectedIndex == 0) {
												Ext.Msg.show({title:"에러",msg:"이전달 기록이 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
											} else {
												Ext.getCmp("LoadItemList").getStore().baseParams.load = Ext.getCmp("load").getStore().getAt(Ext.getCmp("load").selectedIndex-1).get("date");
												Ext.getCmp("load").setValue(Ext.getCmp("LoadItemList").getStore().baseParams.load);
												Ext.getCmp("load").selectedIndex = Ext.getCmp("load").selectedIndex - 1;
												Ext.getCmp("LoadItemList").getStore().reload();
											}
										}
									}),
									' ',
									new Ext.form.ComboBox({
										id:"load",
										store:new Ext.data.Store({
											proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php"}),
											reader:new Ext.data.JsonReader({
												root:"lists",
												totalProperty:"totalCount",
												fields:["date","display"]
											}),
											remoteSort:false,
											sortInfo:{field:"date",direction:"ASC"},
											baseParams:{"action":"month","wno":"<?php echo $this->wno; ?>"}
										}),
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
													form.setValue(Ext.getCmp("month").getValue());
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

													Ext.getCmp("LoadItemList").getStore().baseParams.load = Ext.getCmp("load").getValue();
													Ext.getCmp("LoadItemList").getStore().load();
												});
											}},
											select:{fn:function(form) {
												alert("select");
												Ext.getCmp("LoadItemList").getStore().baseParams.load = form.getValue();
												Ext.getCmp("LoadItemList").getStore().reload();
											}}
										}
									}),
									' ',
									new Ext.Button({
										icon:"<?php echo $this->moduleDir; ?>/images/common/icon_control_right.png",
										iconAlign:"right",
										text:"다음달",
										handler:function() {
											if (Ext.getCmp("load").selectedIndex+1 == Ext.getCmp("load").getStore().getCount()) {
												Ext.Msg.show({title:"에러",msg:"다음달 기록이 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
											} else {
												Ext.getCmp("LoadItemList").getStore().baseParams.load = Ext.getCmp("load").getStore().getAt(Ext.getCmp("load").selectedIndex+1).get("date");
												Ext.getCmp("load").setValue(Ext.getCmp("LoadItemList").getStore().baseParams.load);
												Ext.getCmp("load").selectedIndex = Ext.getCmp("load").selectedIndex + 1;
												Ext.getCmp("LoadItemList").getStore().reload();
											}
										}
									})
								],
								items:[
									new Ext.grid.GridPanel({
										id:"LoadItemList",
										border:false,
										cm:new Ext.grid.ColumnModel([
											new Ext.ux.grid.CheckboxSelectionModel(),
											{
												dataIndex:"gno",
												hidable:false,
												hidden:true
											},{
												header:"분류",
												dataIndex:"type",
												width:50,
												renderer:function(value) {
													if (value == "WORKSPACE") return "현장";
													else return "본사";
												}
											},{
												header:"업체명",
												dataIndex:"cooperation",
												width:100
											},{
												header:"공종그룹",
												dataIndex:"workgroup",
												width:80
											},{
												dataIndex:"tno",
												hidable:false,
												hidden:true
											},{
												header:"공종명",
												dataIndex:"worktype",
												width:100
											},{
												header:"품명",
												dataIndex:"title",
												width:200
											},{
												header:"규격",
												dataIndex:"size",
												width:80
											},{
												header:"단위",
												dataIndex:"unit",
												width:60
											},{
												header:"수량",
												dataIndex:"ea",
												width:60,
												sortable:true,
												renderer:GridNumberFormat
											},{
												header:"단가",
												dataIndex:"cost",
												width:90,
												sortable:true,
												renderer:GridNumberFormat
											},{
												header:"금액",
												dataIndex:"price",
												width:100,
												sortable:true,
												renderer:function(value,p,record) {
													record.data.price = Math.floor(record.data.ea*record.data.cost);
													return GridNumberFormat(record.data.price);
												}
											}
										]),
										sm:new Ext.ux.grid.CheckboxSelectionModel(),
										store:new Ext.data.Store({
											proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php"}),
											reader:new Ext.data.JsonReader({
												root:"lists",
												totalProperty:"totalCount",
												fields:["type","cooperation","cno","repto","gno","tno","workgroup","worktype","itemcode","code","subcode","title","size","unit",{name:"ea",type:"float"},{name:"cost",type:"int"},{name:"price",type:"int"},{name:"prev_ea",type:"float"},{name:"contract_ea",type:"float"},{name:"contract_cost",type:"int"}]
											}),
											remoteSort:false,
											sortInfo:{field:"title",direction:"ASC"},
											baseParams:{"wno":"<?php echo $this->wno; ?>","action":"monthly","get":"expense","mode":"load","date":Ext.getCmp("month").getValue(),"load":""}
										})
									})
								],
								buttons:[
									new Ext.Button({
										text:"추가하기",
										icon:"<?php echo $this->moduleDir; ?>/images/common/icon_tick.png",
										handler:function() {
											var checked = Ext.getCmp("LoadItemList").selModel.getSelections();

											if (checked.length == 0) {
												Ext.Msg.show({title:"에러",msg:"추가할 자재를 선택하세요.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
												return false;
											}

											var insert = new Array();
											for (var i=0, loop=checked.length;i<loop;i++) {
												insert[i] = {"idx":"0","cno":checked[i].get("cno"),"repto":checked[i].get("repto"),"group":checked[i].get("gno")+"-"+checked[i].get("tno"),"cooperation":checked[i].get("cooperation"),"gno":checked[i].get("gno"),"tno":checked[i].get("tno"),"workgroup":checked[i].get("workgroup"),"worktype":checked[i].get("worktype"),"code":checked[i].get("code"),"subcode":checked[i].get("subcode"),"title":checked[i].get("title"),"size":checked[i].get("size"),"unit":checked[i].get("unit"),"contract_ea":checked[i].get("contract_ea"),"contract_cost":checked[i].get("contract_cost"),"prev_ea":checked[i].get("prev_ea"),"ea":checked[i].get("ea"),"cost":checked[i].get("cost")}
											}

											GridInsertRow(Ext.getCmp("MonthlyList"),insert);
											Ext.getCmp("MonthlyList").getStore().clearGrouping();
											if (Ext.getCmp("BtnCooperation").pressed == true) Ext.getCmp("MonthlyList").getStore().groupBy("cooperation");
											else Ext.getCmp("MonthlyList").getStore().groupBy("group");
											Ext.getCmp("LoadItemWindow").close();
										}
									}),
									new Ext.Button({
										text:"취소",
										icon:"<?php echo $this->moduleDir; ?>/images/common/icon_cross.png",
										handler:function() {
											Ext.getCmp("LoadItemWindow").close();
										}
									})
								]
							}).show();
						}
					}),
					new Ext.Button({
						id:"BtnDelete",
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_table_row_delete.png",
						text:"삭제",
						disabled:true,
						handler:function() {
							GridDeleteRow(Ext.getCmp("MonthlyList"));
						}
					}),
					'-',
					new Ext.Button({
						id:"BtnCooperation",
						text:"업체별보기",
						pressed:true,
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_building.png",
						handler:function() {
							Ext.getCmp("BtnCooperation").toggle(true);
							Ext.getCmp("BtnWork").toggle(false);

							Ext.getCmp("MonthlyList").getStore().groupBy("cooperation");
						}
					}),
					' ',
					new Ext.Button({
						id:"BtnWork",
						text:"공종별보기",
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_package.png",
						handler:function() {
							Ext.getCmp("BtnWork").toggle(true);
							Ext.getCmp("BtnCooperation").toggle(false);

							Ext.getCmp("MonthlyList").getStore().groupBy("group");
						}
					}),
					'-',
					new Ext.Button({
						id:"BtnSave",
						text:"변경사항저장하기",
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_report_disk.png",
						disabled:true,
						handler:function() {
							var data = GetGridData(Ext.getCmp("MonthlyList"));

							Ext.Ajax.request({
								url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php",
								success:function() {
									Ext.Msg.show({title:"안내",msg:"성공적으로 저장되었습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO,animEl:"SaveButton"});
									//Ext.getCmp("MonthlyList").getStore().reload();
								},
								failure:function() {
									Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 저장하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
								},
								headers:{},
								params:{"action":"monthly","do":"expense","mode":"save","wno":"<?php echo $this->wno; ?>","date":Ext.getCmp("month").getValue(),"data":data}
							});
						}
					})
				],
				cm:new Ext.grid.ColumnModel([
					new Ext.ux.grid.CheckboxSelectionModel(),
					{
						dataIndex:"group",
						hidden:true,
						hideable:false,
						renderer:function(value,p,record) {
							return record.data.workgroup+" > "+record.data.worktype;
						}
					},{
						dataIndex:"idx",
						hideable:false,
						hidden:true
					},{
						dataIndex:"cno",
						hideable:false,
						hidden:true
					},{
						dataIndex:"repto",
						hideable:false,
						hidden:true
					},{
						dataIndex:"code",
						hideable:false,
						hidden:true
					},{
						dataIndex:"subcode",
						hideable:false,
						hidden:true
					},{
						dataIndex:"gno",
						hidable:false,
						hidden:true
					},{
						dataIndex:"tno",
						hidable:false,
						hidden:true
					},{
						header:"업체명",
						dataIndex:"cooperation",
						hidden:true,
						hideable:false,
						renderer:function(value,p,record) {
							var sHTML = value;
							if (record.data.repto != "0") sHTML+= "(계약)";
							return sHTML;
						}
					},{
						header:"공종그룹",
						dataIndex:"workgroup",
						width:80
					},{
						header:"공종명",
						dataIndex:"worktype",
						width:100
					},{
						header:"품명",
						dataIndex:"title",
						width:200
					},{
						header:"규격",
						dataIndex:"size",
						width:80
					},{
						header:"단위",
						dataIndex:"unit",
						width:60
					},{
						header:"수량",
						dataIndex:"contract_ea",
						width:50,
						renderer:GridNumberFormat
					},{
						header:"단가",
						dataIndex:"contract_cost",
						width:80,
						renderer:GridNumberFormat
					},{
						header:"금액",
						dataIndex:"contract_price",
						width:90,
						renderer:function(value,p,record) {
							record.data.contract_price = Math.floor(record.data.contract_ea*record.data.contract_cost);
							return GridNumberFormat(record.data.contract_price);
						},
						summaryRenderer:GridNumberFormat
					},{
						header:"수량",
						width:50,
						dataIndex:"prev_ea",
						renderer:GridNumberFormat
					},{
						header:"금액",
						width:90,
						dataIndex:"prev_price",
						summaryType:"sum",
						renderer:function(value,p,record) {
							record.data.prev_price = Math.floor(record.data.prev_ea*record.data.cost);
							return GridNumberFormat(record.data.prev_price);
						},
						summaryRenderer:GridNumberFormat
					},{
						header:"수량",
						width:50,
						dataIndex:"ea",
						renderer:GridNumberFormat,
						editor:new Ext.form.NumberField({selectOnFocus:true})
					},{
						header:"단가",
						width:85,
						dataIndex:"cost",
						renderer:GridNumberFormat
					},{
						header:"금액",
						width:90,
						dataIndex:"price",
						summaryType:"sum",
						renderer:function(value,p,record) {
							record.data.price = Math.floor(record.data.ea*record.data.cost);
							return GridNumberFormat(record.data.price);
						},
						summaryRenderer:GridNumberFormat
					},{
						header:"수량",
						width:50,
						dataIndex:"total_ea",
						renderer:function(value,p,record) {
							record.data.total_ea = record.data.prev_ea+record.data.ea;
							return GridNumberFormat(record.data.total_ea);
						}
					},{
						header:"금액",
						width:90,
						dataIndex:"total_price",
						summaryType:"sum",
						renderer:function(value,p,record) {
							record.data.total_price = Math.floor(record.data.total_ea*record.data.cost);
							return GridNumberFormat(record.data.total_price);
						},
						summaryRenderer:GridNumberFormat
					},{
						header:"비고",
						width:100,
						dataIndex:"etc"
					}
				]),
				store:new Ext.data.GroupingStore({
					proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php"}),
					reader:new Ext.data.JsonReader({
						root:"lists",
						totalProperty:"totalCount",
						fields:["idx","group","cno","cooperation","repto","gno","tno","code","subcode","itemcode","workgroup","worktype","title","size","unit",{name:"contract_ea",type:"float"},{name:"contract_cost",type:"int"},{name:"contract_price",type:"int"},{name:"prev_ea",type:"float"},{name:"prev_price",type:"int"},{name:"ea",type:"float"},{name:"cost",type:"int"},{name:"price",type:"int"},{name:"total_ea",type:"float"},{name:"total_price",type:"int"},"etc"]
					}),
					remoteSort:false,
					groupField:"cooperation",
					sortInfo:{field:"idx",direction:"ASC"},
					baseParams:{"wno":"<?php echo $this->wno; ?>","action":"monthly","get":"expense","mode":"list","date":"<?php echo Request('iErpMonth','cookie') != null ? Request('iErpMonth','cookie') : GetTime('Y-m'); ?>"}
				}),
				autoScroll:true,
				trackMouseOver:true,
				plugins:[new Ext.ux.grid.ColumnHeaderGroup({
					rows:[[
						{},
						{},
						{},
						{},
						{},
						{},
						{},
						{},
						{},
						{},
						{header:"품목",colspan:5,align:"center"},
						{header:"계약금액",colspan:3,align:"center"},
						{header:"전회누계",colspan:2,align:"center"},
						{header:"금회기성",colspan:3,align:"center"},
						{header:"누계",colspan:2,align:"center"},
						{},
					]],
					hierarchicalColMenu:true
				}),new Ext.grid.GroupSummary()],
				view:new Ext.grid.GroupingView({
					enableGroupingMenu:false,
					hideGroupedColumn:false,
					showGroupName:false,
					enableNoGroups:false,
					headersDisabled:false
				}),
				listeners:{
				}
			})
		]
	});

	Ext.getCmp("MonthlyList").getStore().on("load",function() {
		SetCookie("iErpMonth",Ext.getCmp("MonthlyList").getStore().baseParams.date);
		CheckStore.baseParams.date = Ext.getCmp("MonthlyList").getStore().baseParams.date;
		CheckStore.load();
	});
	CheckStore.on("load",function() {
		if (CheckStore.getAt(0).get("is_confirm") == "TRUE") {
			Ext.getCmp("BtnLoad").disable();
			Ext.getCmp("BtnDelete").disable();
			Ext.getCmp("BtnSave").disable();
		} else {
			Ext.getCmp("BtnLoad").enable();
			Ext.getCmp("BtnDelete").enable();
			Ext.getCmp("BtnSave").enable();
		}
	});

	Ext.getCmp("MonthlyList").getStore().load();
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>