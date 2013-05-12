<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	var ItemStore = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:'lists',
			totalProperty:'totalCount',
			fields:[{name:"idx",type:"int"},"workgroup","worktype","title","size","unit","cost1","cost2","cost3","avgcost1","avgcost2","avgcost3","yearcost1","yearcost2","yearcost3"]
		}),
		remoteSort:true,
		sortInfo:{field:"title",direction:"ASC"},
		baseParams:{"action":"item","get":"list","gno":"","tno":"","keyword":""}
	});

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"품명DB관리",
		layout:"fit",
		items:[
			new Ext.grid.EditorGridPanel({
				id:"ListPanel",
				border:false,
				tbar:[
					new Ext.form.ComboBox({
						id:"ItemWorkgroup",
						typeAhead:true,
						triggerAction:"all",
						lazyRender:true,
						store:new Ext.data.Store({
							proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
							reader:new Ext.data.JsonReader({
								root:"lists",
								totalProperty:"totalCount",
								fields:["idx","workgroup","sort"]
							}),
							remoteSort:false,
							sortInfo:{field:"sort",direction:"ASC"},
							baseParams:{"action":"base","get":"workgroup","is_all":"true"}
						}),
						width:80,
						editable:false,
						mode:"local",
						displayField:"workgroup",
						valueField:"idx",
						listeners:{
							render:{fn:function() {
								Ext.getCmp("ItemWorkgroup").getStore().load();
								Ext.getCmp("ItemWorkgroup").getStore().on("load",function(store) {
									Ext.getCmp("ItemWorkgroup").setValue(store.getAt(0).get("idx"));
								});
							}},
							select:{fn:function(form) {
								Ext.getCmp("ItemWorktype").getStore().baseParams.bgno = form.getValue();
								Ext.getCmp("ItemWorktype").getStore().load();
							}}
						}
					}),
					' ',
					new Ext.form.ComboBox({
						id:"ItemWorktype",
						typeAhead:true,
						triggerAction:"all",
						lazyRender:true,
						store:new Ext.data.Store({
							proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
							reader:new Ext.data.JsonReader({
								root:"lists",
								totalProperty:"totalCount",
								fields:["idx","worktype","value","sort"]
							}),
							remoteSort:false,
							sortInfo:{field:"sort",direction:"ASC"},
							baseParams:{"action":"base","get":"worktype","bgno":"0","is_all":"true"}
						}),
						width:120,
						editable:false,
						mode:"local",
						displayField:"worktype",
						valueField:"idx",
						listeners:{
							render:{fn:function() {
								Ext.getCmp("ItemWorktype").getStore().load();
								Ext.getCmp("ItemWorktype").getStore().on("load",function(store) {
									Ext.getCmp("ItemWorktype").setValue(store.getAt(0).get("idx"));
								});
							}}
						}
					}),
					' ',
					new Ext.form.TextField({
						id:"ItemKeyword",
						width:150,
						emptyText:"검색어를 입력하세요.",
						enableKeyEvents:true,
						listeners:{keydown:{fn:function(form,e) {
							if (e.keyCode == 13) {
								ItemStore.baseParams.keyword = Ext.getCmp("ItemKeyword").getValue();
								ItemStore.baseParams.bgno = Ext.getCmp("ItemWorkgroup").getValue();
								ItemStore.baseParams.btno = Ext.getCmp("ItemWorktype").getValue();
								ItemStore.load({params:{start:0,limit:30}});
							}
						}}}
					}),
					' ',
					new Ext.Button({
						text:"검색",
						icon:ENV.dir+"/module/erp/images/common/icon_magnifier.png",
						handler:function() {
							ItemStore.baseParams.keyword = Ext.getCmp("ItemKeyword").getValue();
							ItemStore.baseParams.bgno = Ext.getCmp("ItemWorkgroup").getValue();
							ItemStore.baseParams.btno = Ext.getCmp("ItemWorktype").getValue();
							ItemStore.load({params:{start:0,limit:30}});
						}
					}),
					'-',
					new Ext.Button({
						text:"추가하기",
						icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_add.png",
						handler:function() {

						}
					}),
					new Ext.Button({
						text:"삭제하기",
						icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_table_delete.png",
						handler:function() {

						}
					})
				],
				cm:new Ext.grid.ColumnModel([
					new Ext.ux.grid.CheckboxSelectionModel(),
					{
						dataIndex:"idx",
						hideable:false,
						hidden:true,
						sortable:false
					},{
						header:"타입",
						dataIndex:"workgroup",
						width:50
					},{
						header:"공종명",
						dataIndex:"worktype",
						width:80
					},{
						header:"품명",
						dataIndex:"title",
						width:200
					},{
						header:"규격",
						dataIndex:"size",
						width:100
					},{
						header:"단위",
						dataIndex:"unit",
						width:40
					},{
						header:"재료비",
						dataIndex:"cost1",
						width:60,
						renderer:function(value,p,record) {
							return GridItemAvgCost(value,record.data.avgcost1);
						},
						editor:new Ext.form.NumberField({selectOnFocus:true})
					},{
						header:"노무비",
						dataIndex:"cost2",
						width:60,
						renderer:function(value,p,record) {
							return GridItemAvgCost(value,record.data.avgcost2);
						},
						editor:new Ext.form.NumberField({selectOnFocus:true})
					},{
						header:"경비",
						dataIndex:"cost3",
						width:60,
						renderer:function(value,p,record) {
							return GridItemAvgCost(value,record.data.avgcost3);
						},
						editor:new Ext.form.NumberField({selectOnFocus:true})
					},{
						header:"평균재료비",
						dataIndex:"yearcost1",
						width:70,
						renderer:GridNumberFormat
					},{
						header:"평균노무비",
						dataIndex:"yearcost2",
						width:70,
						renderer:GridNumberFormat
					},{
						header:"평균경비",
						dataIndex:"yearcost3",
						width:70,
						renderer:GridNumberFormat
					}
				]),
				store:ItemStore,
				sm:new Ext.ux.grid.CheckboxSelectionModel(),
				trackMouseOver:true,
				bbar:new Ext.PagingToolbar({
					pageSize:30,
					store:ItemStore,
					displayInfo:true,
					displayMsg:"{0} - {1} of {2}",
					emptyMsg:"데이터없음"
				}),
				listeners:{
					rowdblclick:{fn:function(grid,idx) {
						var title = grid.getStore().getAt(idx).get("title");
						if (grid.getStore().getAt(idx).get("size")) {
							title+= " ("+grid.getStore().getAt(idx).get("size")+")";
						}
						title+= " 금액변동기록";
						var AvgStore = new Ext.data.Store({
							proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
							reader:new Ext.data.JsonReader({
								root:'lists',
								totalProperty:'totalCount',
								fields:[{name:"idx",type:"int"},"type","workspace",{name:"cost1",type:"int"},{name:"cost2",type:"int"},{name:"cost3",type:"int"},"reg_date","chart_date"]
							}),
							remoteSort:true,
							sortInfo:{field:"reg_date",direction:"DESC"},
							baseParams:{"action":"item","get":"avglist","gno":"","tno":"","keyword":""}
						});
						AvgStore.load({params:{start:0,limit:30}});

						new Ext.Window({
							id:"ItemWindow",
							width:700,
							height:450,
							layout:"fit",
							modal:true,
							title:title,
							items:[
								new Ext.Panel({
									border:false,
									layout:"border",
									items:[
										new Ext.grid.GridPanel({
											region:"center",
											border:false,
											cm:new Ext.grid.ColumnModel([
												new Ext.grid.RowNumberer(),
												{
													header:"타입",
													dataIndex:"type",
													width:50,
													renderer:function(value,p,record) {
														var type = new Array();
														type["ESTIMATE"] = "견적";
														type["CONTRACT"] = "계약";
														type["ORDER"] = "발주";
														type["COST"] = "실행";

														return type[value];
													}
												},{
													header:"현장명",
													dataIndex:"workspace",
													width:250
												},{
													header:"재료비",
													dataIndex:"cost1",
													width:70,
													sortable:true,
													renderer:GridNumberFormat
												},{
													header:"노무비",
													dataIndex:"cost2",
													width:70,
													sortable:true,
													renderer:GridNumberFormat
												},{
													header:"경비",
													dataIndex:"cost3",
													width:70,
													sortable:true,
													renderer:GridNumberFormat
												},{
													header:"일자",
													dataIndex:"reg_date",
													width:110,
													sortable:true
												}
											]),
											store:AvgStore,
											bbar:new Ext.PagingToolbar({
												pageSize:30,
												store:AvgStore,
												displayInfo:true,
												displayMsg:"{0} - {1} of {2}",
												emptyMsg:"데이터없음"
											})
										}),
										new Ext.TabPanel({
											region:"south",
											border:true,
											margins:"0 -1 -1 -1",
											height:200,
											tabPosition:"bottom",
											activeTab:0,
											items:[
												new Ext.Panel({
													title:"재료비",
													items:{
														xtype:"linechart",
														store:AvgStore,
														xField:"idx",
														yField:"cost1",
														yAxis:new Ext.chart.NumericAxis({
															displayName:"cost1",
															labelRenderer:Ext.util.Format.numberRenderer("0,0")
														}),
														tipRenderer:function(chart,record){
															return GetNumberFormat(record.data.cost1)+" ("+record.data.chart_date+")";
														}
													}
												}),
												new Ext.Panel({
													title:"노무비",
													items:{
														xtype:"linechart",
														store:AvgStore,
														xField:"idx",
														yField:"cost2",
														yAxis:new Ext.chart.NumericAxis({
															displayName:"cost2",
															labelRenderer:Ext.util.Format.numberRenderer("0,0")
														}),
														tipRenderer:function(chart,record){
															return GetNumberFormat(record.data.cost2)+" ("+record.data.chart_date+")";
														}
													}
												}),
												new Ext.Panel({
													title:"경비",
													items:{
														xtype:"linechart",
														store:AvgStore,
														xField:"idx",
														yField:"cost3",
														yAxis:new Ext.chart.NumericAxis({
															displayName:"cost3",
															labelRenderer:Ext.util.Format.numberRenderer("0,0")
														}),
														tipRenderer:function(chart,record){
															return GetNumberFormat(record.data.cost3)+" ("+record.data.chart_date+")";
														}
													}
												})
											]
										})
									]
								})
							]
						}).show();
					}}
				}
			})
		]
	});

	ItemStore.load({params:{start:0,limit:30}});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>