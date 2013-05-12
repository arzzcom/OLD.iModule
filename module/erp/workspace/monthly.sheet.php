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
		title:"기성집계표",
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
						id:"BtnSave",
						text:"기성청구하기",
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_report_disk.png",
						handler:function() {
							Ext.Msg.show({title:"확인",msg:"기성을 청구시 더이상 세부기성을 수정할 수 없습니다.<br />기성을 청구하시겠습니까?",buttons:Ext.Msg.OK,icon:Ext.MessageBox.QUESTION,fn:function(button) {
								if (button == "ok") {
									var data = GetGridData(Ext.getCmp("MonthlyList"));

									Ext.Ajax.request({
										url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php",
										success:function() {
											Ext.Msg.show({title:"안내",msg:"성공적으로 저장되었습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO,animEl:"SaveButton"});
											Ext.getCmp("MonthlyList").getStore().reload();
											CheckStore.reload();
										},
										failure:function() {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 저장하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
										},
										headers:{},
										params:{"action":"monthly","do":"sheet","mode":"save","wno":"<?php echo $this->wno; ?>","date":Ext.getCmp("month").getValue(),"data":data}
									});
								}
							}});
						}
					})
				],
				cm:new Ext.grid.ColumnModel([
					new Ext.grid.RowNumberer(),
					{
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
						header:"비목",
						dataIndex:"type",
						width:50,
						renderer:function(value,p,record) {
							if (value == "OUTSOURCING") return "외주비";
							else if (value == "ITEM") return "자재비";
							else if (value == "EXPENSE") return "경비";
							else if (value == "EQUIPMENT") return "장비비";
							else if (value == "WORKER") return "노무비";
						}
					},{
						header:"업체명/이름",
						dataIndex:"cooperation",
						width:100
					},{
						header:"도급금액",
						dataIndex:"original",
						width:90,
						summaryType:"sum",
						renderer:GridNumberFormat,
						editor:new Ext.form.NumberField({selectOnFocus:true})
					},{
						header:"공급가액",
						dataIndex:"contract",
						width:90,
						summaryType:"sum",
						renderer:function(value,p,record) {
							if (value == -1) record.data.contract = record.data.prev+record.data.monthly;
							return GridNumberFormat(record.data.contract);
						},
						editor:new Ext.form.NumberField({selectOnFocus:true})
					},{
						header:"부가세",
						dataIndex:"contract_tax",
						width:80,
						summaryType:"sum",
						renderer:function(value,p,record) {
							record.data.contract_tax = Math.floor(record.data.contract*0.1);
							return GridNumberFormat(record.data.contract_tax);
						}
					},{
						header:"소계",
						dataIndex:"contract_total",
						width:90,
						summaryType:"sum",
						renderer:function(value,p,record) {
							record.data.contract_total = record.data.contract+record.data.contract_tax;
							return GridNumberFormat(record.data.contract_total);
						}
					},{
						header:"공급가액",
						dataIndex:"prev",
						width:90,
						summaryType:"sum",
						renderer:GridNumberFormat
					},{
						header:"부가세",
						dataIndex:"prev_tax",
						width:80,
						summaryType:"sum",
						renderer:function(value,p,record) {
							record.data.prev_tax = Math.floor(record.data.prev*0.1);
							return GridNumberFormat(record.data.prev_tax);
						}
					},{
						header:"소계",
						dataIndex:"prev_total",
						width:90,
						summaryType:"sum",
						renderer:function(value,p,record) {
							record.data.prev_total = record.data.prev+record.data.prev_tax;
							return GridNumberFormat(record.data.prev_total);
						}
					},{
						header:"공급가액",
						dataIndex:"monthly",
						width:90,
						summaryType:"sum",
						renderer:GridNumberFormat
					},{
						header:"부가세",
						dataIndex:"monthly_tax",
						width:80,
						summaryType:"sum",
						renderer:function(value,p,record) {
							record.data.monthly_tax = Math.floor(record.data.monthly*0.1);
							return GridNumberFormat(record.data.monthly_tax);
						}
					},{
						header:"소계",
						dataIndex:"monthly_total",
						width:90,
						summaryType:"sum",
						renderer:function(value,p,record) {
							record.data.monthly_total = record.data.monthly+record.data.monthly_tax;
							return GridNumberFormat(record.data.monthly_total);
						}
					},{
						header:"공급가액",
						dataIndex:"total",
						width:90,
						summaryType:"sum",
						renderer:function(value,p,record) {
							record.data.total = record.data.prev+record.data.monthly;
							return GridNumberFormat(record.data.total);
						}
					},{
						header:"부가세",
						dataIndex:"total_tax",
						width:80,
						summaryType:"sum",
						renderer:function(value,p,record) {
							record.data.total_tax = Math.floor(record.data.total*0.1);
							return GridNumberFormat(record.data.total_tax);
						}
					},{
						header:"소계",
						dataIndex:"total_total",
						width:90,
						summaryType:"sum",
						renderer:function(value,p,record) {
							record.data.total_total = record.data.total+record.data.total_tax;
							return GridNumberFormat(record.data.total_total);
						}
					},{
						header:"잔액",
						dataIndex:"remain",
						width:90,
						summaryType:"sum",
						renderer:function(value,p,record) {
							record.data.remain = record.data.contract-record.data.total;
							return GridNumberFormat(record.data.remain);
						}
					}
				]),
				store:new Ext.data.GroupingStore({
					proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php"}),
					reader:new Ext.data.JsonReader({
						root:"lists",
						totalProperty:"totalCount",
						fields:["idx","type","cno","repto","cooperation",{name:"original",type:"int"},{name:"contract",type:"int"},{name:"contract_tax",type:"int"},{name:"contract_total",type:"int"},{name:"prev",type:"int"},{name:"prev_tax",type:"int"},{name:"prev_total",type:"int"},{name:"monthly",type:"int"},{name:"monthly_tax",type:"int"},{name:"monthly_total",type:"int"},{name:"total",type:"int"},{name:"total_tax",type:"int"},{name:"total_total",type:"int"},{name:"remain",type:"int"}]
					}),
					remoteSort:false,
					groupField:"type",
					sortInfo:{field:"idx",direction:"ASC"},
					baseParams:{"wno":"<?php echo $this->wno; ?>","action":"monthly","get":"sheet","date":"<?php echo Request('iErpMonth','cookie') != null ? Request('iErpMonth','cookie') : GetTime('Y-m'); ?>"}
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
						{header:"계약금액",colspan:3,align:"center"},
						{header:"전회누계",colspan:3,align:"center"},
						{header:"금회기성",colspan:3,align:"center"},
						{header:"누계",colspan:3,align:"center"},
						{}
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

	Ext.getCmp("MonthlyList").getStore().load();

	CheckStore.on("load",function() {
		if (CheckStore.getAt(0).get("is_confirm") == "TRUE") {
			Ext.getCmp("BtnSave").disable();
		} else {
			Ext.getCmp("BtnSave").enable();
		}
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>