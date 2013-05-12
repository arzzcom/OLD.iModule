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

	var MonthlyStore1 = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:[{name:"idx",type:"int"},"title",{name:"contract",type:"int"},{name:"exec",type:"int"},{name:"prev",type:"int"},{name:"monthly",type:"int"},{name:"total",type:"int"},{name:"remain_contract",type:"int"},{name:"remain_exec",type:"int"}]
		}),
		remoteSort:true,
		sortInfo:{field:"title",direction:"ASC"},
		baseParams:{"action":"monthly","get":"list","mode":"working","year":"<?php echo Request('iErpYear','cookie') ? Request('iErpYear','cookie') : date('Y'); ?>"}
	});

	var MonthlyCm = new Ext.grid.ColumnModel([
		new Ext.grid.RowNumberer(),
		{
			header:"현장명",
			dataIndex:"title",
			width:280
		},{
			header:"계약금액",
			dataIndex:"contract",
			width:110,
			renderer:GridNumberFormat
		},{
			header:"실행금액",
			dataIndex:"exec",
			width:110,
			renderer:GridNumberFormat
		},{
			header:"전회누계",
			dataIndex:"prev",
			width:110,
			renderer:GridNumberFormat
		},{
			header:"금회기성",
			dataIndex:"monthly",
			width:110,
			renderer:GridNumberFormat
		},{
			header:"누계",
			dataIndex:"total",
			width:110,
			renderer:function(value,p,record) {
				record.data.total = record.data.prev+record.data.monthly;
				return GridNumberFormat(record.data.total);
			}
		},{
			header:"잔여계약금",
			dataIndex:"remain_contract",
			width:110,
			renderer:function(value,p,record) {
				record.data.remain_contract = record.data.contract-record.data.total;
				return GridNumberFormat(record.data.remain_contract);
			}
		},{
			header:"잔여실행금",
			dataIndex:"remain_exec",
			width:110,
			renderer:function(value,p,record) {
				record.data.remain_exec = record.data.exec-record.data.total;
				return GridNumberFormat(record.data.remain_exec);
			}
		}
	]);

	function MonthlyDetailFunction(wno) {
		new Ext.Window({
			title:"기성내역보기",
			modal:true,
			width:980,
			height:550,
			layout:"fit",
			items:[
				new Ext.TabPanel({
					border:false,
					tabPosition:"bottom",
					activeTab:0,
					tbar:[
						new Ext.Button({
							text:"엑셀파일로 변환",
							icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_page_white_excel.png",
							handler:function() {
								ExcelConvert("<?php echo $_ENV['dir']; ?>/module/erp/exec/GetExcel.do.php?action=commander&get=monthly&wno="+wno+"&date="+Ext.getCmp("month").getValue());
							}
						})
					],
					items:[
						new Ext.grid.GridPanel({
							id:"DetailSheet",
							title:"기성집계표",
							cm:new Ext.grid.ColumnModel([
								new Ext.grid.RowNumberer(),
								{
									dataIndex:"group",
									hideable:false
								},{
									header:"비목",
									dataIndex:"type",
									width:80,
									renderer:function(value) {
										if (value == "ITEM") return "자재비";
										else if (value == "EXPENSE") return "경비";
										else if (value == "EQUIPMENT") return "장비비";
										else if (value == "OUTSOURCING") return "외주비";
										else return "노무비";
									}
								},{
									header:"도급금액",
									dataIndex:"original",
									width:100,
									summaryType:"sum",
									renderer:GridNumberFormat
								},{
									header:"공급가액",
									dataIndex:"contract",
									width:100,
									summaryType:"sum",
									renderer:GridNumberFormat
								},{
									header:"부가세",
									dataIndex:"contract_tax",
									width:90,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.contract_tax = Math.floor(record.data.contract*0.1);
										return GridNumberFormat(record.data.contract_tax);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"소계",
									dataIndex:"contract_total",
									width:100,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.contract_total = record.data.contract+record.data.contract_tax;
										return GridNumberFormat(record.data.contract_total);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"공급가액",
									dataIndex:"prev",
									width:100,
									summaryType:"sum",
									renderer:GridNumberFormat
								},{
									header:"부가세",
									dataIndex:"prev_tax",
									width:90,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.prev_tax = Math.floor(record.data.prev*0.1);
										return GridNumberFormat(record.data.prev_tax);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"소계",
									dataIndex:"prev_total",
									width:100,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.prev_total = record.data.prev+record.data.prev_tax;
										return GridNumberFormat(record.data.prev_total);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"공급가액",
									dataIndex:"monthly",
									width:100,
									summaryType:"sum",
									renderer:GridNumberFormat
								},{
									header:"부가세",
									dataIndex:"monthly_tax",
									width:90,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.monthly_tax = Math.floor(record.data.monthly*0.1);
										return GridNumberFormat(record.data.monthly_tax);
									},
									summaryRenderer:GridNumberFormat

								},{
									header:"소계",
									dataIndex:"monthly_total",
									width:100,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.monthly_total = record.data.monthly+record.data.monthly_tax;
										return GridNumberFormat(record.data.monthly_total);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"공급가액",
									dataIndex:"total",
									width:100,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.total = record.data.monthly+record.data.prev;
										return GridNumberFormat(record.data.total);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"부가세",
									dataIndex:"total_tax",
									width:90,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.total_tax = Math.floor(record.data.total*0.1);
										return GridNumberFormat(record.data.total_tax);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"소계",
									dataIndex:"total_total",
									width:100,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.total_total = record.data.total+record.data.total_tax;
										return GridNumberFormat(record.data.total_total);
									},
									summaryRenderer:GridNumberFormat
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
								proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
								reader:new Ext.data.JsonReader({
									root:"lists",
									totalProperty:"totalCount",
									fields:["group","type",{name:"original",type:"int"},{name:"contract",type:"int"},{name:"contract_tax",type:"int"},{name:"contract_total",type:"int"},{name:"prev",type:"int"},{name:"prev_tax",type:"int"},{name:"prev_total",type:"int"},{name:"monthly",type:"int"},{name:"monthly_tax",type:"int"},{name:"monthly_total",type:"int"},{name:"total",type:"int"},{name:"total_tax",type:"int"},{name:"total_total",type:"int"},{name:"sort",type:"int"},{name:"remain",type:"int"}]
								}),
								remoteSort:false,
								groupField:"group",
								sortInfo:{field:"sort",direction:"ASC"},
								baseParams:{"action":"monthly","get":"sheet","date":Ext.getCmp("month").getValue(),"wno":wno}
							}),
							plugins:[new Ext.ux.grid.ColumnHeaderGroup({
								rows:[[
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
								hideGroupedColumn:true,
								showGroupName:false,
								enableNoGroups:false,
								headersDisabled:false,
								showGroupHeader:false
							})
						}),
						new Ext.grid.GridPanel({
							id:"DetailOutsourcing",
							title:"외주비청구서",
							cm:new Ext.grid.ColumnModel([
								new Ext.grid.RowNumberer(),
								{
									dataIndex:"group",
									hideable:false
								},{
									header:"업체명",
									dataIndex:"cooperation",
									width:150
								},{
									header:"도급금액",
									dataIndex:"original",
									width:100,
									summaryType:"sum",
									renderer:GridNumberFormat
								},{
									header:"공급가액",
									dataIndex:"contract",
									width:100,
									summaryType:"sum",
									renderer:GridNumberFormat
								},{
									header:"부가세",
									dataIndex:"contract_tax",
									width:90,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.contract_tax = Math.floor(record.data.contract*0.1);
										return GridNumberFormat(record.data.contract_tax);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"소계",
									dataIndex:"contract_total",
									width:100,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.contract_total = record.data.contract+record.data.contract_tax;
										return GridNumberFormat(record.data.contract_total);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"공급가액",
									dataIndex:"prev",
									width:100,
									summaryType:"sum",
									renderer:GridNumberFormat
								},{
									header:"부가세",
									dataIndex:"prev_tax",
									width:90,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.prev_tax = Math.floor(record.data.prev*0.1);
										return GridNumberFormat(record.data.prev_tax);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"소계",
									dataIndex:"prev_total",
									width:100,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.prev_total = record.data.prev+record.data.prev_tax;
										return GridNumberFormat(record.data.prev_total);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"공급가액",
									dataIndex:"monthly",
									width:100,
									summaryType:"sum",
									renderer:GridNumberFormat
								},{
									header:"부가세",
									dataIndex:"monthly_tax",
									width:90,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.monthly_tax = Math.floor(record.data.monthly*0.1);
										return GridNumberFormat(record.data.monthly_tax);
									},
									summaryRenderer:GridNumberFormat

								},{
									header:"소계",
									dataIndex:"monthly_total",
									width:100,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.monthly_total = record.data.monthly+record.data.monthly_tax;
										return GridNumberFormat(record.data.monthly_total);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"공급가액",
									dataIndex:"total",
									width:100,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.total = record.data.monthly+record.data.prev;
										return GridNumberFormat(record.data.total);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"부가세",
									dataIndex:"total_tax",
									width:90,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.total_tax = Math.floor(record.data.total*0.1);
										return GridNumberFormat(record.data.total_tax);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"소계",
									dataIndex:"total_total",
									width:100,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.total_total = record.data.total+record.data.total_tax;
										return GridNumberFormat(record.data.total_total);
									},
									summaryRenderer:GridNumberFormat
								}
							]),
							store:new Ext.data.GroupingStore({
								proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
								reader:new Ext.data.JsonReader({
									root:"lists",
									totalProperty:"totalCount",
									fields:["group","type","cno","repto","cooperation",{name:"original",type:"int"},{name:"contract",type:"int"},{name:"contract_tax",type:"int"},{name:"contract_total",type:"int"},{name:"prev",type:"int"},{name:"prev_tax",type:"int"},{name:"prev_total",type:"int"},{name:"monthly",type:"int"},{name:"monthly_tax",type:"int"},{name:"monthly_total",type:"int"},{name:"total",type:"int"},{name:"total_tax",type:"int"},{name:"total_total",type:"int"},{name:"sort",type:"int"}]
								}),
								remoteSort:false,
								groupField:"group",
								sortInfo:{field:"sort",direction:"ASC"},
								baseParams:{"action":"monthly","get":"outsourcing","mode":"list","date":Ext.getCmp("month").getValue(),"wno":wno}
							}),
							plugins:[new Ext.ux.grid.ColumnHeaderGroup({
								rows:[[
									{},
									{},
									{},
									{},
									{header:"계약금액",colspan:3,align:"center"},
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
							listeners:{rowdblclick:{fn:function(grid,idx,e) {
								var data = grid.getStore().getAt(idx);
								MonthlyDetailItemFunction(wno,data.get("type"),data.get("cooperation"),data.get("cno"),data.get("repto"));
							}}}
						}),
						new Ext.grid.GridPanel({
							id:"DetailItem",
							title:"자재비청구서",
							cm:new Ext.grid.ColumnModel([
								new Ext.grid.RowNumberer(),
								{
									dataIndex:"group",
									hideable:false
								},{
									header:"업체명",
									dataIndex:"cooperation",
									width:150,
									renderer:function(value,p,record) {
										var sHTML = value;
										if (record.data.repto != "0") sHTML+= "(계약)";

										return sHTML;
									}
								},{
									header:"도급금액",
									dataIndex:"original",
									width:100,
									summaryType:"sum",
									renderer:GridNumberFormat
								},{
									header:"공급가액",
									dataIndex:"contract",
									width:100,
									summaryType:"sum",
									renderer:GridNumberFormat
								},{
									header:"부가세",
									dataIndex:"contract_tax",
									width:90,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.contract_tax = Math.floor(record.data.contract*0.1);
										return GridNumberFormat(record.data.contract_tax);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"소계",
									dataIndex:"contract_total",
									width:100,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.contract_total = record.data.contract+record.data.contract_tax;
										return GridNumberFormat(record.data.contract_total);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"공급가액",
									dataIndex:"prev",
									width:100,
									summaryType:"sum",
									renderer:GridNumberFormat
								},{
									header:"부가세",
									dataIndex:"prev_tax",
									width:90,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.prev_tax = Math.floor(record.data.prev*0.1);
										return GridNumberFormat(record.data.prev_tax);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"소계",
									dataIndex:"prev_total",
									width:100,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.prev_total = record.data.prev+record.data.prev_tax;
										return GridNumberFormat(record.data.prev_total);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"공급가액",
									dataIndex:"monthly",
									width:100,
									summaryType:"sum",
									renderer:GridNumberFormat
								},{
									header:"부가세",
									dataIndex:"monthly_tax",
									width:90,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.monthly_tax = Math.floor(record.data.monthly*0.1);
										return GridNumberFormat(record.data.monthly_tax);
									},
									summaryRenderer:GridNumberFormat

								},{
									header:"소계",
									dataIndex:"monthly_total",
									width:100,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.monthly_total = record.data.monthly+record.data.monthly_tax;
										return GridNumberFormat(record.data.monthly_total);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"공급가액",
									dataIndex:"total",
									width:100,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.total = record.data.monthly+record.data.prev;
										return GridNumberFormat(record.data.total);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"부가세",
									dataIndex:"total_tax",
									width:90,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.total_tax = Math.floor(record.data.total*0.1);
										return GridNumberFormat(record.data.total_tax);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"소계",
									dataIndex:"total_total",
									width:100,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.total_total = record.data.total+record.data.total_tax;
										return GridNumberFormat(record.data.total_total);
									},
									summaryRenderer:GridNumberFormat
								}
							]),
							store:new Ext.data.GroupingStore({
								proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
								reader:new Ext.data.JsonReader({
									root:"lists",
									totalProperty:"totalCount",
									fields:["group","type","cno","repto","cooperation",{name:"original",type:"int"},{name:"contract",type:"int"},{name:"contract_tax",type:"int"},{name:"contract_total",type:"int"},{name:"prev",type:"int"},{name:"prev_tax",type:"int"},{name:"prev_total",type:"int"},{name:"monthly",type:"int"},{name:"monthly_tax",type:"int"},{name:"monthly_total",type:"int"},{name:"total",type:"int"},{name:"total_tax",type:"int"},{name:"total_total",type:"int"},{name:"sort",type:"int"}]
								}),
								remoteSort:false,
								groupField:"group",
								sortInfo:{field:"sort",direction:"ASC"},
								baseParams:{"action":"monthly","get":"item","mode":"list","date":Ext.getCmp("month").getValue(),"wno":wno}
							}),
							plugins:[new Ext.ux.grid.ColumnHeaderGroup({
								rows:[[
									{},
									{},
									{},
									{},
									{header:"계약금액",colspan:3,align:"center"},
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
							listeners:{rowdblclick:{fn:function(grid,idx,e) {
								var data = grid.getStore().getAt(idx);
								MonthlyDetailItemFunction(wno,data.get("type"),data.get("cooperation"),data.get("cno"),data.get("repto"));
							}}}
						}),
						new Ext.grid.GridPanel({
							id:"DetailExpense",
							title:"경비청구서",
							cm:new Ext.grid.ColumnModel([
								new Ext.grid.RowNumberer(),
								{
									dataIndex:"group",
									hideable:false
								},{
									header:"업체명",
									dataIndex:"cooperation",
									width:150
								},{
									header:"도급금액",
									dataIndex:"original",
									width:100,
									summaryType:"sum",
									renderer:GridNumberFormat
								},{
									header:"공급가액",
									dataIndex:"contract",
									width:100,
									summaryType:"sum",
									renderer:GridNumberFormat
								},{
									header:"부가세",
									dataIndex:"contract_tax",
									width:90,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.contract_tax = Math.floor(record.data.contract*0.1);
										return GridNumberFormat(record.data.contract_tax);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"소계",
									dataIndex:"contract_total",
									width:100,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.contract_total = record.data.contract+record.data.contract_tax;
										return GridNumberFormat(record.data.contract_total);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"공급가액",
									dataIndex:"prev",
									width:100,
									summaryType:"sum",
									renderer:GridNumberFormat
								},{
									header:"부가세",
									dataIndex:"prev_tax",
									width:90,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.prev_tax = Math.floor(record.data.prev*0.1);
										return GridNumberFormat(record.data.prev_tax);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"소계",
									dataIndex:"prev_total",
									width:100,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.prev_total = record.data.prev+record.data.prev_tax;
										return GridNumberFormat(record.data.prev_total);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"공급가액",
									dataIndex:"monthly",
									width:100,
									summaryType:"sum",
									renderer:GridNumberFormat
								},{
									header:"부가세",
									dataIndex:"monthly_tax",
									width:90,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.monthly_tax = Math.floor(record.data.monthly*0.1);
										return GridNumberFormat(record.data.monthly_tax);
									},
									summaryRenderer:GridNumberFormat

								},{
									header:"소계",
									dataIndex:"monthly_total",
									width:100,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.monthly_total = record.data.monthly+record.data.monthly_tax;
										return GridNumberFormat(record.data.monthly_total);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"공급가액",
									dataIndex:"total",
									width:100,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.total = record.data.monthly+record.data.prev;
										return GridNumberFormat(record.data.total);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"부가세",
									dataIndex:"total_tax",
									width:90,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.total_tax = Math.floor(record.data.total*0.1);
										return GridNumberFormat(record.data.total_tax);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"소계",
									dataIndex:"total_total",
									width:100,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.total_total = record.data.total+record.data.total_tax;
										return GridNumberFormat(record.data.total_total);
									},
									summaryRenderer:GridNumberFormat
								}
							]),
							store:new Ext.data.GroupingStore({
								proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
								reader:new Ext.data.JsonReader({
									root:"lists",
									totalProperty:"totalCount",
									fields:["group","type","cno","repto","cooperation",{name:"original",type:"int"},{name:"contract",type:"int"},{name:"contract_tax",type:"int"},{name:"contract_total",type:"int"},{name:"prev",type:"int"},{name:"prev_tax",type:"int"},{name:"prev_total",type:"int"},{name:"monthly",type:"int"},{name:"monthly_tax",type:"int"},{name:"monthly_total",type:"int"},{name:"total",type:"int"},{name:"total_tax",type:"int"},{name:"total_total",type:"int"},{name:"sort",type:"int"}]
								}),
								remoteSort:false,
								groupField:"group",
								sortInfo:{field:"sort",direction:"ASC"},
								baseParams:{"action":"monthly","get":"expense","mode":"list","date":Ext.getCmp("month").getValue(),"wno":wno}
							}),
							plugins:[new Ext.ux.grid.ColumnHeaderGroup({
								rows:[[
									{},
									{},
									{},
									{},
									{header:"계약금액",colspan:3,align:"center"},
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
							listeners:{rowdblclick:{fn:function(grid,idx,e) {
								var data = grid.getStore().getAt(idx);
								MonthlyDetailItemFunction(wno,data.get("type"),data.get("cooperation"),data.get("cno"),data.get("repto"));
							}}}
						}),
						new Ext.grid.GridPanel({
							id:"DetailEquipment",
							title:"장비비청구서",
							cm:new Ext.grid.ColumnModel([
								new Ext.grid.RowNumberer(),
								{
									dataIndex:"group",
									hideable:false
								},{
									header:"업체명",
									dataIndex:"cooperation",
									width:150
								},{
									header:"도급금액",
									dataIndex:"original",
									width:100,
									summaryType:"sum",
									renderer:GridNumberFormat
								},{
									header:"공급가액",
									dataIndex:"contract",
									width:100,
									summaryType:"sum",
									renderer:GridNumberFormat
								},{
									header:"부가세",
									dataIndex:"contract_tax",
									width:90,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.contract_tax = Math.floor(record.data.contract*0.1);
										return GridNumberFormat(record.data.contract_tax);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"소계",
									dataIndex:"contract_total",
									width:100,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.contract_total = record.data.contract+record.data.contract_tax;
										return GridNumberFormat(record.data.contract_total);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"공급가액",
									dataIndex:"prev",
									width:100,
									summaryType:"sum",
									renderer:GridNumberFormat
								},{
									header:"부가세",
									dataIndex:"prev_tax",
									width:90,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.prev_tax = Math.floor(record.data.prev*0.1);
										return GridNumberFormat(record.data.prev_tax);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"소계",
									dataIndex:"prev_total",
									width:100,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.prev_total = record.data.prev+record.data.prev_tax;
										return GridNumberFormat(record.data.prev_total);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"공급가액",
									dataIndex:"monthly",
									width:100,
									summaryType:"sum",
									renderer:GridNumberFormat
								},{
									header:"부가세",
									dataIndex:"monthly_tax",
									width:90,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.monthly_tax = Math.floor(record.data.monthly*0.1);
										return GridNumberFormat(record.data.monthly_tax);
									},
									summaryRenderer:GridNumberFormat

								},{
									header:"소계",
									dataIndex:"monthly_total",
									width:100,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.monthly_total = record.data.monthly+record.data.monthly_tax;
										return GridNumberFormat(record.data.monthly_total);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"공급가액",
									dataIndex:"total",
									width:100,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.total = record.data.monthly+record.data.prev;
										return GridNumberFormat(record.data.total);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"부가세",
									dataIndex:"total_tax",
									width:90,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.total_tax = Math.floor(record.data.total*0.1);
										return GridNumberFormat(record.data.total_tax);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"소계",
									dataIndex:"total_total",
									width:100,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.total_total = record.data.total+record.data.total_tax;
										return GridNumberFormat(record.data.total_total);
									},
									summaryRenderer:GridNumberFormat
								}
							]),
							store:new Ext.data.GroupingStore({
								proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
								reader:new Ext.data.JsonReader({
									root:"lists",
									totalProperty:"totalCount",
									fields:["group","type","cno","repto","cooperation",{name:"original",type:"int"},{name:"contract",type:"int"},{name:"contract_tax",type:"int"},{name:"contract_total",type:"int"},{name:"prev",type:"int"},{name:"prev_tax",type:"int"},{name:"prev_total",type:"int"},{name:"monthly",type:"int"},{name:"monthly_tax",type:"int"},{name:"monthly_total",type:"int"},{name:"total",type:"int"},{name:"total_tax",type:"int"},{name:"total_total",type:"int"},{name:"sort",type:"int"}]
								}),
								remoteSort:false,
								groupField:"group",
								sortInfo:{field:"sort",direction:"ASC"},
								baseParams:{"action":"monthly","get":"equipment","mode":"list","date":Ext.getCmp("month").getValue(),"wno":wno}
							}),
							plugins:[new Ext.ux.grid.ColumnHeaderGroup({
								rows:[[
									{},
									{},
									{},
									{},
									{header:"계약금액",colspan:3,align:"center"},
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
							listeners:{rowdblclick:{fn:function(grid,idx,e) {
								var data = grid.getStore().getAt(idx);
								MonthlyDetailItemFunction(wno,data.get("type"),data.get("cooperation"),data.get("cno"),data.get("repto"));
							}}}
						}),
						new Ext.grid.GridPanel({
							id:"DetailWorker",
							title:"노무비청구서",
							cm:new Ext.grid.ColumnModel([
								new Ext.grid.RowNumberer(),
								{
									dataIndex:"group",
									hideable:false
								},{
									header:"이름",
									dataIndex:"cooperation",
									width:150
								},{
									header:"도급금액",
									dataIndex:"original",
									width:100,
									summaryType:"sum",
									renderer:GridNumberFormat
								},{
									header:"공급가액",
									dataIndex:"contract",
									width:100,
									summaryType:"sum",
									renderer:GridNumberFormat
								},{
									header:"부가세",
									dataIndex:"contract_tax",
									width:90,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.contract_tax = Math.floor(record.data.contract*0.1);
										return GridNumberFormat(record.data.contract_tax);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"소계",
									dataIndex:"contract_total",
									width:100,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.contract_total = record.data.contract+record.data.contract_tax;
										return GridNumberFormat(record.data.contract_total);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"공급가액",
									dataIndex:"prev",
									width:100,
									summaryType:"sum",
									renderer:GridNumberFormat
								},{
									header:"부가세",
									dataIndex:"prev_tax",
									width:90,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.prev_tax = Math.floor(record.data.prev*0.1);
										return GridNumberFormat(record.data.prev_tax);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"소계",
									dataIndex:"prev_total",
									width:100,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.prev_total = record.data.prev+record.data.prev_tax;
										return GridNumberFormat(record.data.prev_total);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"공급가액",
									dataIndex:"monthly",
									width:100,
									summaryType:"sum",
									renderer:GridNumberFormat
								},{
									header:"부가세",
									dataIndex:"monthly_tax",
									width:90,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.monthly_tax = Math.floor(record.data.monthly*0.1);
										return GridNumberFormat(record.data.monthly_tax);
									},
									summaryRenderer:GridNumberFormat

								},{
									header:"소계",
									dataIndex:"monthly_total",
									width:100,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.monthly_total = record.data.monthly+record.data.monthly_tax;
										return GridNumberFormat(record.data.monthly_total);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"공급가액",
									dataIndex:"total",
									width:100,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.total = record.data.monthly+record.data.prev;
										return GridNumberFormat(record.data.total);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"부가세",
									dataIndex:"total_tax",
									width:90,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.total_tax = Math.floor(record.data.total*0.1);
										return GridNumberFormat(record.data.total_tax);
									},
									summaryRenderer:GridNumberFormat
								},{
									header:"소계",
									dataIndex:"total_total",
									width:100,
									summaryType:"sum",
									renderer:function(value,p,record) {
										record.data.total_total = record.data.total+record.data.total_tax;
										return GridNumberFormat(record.data.total_total);
									},
									summaryRenderer:GridNumberFormat
								}
							]),
							store:new Ext.data.GroupingStore({
								proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
								reader:new Ext.data.JsonReader({
									root:"lists",
									totalProperty:"totalCount",
									fields:["group","type","cno","repto","cooperation",{name:"original",type:"int"},{name:"contract",type:"int"},{name:"contract_tax",type:"int"},{name:"contract_total",type:"int"},{name:"prev",type:"int"},{name:"prev_tax",type:"int"},{name:"prev_total",type:"int"},{name:"monthly",type:"int"},{name:"monthly_tax",type:"int"},{name:"monthly_total",type:"int"},{name:"total",type:"int"},{name:"total_tax",type:"int"},{name:"total_total",type:"int"},{name:"sort",type:"int"}]
								}),
								remoteSort:false,
								groupField:"group",
								sortInfo:{field:"sort",direction:"ASC"},
								baseParams:{"action":"monthly","get":"worker","mode":"list","date":Ext.getCmp("month").getValue(),"wno":wno}
							}),
							plugins:[new Ext.ux.grid.ColumnHeaderGroup({
								rows:[[
									{},
									{},
									{},
									{},
									{header:"계약금액",colspan:3,align:"center"},
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
							})
						})
						/*
						new Ext.grid.GridPanel({
							id:"DetailItem",
							title:"자재비청구서",
							cm:new Ext.grid.ColumnModel([
								new Ext.grid.RowNumberer(),
								{
									dataIndex:"group",
									hideable:false
								},{
									dataIndex:"wno",
									hidden:true,
									hideable:false
								},{
									header:"업체명",
									dataIndex:"cooperation",
									width:100
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
									renderer:GridNumberFormat
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
								proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
								reader:new Ext.data.JsonReader({
									root:"lists",
									totalProperty:"totalCount",
									fields:["wno","group","cooperation","gno","tno","code","subcode","itemcode","workgroup","worktype","title","size","unit",{name:"contract_ea",type:"float"},{name:"contract_cost",type:"int"},{name:"contract_price",type:"int"},{name:"prev_ea",type:"float"},{name:"prev_price",type:"int"},{name:"ea",type:"float"},{name:"cost",type:"int"},{name:"price",type:"int"},{name:"total_ea",type:"float"},{name:"total_price",type:"int"},"etc"]
								}),
								remoteSort:false,
								groupField:"group",
								sortInfo:{field:"title",direction:"ASC"},
								baseParams:{"action":"monthly","get":"item","date":Ext.getCmp("month").getValue(),"wno":wno}
							}),
							plugins:[new Ext.ux.grid.ColumnHeaderGroup({
								rows:[[
									{},
									{},
									{},
									{},
									{header:"품목",colspan:5,align:"center"},
									{header:"계약금액",colspan:3,align:"center"},
									{header:"전회누계",colspan:2,align:"center"},
									{header:"금회기성",colspan:3,align:"center"},
									{header:"누계",colspan:2,align:"center"},
									{}
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
							})
						})
						*/
					]
				})
			],
			listeners:{show:{fn:function() {
				Ext.getCmp("DetailSheet").getStore().load();
				Ext.getCmp("DetailOutsourcing").getStore().load();
				Ext.getCmp("DetailItem").getStore().load();
				Ext.getCmp("DetailExpense").getStore().load();
				Ext.getCmp("DetailEquipment").getStore().load();
				Ext.getCmp("DetailWorker").getStore().load();
			}}}
		}).show();
	}

	function MonthlyDetailItemFunction(wno,type,cooperation,cno,repto) {
		new Ext.Window({
			title:cooperation+" 세부품목보기",
			modal:true,
			width:950,
			height:520,
			layout:"fit",
			items:[
				new Ext.grid.GridPanel({
					id:"ItemList",
					border:false,
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
							renderer:GridNumberFormat
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
						proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
						reader:new Ext.data.JsonReader({
							root:"lists",
							totalProperty:"totalCount",
							fields:["idx","group","gno","tno","code","subcode","itemcode","workgroup","worktype","title","size","unit",{name:"contract_ea",type:"float"},{name:"contract_cost",type:"int"},{name:"contract_price",type:"int"},{name:"prev_ea",type:"float"},{name:"prev_price",type:"int"},{name:"ea",type:"float"},{name:"cost",type:"int"},{name:"price",type:"int"},{name:"total_ea",type:"float"},{name:"total_price",type:"int"},"etc"]
						}),
						remoteSort:false,
						groupField:"group",
						sortInfo:{field:"title",direction:"ASC"},
						baseParams:{"action":"monthly","get":"item","mode":"detail","date":Ext.getCmp("month").getValue(),"cooperation":cooperation,"cno":cno,"repto":repto,"wno":wno}
					}),
					plugins:[new Ext.ux.grid.ColumnHeaderGroup({
						rows:[[
							{},
							{},
							{},
							{header:"품목",colspan:5,align:"center"},
							{header:"계약금액",colspan:3,align:"center"},
							{header:"전회누계",colspan:2,align:"center"},
							{header:"금회기성",colspan:3,align:"center"},
							{header:"누계",colspan:2,align:"center"},
							{}
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
					})
				})
			],
			listeners:{show:{fn:function() {
				Ext.getCmp("ItemList").getStore().load();
			}}}
		}).show();
	}

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"기성관리 및 현황",
		layout:"fit",
		tbar:[
			new Ext.Button({
				icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_control_left.png",
				text:"이전달",
				handler:function() {
					if (Ext.getCmp("month").selectedIndex == 0) {
						Ext.Msg.show({title:"에러",msg:"이전달 기록이 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
					} else {
						Ext.getCmp("ListTab1").getStore().baseParams.date = Ext.getCmp("ListTab2").getStore().baseParams.date = Ext.getCmp("month").getStore().getAt(Ext.getCmp("month").selectedIndex-1).get("date");
						Ext.getCmp("month").setValue(Ext.getCmp("ListTab1").getStore().baseParams.date);
						Ext.getCmp("month").selectedIndex = Ext.getCmp("month").selectedIndex - 1;
						Ext.getCmp("ListTab1").getStore().reload();
						Ext.getCmp("ListTab2").getStore().reload();
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
							Ext.getCmp("ListTab1").getStore().load({params:{start:0,limit:30}});

							Ext.getCmp("ListTab2").getStore().baseParams.date = Ext.getCmp("month").getValue();
							Ext.getCmp("ListTab2").getStore().load({params:{start:0,limit:30}});
						});
					}},
					select:{fn:function(form) {
						Ext.getCmp("ListTab1").getStore().baseParams.date = form.getValue();
						Ext.getCmp("ListTab1").getStore().reload();

						Ext.getCmp("ListTab2").getStore().baseParams.date = form.getValue();
						Ext.getCmp("ListTab2").getStore().reload();
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
						Ext.getCmp("ListTab1").getStore().baseParams.date = Ext.getCmp("ListTab2").getStore().baseParams.date = Ext.getCmp("month").getStore().getAt(Ext.getCmp("month").selectedIndex+1).get("date");
						Ext.getCmp("month").setValue(Ext.getCmp("ListTab1").getStore().baseParams.date);
						Ext.getCmp("month").selectedIndex = Ext.getCmp("month").selectedIndex + 1;
						Ext.getCmp("ListTab1").getStore().reload();
						Ext.getCmp("ListTab2").getStore().reload();
					}
				}
			})
		],
		items:[
			new Ext.TabPanel({
				id:"ListTab",
				tabPosition:"bottom",
				activeTab:0,
				border:false,
				items:[
					new Ext.grid.GridPanel({
						id:"ListTab1",
						title:"공사현장",
						border:false,
						autoScroll:true,
						cm:MonthlyCm,
						store:MonthlyStore1,
						plugins:new Ext.grid.GroupSummary(),
						listeners:{
							rowdblclick:{fn:function(grid,idx,col,e) {
								MonthlyDetailFunction(grid.getStore().getAt(idx).get("idx"));
							}}
						}
					}),
					new Ext.grid.GridPanel({
						id:"ListTab2",
						title:"완료현장",
						border:false,
						autoScroll:true,
						cm:MonthlyCm,
						store:MonthlyStore1,
						plugins:new Ext.grid.GroupSummary(),
						listeners:{
							rowdblclick:{fn:function(grid,idx,col,e) {
								MonthlyDetailFunction(grid.getStore().getAt(idx).get("idx"));
							}}
						}
					})
				],
				listeners:{tabchange:{fn:function(tabs,tab) {
					Ext.getCmp(tab.getId()).getStore().load({params:{start:0,limit:30}});
				}}}
			})
		]
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>