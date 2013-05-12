<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"현장상황일지",
		layout:"fit",
		items:[
			new Ext.grid.GridPanel({
				id:"DailyList",
				border:false,
				tbar:[
					new Ext.Button({
						icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_control_left.png",
						text:"이전일",
						handler:function() {
							var today = new Date(Ext.getCmp("today").getValue()).add("d",-1).format("Y-m-d");
							Ext.getCmp("today").setValue(today);

							SetCookie("iErpDate",today);

							Ext.getCmp("DailyList").getStore().baseParams.date = today;
							Ext.getCmp("DailyList").getStore().load();
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

							Ext.getCmp("DailyList").getStore().baseParams.date = today;
							Ext.getCmp("DailyList").getStore().load();
						}}}
					}),
					' ',
					new Ext.Button({
						icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_control_right.png",
						iconAlign:"right",
						text:"다음일",
						handler:function() {
							var today = new Date(Ext.getCmp("today").getValue()).add("d",1).format("Y-m-d");
							Ext.getCmp("today").setValue(today);

							SetCookie("iErpDate",today);

							Ext.getCmp("DailyList").getStore().baseParams.date = today;
							Ext.getCmp("DailyList").getStore().load();
						}
					}),
					'-',
					new Ext.form.ComboBox({
						id:"workspace",
						store:new Ext.data.Store({
							proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
							reader:new Ext.data.JsonReader({
								root:"lists",
								totalProperty:"totalCount",
								fields:["idx","title"]
							}),
							remoteSort:false,
							sortInfo:{field:"title",direction:"ASC"},
							baseParams:{"action":"workspace","get":"list","category":"working"}
						}),
						displayField:"title",
						valueField:"idx",
						typeAhead:true,
						mode:"local",
						triggerAction:"all",
						width:160,
						editable:false,
						emptyText:"현장별 보기",
						listeners:{
							render:{fn:function(form) {
								form.getStore().load();
							}},
							select:{fn:function(form) {
								Ext.getCmp("DailyList").getStore().baseParams.wno = form.getValue();
								Ext.getCmp("DailyList").getStore().reload();
							}}
						}
					}),
					' ',
					new Ext.Button({
						id:"AllButton",
						text:"전체현장보기",
						icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_paste_plain.png",
						handler:function() {
							Ext.getCmp("DailyList").getStore().baseParams.wno = "";
							Ext.getCmp("workspace").setValue("");
							Ext.getCmp("DailyList").getStore().reload();
						}
					}),
					new Ext.Button({
						text:"엑셀파일로 변환",
						icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_page_white_excel.png",
						handler:function() {
							ExcelConvert("<?php echo $_ENV['dir']; ?>/module/erp/exec/GetExcel.do.php?action=commander&get=daily&wno="+Ext.getCmp("workspace").getValue()+"&date="+new Date(Ext.getCmp("today").getValue()).format("Y-m-d"));
						}
					})
				],
				cm:new Ext.grid.ColumnModel([
					new Ext.grid.RowNumberer(),
					{
						dataIndex:"group",
						hidden:true,
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
						dataIndex:"workgroup",
						width:80
					},{
						header:"공종명",
						dataIndex:"worktype",
						width:100
					},{
						header:"품명",
						dataIndex:"title",
						width:220
					},{
						header:"규격",
						dataIndex:"size",
						width:100
					},{
						header:"적요",
						dataIndex:"content",
						width:220
					},{
						header:"비목",
						dataIndex:"type",
						width:40,
						renderer:function(value) {
							if (value == "member" || value == "dayworker") return "노무";
							else if (value == "outsourcing") return "외주";
							else if (value == "itemorder" || value == "item") return "자재";
							else if (value == "expense") return "경비";
							else if (value == "equipment") return "장비";
						}
					},{
						header:"지불처",
						dataIndex:"cooperation",
						width:100
					},{
						header:"수량",
						dataIndex:"ea",
						width:60,
						sortable:true,
						renderer:GridNumberFormat
					},{
						header:"단위",
						dataIndex:"unit",
						width:40
					},{
						header:"단가",
						dataIndex:"cost",
						width:80,
						sortable:true,
						summaryType:"sum",
						renderer:GridNumberFormat
					},{
						header:"금액",
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
						}
					}
				]),
				store:new Ext.data.GroupingStore({
					proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
					reader:new Ext.data.JsonReader({
						root:"lists",
						totalProperty:"totalCount",
						fields:["idx","is_new","group","date","workgroup","worktype","content","type","code","subcode","title","size","unit",{name:"ea",type:"float"},"order_ea",{name:"cost",type:"int"},{name:"price",type:"int"},"payment","cooperation","avgcost"]
					}),
					remoteSort:false,
					groupField:"group",
					sortInfo:{field:"date",direction:"ASC"},
					baseParams:{"action":"daily","date":new Date(Ext.getCmp("today").getValue()).format("Y-m-d"),"wno":""}
				}),
				trackMouseOver:true,
				plugins:new Ext.grid.GroupSummary(),
				view:new Ext.grid.GroupingView({
					enableGroupingMenu:false,
					hideGroupedColumn:false,
					showGroupName:false,
					enableNoGroups:false,
					headersDisabled:false,
					showGroupHeader:true
				})
			})
		]
	});

	Ext.getCmp("DailyList").getStore().load();

	Ext.getCmp("DailyList").getStore().on("load",function(store) {
		if (store.baseParams.wno != "") {
			store.clearGrouping();
		} else {
			store.groupBy("group");
		}
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>