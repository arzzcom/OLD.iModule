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
		title:"하도급기성관리",
		layout:"fit",
		items:[
			new Ext.grid.GridPanel({
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
					})
				],
				cm:new Ext.grid.ColumnModel([
					new Ext.grid.RowNumberer(),
					{
						dataIndex:"group",
						hideable:false
					},{
						header:"업체명",
						dataIndex:"cooperation",
						width:120
					},{
						header:"계약명",
						dataIndex:"title",
						width:150
					},{
						header:"도급금액",
						dataIndex:"original",
						width:85,
						renderer:GridNumberFormat
					},{
						header:"공급가액",
						width:85,
						dataIndex:"contract",
						summaryType:"sum",
						renderer:GridNumberFormat
					},{
						header:"부가세",
						width:75,
						dataIndex:"contract_tax",
						summaryType:"sum",
						renderer:function(value,p,record) {
							record.data.contract_tax = Math.floor(record.data.contract*0.1);
							return GridNumberFormat(record.data.contract_tax);
						}
					},{
						header:"소계",
						width:85,
						dataIndex:"contract_total",
						summaryType:"sum",
						renderer:function(value,p,record) {
							record.data.contract_total = record.data.contract+record.data.contract_tax;
							return GridNumberFormat(record.data.contract_total);
						}
					},{
						header:"공급가액",
						width:85,
						dataIndex:"prevmonthly",
						summaryType:"sum",
						renderer:GridNumberFormat
					},{
						header:"부가세",
						width:75,
						dataIndex:"prevmonthly_tax",
						summaryType:"sum",
						renderer:function(value,p,record) {
							record.data.prevmonthly_tax = Math.floor(record.data.prevmonthly*0.1);
							return GridNumberFormat(record.data.prevmonthly_tax);
						}
					},{
						header:"소계",
						width:85,
						dataIndex:"prevmonthly_total",
						summaryType:"sum",
						renderer:function(value,p,record) {
							record.data.prevmonthly_total = record.data.prevmonthly+record.data.prevmonthly_tax;
							return GridNumberFormat(record.data.prevmonthly_total);
						}
					},{
						header:"공급가액",
						width:85,
						dataIndex:"monthly",
						summaryType:"sum",
						renderer:GridNumberFormat
					},{
						header:"부가세",
						width:75,
						dataIndex:"monthly_tax",
						summaryType:"sum",
						renderer:function(value,p,record) {
							record.data.monthly_tax = Math.floor(record.data.monthly*0.1);
							return GridNumberFormat(record.data.monthly_tax);
						}
					},{
						header:"소계",
						width:85,
						dataIndex:"monthly_total",
						summaryType:"sum",
						renderer:function(value,p,record) {
							record.data.monthly_total = record.data.monthly+record.data.monthly_tax;
							return GridNumberFormat(record.data.monthly_total);
						}
					},{
						header:"공급가액",
						width:85,
						dataIndex:"sum",
						summaryType:"sum",
						renderer:function(value,p,record) {
							record.data.sum = record.data.prevmonthly+record.data.monthly;
							return GridNumberFormat(record.data.sum);
						}
					},{
						header:"부가세",
						width:75,
						dataIndex:"sum_tax",
						summaryType:"sum",
						renderer:function(value,p,record) {
							record.data.sum_tax = Math.floor(record.data.sum*0.1);
							return GridNumberFormat(record.data.sum_tax);
						}
					},{
						header:"소계",
						width:85,
						dataIndex:"sum_total",
						summaryType:"sum",
						renderer:function(value,p,record) {
							record.data.sum_total = record.data.sum+record.data.sum_tax;
							return GridNumberFormat(record.data.sum_total);
						}
					}
				]),
				store:new Ext.data.GroupingStore({
					proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php"}),
					reader:new Ext.data.JsonReader({
						root:"lists",
						totalProperty:"totalCount",
						fields:["idx","group","cooperation","title",{name:"original",type:"int"},{name:"contract",type:"int"},{name:"prevmonthly",type:"int"},{name:"monthly",type:"int"},{name:"sum",type:"int"}]
					}),
					remoteSort:false,
					groupField:"group",
					sortInfo:{field:"cooperation",direction:"ASC"},
					baseParams:{"wno":"<?php echo $this->wno; ?>","action":"monthly","get":"outsourcing","mode":"list","date":"<?php echo Request('iErpMonth','cookie') != null ? Request('iErpMonth','cookie') : GetTime('Y-m'); ?>"}
				}),
				autoScroll:true,
				trackMouseOver:true,
				plugins:[new Ext.ux.grid.ColumnHeaderGroup({
					rows:[[
						{},
						{},
						{header:"하도급정보",colspan:3,align:"center"},
						{header:"하도급금액",colspan:3,align:"center"},
						{header:"전회누계",colspan:3,align:"center"},
						{header:"금회기성",colspan:3,align:"center"},
						{header:"누계",colspan:3,align:"center"}
					]],
					hierarchicalColMenu:true
				}),new Ext.grid.GroupSummary()],
				view:new Ext.grid.GroupingView({
					enableGroupingMenu:false,
					hideGroupedColumn:true,
					showGroupName:false,
					enableNoGroups:false,
					headersDisabled:false,
					showGroupHeader:false
				}),
				listeners:{
					rowdblclick:{fn:function(grid,idx,e) {
						var data = grid.getStore().getAt(idx);
						new Ext.Window({
							id:"MonthlyViewWindow",
							title:"["+data.get("cooperation")+"] "+data.get("title"),
							width:980,
							height:550,
							modal:true,
							layout:"fit",
							items:[
								new Ext.grid.EditorGridPanel({
									id:"MonthlyViewList",
									border:false,
									tbar:[
										new Ext.Button({
											text:"금액일괄수정",
											icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_calculator.png",
											handler:function() {
												new Ext.Window({
													id:"CalculatorWindow",
													title:"금액일괄수정",
													width:300,
													layout:"fit",
													modal:true,
													resizable:false,
													items:[
														new Ext.form.FormPanel({
															id:"CalculatorForm",
															border:false,
															style:"padding:10px; background:#FFFFFF;",
															labelAlign:"right",
															labelWidth:120,
															autoWidth:true,
															autoHeight:true,
															errorReader:new Ext.form.XmlErrorReader(),
															items:[
																new Ext.ux.form.SpinnerField({
																	fieldLabel:"계약수량대비(%)",
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
																Ext.Msg.show({title:"확인",msg:"계약수량대비 자동으로 기성금액을 계산하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
																	if (button == "ok") {
																		for (var i=0, loop=Ext.getCmp("MonthlyViewList").getStore().getCount();i<loop;i++) {
																			Ext.getCmp("MonthlyViewList").getStore().getAt(i).set("monthly_cost",Ext.getCmp("MonthlyViewList").getStore().getAt(i).get("contract_cost"));
																			var ea = Ext.getCmp("MonthlyViewList").getStore().getAt(i).get("contract_ea")*Ext.getCmp("CalculatorForm").getForm().findField("percent").getValue()/100;
																			Ext.getCmp("MonthlyViewList").getStore().getAt(i).set("monthly_ea",(Math.floor(ea) != ea ? ea.toFixed(2) : ea));
																		}

																		Ext.getCmp("CalculatorWindow").close();
																	}
																}});
															}
														}),
														new Ext.Button({
															text:"취소",
															icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
															handler:function() {
																Ext.getCmp("CalculatorWindow").close();
															}
														})
													]
												}).show();
											}
										})
									],
									cm:new Ext.grid.ColumnModel([
										{
											dataIndex:"group",
											summaryType:"data",
											hideable:false
										},{
											dataIndex:"itemcode",
											hidden:true,
											hideable:false
										},{
											dataIndex:"code",
											hidden:true,
											hideable:false
										},{
											dataIndex:"subcode",
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
											width:200,
											summaryType:"data",
											summaryRenderer:function(value,p,record) {
												var lastRow = Ext.getCmp("MonthlyViewList").getStore().getAt(Ext.getCmp("MonthlyViewList").getStore().getCount()-1);
												if (lastRow.get("group") == record.data.group) {
													return '<div>소계</div><div class="x-grid3-summary-double">계</div>';
												} else {
													return "소계";
												}
											}
										},{
											header:"규격",
											dataIndex:"size",
											width:80
										},{
											header:"단위",
											dataIndex:"unit",
											width:50
										},{
											header:"수량",
											dataIndex:"contract_ea",
											width:50,
											renderer:GridNumberFormat
										},{
											header:"단가",
											dataIndex:"contract_cost",
											width:75,
											renderer:GridNumberFormat,
											summaryType:"sum",
											summaryRenderer:function(value,p,record) {
												var lastRow = Ext.getCmp("MonthlyViewList").getStore().getAt(Ext.getCmp("MonthlyViewList").getStore().getCount()-1);
												if (lastRow.get("group") == record.data.group) {
													return '<div>'+GridNumberFormat(value)+'</div><div class="x-grid3-summary-double">'+GridNumberFormat(Ext.getCmp("MonthlyViewList").getStore().sum("contract_cost"))+'</div>';
												} else {
													return GridNumberFormat(value);
												}
											}
										},{
											header:"금액",
											dataIndex:"contract_total",
											width:80,
											renderer:function(value,p,record) {
												record.data.contract_total = Math.floor(record.data.contract_cost*record.data.contract_ea);
												return GridNumberFormat(record.data.contract_total);
											},
											summaryType:"sum",
											summaryRenderer:function(value,p,record) {
												var lastRow = Ext.getCmp("MonthlyViewList").getStore().getAt(Ext.getCmp("MonthlyViewList").getStore().getCount()-1);
												if (lastRow.get("group") == record.data.group) {
													return '<div>'+GridNumberFormat(value)+'</div><div class="x-grid3-summary-double">'+GridNumberFormat(Ext.getCmp("MonthlyViewList").getStore().sum("contract_total"))+'</div>';
												} else {
													return GridNumberFormat(value);
												}
											}
										},{
											header:"수량",
											dataIndex:"prevmonthly_ea",
											width:50,
											renderer:GridNumberFormat
										},{
											header:"단가",
											dataIndex:"prevmonthly_cost",
											width:75,
											renderer:GridNumberFormat,
											summaryType:"sum",
											summaryRenderer:function(value,p,record) {
												var lastRow = Ext.getCmp("MonthlyViewList").getStore().getAt(Ext.getCmp("MonthlyViewList").getStore().getCount()-1);
												if (lastRow.get("group") == record.data.group) {
													return '<div>'+GridNumberFormat(value)+'</div><div class="x-grid3-summary-double">'+GridNumberFormat(Ext.getCmp("MonthlyViewList").getStore().sum("prevmonthly_cost"))+'</div>';
												} else {
													return GridNumberFormat(value);
												}
											}
										},{
											header:"금액",
											dataIndex:"prevmonthly_total",
											width:80,
											renderer:function(value,p,record) {
												record.data.prevmonthly_total = Math.floor(record.data.prevmonthly_cost*record.data.prevmonthly_ea);
												return GridNumberFormat(record.data.prevmonthly_total);
											},
											summaryType:"sum",
											summaryRenderer:function(value,p,record) {
												var lastRow = Ext.getCmp("MonthlyViewList").getStore().getAt(Ext.getCmp("MonthlyViewList").getStore().getCount()-1);
												if (lastRow.get("group") == record.data.group) {
													return '<div>'+GridNumberFormat(value)+'</div><div class="x-grid3-summary-double">'+GridNumberFormat(Ext.getCmp("MonthlyViewList").getStore().sum("prevmonthly_total"))+'</div>';
												} else {
													return GridNumberFormat(value);
												}
											}
										},{
											header:"수량",
											dataIndex:"monthly_ea",
											width:50,
											renderer:GridNumberFormat,
											editor:new Ext.form.NumberField({selectOnFocus:true})
										},{
											header:"단가",
											dataIndex:"monthly_cost",
											width:75,
											renderer:GridNumberFormat,
											editor:new Ext.form.NumberField({selectOnFocus:true}),
											summaryType:"sum",
											summaryRenderer:function(value,p,record) {
												var lastRow = Ext.getCmp("MonthlyViewList").getStore().getAt(Ext.getCmp("MonthlyViewList").getStore().getCount()-1);
												if (lastRow.get("group") == record.data.group) {
													return '<div>'+GridNumberFormat(value)+'</div><div class="x-grid3-summary-double">'+GridNumberFormat(Ext.getCmp("MonthlyViewList").getStore().sum("monthly_cost"))+'</div>';
												} else {
													return GridNumberFormat(value);
												}
											}
										},{
											header:"금액",
											dataIndex:"monthly_total",
											width:80,
											renderer:function(value,p,record) {
												record.data.monthly_total = Math.floor(record.data.monthly_cost*record.data.monthly_ea);
												return GridNumberFormat(record.data.monthly_total);
											},
											summaryType:"sum",
											summaryRenderer:function(value,p,record) {
												var lastRow = Ext.getCmp("MonthlyViewList").getStore().getAt(Ext.getCmp("MonthlyViewList").getStore().getCount()-1);
												if (lastRow.get("group") == record.data.group) {
													return '<div>'+GridNumberFormat(value)+'</div><div class="x-grid3-summary-double">'+GridNumberFormat(Ext.getCmp("MonthlyViewList").getStore().sum("monthly_total"))+'</div>';
												} else {
													return GridNumberFormat(value);
												}
											}
										}
									]),
									store:new Ext.data.GroupingStore({
										proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php"}),
										reader:new Ext.data.JsonReader({
											root:"lists",
											totalProperty:"totalCount",
											fields:["group","gno","tno","itemcode","code","subcode","title","size","unit",{name:"contract_ea",type:"float"},{name:"contract_cost",type:"int"},{name:"contract_total",type:"int"},{name:"prevmonthly_ea",type:"float"},{name:"prevmonthly_cost",type:"int"},{name:"prevmonthly_total",type:"int"},{name:"monthly_ea",type:"float"},{name:"monthly_cost",type:"int"},{name:"monthly_total",type:"int"},{name:"sort",type:"int"}]
										}),
										remoteSort:false,
										groupField:"group",
										sortInfo:{field:"sort",direction:"ASC"},
										baseParams:{"wno":"<?php echo $this->wno; ?>","action":"monthly","get":"outsourcing","mode":"item","date":Ext.getCmp("month").getValue(),"idx":data.get("idx")}
									}),
									trackMouseOver:true,
									plugins:[new Ext.ux.grid.ColumnHeaderGroup({
										rows:[[
											{},
											{},
											{},
											{},
											{},
											{},
											{header:"품목",colspan:3,align:"center"},
											{header:"하도급금액",colspan:3,align:"center"},
											{header:"전회누계",colspan:3,align:"center"},
											{header:"기성금액",colspan:3,align:"center"}
										]],
										hierarchicalColMenu:true
									}),new Ext.grid.GroupSummary()],
									view:new Ext.grid.GroupingView({
										enableGroupingMenu:false,
										hideGroupedColumn:true,
										showGroupName:false,
										enableNoGroups:false,
										headersDisabled:false
									})
								})
							],
							buttons:[
								new Ext.Button({
									text:"확인",
									icon:"<?php echo $this->moduleDir; ?>/images/common/icon_tick.png",
									handler:function() {
										Ext.Msg.wait("처리중입니다.","Please Wait...");
										Ext.Ajax.request({
											url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php",
											success:function() {
												Ext.Msg.show({title:"안내",msg:"성공적으로 저장되었습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO,animEl:"SaveButton"});
												Ext.getCmp("MonthlyViewList").getStore().commitChanges();
											},
											failure:function() {
												Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 저장하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
											},
											headers:{},
											params:{"action":"monthly","do":"outsourcing","wno":"<?php echo $this->wno; ?>","idx":data.get("idx"),"date":Ext.getCmp("month").getValue(),"data":GetGridData(Ext.getCmp("MonthlyViewList"))}
										});
									}
								}),
								new Ext.Button({
									text:"닫기",
									icon:"<?php echo $this->moduleDir; ?>/images/common/icon_cross.png",
									handler:function() {
										Ext.getCmp("MonthlyViewWindow").close();
									}
								})
							],
							listeners:{
								show:{fn:function() {
									Ext.getCmp("MonthlyViewList").getStore().load();
								}},
								close:{fn:function() {
									Ext.getCmp("MonthlyList").getStore().reload();
								}},
							}
						}).show();
					}}
				}
			})
		]
	});

	Ext.getCmp("MonthlyList").getStore().on("load",function() {
		var thisMonth = new Date(Ext.getCmp("month").getValue()+"-01");
		Ext.getCmp("MonthlyList").getColumnModel().setEditor(2,new Ext.grid.GridEditor(
			new Ext.form.DateField({
				minValue:thisMonth.format("Y-m-d"),
				maxValue:new Date(thisMonth.format("Y-m")+"-"+thisMonth.format("t")).format("Y-m-d"),
				value:new Date().format("Y-m-d"),
				format:"Y-m-d"
			})
		));
		SetCookie("iErpMonth",Ext.getCmp("MonthlyList").getStore().baseParams.date);
	});

	Ext.getCmp("MonthlyList").getStore().load();
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>