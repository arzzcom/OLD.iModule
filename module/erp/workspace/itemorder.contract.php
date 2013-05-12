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
		title:"자재입고관리",
		layout:"fit",
		items:[
			new Ext.grid.GridPanel({
				id:"ListPanel",
				border:false,
				tbar:[

				],
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
						header:"계약업체명",
						dataIndex:"cooperation",
						width:150,
						summaryType:"count",
						summaryRenderer:GridSummaryCount
					},{
						header:"계약서명",
						dataIndex:"title",
						sortable:false,
						width:250,
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
						width:60,
						summaryType:"sum",
						renderer:GridNumberFormat
					},{
						header:"도급금액",
						dataIndex:"original_price",
						sortable:false,
						width:90,
						summaryType:"sum",
						renderer:GridNumberFormat
					},{
						header:"계약금액",
						dataIndex:"price",
						sortable:false,
						width:90,
						summaryType:"sum",
						renderer:GridNumberFormat
					},{
						header:"입고금액",
						dataIndex:"income_price",
						sortable:false,
						width:90,
						summaryType:"sum",
						renderer:GridNumberFormat
					},{
						header:"입고율",
						width:80,
						sortable:true,
						summaryType:"sum",
						renderer:function(value,p,record) {
							var percent = record.data.income_price/record.data.price*100;
							var sHTML = '<div style="font-family:tahoma; font-size:10px;">';
							sHTML+= '<span style="font-weight:bold; letter-spacing:-3px;">';
							for (var i=10;i<=100;i=i+10) {
								if (i < percent) sHTML+= '<span style="color:#EF5600;">|</span>';
								else sHTML+= '<span style="color:#CCCCCC;">|</span>';
							}
							sHTML+= '</span>';
			
							sHTML+= " "+percent.toFixed(2)+"%";
			
							return sHTML;
						},
						summaryRenderer:function(value,p,record) {
							var percent = record.data.income_price/record.data.price*100;
							var sHTML = '<div style="font-family:tahoma; font-size:10px;">';
							sHTML+= '<span style="font-weight:bold; letter-spacing:-3px;">';
							for (var i=10;i<=100;i=i+10) {
								if (i < percent) sHTML+= '<span style="color:#EF5600;">|</span>';
								else sHTML+= '<span style="color:#CCCCCC;">|</span>';
							}
							sHTML+= '</span>';
			
							sHTML+= " "+percent.toFixed(2)+"%";
			
							return sHTML;
						}
					},{
						header:"계약일",
						dataIndex:"date",
						sortable:true,
						width:110
					}
				]),
				plugins:new Ext.ux.grid.GroupSummary(),
				store:new Ext.data.GroupingStore({
					proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php"}),
					reader:new Ext.data.JsonReader({
						root:"lists",
						totalProperty:"totalCount",
						fields:[{name:"idx",type:"int"},"group","cooperation","title",{name:"item",type:"int"},{name:"price",type:"int"},{name:"income_price",type:"int"},{name:"original_price",type:"int"},{name:"income_price",type:"int"},"file","date"]
					}),
					remoteSort:false,
					sortInfo:{field:"date",direction:"DESC"},
					groupField:"group",
					baseParams:{"wno":"<?php echo $this->wno; ?>","action":"itemorder","get":"contract","mode":"list"}
				}),
				loadMask:{msg:"데이터를 로딩중입니다."},
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
							id:"ContractViewWindow",
							title:"["+data.get("cooperation")+"] "+data.get("title"),
							width:980,
							height:550,
							modal:true,
							layout:"fit",
							items:[
								new Ext.grid.GridPanel({
									border:false,
									id:"ContractViewItemList",
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
											width:80,
											summaryType:"count",
											summaryRenderer:GridSummaryCount
										},{
											header:"공종명",
											dataIndex:"worktype",
											width:100,
											sortable:false
										},{
											header:"품명",
											dataIndex:"title",
											width:220,
											renderer:GridContractItemNotFound
										},{
											header:"규격",
											dataIndex:"size",
											width:80,
											renderer:GridContractItemNotFound
										},{
											header:"단위",
											dataIndex:"unit",
											width:40,
											renderer:GridContractItemNotFound
										},{
											header:"발주금액",
											dataIndex:"price",
											width:80,
											sortable:true,
											summaryType:"sum",
											renderer:GridNumberFormat
										},{
											header:"입고금액",
											dataIndex:"price",
											width:80,
											sortable:true,
											summaryType:"sum",
											renderer:GridNumberFormat
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
											header:"반출수량",
											dataIndex:"outcome_ea",
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
												//record.data.remain_ea = record.data.order_ea - record.data.income_ea;
												//return GridNumberFormat(record.data.remain_ea);
											}
										}
									]),
									plugins:new Ext.ux.grid.GroupSummary(),
									store:new Ext.data.GroupingStore({
										proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php"}),
										reader:new Ext.data.JsonReader({
											root:"lists",
											totalProperty:"totalCount",
											fields:[{name:"idx",type:"int"},"group","itemcode","code","workgroup","worktype","title","size","unit",{name:"order_ea",type:"float"},{name:"income_ea",type:"float"},{name:"outcome_ea",type:"float"},{name:"remain_ea",type:"float"},{name:"price",type:"float"}]
										}),
										remoteSort:false,
										sortInfo:{field:"idx",direction:"ASC"},
										groupField:"group",
										baseParams:{"wno":"<?php echo $this->wno; ?>","action":"itemorder","get":"contract","mode":"item","idx":data.get("idx")}
									}),
									loadMask:{msg:"데이터를 로딩중입니다."},
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
								Ext.getCmp("ContractViewItemList").getStore().load();
							}}}
						}).show();
					}}
				}
			})
		]
	});
	
	Ext.getCmp("ListPanel").getStore().load();
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>